#堆

堆是程序虚拟地址空间的一块连续的线性区域，由低地址向高地址方向增长。管理堆的那部分程序为堆管理器，处于用户程序和内核中间
。linux中glic堆进行实现，堆分配器是ptmalloc,在glibc-2.3.x之后，glibc中集成了ptmalloc2。ptmalloc2主要是通过malloc/free函数来分配和释放内存块。Linux有这样的一个基本内存管理思想，只有当真正访问一个地址的时候，系统才会建立虚拟页面与物理页面的映射关系。

##malloc(size_t n)

现在的实现支持多线程。
n=0, 返回当前系统允许的堆的最小内存块。
n为负数，一般申请失败。
申请后内存空间free后，并不会归还给系统，可以看/proc/program id/maps

##free(void* p)

释放由p所指向的内存块(malloc or realloc 分配的)
当p为空指针，不执行任何操作。
当p被释放，再释放会出现乱七八糟的结果，即 double free.
除了被mallopt(控制内存分配的函数)禁用，当释放很大的内存空间时，程序会将这些内存空间归还给系统，减少程序所使用的内存空间。

##内存分配背后的系统调用

对于堆操作，brk(调整heap的结尾,操作系统提供的接口), sbrk函数(sbrp(0)获取heap开始地址, glibc 提供的接口)，通过增加brk的大小来向操作系统申请内存，start_brk以及brk指向data/bss的结尾(跟ASLR有关)。初始时，start_brk = brk.
program break = brk = heap ?

mmap(), malloc会使用mmap来创建独立的匿名映射段(申请填充0，仅被调用程序使用)。
munmap()去除 mmap()创建的内存空间。
/proc/pragram id/maps: 保存当前进程的内存空间信息。

arena: 程序像操作系统申请很小的内存，但是为了方便，操作系统把很大的内存分配给程序，这样的一块连续的内存区域为arena.

#堆相关的数据结构

##malloc_chunk
