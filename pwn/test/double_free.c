#include<stdio.h>
void main()
{
    int* p = (int *)malloc(0x80);
    *p = 1;
    int* p1 = (int *)malloc(sizeof(int));
    *p1 = 2;
    int* p2 = (int *)malloc(sizeof(int));
    *p2 = 3;
    printf("%d", *p);
    free(p1);
    free(p);
    free(p2);
}
