/**
 *
 */
#include <stdio.h>
#include <stdlib.h>

#include "acseg_util.h"
#include "mem_collector.h"

static unsigned char mblen_table_utf8[] = 
{
	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
	2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2,
	2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2,
	3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3,
	4, 4, 4, 4, 4, 4, 4, 4, 5, 5, 5, 5, 6, 6, 1, 1
};

unsigned int 
get_word_size(const char *word, unsigned int word_len)
{
	unsigned int i    = 0;
	unsigned int size = 0;

	while (i < word_len){
		i = i + mblen_table_utf8[ (u_char)word[i] ];
		size = size + 1;
	}

	return size;
}

 unsigned int get_mblen(u_char ch)
{
	return mblen_table_utf8[ch];
}

uint64_t 
ord_utf8_wch(const char *wch_str)
{
	int i, mblen;
	uint64_t code_value;

	i = 0;
	code_value = 0;

	mblen = get_mblen((u_char)wch_str[0]);
	while (i < mblen) {
		code_value = (code_value << 8) | ((u_char) wch_str[i]);
		i = i + 1;
	}
	return code_value;
}

void
acseg_copy_str_t(acseg_str_t *dest, acseg_str_t *src, mc_collector_t **mc)
{

	if (src->data == NULL){
		dest->data = NULL;	
		dest->len = 0;
	} else {
		dest->data = (u_char *) mc_calloc(mc, src->len);
		memcpy(dest->data, src->data, src->len);
		dest->len = src->len;
	}
}

/* 
 * ac list
 */

acseg_list_t * 
acseg_list_init(mc_collector_t **mc)
{
	acseg_list_t *list;

	list = (acseg_list_t *) mc_calloc(mc, sizeof(acseg_list_t));

	list->first = NULL;
	list->last = NULL;

	return list;
}

void
acseg_list_add(acseg_list_t *list, void *data, mc_collector_t **mc)
{
	acseg_list_item_t *list_item;

	list_item = (acseg_list_item_t *) mc_calloc(mc, sizeof(acseg_list_item_t));

	list_item->next = NULL;
	list_item->data = data;

	if (list->first == NULL) {
		list->first = list_item;
	}

	if (list->last) {
		list->last->next = list_item;
	}

	list->last = list_item;
}

void 
acseg_list_extend(acseg_list_t *list, acseg_list_t *addon, mc_collector_t **mc)
{
	acseg_list_item_t *tmp;

	tmp = addon->first;
	while (tmp){
		//printf("add: \n");
		acseg_list_add(list, tmp->data, mc);
		tmp = tmp->next;
	}
}

void 
acseg_queue_push(acseg_list_t *list, void *data, mc_collector_t **mc)
{
	acseg_list_add(list, data, mc);
}

void *
acseg_queue_pop(acseg_list_t *list)
{
	void * result;
	acseg_list_item_t *next;

	result = NULL;
	if (list->first){
		result = list->first->data;
		next = list->first->next;
		mc_free(list->first);
		list->first = next;
	}

	if (list->first == NULL){
		list->last = NULL;
	}

	return result;
}
