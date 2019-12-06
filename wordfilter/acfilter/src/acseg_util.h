/**
 *
 */
#ifndef ACSEG_UTIL_H_INCLUDED_
#define ACSEG_UTIL_H_INCLUDED_

#include <string.h>
#include <stdlib.h>
#include <stdint.h>

#include "uchar.h"
#include "mem_collector.h"


typedef struct {
	size_t      len;
	u_char     *data;
} acseg_str_t;

typedef struct {
	size_t len;
	const u_char *data;
} acseg_const_str_t;

void acseg_copy_str_t(acseg_str_t *dest, acseg_str_t *src, mc_collector_t **mc);

unsigned int get_word_size(const char *word, unsigned int word_len);

 unsigned int get_mblen(u_char ch);

uint64_t ord_utf8_wch(const char *wch_str);

/*
 * acseg list
 */
typedef struct acseg_list_item_s acseg_list_item_t;

struct acseg_list_item_s {
	void *data;
	acseg_list_item_t *next;
};

typedef struct {
	acseg_list_item_t *first;
	acseg_list_item_t *last;
} acseg_list_t;

acseg_list_t * acseg_list_init(mc_collector_t **mc);

void acseg_list_add(acseg_list_t *list, void *data, mc_collector_t **mc);

void acseg_list_extend(acseg_list_t *list, acseg_list_t *added, mc_collector_t **mc);


void acseg_queue_push(acseg_list_t *list, void *data, mc_collector_t **mc);

void * acseg_queue_pop(acseg_list_t *list);

#endif
