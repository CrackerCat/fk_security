#include<iostream>
#include<stdio.h>
using namespace std;
void fun1() {
  //[ebp-0x9]
  char a;
  //scanf("%s", &a);
  *(long int *)(&a-0x1) = &main;
  *(long int *)(&a-0x9) = &a+0x1-0x8;
  throw 1;
  printf("\nfun1 exit");
}
void fun2() {
  try{
    fun1();
  }catch(...) {
    printf("enter exception handler");
  }
  printf("\nexit");
}
int main() {
  fun2();
  return 0;
}
