/* @(#) $Header$ */
/* This source code is in the public domain. */
/*
 * Willow: Lightweight HTTP reverse-proxy.
 * whttp: HTTP implementation.
 */

#ifdef __SUNPRO_C
# pragma ident "@(#)$Header$"
#endif

/*
 * The logic of whttp is explained in whttp_entity.c
 */
 
#include <sys/types.h>
#include <sys/stat.h>
#include <sys/param.h>

#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <unistd.h>
#include <errno.h>
#include <netdb.h>
#include <fcntl.h>
#include <strings.h>

#include "willow.h"
#include "whttp.h"
#include "wnet.h"
#include "wbackend.h"
#include "wconfig.h"
#include "wlogwriter.h"
#include "whttp_entity.h"
#include "wlog.h"
#include "wcache.h"

#ifndef MAXHOSTNAMELEN
# define MAXHOSTNAMELEN HOST_NAME_MAX /* SysV / BSD disagreement */
#endif

/*
 * Error handling.
 */
#define ERR_GENERAL	0
#define ERR_BADREQUEST	1
#define ERR_BADRESPONSE	2
#define ERR_CACHE_IO	3

static const char *error_files[] = {
	/* ERR_GENERAL		*/	DATADIR "/errors/ERR_GENERAL",
	/* ERR_BADREQUEST	*/	DATADIR "/errors/ERR_BADREQUEST",
	/* ERR_BADRESPONSE	*/	DATADIR "/errors/ERR_BADRESPONSE",
	/* ERR_CACHE_IO		*/	DATADIR "/errors/ERR_CACHE_IO",
};

#define MAX_HEADERS	64	/* maximum # of headers to allow	*/

const char *request_string[] = {
	"GET",
	"POST",
	"HEAD",
	"TRACE",
	"OPTIONS",
};

struct request_type supported_reqtypes[] = {
	{ "GET",	3,	REQTYPE_GET	},
	{ "POST",	4,	REQTYPE_POST	},
	{ "HEAD",	4,	REQTYPE_HEAD	},
	{ "TRACE",	5,	REQTYPE_TRACE	},
	{ "OPTIONS",	7,	REQTYPE_OPTIONS	},
	{ NULL,		0,	REQTYPE_INVALID }
};

struct http_client {
struct	fde		*cl_fde;	/* backref to fd			*/
	int		 cl_reqtype;	/* request type or 0			*/
	char		*cl_path;	/* path they want			*/
	char		*cl_wrtbuf;	/* write buf (either to client or be)	*/
struct	backend		*cl_backend;	/* backend servicing this client	*/
struct	fde		*cl_backendfde;	/* fde for backend			*/
struct	http_entity	 cl_entity;	/* reply to send back			*/

	/* Cache-related data */
	int		 cl_cfd;	/* FD of cache file for writing, or 0	*/
struct	cache_key	 cl_key;	/* Cache key				*/
struct	cache_object	*cl_co;		/* Cache object				*/
	struct {
		int	f_cached:1;
	}		cl_flags;
};

static void client_close(struct http_client *);
static void proxy_start_backend(struct backend *, struct fde *, void *);
static void client_read_done(struct http_entity *, void *, int);
static void client_response_done(struct http_entity *, void *, int);
static void backend_headers_done(struct http_entity *, void *, int);
static void client_headers_done(struct http_entity *, void *, int);
static void client_write_cached(struct http_client *);

static void client_send_error(struct http_client *, int, const char *);
static void client_log_request(struct http_client *);

static void do_cache_write(const char *, size_t, void *);

static char via_hdr[1024];
static char *cache_hit_hdr;
static char *cache_miss_hdr;

static char my_hostname[MAXHOSTNAMELEN + 1];
static char my_version[64];
static int logwr_pipe[2];
static FILE *alf;

/*
 * Initialize whttp, start loggers.
 */
void
whttp_init(void)
{
	size_t	hsize;
	
	if (gethostname(my_hostname, MAXHOSTNAMELEN) < 0) {
		perror("gethostname");
		exit(8);
	}

	(void)strlcpy(my_version, "Willow/" VERSION, 64);
	safe_snprintf(via_hdr, 1023, "1.0 %s (%s)", my_hostname, my_version);

	hsize = sizeof("MISS from ") + strlen(my_hostname);
	cache_hit_hdr = wmalloc(hsize + 1);
	cache_miss_hdr = wmalloc(hsize + 1);
	
	if (cache_hit_hdr == NULL || cache_miss_hdr == NULL)
		outofmemory();
	
	safe_snprintf(cache_hit_hdr, hsize, "HIT from %s", my_hostname);
	safe_snprintf(cache_miss_hdr, hsize, "MISS from %s", my_hostname);
	
	/*
	 * Fork the logwriter.
	 */
	if (pipe(logwr_pipe) < 0) {
		perror("pipe");
		exit(8);
	}
	wlogwriter_start(logwr_pipe);
	if ((alf = fdopen(logwr_pipe[0], "w")) == NULL) {
		perror("fdopen");
		exit(8);
	}
}

void
whttp_shutdown(void)
{
	wfree(cache_hit_hdr);
	wfree(cache_miss_hdr);
}

/*
 * Create a new client associated with the FDE 'e'.
 */
static struct http_client *
new_client(e)
	struct fde *e;
{
struct	http_client	*cl;

	if ((cl = wmalloc(sizeof(*cl))) == NULL)
		outofmemory();

	bzero(cl, sizeof(*cl));
	cl->cl_fde = e;
	return cl;
}

/*
 * Called by wnet_accept to regiister a new client.  Reads the request headers
 * from the client.
 */
void
http_new(e)
	struct fde *e;
{
struct	http_client	*cl;

	cl = new_client(e);
	cl->cl_entity.he_source_type = ENT_SOURCE_FDE;
	cl->cl_entity.he_source.fde = e;
	
	WDEBUG((WLOG_DEBUG, "http_new: starting header read for %d", cl->cl_fde->fde_fd));
	entity_read_headers(&cl->cl_entity, client_read_done, cl);
}

/*
 * Called when the initial request has been read.  Checks if the object is
 * cached, and starts a backend request if not.  If it it, sends the cached
 * object to the client.
 */
/*ARGSUSED*/
static void
client_read_done(entity, data, res)
	struct http_entity *entity;
	void *data;
	int res;
{
struct	http_client	*client = data;
struct	cache_key	 ckey;
struct	cache_object	*cobj;

	WDEBUG((WLOG_DEBUG, "client_read_done: called"));

	if (res == -1) {
		client_close(client);
		return;
	}
	
	if (client->cl_entity.he_rdata.request.host == NULL)
		client->cl_path = wstrdup(client->cl_entity.he_rdata.request.path);
	else {
		size_t	len;
		
		len = strlen(client->cl_entity.he_rdata.request.host) +
			strlen(client->cl_entity.he_rdata.request.path)	+ 7;
		
		client->cl_path = wmalloc(len + 1);
		if (client->cl_path == NULL)
			outofmemory();
		safe_snprintf(client->cl_path, len + 1, "http://%s%s", client->cl_entity.he_rdata.request.host,
				client->cl_entity.he_rdata.request.path);
	}
	
	client->cl_reqtype = client->cl_entity.he_rdata.request.reqtype;

	/*
	 * Check for cached object.
	 */
	if (client->cl_reqtype == REQTYPE_GET) {
		ckey.ck_len = strlen(client->cl_path);
		ckey.ck_key = client->cl_path;
		cobj = wcache_find_object(&ckey);
		if (cobj != NULL) {
			client->cl_co = cobj;
			WDEBUG((WLOG_DEBUG, "client_read_done: object %s cached", client->cl_path));
			client_write_cached(client);
			return;
		}
	}
	
	/*
	 * Not cached.  Find a backend.
	 */
	if (get_backend(proxy_start_backend, client) == -1) {
		client_send_error(client, ERR_GENERAL, strerror(errno));
		return;
	}
}

/*
 * Called when backend is ready.  backend==NULL if none was found.
 */
static void
proxy_start_backend(backend, e, data)
	struct backend *backend;
	struct fde *e;
	void *data;
{
struct	http_client	*client = data;
struct	header_list	 *it;
	
	WDEBUG((WLOG_DEBUG, "proxy_start_backend: called"));
	
	if (backend == NULL) {
		client_send_error(client, ERR_GENERAL, strerror(errno));
		return;
	}
	
	client->cl_backend = backend;
	client->cl_backendfde = e;

	for (it = client->cl_entity.he_headers.hl_next; it; it = it->hl_next) {
		if (!strcmp(it->hl_name, "Connection")) {
			header_remove(&client->cl_entity.he_headers, it);
			it = client->cl_entity.he_headers.hl_next;
			continue;
		}
	}

	header_add(&client->cl_entity.he_headers, "X-Forwarded-For", client->cl_fde->fde_straddr);
	client->cl_entity.he_source_type = ENT_SOURCE_NONE;
	entity_send(e, &client->cl_entity, backend_headers_done, client);
}

/*
 * Called when clients headers were written to the backend.
 */
/*ARGSUSED*/
static void
backend_headers_done(entity, data, res)
	struct http_entity *entity;
	void *data;
	int res;
{
struct	http_client	*client = data;
	
	WDEBUG((WLOG_DEBUG, "backend_headers_done: called"));
	if (res == -1) {
		client_send_error(client, ERR_GENERAL, strerror(errno));
		return;
	}
	
	entity_free(&client->cl_entity);
	bzero(&client->cl_entity, sizeof(client->cl_entity));
	client->cl_entity.he_source_type = ENT_SOURCE_FDE;
	client->cl_entity.he_source.fde = client->cl_backendfde;
	client->cl_entity.he_flags.response = 1;

	/*
	 * This should probably be handled somewhere inside
	 * whttp_entity.c ...
	 */
	entity_read_headers(&client->cl_entity, client_headers_done, client);
}

/*
 * Called when backend's headers are finished reading.
 */
static void
client_headers_done(entity, data, res)
	struct http_entity *entity;
	void *data;
	int res;
{
struct	http_client	*client = data;
	char		*cache_path;
	size_t		 plen;
	
	WDEBUG((WLOG_DEBUG, "client_headers_done: called"));
	
	if (res == -1) {
		client_close(client);
		return;
	}
	
	/*
	 * If cachable, open the cache file and write data.
	 *
	 * Don't cache responses to non-GET requests, or non-200 replies.
	 */
	if (client->cl_reqtype == REQTYPE_GET && entity->he_rdata.response.status == 200
	    && config.ncaches) {
		client->cl_key.ck_len = strlen(client->cl_path);
		client->cl_key.ck_key = client->cl_path;
		client->cl_co = wcache_new_object();
		plen = strlen(config.caches[0].dir) + client->cl_co->co_plen + 12 + 2;
		cache_path = wmalloc(plen + 1);
		bzero(cache_path, plen + 1);
		safe_snprintf(cache_path, plen, "%s/__objects__/%s", config.caches[0].dir, client->cl_co->co_path);
		WDEBUG((WLOG_DEBUG, "caching %s at %s", client->cl_path, cache_path));
		if ((client->cl_cfd = open(cache_path, O_WRONLY | O_CREAT | O_EXCL, 0600)) == -1) {
			wlog(WLOG_WARNING, "opening cache file %s: %s", cache_path, strerror(errno));
			client->cl_cfd = 0;
		} else {
			entity->he_cache_callback = do_cache_write;
			entity->he_cache_callback_data = client;
			header_dump(&client->cl_entity.he_headers, client->cl_cfd);
		}
		wfree(cache_path);
	}
	
	header_add(&client->cl_entity.he_headers, "Via", via_hdr);
	header_add(&client->cl_entity.he_headers, "X-Cache", cache_miss_hdr);
	entity_send(client->cl_fde, &client->cl_entity, client_response_done, client);
}

/*
 * Write a cached object to the client.
 */
static void
client_write_cached(client)
	struct http_client *client;
{
	size_t	 plen;
	char	*cache_path;
struct	stat	 sb;
	
	plen = strlen(config.caches[0].dir) + client->cl_co->co_plen + 12 + 2;
	cache_path = wmalloc(plen + 1);
	bzero(cache_path, plen + 1);
	safe_snprintf(cache_path, plen, "%s/__objects__/%s", config.caches[0].dir, client->cl_co->co_path);
	WDEBUG((WLOG_DEBUG, "serving %s from cache at %s [path %s]", client->cl_path, cache_path, client->cl_co->co_path));

	if ((client->cl_cfd = open(cache_path, O_RDONLY)) == -1) {
		wlog(WLOG_WARNING, "opening cache file %s: %s", cache_path, strerror(errno));
		client_send_error(client, ERR_CACHE_IO, strerror(errno));
		wfree(cache_path);
		return;
	}

	if (fstat(client->cl_cfd, &sb) < 0) {
		wlog(WLOG_WARNING, "stat(%s): %s", cache_path, strerror(errno));
		client_send_error(client, ERR_CACHE_IO, strerror(errno));
		wfree(cache_path);
		return;
	}
		
	wfree(cache_path);
	
	entity_free(&client->cl_entity);
	bzero(&client->cl_entity, sizeof(client->cl_entity));
	header_undump(&client->cl_entity.he_headers, client->cl_cfd, &client->cl_entity.he_source.fd.off);
	header_add(&client->cl_entity.he_headers, "Via", via_hdr);
	header_add(&client->cl_entity.he_headers, "X-Cache", cache_hit_hdr);
	
	client->cl_entity.he_flags.response = 1;
	client->cl_entity.he_rdata.response.status = 200;
	client->cl_entity.he_rdata.response.status_str = "OK";
			
	client->cl_entity.he_source.fd.fd = client->cl_cfd;
	client->cl_entity.he_source.fd.size = sb.st_size;
	
	client->cl_entity.he_source_type = ENT_SOURCE_FILE;

	client->cl_flags.f_cached = 1;
	entity_send(client->cl_fde, &client->cl_entity, client_response_done, client);
}

/*
 * Called when response was finished writing to the client.
 */
/*ARGSUSED*/
static void
client_response_done(entity, data, res)
	struct http_entity *entity;
	void *data;
	int res;
{
struct	http_client	*client = data;

	WDEBUG((WLOG_DEBUG, "client_response_done: called"));

	if (client->cl_cfd) {
		if (close(client->cl_cfd) < 0) {
			wlog(WLOG_WARNING, "writing cache file: %s\n", strerror(errno));
		}
	}
	
	if (client->cl_co) {
		if (res != -1) {
			if (!client->cl_flags.f_cached) {
				if (wcache_store_object(&client->cl_key, client->cl_co) == -1) {
					/* normally, this means someone else cached it before us */
					wlog(WLOG_WARNING, "object cache store failed");
				}
			}
		} else {
			wlog(WLOG_WARNING, "writing cached file: %s", strerror(errno));
			/* XXX should unlink cached file */
		}
		wcache_free_object(client->cl_co);
	}
	
	client_log_request(client);
	client_close(client);
}
	
static void
client_close(client)
	struct http_client *client;
{
	WDEBUG((WLOG_DEBUG, "close client %d", client->cl_fde->fde_fd));
	if (client->cl_wrtbuf)
		wfree(client->cl_wrtbuf);
	if (client->cl_path)
		wfree(client->cl_path);
	wnet_close(client->cl_fde->fde_fd);
	if (client->cl_backendfde)
		wnet_close(client->cl_backendfde->fde_fd);
	entity_free(&client->cl_entity);
	wfree(client);
}

static void
client_send_error(client, errnum, errdata)
	struct http_client *client;
	int errnum;
	const char *errdata;
{
	FILE		*errfile;
	char		 errbuf[8192];
	char		*p = errbuf, *u;
	ssize_t		 size;
	
	if ((errfile = fopen(error_files[errnum], "r")) == NULL) {
		client_close(client);
		return;
	}

	if ((size = fread(errbuf, 1, sizeof(errbuf) - 1, errfile)) < 0) {
		(void)fclose(errfile);
		client_close(client);
		return;
	}

	(void)fclose(errfile);
	errbuf[size] = '\0';

	if (!errdata)
		errdata = "Unknown error";
	if (!client->cl_path)
		client->cl_path = wstrdup("NONE");

	u = NULL;

	while (*p) {
		switch(*p) {
		case '%':
			switch (*++p) {
			case 'U':
				realloc_strcat(&u, client->cl_path);
				break;
			case 'D':
				realloc_strcat(&u, current_time_str);
				break;
			case 'H':
				realloc_strcat(&u, my_hostname);
				break;
			case 'E':
				realloc_strcat(&u, errdata);
				break;
			case 'V':
				realloc_strcat(&u, my_version);
				break;
			default:
				break;
			}
			p++;
			continue;
		default:
			realloc_addchar(&u, *p);
			break;
		}
		++p;
	}

	client->cl_wrtbuf = u;
	
	bzero(&client->cl_entity.he_headers, sizeof(client->cl_entity.he_headers));
	header_add(&client->cl_entity.he_headers, "Date", current_time_str);
	header_add(&client->cl_entity.he_headers, "Expires", current_time_str);
	header_add(&client->cl_entity.he_headers, "Server", my_version);
	header_add(&client->cl_entity.he_headers, "Content-Type", "text/html");
	header_add(&client->cl_entity.he_headers, "Connection", "close");

	client->cl_entity.he_flags.response = 1;
	client->cl_entity.he_rdata.response.status = 503;
	client->cl_entity.he_rdata.response.status_str = "Service unavailable";
	client->cl_entity.he_source_type = ENT_SOURCE_BUFFER;
	client->cl_entity.he_source.buffer.addr = client->cl_wrtbuf;
	client->cl_entity.he_source.buffer.len = strlen(client->cl_wrtbuf);

	client->cl_entity.he_flags.cachable = 0;
	entity_send(client->cl_fde, &client->cl_entity, client_response_done, client);
}

static void
client_log_request(client)
	struct http_client *client;
{
#ifdef THREADED_IO
static	pthread_mutex_t	mtx;
#endif

	int	i;
	
	if (!config.access_log)
		return;

#ifdef THREADED_IO
	pthread_mutex_lock(&mtx);
#endif
	i = fprintf(alf, "[%s] %s %s \"%s\" %d %s %s\n",
			current_time_short, client->cl_fde->fde_straddr,
			request_string[client->cl_reqtype],
			client->cl_path, client->cl_entity.he_rdata.response.status,
			client->cl_backend ? client->cl_backend->be_name : "-",
			client->cl_flags.f_cached ? "HIT" : "MISS");
	if (i < 0) {
		wlog(WLOG_ERROR, "writing logfile: %s", strerror(errno));
		exit(8);
	}
	
	if (fflush(alf) == EOF) {
		wlog(WLOG_ERROR, "flushing logfile: %s", strerror(errno));
		exit(8);
	}
#ifdef THREADED_IO
	pthread_mutex_unlock(&mtx);
#endif
}

static void
do_cache_write(buf, len, data)
	const char *buf;
	size_t len;
	void *data;
{
struct	http_client	*client = data;

	if (write(client->cl_cfd, buf, len) < 0) {
		/*EMPTY*/
		WDEBUG((WLOG_WARNING, "writing cached data: %s", strerror(errno)));
	}
}
		
