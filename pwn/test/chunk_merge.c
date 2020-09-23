#include<stdio.h>
void main() {
  char *a=(char*)malloc(0x80);
  char *b=(char*)malloc(0x80);
  char *c=(char*)malloc(0x80);
  free(a);
  free(b);
}
