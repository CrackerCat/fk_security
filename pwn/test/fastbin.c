#include<stdio.h>
void main() {
  char *t = (char *)malloc(sizeof(char)*0x8);
  void *chunk1,*chunk2,*chunk3;
  chunk1=malloc(0x71);
  chunk2=malloc(0x78);
  chunk3=malloc(0x79);
  //进行释放
  free(chunk1);
  free(chunk2);
  free(chunk3);
}
