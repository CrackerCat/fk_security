# format string vulnerability  
## printf(s)  
- 可以通过修改s，使s含有格式化字符(%x %n等, 结尾通过\x00跟其他字符串分隔）达到修改任意地址的值的目的。  
- 64bit下的%m$n(linux valid), m的个数要多6，多了6个寄存器,从这个格式化字符串往后数第m个存储单元。  
- %hhn 向某个地址写入一个字节  
- %hn 向某个地址写入两个字节  

### exploit
	1. 将目标地址读入到内存中，然后可以任意写。
### reference
- https://www.cnblogs.com/ichunqiu/p/9329387.html : 解释性文章

## scanf(s)
- "%7$d" 就是从这个格式化字符串往后数第七个存储单元(8-1)。

## c语言：  
命令行参数输入s，printf(s)可以达到执行shell命令的目的。  

## pwntools
- pwn.fmtstr_payload(offset, writes, numbwritten=0, write_size='byte')
-- offset：the first formatter's offset you control
-- writes: dict with addr, value ``{addr: value, addr2: value2}``
-- numbwritten(int): number of byte already written by the printf function
-- write_size(str): must be ``byte``, ``short`` or ``int``. Tells if you want to write byte by byte, short by short or int by int (hhn, hn or n).
