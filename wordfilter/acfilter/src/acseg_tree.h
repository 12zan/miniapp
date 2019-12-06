/*
 * ac seg tree
 */
#ifndef _AC_SEG_TREE_H_INCLUDED_
#define _AC_SEG_TREE_H_INCLUDED_

#include "mem_collector.h"
#include "acseg_rbtree.h"
#include "acseg_util.h"

typedef struct acseg_index_s  acseg_index_t;

typedef struct acseg_index_item_s acseg_index_item_t;

typedef enum {
	AC_INDEX_UNFIXED,
	AC_INDEX_FIXED
} acseg_index_state;

struct acseg_index_s {
	acseg_index_state state;
	acseg_index_item_t *root;

	mc_collector_t *mc;
};


struct acseg_index_item_s {
	acseg_str_t atom;

	acseg_list_t *output;

	acseg_list_t *extra_outputs;

	acseg_rbtree_t *childs_rbtree;

	acseg_index_item_t *failure;
};

typedef struct {
	unsigned int num;

	acseg_list_t *list;
	mc_collector_t *mc;
} acseg_result_t;

acseg_index_t * acseg_index_init(void);

acseg_index_t * acseg_index_add(acseg_index_t *acseg_index, acseg_str_t *phrase);

acseg_index_t * acseg_index_load(acseg_index_t *acseg_index, const char *fpath);

void acseg_index_fix(acseg_index_t *acseg_index);

void acseg_destory_index(acseg_index_t **acseg_index);

acseg_result_t * acseg_full_seg(acseg_index_t *acseg_index, acseg_str_t *text,int max_seek);

void acseg_destory_result(acseg_result_t **result);

#endif
