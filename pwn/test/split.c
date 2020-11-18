#include<stdio.h>
void main() {
  char *p = malloc(0x80);
  char *p1 = malloc(0x80);
  free(p);
  char *p2 = malloc(0x40);
}
