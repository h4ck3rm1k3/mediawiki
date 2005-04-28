/* @(#) $Header$ */
/* This source code is in the public domain. */
/*
 * Willow: Lightweight HTTP reverse-proxy.
 */

#ifdef __SUNPRO_C
# pragma ident "@(#)$Header$"
#endif

#include <sys/mman.h>

#include <stdio.h>
#include <stdlib.h>
#include <signal.h>
#include <stdarg.h>
#include <string.h>
#include <unistd.h>
#include <errno.h>

#include "wlog.h"
#include "wnet.h"
#include "wconfig.h"
#include "willow.h"
#include "whttp.h"
#include "wcache.h"

#ifdef WDEBUG_ALLOC
static void ae_checkleaks(void);
static void segv_action(int, siginfo_t *, void *);
#endif

static const char *progname;

#define min(x,y) ((x) < (y) ? (x) : (y))

/*ARGSUSED*/
static void 
sig_exit(s)
	int s;
{
	wnet_exit = 1;
}

static void
usage(void)
{
	(void)fprintf(stderr, "usage: %s [-fzv]\n"
			"\t-f\trun in foreground (don't detach)\n"
			"\t-z\tcreate cache directory structure and exit\n"
			"\t-v\tprint version number and exit\n"
			, progname);
}

#ifdef __lint
# pragma error_messages(off, E_H_C_CHECK2)
#endif

int 
main(argc, argv)
	char *argv[];
	int argc;
{
	int	i;
	int	zflag = 0;
	
#ifdef WDEBUG_ALLOC
struct	sigaction	segv_act;
	bzero(&segv_act, sizeof(segv_act));
	segv_act.sa_sigaction = segv_action;
	segv_act.sa_flags = SA_SIGINFO;
	
	sigaction(SIGSEGV, &segv_act, NULL);
#endif
	
	progname = argv[0];
	
	while ((i = getopt(argc, argv, "fzv")) != -1) {
		switch (i) {
			case 'z':
				zflag++;
			/*FALLTHRU*/
			case 'f':
				config.foreground = 1;
				break;
			case 'v':
				(void)fprintf(stderr, "%s\n", VERSION);
				exit(0);
			default:
				usage();
				exit(8);
		}
	}

	argv += optind;
	argc -= optind;

	if (argc) {
		(void)fprintf(stderr, "%s: too many argments\n", progname);
		usage();
		exit(8);
	}
	
	wnet_set_time();

	wconfig_init(NULL);
	wlog_init();
	if (zflag) {
		wcache_setupfs();
		exit(0);
	}
	wcache_init(1);
		
	/*
	 * HTTP should be initialised before the network so that
	 * the wlogwriter exits cleanly.
	 */
	whttp_init();
	wnet_init();

	(void)signal(SIGINT, sig_exit);
	(void)signal(SIGTERM, sig_exit);
	
	wlog(WLOG_NOTICE, "running");

#ifdef WDEBUG_ALLOC
	(void)fprintf(stderr, "debug allocator enabled, assuming -f\n");
	config.foreground = 1;
#endif
	
	if (!config.foreground)
		daemon(0, 0);

	wnet_run();
	wlog_close();
	wcache_shutdown();
	whttp_shutdown();
	
#ifdef WDEBUG_ALLOC
	ae_checkleaks();
#endif
	return EXIT_SUCCESS;
}

#ifdef __lint
# pragma error_messages(on, E_H_C_CHECK2)
#endif

void
outofmemory(void)
{
	static int count;
	
	if (count++)
		abort();
	
	wlog(WLOG_ERROR, "fatal: out of memory. exiting.");
	exit(8);
}

void
realloc_addchar(sp, c)
	char **sp;
	int c;
{
	char	*p;
	
	if ((*sp = wrealloc(*sp, strlen(*sp) + 2)) == NULL)
		outofmemory();
	p = *sp + strlen(*sp);
	*p++ = (char) c;
	*p++ = '\0';
}

void
realloc_strcat(sp, s)
	char **sp;
	const char *s;
{
	if ((*sp = wrealloc(*sp, strlen(*sp) + strlen(s) + 1)) == NULL)
		outofmemory();
	(void)strcat(*sp, s);
}
			
#ifdef WDEBUG_ALLOC
# ifdef THREADED_IO
pthread_mutex_t ae_mtx = PTHREAD_MUTEX_INITIALIZER;
#  define ALLOC_LOCK pthread_mutex_lock(&ae_mtx)
#  define ALLOC_UNLOCK pthread_mutex_unlock(&ae_mtx)
# else
#  define ALLOC_LOCK ((void)0)
#  define ALLOC_UNLOCK ((void)0)
# endif

struct alloc_entry {
	char		*ae_addr;
	char		*ae_mapping;
	size_t		 ae_mapsize;
	size_t		 ae_size;
	int		 ae_freed;
	const char	*ae_freed_file;
	int		 ae_freed_line;
	const char	*ae_alloced_file;
	int		 ae_alloced_line;
struct	alloc_entry	*ae_next;
};

static struct alloc_entry allocs;
static int pgsize;

static void
segv_action(sig, si, data)
	int sig;
	siginfo_t *si;
	void *data;
{
struct	alloc_entry	*ae;

	/*
	 * This is mostly non-standard, unportable and unreliable, but if the debug allocator
	 * is enabled, it's more important to produce useful errors than conform to the letter
	 * of the law.
	 */
	(void)fprintf(stderr, "SEGV at %p%s (pid %d)\n", si->si_addr, si->si_code == SI_NOINFO ? " [SI_NOINFO]" : "",
			(int) getpid());
	for (ae = allocs.ae_next; ae; ae = ae->ae_next)
		if (!ae->ae_freed && (char *)si->si_addr > ae->ae_mapping && 
				(char *)si->si_addr < ae->ae_mapping + ae->ae_mapsize) {
			(void)fprintf(stderr, "\t%p [map @ %p size %d] from %s:%d\n", ae->ae_addr, ae->ae_mapping,
					ae->ae_mapsize, ae->ae_alloced_file, ae->ae_alloced_line);
			break;
		}
	if (ae == NULL)
		(void)fprintf(stderr, "\tunknown address\n");
	abort();
	_exit(1);
}		
	
static void
ae_checkleaks(void)
{
struct	alloc_entry	*ae;

	ALLOC_LOCK();
	for (ae = allocs.ae_next; ae; ae = ae->ae_next)
		if (!ae->ae_freed)
			(void)fprintf(stderr, "%p @ %s:%d\n", ae->ae_addr, ae->ae_alloced_file, ae->ae_alloced_line);
	ALLOC_UNLOCK();
}

void *
internal_wmalloc(size, file, line)
	size_t size;
	const char *file;
	int line;
{
	void		*p;
struct	alloc_entry	*ae;
	size_t		 mapsize;
	
	ALLOC_LOCK();
	
	if (pgsize == 0)
		pgsize = sysconf(_SC_PAGESIZE);
	
	mapsize = (size/pgsize + 2) * pgsize;
	if ((p = mmap(NULL, mapsize, PROT_READ|PROT_WRITE, MAP_PRIVATE | MAP_ANON, -1, 0)) == (void *)-1) {
		(void)fprintf(stderr, "mmap: %s\n", strerror(errno));
		ALLOC_UNLOCK();
		return NULL;
	}

	for (ae = &allocs; ae->ae_next; ae = ae->ae_next)
		if (ae->ae_next->ae_mapping == p)
			break;

	if (!ae->ae_next) {
		if ((ae->ae_next = malloc(sizeof(struct alloc_entry))) == NULL) {
			(void)fputs("out of memory\n", stderr);
			abort();
		}
		bzero(ae->ae_next, sizeof(struct alloc_entry));
	}

	ae = ae->ae_next;
	ae->ae_addr = ((char *)p + (mapsize - pgsize)) - size;
	ae->ae_mapping = p;
	ae->ae_mapsize = mapsize;
	ae->ae_size = size;
	ae->ae_freed = 0;
	ae->ae_alloced_file = file;
	ae->ae_alloced_line = line;
	(void)fprintf(stderr, "alloc %d @ %p [map @ %p:%p, size %d] at %s:%d\n", size, ae->ae_addr,
			ae->ae_mapping, ae->ae_mapping + ae->ae_mapsize, ae->ae_mapsize, file, line);
	if (mprotect(ae->ae_addr + size, pgsize, PROT_NONE) < 0) {
		(void)fprintf(stderr, "mprotect(0x%p, %d, PROT_NONE): %s\n", ae->ae_addr + size, pgsize, strerror(errno));
		exit(8);
	}

	ALLOC_UNLOCK();
	return ae->ae_addr;
}

void
internal_wfree(p, file, line)
	void *p;
	const char *file;
	int line;
{
struct	alloc_entry	*ae;

	ALLOC_LOCK();
	
	(void)fprintf(stderr, "free %p @ %s:%d\n", p, file, line);
	
	for (ae = allocs.ae_next; ae; ae = ae->ae_next) {
		if (ae->ae_addr == p) {
			if (ae->ae_freed) {
				(void)fprintf(stderr, "wfree: ptr %p already freed @ %s:%d! [alloced at %s:%d]\n", 
						p, ae->ae_freed_file, ae->ae_freed_line,
						ae->ae_alloced_file, ae->ae_alloced_line);
				ae_checkleaks();
				abort();
			}
			ae->ae_freed = 1;
			ae->ae_freed_file = file;
			ae->ae_freed_line = line;
			if (mprotect(ae->ae_addr + ae->ae_size, pgsize, PROT_READ | PROT_WRITE) < 0) {
				(void)fprintf(stderr, "mprotect(0x%p, %d, PROT_READ | PROT_WRITE): %s\n", 
						ae->ae_addr + ae->ae_size, pgsize, strerror(errno));
				exit(8);
			}
			munmap(ae->ae_mapping, ae->ae_mapsize);
			ALLOC_UNLOCK();
			return;
		}
	}

	(void)fprintf(stderr, "wfree: ptr %p never malloced!\n", p);
	ae_checkleaks();
	abort();
}

char *
internal_wstrdup(s, file, line)
	const char *s, *file;
	int line;
{
	char *ret = internal_wmalloc(strlen(s) + 1, file, line);
	(void)strcpy(ret, s);
	return ret;
}

void *
internal_wrealloc(p, size, file, line)
	void *p;
	const char *file;
	int line;
	size_t size;
{
	void 		*new;
struct	alloc_entry	*ae;
	size_t		 osize = 0;
		
	if (!p)
		return internal_wmalloc(size, file, line);
	
	ALLOC_LOCK();
	
	for (ae = allocs.ae_next; ae; ae = ae->ae_next)
		if (ae->ae_addr == p) {
			osize = ae->ae_size;
			break;
		}
		
	if (osize == 0) {
		(void)fprintf(stderr, "wrealloc: ptr %p never malloced!\n", p);
		ae_checkleaks();
		abort();
	}

	ALLOC_UNLOCK();
		
	new = internal_wmalloc(size, file, line);
	bcopy(p, new, min(osize, size));
	internal_wfree(p, file, line);
	
	return new;
}
#endif
