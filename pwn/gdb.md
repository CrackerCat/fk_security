# gdb 
## common commad:  
- p/<x,f> addr
- finish ： 执行完整个函数  
- c : 继续运行  
- x/num w(四字节)/g(八字节)/c(字符格式)/b(字节) x(16进制)/i(显示指令)/s(字符串) $寄存器/地址  查看地址值  
- r <args> : 运行  
- ni : 单步跳过  
- si : 单步进入  
- distance addr1 addr2  = addr2 - addr1  
- search -s "target string"  
- vmmap 查看进程中的权限及地址范围  
- bt backtrace 函数调用路径,可查看当前运行到哪一行
- break line-or-function if expr 条件断点
- set var $reg/*addr=value  or set {unsigned int}0x8048a51=0x0 : 修改变量值  
- list <linenum>/<function>/<first>,<last>/,<last>/-/+/empty  显示源代码(-g)  
- until 执行完循环
- call func(para list)  调用函数
- display expression  在每一次单步调试都会打印expression的值
- watch expression 一旦表达式的值改变就会中止程序
- info locals
- info r : 打印寄存器
- ret 直接返回，不执行完剩余指令
- fin 完成当前函数的执行
- got/plt 打印got/plt
- cyclic number 生成一条有规律的字符串，cyclic -l string 查找字符串的偏移 eg aaaabaaacaaa。。。
- arena
- heap: 打印heap信息。
- parseheap : heap的状态。
- heapinfo ： 显示空闲块的状态。
- unlink
- orange
- bins
- magic : 显示一些可以利用的函数。
- tcache
- set args arg1(可以使用$(python -c "print()")传入不可显示字符) arg2 ... 设置程序参数
- show args 显示程序参数
- unset env field1=value1; show env  gdb中环境变量相关命令
- set env field1=value1
- handle SIGSEGV nostop : 关闭gdb对SIGSEGV信号的处理
- dump memory file_name addr_begin addr_end : dump某个段的地址空间，可以用ida进行分析
- target remote host:port  : 远程调试(set arch/endian)
- set print asm-demangle on ： 让function好看一点
- fork跟踪调试，选择跟踪哪个进程
	- set follow-fork-mode parent : 父进程
	- set follow-fork-mode child ： 子进程
	- set detach-on-fork off ： 同时调试父进程和子进程
	- inferior X 切换跟踪调试进程， X 可以是 info inferiors 得到的任意数字。
- add-symbol-file FILE textaddr ：从FILE中加载符号。 
	- 对于内核模块，.text 段的地址可以通过 /sys/modules/[module_name]/section/.text。 根据代码段基址+偏移量下断点。
- help command : 查看command帮助文档。
- ptype var : 打印变量的类型。
- 可以使用 source 加载gdb命令

## gdb argument
- gdb --args program arg1 arg2 ... 带参调试
- command/x 使用命令文件自动化调试
- ulimit -c size 设置coredump文件大小； gdb binary core_dump :  可加载core文件确认错误位置。
- 程序收到SIGSEGV信号，触发段错误，并提示地址。
---

## pwndbg
- b *$rebase(offset)，需要在running阶段使用。
### problems
- https://github.com/cyrus-and/gdb-dashboard/issues/1 : 安装问题
### pwngdb
	增加 parseheap/heapinfo等命令。

## multi-arch gdb
1. 交叉编译出(arch)-linux-gdb
   - 下载gdb源码
   - ./configure --target=目标架构 --prefix=安装路径
2. qemu 模拟运行
   - qemu 系统模式运行; gdbserver(ip:port file) 在模拟系统中开启调试进程; (麻烦，但是有效)
   or
   - qemu 用户模式运行(-g port);
3. (arch)-linux-gdb(target remote ip:port)

### 结合pwntools进行跨架构调试
	1. 利用pwntools工具，进行qemu启动程序。 
```python
process("qemu-*arch*", "-cpu", "max", "-L", ".", "program"])
```
	2. 另起一个终端，target remote 附加调试程序。 
	3. 用pwntools进行输入输出。
#### 加断点
	对于qemu-user运行的程序，text的基址是0x4000000000, 因为qemu-user无alsr。

### referrence
- [参考文献](https://blog.csdn.net/zqj6893/article/details/84662579 "跨架构调试")

## other
- gdb调试的时候，可能会额外打开一些文件，导致fd号增加。