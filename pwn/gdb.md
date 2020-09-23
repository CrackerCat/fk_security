# gdb 
## common commad:  
- finish ： 执行完整个函数  
- c : 继续运行  
- x/64 w(四字节)/g(八字节)/c(字符格式)/b(字节) x(16进制)/i(显示指令)/s(字符串) $寄存器/地址  查看地址值  
- r : 运行  
- ni : 单步跳过  
- si : 单步进入  
- distance addr1 addr2  = addr2 - addr1  
- search -s "target string"  
- vmmap 查看进程中的权限及地址范围  
- bt backtrace 函数调用路径,可查看当前运行到哪一行
- break line-or-function if expr 条件断点
- set var $reg/addr=value  修改变量值  
- list <linenum>/<function>/<first>,<last>/,<last>/-/+/empty  显示源代码(-g)  
- until 执行完循环
- call func(para list)  调用函数
- display expression  在每一次单步调试都会打印expression的值
- watch expression 一旦表达式的值改变就会中止程序
- info locals
- ret 直接返回，不执行完剩余指令
- fin 完成当前函数的执行
- got/plt 打印got/plt
- cyclic number 生成一条有规律的字符串，cyclic -l string 查找字符串的偏移 eg aaaabaaacaaa。。。
- arena
- heap: 打印heap信息。
- paseheap
- heapinfo
- unlink
- orange
- bins
- magic
- tcache
- set args arg1(可以使用$(python -c "print()")传入不可显示字符) arg2 ... 设置程序参数
- show args 显示程序参数
- unset env field1=value1; show env  gdb中环境变量相关命令
- set env field1=value1
- handle SIGSEGV nostop : 关闭gdb对SIGSEGV信号的处理
- dump memory file_name addr_begin addr_end : dump某个段的地址空间，可以用ida进行分析
- target remote host:port  : 远程调试(set arch/endian)

## gdb argument
- gdb --args program arg1 arg2 ... 带参调试
- command/x 使用命令文件自动化调试
---

## pwndbg
- $rebase(offset)

## multi-arch gdb
1. 交叉编译出(arch)-linux-gdb
   - 下载源码
   - ./configure --target=目标架构 --prefix=安装路径
2. qemu 模拟运行
   - qemu 系统模式运行; gdbserver(ip:port file) 在模拟系统中开启调试进程; (麻烦，但是有效)
   or
   - qemu 用户模式运行(-g port);
3. (arch)-linux-gdb(target remote ip:port)
[参考文献](https://blog.csdn.net/zqj6893/article/details/84662579 "跨架构调试")
