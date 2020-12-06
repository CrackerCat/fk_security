#include<unistd.h>
void main() {
  char *sc[2]; 
  sc[0]="/bin/sh"; 
  sc[1]= NULL; 
  execve(sc[0],sc,NULL);
}
