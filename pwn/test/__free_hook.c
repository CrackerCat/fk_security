#include<stdio.h>
#include<stdlib.h>
#include<string.h>

extern void (*__free_hook) (void *__ptr,const void *);

int main()
{
	char *str = malloc(160);
	strcpy(str,"/bin/sh");
	
	printf("__free_hook: 0x%016X\n",__free_hook);
        __free_hook = system;
	printf("__free_hook: 0x%016X\n",__free_hook);

	free(str);
	return 0;
}
