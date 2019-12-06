#when install the cgi, you should set a proper path where you want to intall.

OBJECTS = acseg_util.o acseg_rbtree.o acseg_tree.o mem_collector.o
LIB=libacseg.so

all: dlink

$(OBJECTS):acseg_util.h acseg_rbtree.h acseg_tree.h mem_collector.h

test: $(OBJECTS)
	gcc -o test $(OBJECTS)

dll: $(OBJECTS)
	gcc -shared -o acseg.dll $(OBJECTS) -Wl

dlink: $(OBJECTS)
	gcc -c -fPIC *.c
	gcc -shared -fPIC -o libacseg.so $(OBJECTS)

.PHONY: clean
clean: 
	-rm -rf  test *.o 
