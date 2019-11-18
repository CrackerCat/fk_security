#include <stdio.h>

int main(void)
{
    void *chunk1,*chunk2,*chunk3;
    chunk1=malloc(0x30);
    strncpy((char*)chunk1, "asdfg", 5);
    chunk2=malloc(0x30);
    strncpy((char*)chunk2, "asdfg", 5);
    chunk3=malloc(0x30);
    strncpy((char*)chunk3, "asdfg", 5);
    getchar();
    free(chunk1);
    free(chunk2);
    free(chunk3);
    getchar();
    printf("ok\n");
    return 0;
}
