# 堆  
  
堆是程序虚拟地址空间的一块连续的线性区域，由低地址向高地址方向增长。管理堆的那部分程序为堆管理器，处于用户程序和内核中间  
。linux中glic堆进行实现，堆分配器是ptmalloc,在glibc-2.3.x之后，glibc中集成了ptmalloc2。ptmalloc2主要是通过malloc/free函数来分配和释放内存块。Linux有这样的一个基本内存管理思想，只有当真正访问一个地址的时候，系统才会建立虚拟页面与物理页面的映射关系。  
  
## malloc(size_t n)  
  
现在的实现支持多线程。    
n=0, 返回当前系统允许的堆的最小内存块。    
n为负数，一般申请失败。    
申请后内存空间free后，并不会归还给系统，可以看/proc/program id/maps    
  
## free(void* p)  
  
释放由p所指向的内存块(malloc or realloc 分配的)    
当p为空指针，不执行任何操作。    
当p被释放，再释放会出现乱七八糟的结果，即 double free.    
除了被mallopt(控制内存分配的函数)禁用，当释放很大的内存空间时，程序会将这些内存空间归还给系统，减少程序所使用的内存空间。    
  
## 内存分配背后的系统调用  
  
对于堆操作，brk(调整heap的结尾,操作系统提供的接口), sbrk函数(sbrp(0)获取heap开始地址, glibc 提供的接口)，通过增加brk的大小来向操作系统申请内存，start_brk以及brk指向data/bss的结尾(跟ASLR有关)。初始时，start_brk = brk.    
program break = brk = heap ?    
  
mmap(), malloc会使用mmap来创建独立的匿名映射段(申请填充0，仅被调用程序使用)。    
munmap()去除 mmap()创建的内存空间。    
/proc/pragram id/maps: 保存当前进程的内存空间信息。    
  
arena: 程序像操作系统申请很小的内存，但是为了方便，操作系统把很大的内存分配给程序，这样的一块连续的内存区域为arena.    
  
# 堆相关的数据结构  
  
## malloc_chunk  
堆申请的内存为chunk。这块内存在 ptmalloc 内部用 malloc_chunk 结构体来表示。无论一个 chunk 的大小如何，处于分配状态还是释放状态，它们都使用一个统一的结构。  
```  
truct malloc_chunk {  
  
  INTERNAL_SIZE_T      prev_size;  /* Size of previous chunk (if free).  */  
  INTERNAL_SIZE_T      size;       /* Size in bytes, including overhead. */  
  
  struct malloc_chunk* fd;         /* double links -- used only if free. */  
  struct malloc_chunk* bk;  
  
  /* Only used for large blocks: pointer to next larger size.  */  
  struct malloc_chunk* fd_nextsize; /* double links -- used only if free. */  
  struct malloc_chunk* bk_nextsize;  
}  
```  
prev_size: 如果物理相邻的前一个地址chunk是空闲的，该字段记录它的大小(包含chunk头)。该字段可以用来存储相邻的前一个chunk的数据。这里的前一chunk指的是较低地址的chunk。   
size: 该chunk的大小，大小必须是2*SIZE_SZ的整数倍(SIZE_SZ = sizeof(size_t),该字段的低三个bit对chunk的大小无影响。从高到低分别表示,NON_MAIN_ARENA(是否属于主线程), IS_MAPPED, PREV_INUSE(记录前一个chunk是否分配,堆中第一个分配的内存块为1)。    
fd: 分配状态是用户数据。空闲状态是指向下一个(不一定物理相邻)空闲chunk。    
bk: 指向上一个(不一定物理空闲)空闲chunk     
fd_nextsize,bk_nextsize: 只有chunk空闲的时候才使用，用于较大的chunk。    
chunk header = prev_size + size, user data = 之后的变量。每次malloc申请到的地址是指向user data的地址。    
```  
chunk-> +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+  
        |             Size of previous chunk, if unallocated (P clear)  |  
        +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+  
`head:' |             Size of chunk, in bytes                     |A|0|P|  
  mem-> +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+  
        |             Forward pointer to next chunk in list             |  
        +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+  
        |             Back pointer to previous chunk in list            |  
        +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+  
        |             Unused space (may be 0 bytes long)                .  
        .                                                               .  
 next   .                                                               |  
chunk-> +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+  
`foot:' |             Size of chunk, in bytes                           |  
        +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+  
        |             Size of next chunk, in bytes                |A|0|0|  
        +-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+  
```  
---> 如果chunk free时，会有两个位置记录其大小，本身的size会记录，他后面的chunk会记录。    
一般情况下，物理相邻的两个空闲 chunk 会被合并为一个 chunk 。堆管理器会通过 prev_size 字段以及 size 字段合并两个物理相邻的空闲 chunk 块。    
**chunk和mem的转换**
```
/* conversion from malloc headers to user pointers, and back */
#define chunk2mem(p) ((void *) ((char *) (p) + 2 * SIZE_SZ))
#define mem2chunk(mem) ((mchunkptr)((char *) (mem) -2 * SIZE_SZ))
```
**最小的chunk大小**
```
/* The smallest possible chunk */
#define MIN_CHUNK_SIZE (offsetof(struct malloc_chunk, fd_nextsize)) //offsetof 函数计算出 fd_nextsize 在 malloc_chunk 中的偏移
```
## bin
ptmallocc对空闲的chunk进行管理。根据空闲的chunk的大小以及使用状态分成四类，fast bins,small bins,large bins, unsorted bins.
对于small bins，large bins，unsorted bin 来说，ptmalloc 将它们维护在同一个数组中。这些 bin 对应的数据结构在 malloc_state中
```   
/* Fastbins */  
mfastbinptr fastbinsY[NFASTBINS]; 
#define NBINS 128
/* Normal bins packed as described above */
mchunkptr bins[ NBINS * 2 - 2 ];
```
mchunkptr就是malloc_chunk指针，当作chunk的fd和bk指针来操作。
数组中的 bin 依次介绍如下
- 1. 第一个为 unsorted bin，字如其面，这里面的 chunk 没有进行排序，存储的 chunk 比较杂。
- 2. 索引从 2 到 63 的 bin 称为 small bin，同一个 small bin 链表中的 chunk 的大小相同。两个相邻索引的 small bin 链表中的 chunk 大小相差的字节数为 2 个机器字长(size_t)，即 32 位相差 8 字节，64 位相差 16 字节。
- 3. small bins 后面的 bin 被称作 large bins。large bins 中的每一个 bin 都包含一定范围内的 chunk，其中的 chunk 按 fd 指针的顺序从大到小排列。相同大小的 chunk 同样按照最近使用顺序排列。
ptmalloc 为了提高分配的速度，会把一些小的 chunk 先放到 fast bins 的容器内。而且，fastbin 容器中的 chunk 的使用标记总是被置位的，所以不满足上面的原则。

### fast bin
glibc 采用单向链表对每个 fast bin 进行组织，并且每个 bin 采取 LIFO 策略，最近释放的 chunk 会更早地被分配.ptmalloc 默认情况下会调用 set_max_fast(s) 将全局变量 global_max_fast 设置为 DEFAULT_MXFAST，也就是设置 fast bins 中 chunk 的最大值。

### small bin
每个 chunk 的大小与其所在的 bin 的 index 的关系为：chunk_size = 2 * SIZE_SZ *index
small bins 中一共有 62 个循环双向链表，每个链表中存储的 chunk 大小都一致.每个链表都有链表头结点，这样可以方便对于链表内部结点的管理。此外，small bins 中每个 bin 对应的链表采用 FIFO 的规则，所以同一个链表中先被释放的 chunk 会先被分配出去。fast bin 中的 chunk 是有可能被放到 small bin 中去的。	

### large bin
每个 bin 中的 chunk 的大小不一致，而是处于一定区间范围内。
