#include<stdio.h>
void main() {
  char * chunk0 = malloc((0x80));
  char * chunk1 = malloc((0x80));
  free(chunk0);
  char * chunk2 = malloc((0x40));
  char * chunk3 = malloc((0x80));
  char * chunk4 = malloc((0x80));
  char * chunk5 = malloc((0x80));
  free(chunk2);
  free(chunk4);
  char * chunk6 = malloc((0xa0));
}
  
