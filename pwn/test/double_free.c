#include<stdio.h>
void main()
{
    int* p = (int *)malloc(sizeof(int));
    *p = 1;
    printf("%d", *p);
    free(p);
    free(p);
}
