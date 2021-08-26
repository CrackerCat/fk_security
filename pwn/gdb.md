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
	- 若异构调试无法显示libc和code基址，则使用 export LD_LIBRARY_PATH=/path/lib/:$LD_LIBRARY_PATH设定库地址. 参考：http://blog.eonew.cn/archives/454。
- bt backtrace 函数调用路径,可查看当前运行到哪一行
- break line-or-function if expr 条件软件断点
- hbreak : 硬件断点，这种类型的断点被设置在 CPU 级别，并用特定的寄存器：调试寄存器。
	- 当特定的地址上有指令执行的时候中断
	- 当特定的地址上有数据可以写入的时候
	- 当特定的地址上有数据读或者写但不执行的时候
- set var $reg/*addr=value  or set {unsigned int}0x8048a51=0x0 : 修改变量值  
- list <linenum>/<function>/<first>,<last>/,<last>/-/+/empty  显示源代码(-g)  
- until 执行完循环
- call func(para list)  调用函数
- display expression  在每一次单步调试都会打印expression的值
- watch expression 一旦表达式的值改变就会中止程序
- catch <event,event 参数表示要监控的具体事件> : 监控程序中某一事件的发生,则程序停止执行。例如系统调用。 
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

## 原理
	本地调试。
	远程调试：GDB与GdbServer之间通过网络或者串口使用GDB远程通信协议RSP通讯。协议格式：$string#two_bit_check_sum。
### 基本流程
	对于静态程序的调试：
	1. 首先启动gdb进程a。
	2. a进程 fork 子进程b。
		i. 调用系统函数ptrace(PTRACE_TRACEME，[其他参数])；： 相当于子进程对操作系统说：gdb进程是我的爸爸，以后你有任何想发给我的信号，请直接发给gdb进程吧！
		ii. 通过exec来加载、执行可执行程序c，那么程序c就在这个子进程b中开始执行了。
		
	对于运行进程的调试：
	1. 首先启动gdb进程。
	2. 在gdb进程中调用ptrace(PTRACE_ATTACH,[其他参数])，会attach(绑定)到已经执行的进程B，gdb把进程B收养成为自己的子进程，而子进程B的行为等同于它进行了一次 PTRACE_TRACEME操作。。
	3. gdb进程会发送SIGSTO信号给子进程B。
	4. 子进程B接收到SIGSTOP信号后，就会暂停执行进入TASK_STOPED状态，表示自己准备好被调试了。
	
	最终结果：gdb程序是父进程，被调试程序是子进程，子进程的所有信号都被父进程gdb来接管，并且父进程gdb可查看、修改子进程的内部信息，包括：堆栈、寄存器等。
#### 系统调用ptrace
	long ptrace(enum __ptrace_request request, pid_t pid, void *addr, void *data);
	ptrace，Linux内核提供的一个用于进程跟踪的系统调用。
	使用ptrace:
		1. gdb可以读取被调试进程c的指令空间、数据空间、堆栈和寄存器的值.
		2. 接管了进程c的所有信号。根据信号的属性来决定：在继续运行目标程序时是否把当前截获的信号转交给目标程序。
### break断点的实现
	1. 将断点位置的汇编代码存储到断点链表中。
	2. 在断点位置替换一字节，修改成中断指令INT3。
	3. 当执行断点位置INT3指令，于是操作系统就发送一个SIGTRAP信号给被调试进程。
		i. gdb会首先接收到这SIGTRAP个信号。
			a. 注册信号处理函数，调用sigaction系统调用，把函数指针传给 PCB 表。
			b. 当从内核态准备返回用户态的时候，调用 do_sigaction 函数，修改用户堆栈和内核堆栈，让内核态返回后执行的是信号处理函数。
		ii. 把断点位置"INT3"替换为断点链表中原来的代码。
		iii. 把 PC 指针回退一步, 即指向原来的断点位置。
### 指令next的实现
	在计算出需要停止的那一行，插入INT3指令，从而让程序停止下来的，类似break断点的实现。
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