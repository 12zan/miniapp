/*
 * Copy from nginx, a little change
 */


#ifndef _ACSEG_RBTREE_H_INCLUDED_
#define _ACSEG_RBTREE_H_INCLUDED_

#include <stdlib.h>
#include <stdint.h>
#include "uchar.h"
typedef uint64_t  acseg_rbtree_key_t;

typedef struct acseg_rbtree_node_s  acseg_rbtree_node_t;

typedef struct acseg_rbtree_s  acseg_rbtree_t;

struct acseg_rbtree_node_s {
    acseg_rbtree_key_t       key;
    acseg_rbtree_node_t     *left;
    acseg_rbtree_node_t     *right;
    acseg_rbtree_node_t     *parent;
    u_char                 color;

	void *data;
};

 
typedef acseg_rbtree_node_t * (*acseg_rbtree_insert_pt) (acseg_rbtree_node_t*root, 
	acseg_rbtree_node_t*node, acseg_rbtree_node_t*sentinel);

struct acseg_rbtree_s {
    acseg_rbtree_node_t     *root;
    acseg_rbtree_node_t     *sentinel;
	acseg_rbtree_insert_pt   insert;
};

#define acseg_rbtree_init(tree, s, i)                                           \
    acseg_rbtree_sentinel_init(s);                                              \
    (tree)->root = s;                                                         \
    (tree)->sentinel = s;                                                     \
    (tree)->insert = i;

void acseg_rbtree_insert(acseg_rbtree_t *tree, acseg_rbtree_node_t *node);

void acseg_rbtree_delete(acseg_rbtree_t *tree, acseg_rbtree_node_t *node);

acseg_rbtree_node_t * acseg_rbtree_search(acseg_rbtree_t *tree, 
	acseg_rbtree_key_t key);

#define acseg_rbt_red(node)               ((node)->color = 1)
#define acseg_rbt_black(node)             ((node)->color = 0)
#define acseg_rbt_is_red(node)            ((node)->color)
#define acseg_rbt_is_black(node)          (!acseg_rbt_is_red(node))
#define acseg_rbt_copy_color(n1, n2)      (n1->color = n2->color)


/* a sentinel must be black */

#define acseg_rbtree_sentinel_init(node)  acseg_rbt_black(node)
acseg_rbtree_node_t * acseg_rbtree_insert_value(
		acseg_rbtree_node_t *temp, acseg_rbtree_node_t *node, acseg_rbtree_node_t *sentinel);

static inline acseg_rbtree_node_t *
acseg_rbtree_min(acseg_rbtree_node_t *node, acseg_rbtree_node_t *sentinel)
{
    while (node->left != sentinel) {
        node = node->left;
    }

    return node;
}

#endif /* _ACSEG_RBTREE_H_INCLUDED_ */
