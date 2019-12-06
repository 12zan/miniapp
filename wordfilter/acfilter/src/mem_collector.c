/* 
 * Make memory management easy 
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "mem_collector.h"
#include "uchar.h"


void *
mc_malloc(mc_collector_t **mc, size_t size)
{
	mc_collector_t *mem_block;

	mem_block = malloc(sizeof(mc_collector_t) + size);
	if (mem_block == NULL){
		return NULL;
	}

	if (*mc == NULL){
		mem_block->self = mc;
		mem_block->next = NULL;
	} else {
		mem_block->next = *mc;
		mem_block->self = mc;

		mem_block->next->self = &(mem_block->next);
	}

	*mc = mem_block;
	return (u_char *) mem_block + sizeof(mc_collector_t);
}

void *
mc_calloc(mc_collector_t **mc, size_t size)
{
	void *p;

	p = mc_malloc(mc, size);
	if (p){
		memset(p, 0, size);	
	}

	return p;
}

void 
mc_free(void *data)
{
	mc_collector_t *mem_block;

	mem_block = (mc_collector_t *) ((u_char *) data - sizeof(mc_collector_t));

	if (mem_block->next) {
		*mem_block->self = mem_block->next;
	} else {
		*mem_block->self = NULL;	
	}
	
	free(mem_block);
}

void 
mc_destory(mc_collector_t *mc)
{
	mc_collector_t *next, *current;

	current = mc;
	while (current) {
		next = current->next;
		free(current);
		current = next;
	}

}
