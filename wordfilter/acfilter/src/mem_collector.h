/* 
 * Make memory management easy
 */

#ifndef MEM_COLLECTOR_H_INCLUDED_
#define MEM_COLLECTOR_H_INCLUDED_

#include <stdlib.h>

typedef struct mc_collector_s mc_collector_t;

struct mc_collector_s {
	mc_collector_t **self;
	mc_collector_t *next;
};

void* mc_malloc(mc_collector_t **mc, size_t size);

void* mc_calloc(mc_collector_t **mc, size_t size);

void mc_free(void *data);

void mc_destory(mc_collector_t *mc);

#endif
