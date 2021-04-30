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
	1. 设置config  
		make config : 配置需要编译哪些模块。  遍历选择所要编译的内核特性。
		make allyesconfig：配置所有可编译的内核特性。
		可手动配置.config文件。
	2. 编译
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

## net
### drivers
#### RTL8169
	Realtek公司生产的一款千兆以太网卡.
## modules
### BPF
	Berkeley Packet Filter, 主要涉及包过滤这一部分，分析网络流量。
	他在数据链路层上提供了接口。BPF支持包过滤，允许用户态进程提供一个过滤程序，此程序指定了我们想要接收到那种包。 常见的一些抓包工具的实现都与其有关，比如tcpdump工具。
	这种机制避免了拷贝一些不需要的包从内核态到进程，极大的提升了性能。
	seccomp沙箱，在设计时也借用了BPF的思想。

## slab allocator
	Linux 所使用的 slab 分配器的基础是 Jeff Bonwick 为 SunOS 操作系统首次引入的一种算法。Jeff 的分配器是围绕对象缓存进行的。在内核中，会为有限的对象集（例如文件描述符和其他常见结构）分配大量内存。Jeff 发现对内核中普通对象进行初始化所需的时间超过了对其进行分配和释放所需的时间。因此他的结论是不应该将内存释放回一个全局的内存池，而是将内存保持为针对特定目的而初始化的状态。
### slab allocator 的主要结构
![](images/main_structure_of_slabAllocator.jpg "")
	这是slab 结构的高层组织结构。
	1. 在最高层是 cache_chain，这是一个 slab 缓存的链接列表。 这对于 best-fit 算法非常有用，可以用来查找最适合所需要的分配大小的缓存（遍历列表）。 cache_chain 的每个元素都是一个 kmem_cache 结构的引用（称为一个 cache）。它定义了一个要管理的给定大小的对象池。
	2. 每个缓存 kmem_cache 都包含了一个 slabs 列表，这是一段连续的内存块（通常都是页面）。存在 3 种 slab：
	* slabs_full
	* slabs_partial
	* slabs_empty
		slabs_empty 列表中的 slab 是进行回收（reaping）的主要备选对象。正是通过此过程，slab 所使用的内存被返回给操作系统供其他用户使用。
	3. slabs 列表中的每个 slab 都是一个连续的内存块（一个或多个连续页），它们被划分成一个个对象。这些对象是从特定缓存中进行分配和释放的基本元素。注意 ** slab 是 slab 分配器进行操作的最小分配单位 **，因此如果需要对 slab 进行扩展，这也就是所扩展的最小值。通常来说，每个 slab 被分配为多个对象。
	4. 由于对象是从 slab 中进行分配和释放的，因此单个 slab 可以在 slab 列表之间进行移动。例如，当一个 slab 中的所有对象都被使用完时，就从 slabs_partial 列表中移动到 slabs_full 列表中。当一个 slab 完全被分配并且有对象被释放后，就从 slabs_full 列表中移动到 slabs_partial 列表中。当所有对象都被释放之后，就从 slabs_partial 列表移动到 slabs_empty列表中。

## PCI Bus Subsystem
### /proc/pid/pagemap
	允许用户空间程序检查页表和相关信息。例如，将虚拟地址转化为物理地址。
	对于每个虚拟地址，用一个64位的值表示它。 格式如下所示：
	* Bits 0-54  page frame number (PFN) if present
	* Bits 0-4   swap type if swapped
    * Bits 5-54  swap offset if swapped
    * Bit  55    pte is soft-dirty (see Documentation/vm/soft-dirty.txt), which helps to track which pages a task writes to.
    * Bit  56    page exclusively mapped (since 4.2)
    * Bits 57-60 zero
    * Bit  61    page is file-page or shared-anon (since 3.5)
    * Bit  62    page swapped
    * Bit  63    page present 

### Accessing PCI device resources through sysfs
	sysfs, usually mounted at /sys, provides access to PCI resources on platforms that support it.	
```
|-- 0000:17:00.0
|   |-- class
|   |-- config
|   |-- device
|   |-- enable
|   |-- irq
|   |-- local_cpus
|   |-- remove
|   |-- resource
|   |-- resource0
|   |-- resource1
|   |-- resource2
|   |-- revision
|   |-- rom
|   |-- subsystem_device
|   |-- subsystem_vendor
|   `-- vendor
```
	the domain number is 0000 and the bus number is 17 (both values are in hex). This bus contains a single function device in slot 0. 其中文件的含义如下。
	* resource : PCI resource host addresses (ascii, ro)
	* resource0..N : PCI resource N, if present (binary, mmap, rw1)

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
