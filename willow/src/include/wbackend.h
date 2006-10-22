/* @(#) $Id$ */
/* This source code is in the public domain. */
/*
 * Willow: Lightweight HTTP reverse-proxy.
 * wbackend: HTTP backend handling.
 */

#ifndef WBACKEND_H
#define WBACKEND_H

#if defined __SUNPRO_C || defined __DECC || defined __HP_cc
# pragma ident "@(#)$Id$"
#endif

#include <sys/types.h>
#include <netinet/in.h>

#include <string>
using std::string;
#include <vector>
using std::vector;

struct fde;

struct backend {
		backend(string const &, string const &, sockaddr *, socklen_t);

	string			 be_name;	/* IP as specified in config	*/
	int	 		 be_port;	/* port number			*/
	sockaddr_storage	 be_addr;	/* socket address		*/
	socklen_t		 be_addrlen;	/* address length		*/
	string			 be_straddr;	/* formatted address		*/
	int	 		 be_dead;	/* 0 if okay, 1 if unavailable	*/
	time_t			 be_time;	/* If dead, time to retry	*/
	uint32_t		 be_hash;	/* constant carp "host" hash	*/
	uint32_t		 be_carp;	/* carp hash for the last url	*/
	float			 be_load;	/* carp load factor		*/
	float			 be_carplfm;	/* carp LFM after calculation	*/
};

typedef void (*backend_cb)(struct backend *, struct fde *, void *);

void add_backend(string const &, int);
void backend_file(string const &);

int get_backend(string const &url, backend_cb, void *, int);

extern vector<backend *> backends;

#endif
