## 内核源码树的根目录：  

arch 特定架构的源码  
block  
crypto 加密API  
Documentation 内核源码文档  
drivers 设备驱动程序  
firmware 使用某些程序所需的设备固件  
init  内核引导和初始化  
ipc  进程间通信代码  
kenel 像调度程序这样的核心子程序  
lib 通用内核函数  
mm 内存管理和VM  
samples 示例代码  
script 编译内核所用的脚本  
sound 语音子系统  
tools 在linux开发中有用的工具  
virt  虚拟化基础结构  
CREDITS 开发者列表  
Makefile 基本内核的Makefile  

## 内核编译：  

make config : 配置需要编译哪些模块。  
2.6以后的内核，代码之间的依赖关系会自动维护(无需make dep命令)。  
make -jn  不正确的依赖可能导致编译过程出错。  

## 安装内核：  

make modules_install 将编译的模块安装到 /lib/modules  
编译时也会在内核代码树的根目录创建一个system.map符号对照表，将内核符号和它们的起始地址对应起来，调试用。  

## 内核开发：  

内核不能访问c库和c标准头文件，因为c库太大且低效，所以重新实现。  
必须使用GNU C  
缺乏内存保护机制。  
难以执行浮点运算。  
注意同步和并发。  
考虑移植的重要  
容量小且固定的内核栈，用户空间的栈巨大, x86的栈编译时配置大小，进程的内核栈的大小一般是两页。x86三级页表，默认4k页长。  
内核的内存不分页  
    
## 头文件：  

include/*  
arch/<architecture>/include/asm  
    
printk() 可以指定优先级，如 KERN_ERR宏  
    
## 进程：  

进程退出执行后被设置为僵死状态，直到父进程执行wait()或waitpid()  
task == 进程  
x86(寄存器少）: thread_info 结构位于内核栈的尾端分配，少一个寄存器，便于用偏移量访问，所以它必须是task_strcut的第一个元素。  
进程状态切换： fork()创建一个进程，进入TASK_RUNNNING ,经过schedule()函数调用context_switch() ，进入 TASK_RUNNING  
-> do_exit() 结束  
-> 优先级高任务抢占， 进入 就绪状态 TASK_RUNNING  
-> 等待某种特定事件 ，进入TASK_INTERRUPTIBLE 或 TASK_UNINTERRUPTIBLE ，满足条件后 进入就绪状态。  
x86 current_thread_info: sp & ~(thread_size - 1)  

### 进程创建  
由fork和exec两个函数实现。  
写时拷贝, 父进程和子进程共享同一个拷贝，只有在需要写入的时候，数据才会被复制。也就是资源在写之前是以只读的方式共享。  
#### fork  
fork的实际开销就是复制整个父进程的页表和给字进程创建唯一的进程描述符。一般fork后马上exec。  

## 进程家族树：  

所有进程都pid = 1的 init进程的后代.init 读取 initscript并执行其他的相关的程序。init进城的进程描述符由init_task静态分分配。  
进程task_struct都有一个parent, list子进程。任务队列是一个双向的循环的的链表。list_entry(),  
   
## security  
security.c中 security_ops 指向哪个就是使用哪个安全模块(security,smash,tomoyo).  

## 杂项：  
- powerpc: IBM基于RISC的现代微处理器。  
- ppc: 有足够多的寄存器。  
-  __builtin_expect() 是 GCC (version >= 2.96）提供给程序员使用的，目的是将分支转移的信息提供给编译器，这样编译器可以对代码进行优化，以减少指令跳转带来的性能下降。通过这种方式，编译器在编译过程中，会将可能性更大的代码紧跟着起面的代码，从而减少指令跳转带来的性能上的下降。  
```
#define likely(x) __builtin_expect(!!(x), 1) //x很可能为真       
#define unlikely(x) __builtin_expect(!!(x), 0) //x很可能为假
```
- EFI ：和BIOS一样，用于启动过程中完成硬件初始化。UEFI具有安全启动, 主板根据TPM记录硬件签名对各硬件判断，只有符合认证的硬件驱动才会被加载.  

# linux0.11  
## document link  
* 实验环境搭建与调试： https://blog.csdn.net/longintchar/article/details/79685055  
