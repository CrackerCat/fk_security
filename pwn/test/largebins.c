#include<stdio.h>
void main() {
  char *a=(char*)malloc(0x8000);
  char *b=(char*)malloc(0x8000);
  char *c=(char*)malloc(0x21000);
  free(a);
  free(c);
}
