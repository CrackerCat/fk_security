# 堆  
    
堆是程序虚拟地址空间的一块连续的线性区域，由低地址向高地址方向增长。管理堆的那部分程序为堆管理器，处于用户程序和内核中间.  
linux中glic堆进行实现，堆分配器是ptmalloc,在glibc-2.3.x之后，glibc中集成了ptmalloc2。ptmalloc2主要是通过malloc/free函数来分配和释放内存块。Linux有这样的一个基本内存管理思想，只有当真正访问一个地址的时候，系统才会建立虚拟页面与物理页面的映射关系。  
堆是从内存地址向内存高址增长的。栈是从内存高地址向低地址增长。栈位于进程较高的位置，堆位于进程较低的位置。  
    
## malloc(size_t n)  
    
现在的实现支持多线程。  
n=0, 返回当前系统允许的堆的最小内存块。  
n为负数，一般申请失败。  
申请后内存空间free后，并不会归还给系统，可以看/proc/program id/maps  
  
## calloc  
与 malloc 的区别是 calloc 在分配后会自动进行清空，这对于某些信息泄露漏洞的利用来说是致命的。  
  
## realloc  
realloc 函数可以身兼 malloc 和 free 两个函数的功能。  
* 当 realloc(ptr,size) 的 size 不等于 ptr 的 size 时  
* 如果申请 size > 原来 size  
如果 chunk 与 top chunk 相邻，直接扩展这个 chunk 到新 size 大小  
如果 chunk 与 top chunk 不相邻，相当于 free(ptr),malloc(new_size)  
* 如果申请 size < 原来 size  
如果相差不足以容得下一个最小 chunk(64 位下 32 个字节，32 位下 16 个字节)，则保持不变  
如果相差可以容得下一个最小 chunk，则切割原 chunk 为两部分，free 掉后一部分  
* 当 realloc(ptr,size) 的 size 等于 0 时，相当于 free(ptr)  
* 当 realloc(ptr,size) 的 size 等于 ptr 的 size，不进行任何操作  
  
    
## free(void* p)  
    
释放由p所指向的内存块(malloc or realloc 分配的)  
当p为空指针，不执行任何操作。  
当p被释放，再释放会出现乱七八糟的结果，即 double free.  
除了被mallopt(控制内存分配的函数)禁用，当释放很大的内存空间时，程序会将这些内存空间归还给系统，减少程序所使用的内存空间。  

### glibc-2.23以下的free的实现  
```
void weak_variable (*__free_hook) (void *__ptr, const void *) = NULL; //glibc中全局变量
void
__libc_free (void *mem)
{
  mstate ar_ptr;
  mchunkptr p;                          /* chunk corresponding to mem */

  void (*hook) (void *, const void *)
    = atomic_forced_read (__free_hook);
  if (__builtin_expect (hook != NULL, 0))
    {
      (*hook)(mem, RETURN_ADDRESS (0));
      return;
    }
```
从上述代码看到，free函数可以使用用户自定义的函数(钩子),所以我们可以修改__free_hook指针，mem是free释放的参数地址.(—__free_hook劫持)  
    
## 内存分配背后的系统调用  
    
对于堆操作，brk(调整heap的结尾,操作系统提供的接口), sbrk函数(sbrp(0)获取heap开始地址, glibc 提供的接口)，通过增加brk的大小来向操作系统申请内存，start_brk以及brk指向data/bss的结尾(跟ASLR有关)。初始时，start_brk = brk.  
program break = brk = heap ?  
malloc申请的空间小时用brk分配，空间大时用mmap分配。  
    
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
prev_size: 如果*物理相邻的前一个地址chunk*是空闲的，该字段记录它的大小(包含chunk头)。*该字段可以用来存储相邻的前一个chunk的数据。*这里的前一chunk指的是较低地址的chunk。  
size: 该chunk的大小，*申请到的大小必须是2*SIZE_SZ的整数倍*(SIZE_SZ = sizeof(size_t),该字段的低三个bit对chunk的大小无影响。从高到低分别表示,NON_MAIN_ARENA(是否不属于主线程), IS_MAPPED, PREV_INUSE(记录前一个chunk是否分配,堆中第一个分配的内存块为1)。  
用户申请的内存大小与 glibc 中实际分配的内存大小size之间的转换:  
```
/* pad request bytes into a usable size -- internal version */
//MALLOC_ALIGN_MASK = 2 * SIZE_SZ -1
#define request2size(req)                                                      \
    (((req) + SIZE_SZ + MALLOC_ALIGN_MASK < MINSIZE)                           \
fd: 分配状态是用户数据。空闲状态是指向下一个(不一定物理相邻)空闲chunk。单行链表仅用fd。  
bk: 指向上一个(不一定物理空闲)空闲chunk  
fd_nextsize,bk_nextsize: 只有chunk空闲的时候才使用，用于较大的chunk。  
chunk header = prev_size + size, user data = 之后的变量。每次malloc申请到的地址是指向user data的地址。  
chunk content  
fd_nextsize,bk_nextsize: 只有chunk空闲的时候才使用，用于较大的chunk。      
chunk header = prev_size + size, user data = 之后的变量。每次malloc申请到的地址是指向user data的地址。      
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
---> 如果chunk free时，会有两个位置记录其大小，本身的size会记录，他后面的chunk会记录。  
一般情况下，物理相邻的两个空闲 chunk 会被合并为一个 chunk 。堆管理器会通过 prev_size 字段以及 size 字段合并两个物理相邻的空闲 chunk 块。  
**chunk和mem的转换**  
---> 如果chunk free时，会有两个位置记录其大小，本身的size会记录，他后面的chunk会记录。  
一般情况下，物理相邻的两个空闲 chunk 会被合并为一个 chunk 。堆管理器会通过 prev_size 字段以及 size 字段合并两个物理相邻的空闲 chunk 块。  
**chunk和mem的转换**  
一般情况下，物理相邻的两个空闲 chunk 会被合并为一个 chunk 。堆管理器会通过 prev_size 字段以及 size 字段合并两个物理相邻的空闲 chunk 块。      
/* conversion from malloc headers to user pointers, and back */  
**最小的chunk大小**  
**最小的chunk大小**  
**最小的chunk大小**  
## bin  
ptmallocc对空闲的chunk进行管理。根据空闲的chunk的大小以及使用状态分成四类，fast bins,small bins,large bins, unsorted bins.  
对于small bins，large bins，unsorted bin 来说，ptmalloc 将它们维护在同一个数组中。这些 bin 对应的数据结构在 malloc_state中  
## bin  
ptmallocc对空闲的chunk进行管理。根据空闲的chunk的大小以及使用状态分成四类，fast bins,small bins,large bins, unsorted bins.  
/* Fastbins */  
mfastbinptr fastbinsY[NFASTBINS];  
# define NBINS 128  
mchunkptr就是malloc_chunk指针，当作chunk的fd和bk指针来操作。  
数组中的 bin 依次介绍如下  
- 1. 第一个为 unsorted bin，字如其面，这里面的 chunk 没有进行排序，存储的 chunk 比较杂。  
- 2. 索引从 2 到 63 的 bin 称为 small bin，同一个 small bin 链表中的 chunk 的大小相同。两个相邻索引的 small bin 链表中的 chunk 大小相差的字节数为 2 个机器字长(size_t)，即 32 位相差 8 字节，64 位相差 16 字节。  
- 3. small bins 后面的 bin 被称作 large bins。large bins 中的每一个 bin 都包含一定范围内的 chunk，其中的 chunk 按 fd 指针的顺序从大到小排列。相同大小的 chunk 同样按照最近使用顺序排列。  
ptmalloc 为了提高分配的速度，会把一些小的 chunk 先放到 fast bins 的容器内。而且，fastbin 容器中的 chunk 的使用标记总是被置位的，所以不满足上面的原则。  
- 2. 索引从 2 到 63 的 bin 称为 small bin，同一个 small bin 链表中的 chunk 的大小相同。两个相邻索引的 small bin 链表中的 chunk 大小相差的字节数为 2 个机器字长(size_t)，即 32 位相差 8 字节，64 位相差 16 字节。  
### fast bin  
glibc 采用单向链表对每个 fast bin 进行组织，并且每个 bin 采取 LIFO 策略，最近释放的 chunk 会更早地被分配.ptmalloc 默认情况下会调用 set_max_fast(s) 将全局变量 global_max_fast 设置为 DEFAULT_MXFAST，也就是设置 fast bins 中 chunk 的最大值。  
# define DEFAULT_MXFAST (64 * SIZE_SZ / 4) ->  64 * SIZE_SZ/4 - SIZE_SZ*2 是最大长度, 64bit=0x70 32bit=0x38  
### small bin  
chunk size 小于 512 byte  
每个 chunk 的大小与其所在的 bin 的 index 的关系为：chunk_size = 2 * SIZE_SZ *index  
small bins 中一共有 62 个循环双向链表，每个链表中存储的 chunk 大小都一致.每个链表都有链表头结点，这样可以方便对于链表内部结点的管理。此外，small bins 中每个 bin 对应的链表采用 FIFO 的规则，所以同一个链表中先被释放的 chunk 会先被分配出去。fast bin 中的 chunk 是有可能被放到 small bin 中去的。  
chunk size 小于 512 byte  
### large bin  
large bins 中一共包括 63 个 bin。每个 bin 中的 chunk 的大小不一致，而是处于一定区间范围内。这 63 个 bin 被分成了 6 组，每组 bin 中的 chunk 大小之间的公差一致。  
### large bin  
### unsorted bin  
unsorted bin 可以视为空闲 chunk 回归其所属 bin 之前的缓冲区。它只有一个链表，处于bin数组下标1处。进行内存分配查找时现在fastbins，small bins中查找，之后会在unsorted bin中进行查找，并整理unsorted bin中所有的chunk到bins中对应的bin中。  
fd指向双向环链表的头节点，bk指向尾节点，在头部插入新的节点。  
使用过程中，遍历顺序是FIFO。  
unsorted bin 可以视为空闲 chunk 回归其所属 bin 之前的缓冲区。它只有一个链表，处于bin数组下标1处。进行内存分配查找时现在fastbins，small bins中查找，之后会在unsorted bin中进行查找，并整理unsorted bin中所有的chunk到bins中对应的bin中。  
unsorted bin 中的空闲 chunk 处于乱序状态，主要有两个来源:  
- 1. 当一个较大的 chunk 被分割成两半后，如果剩下的部分大于 MINSIZE，就会被放到 unsorted bin 中。  
- 2. 释放一个不属于 fast bin 的 chunk，并且该 chunk 不和 top chunk 紧邻时，该 chunk 会被首先放到 unsorted bin 中; 如果该chunk和 top chunk紧邻时，它会和top chunk 合并。关于 top chunk 的解释，请参考下面的介绍。  
fastbin 范围的 chunk 释放后会被置入 fastbin 链表中，而不处于这个范围的 chunk 被释放后会被置于 unsorted bin 链表中  
- 1. 当一个较大的 chunk 被分割成两半后，如果剩下的部分大于 MINSIZE，就会被放到 unsorted bin 中。  
### top chunk  
程序第一次进行 malloc 的时候，heap 会被分为两块，一块给用户，剩下的那块就是 top chunk。 top chunk 就是处于当前堆的物理地址最高的 chunk。它的作用在于当所有的 bin 都无法满足用户请求的大小时，如果其大小不小于指定的大小，就进行分配，并将剩下的部分作为新的 top chunk。否则，就对 heap 进行扩展后再进行分配。在 main arena 中通过 sbrk 扩展 heap，而在 thread arena 中通过 mmap 分配新的 heap。top chunk 的 prev_inuse 比特位始终为 1。  
初始情况下，我们可以将 unsorted chunk 作为 top chunk。  
对应于 malloc_state 中的 top  
程序第一次进行 malloc 的时候，heap 会被分为两块，一块给用户，剩下的那块就是 top chunk。 top chunk 就是处于当前堆的物理地址最高的 chunk。它的作用在于当所有的 bin 都无法满足用户请求的大小时，如果其大小不小于指定的大小，就进行分配，并将剩下的部分作为新的 top chunk。否则，就对 heap 进行扩展后再进行分配。在 main arena 中通过 sbrk 扩展 heap，而在 thread arena 中通过 mmap 分配新的 heap。top chunk 的 prev_inuse 比特位始终为 1。  
### last remainder  
分割之后的剩余部分称之为 last remainder chunk。top chunk 分割剩下的部分不会作为 last remainer.  
对应于 malloc_state 中的 last_remainder  
### last remainder  
分割之后的剩余部分称之为 last remainder chunk。top chunk 分割剩下的部分不会作为 last remainer.  
### heap_info  
程序刚开始执行时，每个线程是没有 heap 区域的。当其申请内存时，就需要一个结构来记录对应的信息(描述堆的基本信息)，而 heap_info 的作用就是这个.该数据结构是专门为从 Memory Mapping Segment 处申请的内存准备的，即为非主线程准备的。  
### heap_info  
### heap_info  
程序刚开始执行时，每个线程是没有 heap 区域的。当其申请内存时，就需要一个结构来记录对应的信息(描述堆的基本信息)，而 heap_info 的作用就是这个.该数据结构是专门为从 Memory Mapping Segment 处申请的内存准备的，即为非主线程准备的。  
### heap_info  
程序刚开始执行时，每个线程是没有 heap 区域的。当其申请内存时，就需要一个结构来记录对应的信息(描述堆的基本信息)，而 heap_info 的作用就是这个.该数据结构是专门为从 Memory Mapping Segment 处申请的内存准备的，即为非主线程准备的。  
### heap_info  
程序刚开始执行时，每个线程是没有 heap 区域的。当其申请内存时，就需要一个结构来记录对应的信息(描述堆的基本信息)，而 heap_info 的作用就是这个.该数据结构是专门为从 Memory Mapping Segment 处申请的内存准备的，即为非主线程准备的。  
### heap_info   
typedef struct _heap_info  
{  
mstate ar_ptr; /* Arena for this heap. */  
struct _heap_info *prev; /* Previous heap. */  
size_t size;   /* Current size in bytes. */  
size_t mprotect_size; /* Size in bytes that has been mprotected  
PROT_READ|PROT_WRITE.  */  
/* Make sure the following data is properly aligned, particularly  
that sizeof (heap_info) + 2 * SIZE_SZ is a multiple of  
MALLOC_ALIGNMENT. */  
### malloc_state  
该结构用于管理堆，记录每个 arena 当前申请的内存的具体状态。  
无论是 thread arena 还是 main arena，它们都只有一个 malloc state 结构。由于 thread 的 arena 可能有多个，malloc state 结构会在最新申请的 arena 中。  
注意，main arena 的 malloc_state 并不是 heap segment 的一部分，而是一个全局变量，存储在 libc.so 的数据段。  
该结构用于管理堆，记录每个 arena 当前申请的内存的具体状态。  
无论是 thread arena 还是 main arena，它们都只有一个 malloc state 结构。由于 thread 的 arena 可能有多个，malloc state 结构会在最新申请的 arena 中。  
struct malloc_state {  
/* Serialize access.  */  
__libc_lock_define(, mutex);  
/* Serialize access.  */  
/* Flags (formerly in max_fast).  */  
int flags;  
/* Flags (formerly in max_fast).  */  
/* Fastbins */  
mfastbinptr fastbinsY[ NFASTBINS ];  
/* Fastbins */  
/* Base of the topmost chunk -- not otherwise kept in a bin */  
mchunkptr top;  
/* Base of the topmost chunk -- not otherwise kept in a bin */  
/* The remainder from the most recent split of a small request */  
mchunkptr last_remainder;  
/* The remainder from the most recent split of a small request */  
/* Normal bins packed as described above */  
mchunkptr bins[ NBINS * 2 - 2 ];  
/* Normal bins packed as described above */  
/* Bitmap of bins, help to speed up the process of determinating if a given bin is definitely empty.*/  
unsigned int binmap[ BINMAPSIZE ];  
/* Bitmap of bins, help to speed up the process of determinating if a given bin is definitely empty.*/  
/* Linked list, points to the next arena */  
struct malloc_state *next;  
/* Linked list, points to the next arena */  
/* Linked list for free arenas.  Access to this field is serialized  
by free_list_lock in arena.c.  */  
struct malloc_state *next_free;  
by free_list_lock in arena.c.  */  
/* Number of threads attached to this arena.  0 if the arena is on  
the free list.  Access to this field is serialized by  
free_list_lock in arena.c.  */  
INTERNAL_SIZE_T attached_threads;  
free_list_lock in arena.c.  */  
/* Memory allocated from the system in this arena.  */  
INTERNAL_SIZE_T system_mem;  
INTERNAL_SIZE_T max_system_mem;  
## heap overflow  
堆溢出是指程序向某个堆块中写入的字节数超过了堆块本身可使用的字节数.因而导致了数据溢出，并覆盖到物理相邻的高地址的下一个堆块。  
覆盖临近chunk的prev_size,size,chunk content.  
利用前提:  
- 程序向堆上写入数据。  
- 写入的数据大小没有被良好地控制。  
利用前提:  
## off by one  
程序向缓冲区中写入时，写入的字节数超过了这个缓冲区本身所申请的字节数并且只越界了一个字节.  
x86_64是小端覆盖。  
## off by one  
## chunk extend and overlapping(malloc)  
ptmalloc 通过 chunk header 的数据判断 chunk 的使用情况和对 chunk 的前后块进行定位.chunk extend 就是通过*控制 size 和 pre_size 域*来实现跨越块操作从而导致 overlapping 的。  类似也有 chunk shrink.  
可以控制 chunk 中的内容。如果 chunk 存在字符串指针、函数指针等，就可以利用这些指针来进行信息泄漏和控制执行流程。  
- 对inuse fastbin 进行extend  
ptmalloc 通过 chunk header 的数据判断 chunk 的使用情况和对 chunk 的前后块进行定位.chunk extend 就是通过*控制 size 和 pre_size 域*来实现跨越块操作从而导致 overlapping 的。  类似也有 chunk shrink.  
可以控制 chunk 中的内容。如果 chunk 存在字符串指针、函数指针等，就可以利用这些指针来进行信息泄漏和控制执行流程。  
- 对inuse fastbin 进行extend  
可以控制 chunk 中的内容。如果 chunk 存在字符串指针、函数指针等，就可以利用这些指针来进行信息泄漏和控制执行流程。  
- 对inuse fastbin 进行extend  
int main(void)  
{  
void *ptr,*ptr1;  
{  
ptr=malloc(0x10);//分配第一个0x10的chunk  
malloc(0x10);//分配第二个0x10的chunk  
ptr=malloc(0x10);//分配第一个0x10的chunk  
*(long long *)((long long)ptr-0x8)=0x41;// 修改第一个块的size域  
    ptr=malloc(0x10);//分配第一个0x10的chunk
free(ptr);  
ptr1=malloc(0x30);// 实现 extend，控制了第二个块的内容  
- inuse smallbin 进行 extend  
- inuse smallbin 进行 extend  
- inuse smallbin 进行 extend  
int main()  
{  
void *ptr,*ptr1;  
{  
ptr=malloc(0x80);//分配第一个 0x80 的chunk1  
malloc(0x10); //分配第二个 0x10 的chunk2  
malloc(0x10); //防止与top chunk合并  
malloc(0x10); //分配第二个 0x10 的chunk2  
*(int *)((int)ptr-0x8)=0xb1;  
free(ptr);  
ptr1=malloc(0xa0);  
- 对free的small bin 进行extend  
- 对free的small bin 进行extend  
- 对free的small bin 进行extend  
- 对free的small bin 进行extend  
- 对free的small bin 进行extend  
- 对free的small bin 进行extend  
- 对free的small bin 进行extend  
- 对free的small bin 进行extend  

int main()  
{  
void *ptr,*ptr1;  
{
ptr=malloc(0x80);//分配第一个0x80的chunk1  
malloc(0x10);//分配第二个0x10的chunk2  
    ptr=malloc(0x80);//分配第一个0x80的chunk1
free(ptr);//首先进行释放，使得chunk1进入unsorted bin ,下一个chunk size的prev_size域置0.  

*(int *)((int)ptr-0x8)=0xb1;  
ptr1=malloc(0xa0);  
- 通过extend 前向overlapping  
通过修改 pre_inuse 域和 pre_size 域实现合并前面的块.  
前向 extend 利用了 smallbin 的 unlink 机制，通过修改 pre_size 域可以跨越多个 chunk 进行合并实现 overlapping。  
- 通过extend 前向overlapping  
通过修改 pre_inuse 域和 pre_size 域实现合并前面的块.  
int main(void)  
{  
void *ptr1,*ptr2,*ptr3,*ptr4;  
ptr1=malloc(128);//smallbin1  
ptr2=malloc(0x10);//fastbin1  
ptr3=malloc(0x10);//fastbin2  
ptr4=malloc(128);//smallbin2  
malloc(0x10);//防止与top合并  
free(ptr1);  
*(int *)((long long)ptr4-0x8)=0x90;//修改pre_inuse域  
*(int *)((long long)ptr4-0x10)=0xd0;//修改pre_size域  
free(ptr4);//unlink进行前向extend  
malloc(0x150);//占位块  
free(ptr4);//unlink进行前向extend  
}  
    free(ptr4);//unlink进行前向extend
}  

}
```

