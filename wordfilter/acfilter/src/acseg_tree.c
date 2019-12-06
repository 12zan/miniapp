/*  
 * ac seg tree
 */

#include "mem_collector.h"
#include "acseg_util.h"
#include "acseg_rbtree.h"
#include "acseg_tree.h"

#include <unistd.h>
#include <stdio.h>
#define DD printf("debug:%d %s\n",__LINE__,__FILE__);
static acseg_index_item_t *
create_index_item(acseg_str_t *atom, acseg_rbtree_node_t 
		*rbtree_sentinel, mc_collector_t **mc)
{
	acseg_index_item_t *index_item;


	index_item = (acseg_index_item_t *) mc_calloc(mc, sizeof(acseg_index_item_t));

	acseg_copy_str_t(&(index_item->atom), atom, mc);

	index_item->output = acseg_list_init(mc);
	index_item->extra_outputs = acseg_list_init(mc);
	index_item->failure = NULL;

	index_item->childs_rbtree = (acseg_rbtree_t *) mc_calloc(mc, sizeof(acseg_rbtree_t));

	acseg_rbtree_init(index_item->childs_rbtree, 
			rbtree_sentinel, acseg_rbtree_insert_value);
	
	return index_item;
}


acseg_index_t * 
acseg_index_init(void)
{
	acseg_index_t * ac_index;

	acseg_rbtree_node_t * rbtree_sentinel;

	mc_collector_t *mc;

	acseg_str_t atom;

	mc = NULL;

	atom.data = NULL;
	atom.len = 0;

	rbtree_sentinel = (acseg_rbtree_node_t *) mc_calloc(&mc, sizeof(acseg_rbtree_node_t));

	ac_index = (acseg_index_t *) mc_calloc(&mc, sizeof(acseg_index_t));
	ac_index->state = AC_INDEX_UNFIXED;
	ac_index->root = create_index_item(&atom, rbtree_sentinel, &mc);
	ac_index->mc = mc;
	ac_index->root->failure = ac_index->root;

	return ac_index;
}

static acseg_rbtree_node_t *
create_rbtree_node(acseg_rbtree_key_t key, void *data, mc_collector_t **mc)
{
	acseg_rbtree_node_t * node;	

	node = (acseg_rbtree_node_t *) mc_calloc(mc, sizeof(acseg_rbtree_node_t));

	node->key = key;
	node->data = data;

	return node;
}

void print_atom(acseg_str_t *atom){
	char *new_atom;
	void *p;
	p = malloc(atom->len + 1);
	memset(p, 0, atom->len + 1);
	new_atom = (char *) p;
	memcpy(p, atom->data, atom->len);
}

acseg_index_t *
acseg_index_add(acseg_index_t *acseg_index, acseg_str_t *phrase)
{
	int i;

	acseg_rbtree_key_t rbtree_key;

	acseg_str_t atom;
	acseg_str_t * new_phrase;

	acseg_rbtree_t *childs_rbtree;
	acseg_rbtree_node_t * rbtree_sentinel, *s_node, *insert_node;

	acseg_index_item_t *index_item, *new_index_item;

	if (acseg_index->state == AC_INDEX_FIXED){
		return NULL;
	}

	childs_rbtree = acseg_index->root->childs_rbtree;
	rbtree_sentinel = childs_rbtree->sentinel;

	index_item = NULL;
	new_index_item = NULL;

	i = 0;
	while (i < phrase->len) {
		atom.data = &(phrase->data[i]);
		atom.len = get_mblen(atom.data[0]);

		rbtree_key = ord_utf8_wch((char *) atom.data);
		s_node = acseg_rbtree_search(childs_rbtree, rbtree_key);
		if (s_node == NULL){
			break;
		} else {
			i = i + atom.len;
			index_item = (acseg_index_item_t *) s_node->data;
			childs_rbtree = index_item->childs_rbtree;
		}
	}

	while (i < phrase->len){
		atom.data = &(phrase->data[i]);
		atom.len = get_mblen(atom.data[0]);
		
		rbtree_key = ord_utf8_wch((char *) atom.data);
		new_index_item = create_index_item(&atom, rbtree_sentinel, &(acseg_index->mc));

		// insert node
		insert_node = create_rbtree_node(rbtree_key, new_index_item, &(acseg_index->mc));
		acseg_rbtree_insert(childs_rbtree, insert_node);

		index_item = new_index_item;
		childs_rbtree = new_index_item->childs_rbtree;
		i = i + atom.len;
	}

	new_phrase = (acseg_str_t *) mc_calloc(&(acseg_index->mc), sizeof(acseg_str_t));

	acseg_copy_str_t(new_phrase, phrase, &(acseg_index->mc));

	acseg_list_add(index_item->output, new_phrase, &(acseg_index->mc));

	return acseg_index;
}

acseg_index_t * 
acseg_index_load(acseg_index_t *acseg_index, const char *fpath)
{
	FILE *fp;
	int i, word_len;
	char buf[64];
	acseg_str_t phrase;
	if (acseg_index == NULL){
		return NULL;
	}

	if ((fp = fopen(fpath, "r")) == NULL) {
		return NULL;
	}
	while (fgets(buf, sizeof(buf) - 1, fp) != NULL) {
		word_len = strlen(buf);
		for (i=0; i < word_len; i++){
			if (buf[i] == '\n' || buf[i] == '\r'){
				buf[i] = '\0';
				break;
			}
		}
		word_len = strlen(buf);
	    if(word_len==0){
            continue;
        }
		phrase.data = (u_char *) buf;
		phrase.len = strlen(buf);
		acseg_index_add(acseg_index, &phrase);
	}
	fclose(fp);
    
	return acseg_index;
}

static void
add_all_item_to_queue(acseg_rbtree_node_t *node, 
		acseg_rbtree_node_t *sentinel, acseg_list_t *queue, mc_collector_t **mc)
{
	if (node != sentinel) {
		acseg_queue_push(queue, node->data, mc);
	} else {
		return;
	}

	if (node->left != sentinel) {
		add_all_item_to_queue(node->left, sentinel, queue, mc);
	}

	if (node->right != sentinel) {
		add_all_item_to_queue(node->right, sentinel, queue, mc);
	}
}

static acseg_index_item_t *
find_child_index_item(acseg_index_item_t *index_item, acseg_str_t *atom)
{
	acseg_rbtree_key_t rbtree_key;
	
	acseg_rbtree_node_t *node;

	rbtree_key = ord_utf8_wch((char *)atom->data);

	node = acseg_rbtree_search(index_item->childs_rbtree, rbtree_key);
	if (node == NULL) {
		return NULL;
	}
	return (acseg_index_item_t *) node->data;
}

static void 
set_index_item_failure(acseg_list_t *index_item_list, acseg_index_item_t *failure)
{
	acseg_list_item_t *list_item;
	acseg_index_item_t *index_item;

	list_item = index_item_list->first;
	while (list_item) {
		index_item = (acseg_index_item_t *) list_item->data;
		index_item->failure = failure;
		list_item = list_item->next;
	}
}

void 
acseg_index_fix(acseg_index_t *acseg_index)
{
	mc_collector_t *local_mc;

	acseg_list_t *queue, *child_queue;

	acseg_rbtree_t *rbtree, *child_rbtree;

	acseg_index_item_t *index_item, *parent_failure;
	acseg_index_item_t *tmp_index_item, *child_item;

	local_mc = NULL;

	queue = acseg_list_init(&local_mc);
	child_queue = acseg_list_init(&local_mc);

	rbtree = acseg_index->root->childs_rbtree;

	add_all_item_to_queue(rbtree->root, 
			rbtree->sentinel, queue, &local_mc);

	set_index_item_failure(queue, acseg_index->root);

	while ( (index_item = acseg_queue_pop(queue)) ){
		child_rbtree = index_item->childs_rbtree;

		add_all_item_to_queue(child_rbtree->root, 
				child_rbtree->sentinel, child_queue, &local_mc);

		child_item = acseg_queue_pop(child_queue);
		while (child_item) {
			acseg_queue_push(queue, child_item, &local_mc);

			parent_failure = index_item->failure;
			
			while (1) {
				tmp_index_item = find_child_index_item(parent_failure, &(child_item->atom));
				if (tmp_index_item == NULL) {
					if (parent_failure == acseg_index->root) {
						tmp_index_item = parent_failure;
						break;
					} else {
						parent_failure = parent_failure->failure;
					}
				} else {
					break;
				}
			}

			child_item->failure = tmp_index_item;

			acseg_list_extend(child_item->extra_outputs,
					tmp_index_item->output, &(acseg_index->mc));

			acseg_list_extend(child_item->extra_outputs, 
					tmp_index_item->extra_outputs, &(acseg_index->mc));

			child_item = acseg_queue_pop(child_queue);
		}
	}

	mc_destory(local_mc);
	acseg_index->state = AC_INDEX_FIXED;
}

void 
acseg_destory_index(acseg_index_t **acseg_index)
{
	mc_destory( (*acseg_index)->mc );
	*acseg_index = NULL;
}

static acseg_result_t *
acseg_result_init(void)
{
	mc_collector_t *mc;
	acseg_result_t *result;

	mc = NULL;

	result = (acseg_result_t *) mc_calloc(&mc, sizeof(acseg_result_t));
	result->list = acseg_list_init(&mc);
	result->mc = mc;
	result->num = 0;

	return result;
}

static void
add_to_result(acseg_result_t *result, acseg_list_t *addon_list)
{
	acseg_str_t *new_phrase, *phrase;
	acseg_list_item_t *tmp;

	tmp = addon_list->first;
	while (tmp){
		phrase = (acseg_str_t *) tmp->data;

		new_phrase = (acseg_str_t *) mc_calloc(&(result->mc), sizeof(acseg_str_t));

		new_phrase->data = (u_char *) mc_calloc(&(result->mc), phrase->len + 1);
		memcpy(new_phrase->data, phrase->data, phrase->len);
		new_phrase->len = phrase->len;

		acseg_list_add(result->list, new_phrase, &(result->mc));
		result->num = result->num + 1;
		tmp = tmp->next;
		
	}
}

acseg_result_t *
acseg_full_seg(acseg_index_t *acseg_index, acseg_str_t *text,int max_seek)
{
	int j,current_pos,tmp_j;
	acseg_str_t atom,atom2,tmp_atom;
	acseg_result_t *seg_result;
	acseg_index_item_t *index_item, *s_index_item,* tmp_s_index_item;
	seg_result = acseg_result_init();
//    int max_seek=5;
    int seeks=0;
	if (acseg_index->state != AC_INDEX_FIXED) {
		return seg_result;
	}
	current_pos=j = 0;
	index_item = acseg_index->root;
	while (j < text->len) {
        seeks=0;
		atom.data = &(text->data[j]);
		atom.len = get_mblen( ((u_char) atom.data[0]) );
		tmp_atom = atom;	
		tmp_s_index_item = s_index_item = find_child_index_item(index_item, &atom);
        while(
                tmp_s_index_item ==NULL &&
                seeks<max_seek && current_pos <(text->len)){
            atom2.data = &(text->data[current_pos+tmp_atom.len]);
            atom2.len = get_mblen( ((u_char) atom2.data[0]) );
            print_atom(&atom2);
		    tmp_s_index_item = find_child_index_item(index_item, &atom2);
            seeks++;
            if(tmp_s_index_item!=NULL){
                current_pos = j = current_pos +tmp_atom.len; 
                atom = atom2;
                s_index_item = tmp_s_index_item;
                break;
            }
            else{
                current_pos =  current_pos +tmp_atom.len; 
                tmp_atom = atom2;
            }
        }
		while(s_index_item == NULL) {
			if (index_item == acseg_index->root) {
				s_index_item = index_item;
				break;
			}
			index_item = index_item->failure;
			s_index_item = find_child_index_item(index_item, &atom);
		}

		index_item = s_index_item;

		add_to_result(seg_result, index_item->output);

		add_to_result(seg_result, index_item->extra_outputs);

		current_pos = tmp_j =  j = j + atom.len;
	}

	return seg_result;
}

void 
acseg_destory_result(acseg_result_t **result)
{
	mc_destory( (*result)->mc );
	*result = NULL;
}
