
/*
 * Copy from nginx, a little change
 */


#include <stdio.h>
#include "acseg_rbtree.h"

/*
 * The red-black tree code is based on the algorithm described in
 * the "Introduction to Algorithms" by Cormen, Leiserson and Rivest.
 */


static inline void acseg_rbtree_left_rotate(acseg_rbtree_node_t **root,
    acseg_rbtree_node_t *sentinel, acseg_rbtree_node_t *node);
static inline void acseg_rbtree_right_rotate(acseg_rbtree_node_t **root,
    acseg_rbtree_node_t *sentinel, acseg_rbtree_node_t *node);


void
acseg_rbtree_insert(acseg_rbtree_t *tree, acseg_rbtree_node_t *node)
{
    acseg_rbtree_node_t  **root, *temp, *sentinel;

    /* a binary tree insert */

    root = (acseg_rbtree_node_t **) &tree->root;
    sentinel = tree->sentinel;

    if (*root == sentinel) {
        node->parent = NULL;
        node->left = sentinel;
        node->right = sentinel;
        acseg_rbt_black(node);
        *root = node;

        return;
    }

	// just add new node value
    if (tree->insert(*root, node, sentinel) == NULL){
		return;
	}

    /* re-balance tree */

    while (node != *root && acseg_rbt_is_red(node->parent)) {

        if (node->parent == node->parent->parent->left) {
            temp = node->parent->parent->right;

            if (acseg_rbt_is_red(temp)) {
                acseg_rbt_black(node->parent);
                acseg_rbt_black(temp);
                acseg_rbt_red(node->parent->parent);
                node = node->parent->parent;

            } else {
                if (node == node->parent->right) {
                    node = node->parent;
                    acseg_rbtree_left_rotate(root, sentinel, node);
                }

                acseg_rbt_black(node->parent);
                acseg_rbt_red(node->parent->parent);
                acseg_rbtree_right_rotate(root, sentinel, node->parent->parent);
            }

        } else {
            temp = node->parent->parent->left;

            if (acseg_rbt_is_red(temp)) {
                acseg_rbt_black(node->parent);
                acseg_rbt_black(temp);
                acseg_rbt_red(node->parent->parent);
                node = node->parent->parent;

            } else {
                if (node == node->parent->left) {
                    node = node->parent;
                    acseg_rbtree_right_rotate(root, sentinel, node);
                }

                acseg_rbt_black(node->parent);
                acseg_rbt_red(node->parent->parent);
                acseg_rbtree_left_rotate(root, sentinel, node->parent->parent);
            }
        }
    }

    acseg_rbt_black(*root);
}


acseg_rbtree_node_t *
acseg_rbtree_insert_value(acseg_rbtree_node_t *temp, acseg_rbtree_node_t *node,
    acseg_rbtree_node_t *sentinel)
{
    acseg_rbtree_node_t  **p;

    for ( ;; ) {

        p = (node->key < temp->key) ? &temp->left : &temp->right;

        if (*p == sentinel) {
            break;
        }

        temp = *p;
    }

    *p = node;
    node->parent = temp;
    node->left = sentinel;
    node->right = sentinel;
    acseg_rbt_red(node);
	return node;
}

void
acseg_rbtree_delete(acseg_rbtree_t *tree, acseg_rbtree_node_t *node)
{
    unsigned int  red;
    acseg_rbtree_node_t  **root, *sentinel, *subst, *temp, *w;

    /* a binary tree delete */

    root = (acseg_rbtree_node_t **) &tree->root;
    sentinel = tree->sentinel;

    if (node->left == sentinel) {
        temp = node->right;
        subst = node;

    } else if (node->right == sentinel) {
        temp = node->left;
        subst = node;

    } else {
        subst = acseg_rbtree_min(node->right, sentinel);

        if (subst->left != sentinel) {
            temp = subst->left;
        } else {
            temp = subst->right;
        }
    }

    if (subst == *root) {
        *root = temp;
        acseg_rbt_black(temp);

        /* DEBUG stuff */
        node->left = NULL;
        node->right = NULL;
        node->parent = NULL;
        node->key = 0;

        return;
    }

    red = acseg_rbt_is_red(subst);

    if (subst == subst->parent->left) {
        subst->parent->left = temp;

    } else {
        subst->parent->right = temp;
    }

    if (subst == node) {

        temp->parent = subst->parent;

    } else {

        if (subst->parent == node) {
            temp->parent = subst;

        } else {
            temp->parent = subst->parent;
        }

        subst->left = node->left;
        subst->right = node->right;
        subst->parent = node->parent;
        acseg_rbt_copy_color(subst, node);

        if (node == *root) {
            *root = subst;

        } else {
            if (node == node->parent->left) {
                node->parent->left = subst;
            } else {
                node->parent->right = subst;
            }
        }

        if (subst->left != sentinel) {
            subst->left->parent = subst;
        }

        if (subst->right != sentinel) {
            subst->right->parent = subst;
        }
    }

    /* DEBUG stuff */
    node->left = NULL;
    node->right = NULL;
    node->parent = NULL;
    node->key = 0;

    if (red) {
        return;
    }

    /* a delete fixup */

    while (temp != *root && acseg_rbt_is_black(temp)) {

        if (temp == temp->parent->left) {
            w = temp->parent->right;

            if (acseg_rbt_is_red(w)) {
                acseg_rbt_black(w);
                acseg_rbt_red(temp->parent);
                acseg_rbtree_left_rotate(root, sentinel, temp->parent);
                w = temp->parent->right;
            }

            if (acseg_rbt_is_black(w->left) && acseg_rbt_is_black(w->right)) {
                acseg_rbt_red(w);
                temp = temp->parent;

            } else {
                if (acseg_rbt_is_black(w->right)) {
                    acseg_rbt_black(w->left);
                    acseg_rbt_red(w);
                    acseg_rbtree_right_rotate(root, sentinel, w);
                    w = temp->parent->right;
                }

                acseg_rbt_copy_color(w, temp->parent);
                acseg_rbt_black(temp->parent);
                acseg_rbt_black(w->right);
                acseg_rbtree_left_rotate(root, sentinel, temp->parent);
                temp = *root;
            }

        } else {
            w = temp->parent->left;

            if (acseg_rbt_is_red(w)) {
                acseg_rbt_black(w);
                acseg_rbt_red(temp->parent);
                acseg_rbtree_right_rotate(root, sentinel, temp->parent);
                w = temp->parent->left;
            }

            if (acseg_rbt_is_black(w->left) && acseg_rbt_is_black(w->right)) {
                acseg_rbt_red(w);
                temp = temp->parent;

            } else {
                if (acseg_rbt_is_black(w->left)) {
                    acseg_rbt_black(w->right);
                    acseg_rbt_red(w);
                    acseg_rbtree_left_rotate(root, sentinel, w);
                    w = temp->parent->left;
                }

                acseg_rbt_copy_color(w, temp->parent);
                acseg_rbt_black(temp->parent);
                acseg_rbt_black(w->left);
                acseg_rbtree_right_rotate(root, sentinel, temp->parent);
                temp = *root;
            }
        }
    }

    acseg_rbt_black(temp);
}


static inline void
acseg_rbtree_left_rotate(acseg_rbtree_node_t **root, acseg_rbtree_node_t *sentinel,
    acseg_rbtree_node_t *node)
{
    acseg_rbtree_node_t  *temp;

    temp = node->right;
    node->right = temp->left;

    if (temp->left != sentinel) {
        temp->left->parent = node;
    }

    temp->parent = node->parent;

    if (node == *root) {
        *root = temp;

    } else if (node == node->parent->left) {
        node->parent->left = temp;

    } else {
        node->parent->right = temp;
    }

    temp->left = node;
    node->parent = temp;
}


static inline void
acseg_rbtree_right_rotate(acseg_rbtree_node_t **root, acseg_rbtree_node_t *sentinel,
    acseg_rbtree_node_t *node)
{
    acseg_rbtree_node_t  *temp;

    temp = node->left;
    node->left = temp->right;

    if (temp->right != sentinel) {
        temp->right->parent = node;
    }

    temp->parent = node->parent;

    if (node == *root) {
        *root = temp;

    } else if (node == node->parent->right) {
        node->parent->right = temp;

    } else {
        node->parent->left = temp;
    }

    temp->right = node;
    node->parent = temp;
}

acseg_rbtree_node_t *
acseg_rbtree_search(acseg_rbtree_t *tree, acseg_rbtree_key_t key)
{
	int cmp;

	acseg_rbtree_node_t *sentinel, *tmp_node;

	tmp_node = tree->root;
	sentinel = tree->sentinel;
    for ( ;; ) {
		if (tmp_node == sentinel){
			return NULL;
		}

		cmp = tmp_node->key - key;

		if (cmp == 0){
			return tmp_node;
		} else if (cmp > 0){
			tmp_node = tmp_node->left;
		} else {
			tmp_node = tmp_node->right;	
		}
	}

	return NULL;
}
