# format string vulnerability
## printf(s)   
可以通过修改s，使s含有格式化字符(%x %n等）达到修改任意地址的值的目的。  
64bit下的%m$n(linux valid), m的个数要多6，多了6个寄存器。  
%hhn 向某个地址写入一个字节
%hn 向某个地址写入两个字节

## c语言：  
命令行参数输入s，printf(s)可以达到执行shell命令的目的。  

