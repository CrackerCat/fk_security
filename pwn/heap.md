# 堆  
	堆是程序虚拟地址空间的一块连续的线性区域，由低地址向高地址方向增长。管理堆的那部分程序为堆管理器，处于用户程序和内核中间.  
	linux中glic堆进行实现，堆分配器是ptmalloc,在glibc-2.3.x之后，glibc中集成了ptmalloc2。ptmalloc2主要是通过malloc/free函数来分配和释放内存块。Linux有这样的一个基本内存管理思想，只有当真正访问一个地址的时候，系统才会建立虚拟页面与物理页面的映射关系。  
	堆是从内存地址向内存高址增长的。栈是从内存高地址向低地址增长。栈位于进程较高的位置，堆位于进程较低的位置。  
    
## malloc(size_t n)  
	* 现在的实现支持多线程。  
	* n=0, 返回当前系统允许的堆的最小内存块。  
	* n为负数，一般申请失败。  
	* 申请后内存空间free后，并不会归还给系统，可以看/proc/program id/maps  
	* 申请按8/16字节对齐.
	* 该函数真正调用的是 __libc_malloc 函数。 __libc_malloc 函数只是用来简单封装 _int_malloc 函数。_int_malloc 才是申请内存块的核心。
		* __libc_malloc的流程
			1. 判断__malloc_hook钩子。
			2. 寻找一个arena来试图分配内存。
			3. 调用_int_malloc 函数去申请对应的内存。
				* 它根据用户申请的内存块大小以及相应大小 chunk 通常使用的频度（fastbin chunk, small chunk, large chunk），依次实现了不同的分配方法。
				* 它由小到大依次检查不同的 bin 中是否有相应的空闲块可以满足用户请求的内存。
				* 当所有的空闲 chunk 都无法满足时，它会考虑 top chunk。
				* 当 top chunk 也无法满足时，堆分配器才会进行内存块申请。
			4. 如果分配失败的话，ptmalloc 会尝试再去寻找一个可用的 arena，并分配内存。
			5. 如果申请到了 arena，那么在退出之前还得解锁。
			6. 判断目前的状态是否满足以下条件
				* 要么没有申请到内存
				* 要么是 mmap 的内存
				* 要么申请到的内存必须在其所分配的arena中
			7. 最后返回内存。
### fastbin malloc
	申请的大小需跟fastbin中的堆块的大小是一致的，即fastbin index得一致，比如0x77和0x70是一致的，否则报错。
### unsorted malloc
```
        while ((victim = unsorted_chunks(av)->bk) != unsorted_chunks(av)) {
            bck = victim->bk;
            if (__builtin_expect(chunksize_nomask(victim) <= 2 * SIZE_SZ, 0) ||
                __builtin_expect(chunksize_nomask(victim) > av->system_mem, 0))
                malloc_printerr(check_action, "malloc(): memory corruption",
                                chunk2mem(victim), av);
            size = chunksize(victim);

            /*
               If a small request, try to use last remainder if it is the
               only chunk in unsorted bin.  This helps promote locality for
               runs of consecutive small requests. This is the only
               exception to best-fit, and applies only when there is
               no exact fit for a small chunk.
             */
            if (in_smallbin_range(nb) && bck == unsorted_chunks(av) &&
                victim == av->last_remainder &&
                (unsigned long) (size) > (unsigned long) (nb + MINSIZE)) {
                ....
            }

            /* remove from unsorted list */
            unsorted_chunks(av)->bk = bck;
            bck->fd                 = unsorted_chunks(av);
```
	从上述unsoredbin malloc代码可以得知：
		1. 首先检查unsortedbin的bk字段(victim)是否指向自身，即检查是否为空。
		2. 检查victim的大小是否满足.(2*SIZE_SZ, av->system_mem]
		3. 检查大小是否符合smallbin,最后一个chunk的前一个chunk是否是unsortedbin，victim是否是av->last_remainder, size是否合法，则走另一个流程。
		4. remove最后一个chunk。
### smallbin malloc
```
/* 
     If a small request, check regular bin.  Since these "smallbins" 
     hold one size each, no searching within bins is necessary. 
     (For a large request, we need to wait until unsorted chunks are 
     processed to find best fit. But for small ones, fits are exact 
     anyway, so we can check now, which is faster.) 
   */  
  
  if (in_smallbin_range (nb))  
    {  
      idx = smallbin_index (nb);  
      bin = bin_at (av, idx);  
  
      if ((victim = last (bin)) != bin) //取该索引对应的small bin中最后一个chunk  
        {  
          bck = victim->bk;  //获取倒数第二个chunk  
      if (__glibc_unlikely (bck->fd != victim)) //检查双向链表完整性  
        malloc_printerr ("malloc(): smallbin double linked list corrupted");  
          set_inuse_bit_at_offset (victim, nb);  
          bin->bk = bck; //将victim从small bin的链表中卸下  
          bck->fd = bin;  
  
          if (av != &main_arena)  
        set_non_main_arena (victim);  
          check_malloced_chunk (av, victim, nb);  
#if USE_TCACHE  
      /* While we're here, if we see other chunks of the same size, 
         stash them in the tcache.  */  
      size_t tc_idx = csize2tidx (nb); //获取对应size的tcache索引  
      if (tcache && tc_idx < mp_.tcache_bins) //如果该索引在tcache bin范围  
        {  
          mchunkptr tc_victim;  
  
          /* While bin not empty and tcache not full, copy chunks over.  */  
          while (tcache->counts[tc_idx] < mp_.tcache_count  //当tcache bin不为空并且没满，并且small bin不为空，则依次取最后一个chunk插入到tcache bin里  
             && (tc_victim = last (bin)) != bin)  
        {  
          if (tc_victim != 0)  
            {  
              bck = tc_victim->bk;  
              set_inuse_bit_at_offset (tc_victim, nb);  
              if (av != &main_arena)  
            set_non_main_arena (tc_victim);  
              bin->bk = bck; //将当前chunk从small bin里卸下  
              bck->fd = bin;  
                      //放入tcache bin里  
              tcache_put (tc_victim, tc_idx);  
                }  
        }  
        }  
#endif  
          void *p = chunk2mem (victim);  
          alloc_perturb (p, bytes);  
          return p;  
        }  
    }
```
	从2.27上述smallbin malloc代码可知:
		1. 检查申请大小。
		2. 检查最后一个元素的完整性，即它的bck->fd是不是victim。
		3. 移除最后一个元素。
		若支持tcache，
		4. 检查tcache是否为空指针 并且 idx是不是小于mp_.tcache.bins。
		5. 检查相应idx的tcache是否满 并且 最后一个元素是否是smallbin, 条件满足则全放入tcache。
		6. 返回指针。
---
  
## calloc  
  与 malloc 的区别是 calloc 在分配后会自动进行清空，这对于某些信息泄露漏洞的利用来说是致命的。  
  calloc 分配堆块时不从 tcache bin 中选取.

---
  
## realloc  
  realloc 函数可以身兼 malloc 和 free 两个函数的功能。  
* 当 realloc(ptr,size) 的 size 不等于 ptr 的 size 时  
* 如果申请 size > 原来 size  
  * 如果 chunk 与 top chunk 相邻，直接扩展这个 chunk 到新 size 大小  
  * 如果 chunk 与 top chunk 不相邻，相当于 free(ptr),malloc(new_size)  
* 如果申请 size < 原来 size  
  * 如果相差不足以容得下一个最小 chunk(64 位下 32 个字节，32 位下 16 个字节)，则保持不变  
  * 如果相差可以容得下一个最小 chunk，则切割原 chunk 为两部分，free 掉后一部分  
* 当 realloc(ptr,size) 的 size 等于 0 时，相当于 free(ptr)  
* 当 realloc(ptr,size) 的 size 等于 ptr 的 size，不进行任何操作  
  
---
## free(void* p)  
	释放由p所指向的内存块(malloc or realloc 分配的)  
	当p为空指针，不执行任何操作。  
	当p被释放，再释放会出现乱七八糟的结果，即 double free.  
	除了被mallopt(控制内存分配的函数)禁用，当释放很大的内存空间时，程序会将这些内存空间归还给系统，减少程序所使用的内存空间。  
	注意:如果在 free 过程中发现 free 的 chunk 前后有空闲的 chunk ,则会触发 unlink 操作和堆块合并, 合并后再加入 unsorted bin。
```c
//unlink:一个从双向链表删除节点的操作,此处的fd、bk指的是显式空闲链表bins中的前一个块和后一个块
FD = P->fd
BK = P->bk
FD->bk =BK
BK->fd =FD
```

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
从上述代码看到，free函数可以使用用户自定义的函数(钩子),所以我们可以修改__free_hook指针，mem是free释放的参数地址.(—__free_hook劫持)。
同理，malloc同样有一个__malloc_hook.malloc_hook-0x23是一个“固定用法”,一般用这个地址。


### unlink双链表冲突检测校验
  glibc-2.19中加入以下安全机制。
```
// fd bk
if (__builtin_expect (FD->bk != P || BK->fd != P, 0))                      \
  malloc_printerr (check_action, "corrupted double-linked list", P, AV);  \
```

  绕过思路一：
    1. 程序中存在一个全局变量,其地址是ptr,ptr指向伪造的chunk的地址, 即*ptr=P。
    2. *ptr指向的堆内存可由用户控制。
  若具备以上条件，攻击者可伪造一个空闲chunk以满足这个安全机制，其中fd=ptr - size(ptr)*3, bk=ptr - size(ptr)*2。
```math
P->fd->bk=*(ptr - size(ptr)*3 + size(ptr)*3) = *ptr = P
P->bk->fd=*(ptr - size(ptr)*2 + size(ptr)*2) = *ptr = P

```
   unlink后,P->fd->bk = P->BK <=> *ptr = ptr - size(ptr)*2; P->bk->fd = P->fd <=> *ptr = ptr - size(ptr)*3. 
   *ptr->'a'*size(ptr)*3 + free@got表项地址, 即 **ptr = 'a'*size(ptr)*3 + free@got表项地址 => *ptr = free@got表项地址 => *ptr 指向 free@got
   最后，使用ptr就可以修改got表。

--- 
## 内存分配背后的系统调用  
    
	对于堆操作，brk(调整heap的结尾,操作系统提供的接口), sbrk函数(sbrp(0)获取heap开始地址, glibc 提供的接口)，通过增加brk的大小来向操作系统申请内存，start_brk以及brk指向data/bss的结尾(跟ASLR有关)。初始时，start_brk = brk.  
	program break = brk = heap ?  
	malloc申请的空间小时用brk分配，空间大时用mmap分配。  
    
	mmap(), malloc会使用mmap来创建独立的匿名映射段(申请填充0，仅被调用程序使用)。  
	munmap()去除 mmap()创建的内存空间。  
	/proc/pragram id/maps: 保存当前进程的内存空间信息。  
    
	arena: 程序像操作系统申请很小的内存，但是为了方便，操作系统把很大的内存分配给程序，这样的一块连续的内存区域为arena. 一个线程申请的1个/多个堆包含很多的信息：二进制位信息，多个malloc_chunk信息等这些堆需要东西来进行管理，那么Arena就是来管理线程中的这些堆的
		* 一个线程只有一个arnea，并且这些线程的arnea都是独立的不是相同的
		* 主线程的arnea称为“main_arena”。子线程的arnea称为“thread_arena” 
	struct malloc_state（Arena的实现）。
		* glibc的中arnea就是用这个结构体表示的
		* 其中包含很多的信息：各种bins的信息，top chunk以及最后一个剩余chunk等
		* 

---   
## 堆相关的数据结构  
    
### malloc_chunk  
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
/*将用户请求的大小转换成实际分配的大小,SIZE_SZ是下一个chunk的prev_size域的空间复用*/
//MALLOC_ALIGN_MASK = 2 * SIZE_SZ -1
#define request2size(req)                                                      \
    (((req) + SIZE_SZ + MALLOC_ALIGN_MASK < MINSIZE)?MINSIZE					   \
		:(req) + SIZE_SZ + MALLOC_ALIGN_MASK&~MALLOC_ALIGN_MASK                \
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
一般情况下，物理相邻的两个空闲 chunk 会被合并为一个 chunk 。堆管理器会通过 prev_size 字段以及 size 字段合并两个物理相邻的空闲 chunk 块。      
/* conversion from malloc headers to user pointers, and back */  
**最小的chunk大小**  
```
### heap_info  
	程序刚开始执行时，每个线程是没有 heap 区域的。当其申请内存时，就需要一个结构来记录对应的信息(描述堆的基本信息)，而 heap_info 的作用就是这个.该数据结构是专门为从 Memory Mapping Segment 处申请的内存准备的，即为非主线程准备的。  
```   
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
```

### malloc_state  
	该结构用于管理堆，记录每个 arena 当前申请的内存的具体状态。  
	无论是 thread arena 还是 main arena，它们都只有一个 malloc state 结构。由于 thread 的 arena 可能有多个，malloc state 结构会在最新申请的 arena 中。  
	注意，main arena 的 malloc_state 并不是 heap segment 的一部分，而是一个全局变量，存储在 libc.so 的数据段。
```c  
struct malloc_state {  
/* Serialize access.  */  
__libc_lock_define(, mutex);  
/* Serialize access.  */  
/* Flags (formerly in max_fast).  */  
int flags;  
/* Fastbins */  
mfastbinptr fastbinsY[ NFASTBINS ];  
/* Fastbins */  
/* Base of the topmost chunk -- not otherwise kept in a bin */  
mchunkptr top;    
/* The remainder from the most recent split of a small request */  
mchunkptr last_remainder;    
/* Normal bins packed as described above */  
mchunkptr bins[ NBINS * 2 - 2 ];    
/* Bitmap of bins, help to speed up the process of determinating if a given bin is definitely empty.*/  
/*ptmalloc 用一个 bit 来标识某一个 bin 中是否包含空闲 chunk 。*/
unsigned int binmap[ BINMAPSIZE ];    
/* Linked list, points to the next arena */  
struct malloc_state *next;    
/* Linked list for free arenas.  Access to this field is serialized  
by free_list_lock in arena.c.  */  
struct malloc_state *next_free;    
/* Number of threads attached to this arena.  0 if the arena is on  
the free list.  Access to this field is serialized by  
free_list_lock in arena.c.  */  
INTERNAL_SIZE_T attached_threads;  
/* Memory allocated from the system in this arena.  */  
INTERNAL_SIZE_T system_mem;  
INTERNAL_SIZE_T max_system_mem;  
}；
```

## bin  
	ptmallocc对空闲的chunk进行管理。根据空闲的chunk的大小以及使用状态分成四类，fast bins,small bins,large bins, unsorted bins.  
	对于small bins，large bins，unsorted bin 来说，ptmalloc 将它们维护在同一个数组中。这些 bin 对应的数据结构在 malloc_state中。  
	/* Fastbins */  
	mfastbinptr fastbinsY[NFASTBINS];  
	\# define NBINS 128  
	mchunkptr就是malloc_chunk指针，当作chunk的fd和bk指针来操作。  
	数组中的 bin 依次介绍如下  
	- 1. 第一个为 unsorted bin，字如其面，这里面的 chunk 没有进行排序，存储的 chunk 比较杂。  
	- 2. 索引从 2 到 63 的 bin 称为 small bin，同一个 small bin 链表中的 chunk 的大小相同。两个相邻索引的 small bin 链表中的 chunk 大小相差的字节数为 2 个机器字长(size_t)，即 32 位相差 8 字节，64 位相差 16 字节。  
	- 3. small bins 后面的 bin 被称作 large bins。large bins 中的每一个 bin 都包含一定范围内的 chunk，其中的 chunk 按 fd 指针的顺序从大到小排列。相同大小的 chunk 同样按照最近使用顺序排列。  
	ptmalloc 为了提高分配的速度，会把一些小的 chunk 先放到 fast bins 的容器内。而且，fastbin 容器中的 chunk 的使用标记总是被置位的，所以不满足上面的原则。    
### Tcache
	Tcache的引入是从Glibc2.26开始的, 是一个为了内存分配速度而存在的机制，当size不大（这个程度后面讲）堆块free后，不会直接进入各种bin，而是进入tcache，如果下次需要该大小内存，直接讲tcache分配出去，跟fastbin蛮像的，但是其size的范围比fastbin大多了，他有64个bin链数组(按size_sz*2增长)，也就是(64+1)*size_sz*2(>64*size_sz*2)，在64位系统中就是0x410大小。也就是说，在64位情况下，tcache可以接受0x20~0x410大小的堆块。
#### Tcache 重要结构体及函数
* tcache_entry struct

```
* We overlay this structure on the user-data portion of a chunk when the chunk is stored in the per-thread cache.  */
typedef struct tcache_entry
{
  struct tcache_entry *next;
} tcache_entry;
```
* tcache_perthread_struct struct

```
/* There is one of these for each thread, which contains the per-thread cache (hence "tcache_perthread_struct").  Keeping overall size low is mildly important.  Note that COUNTS and ENTRIES are redundant (we could have just counted the linked list each time), this is for performance reasons.  */
typedef struct tcache_perthread_struct
{
  char counts[TCACHE_MAX_BINS];
  tcache_entry *entries[TCACHE_MAX_BINS];
} tcache_perthread_struct;

static __thread tcache_perthread_struct *tcache = NULL;
```

* tcache_get() 和 tcache_put()

```c
static void *
tcache_get (size_t tc_idx)
{
  tcache_entry *e = tcache->entries[tc_idx];
  assert (tc_idx < TCACHE_MAX_BINS);
  assert (tcache->entries[tc_idx] > 0);
  tcache->entries[tc_idx] = e->next;
  --(tcache->counts[tc_idx]);
  return (void *) e;
}

static void
tcache_put (mchunkptr chunk, size_t tc_idx)
{
  tcache_entry *e = (tcache_entry *) chunk2mem (chunk);
  assert (tc_idx < TCACHE_MAX_BINS);
  e->next = tcache->entries[tc_idx];
  tcache->entries[tc_idx] = e;
  ++(tcache->counts[tc_idx]);
}
```
	这两个函数会在函数 _int_free 和 __libc_malloc 的开头被调用，其中 tcache_put 当所请求的分配大小不大于0x408并且当给定大小的 tcache bin 未满时调用。一个 tcache bin(某一个大小) 中的最大块数mp_.tcache_count是7, 超过7就不放入tcache中。
	在 tcache_get 中，仅仅检查了 tc_idx ，此外，我们可以将 tcache 当作一个类似于 fastbin 的单独链表，只是它的 check，并没有 fastbin 那么复杂，连size域都没检查，仅仅检查 tcache->entries[tc_idx] = e->next;
#### 内存释放与分配
##### 内存释放
	在 free 函数的最先处理部分，首先是检查释放块是否页对齐及前后堆块的释放情况，便优先放入 tcache 结构中。
##### 内存分配
	在内存分配的 malloc 函数中有多处，会将内存块移入 tcache 中。
	（1）首先，申请的内存块符合 fastbin 大小时并且在 fastbin 内找到可用的空闲块时，会把该 fastbin 链上的其他内存块放入 tcache 中。
	（2）其次，申请的内存块符合 smallbin 大小时并且在 smallbin 内找到可用的空闲块时，会把该 smallbin 链上的其他内存块放入 tcache 中。
	（3）当在 unsorted bin 链上循环处理时，当找到大小合适的链时，并不直接返回，而是先放到 tcache 中，继续处理。
##### tcache 取出
	在内存申请的开始部分，首先会判断申请大小块，在 tcache 是否存在，如果存在就直接从 tcache 中摘取，否则再使用_int_malloc 分配。 
	在循环处理 unsorted bin 内存块时，如果达到放入 unsorted bin 块最大数量，会立即返回。默认是 0，即不存在上限。
	在循环处理 unsorted bin 内存块后，如果之前曾放入过 tcache 块，则会取出一个并返回。

### fast bin  
	glibc 采用单向链表对每个 fast bin 进行组织，并且每个 bin 采取 LIFO 策略(头插法），最近释放的 chunk 会更早地被分配.ptmalloc 默认情况下会调用 set_max_fast(s) 将全局变量 global_max_fast 设置为 DEFAULT_MXFAST，也就是设置 fast bins 中 chunk 的最大值。  
	\# define DEFAULT_MXFAST (64 * SIZE_SZ / 4) ->  64 * SIZE_SZ/4 - SIZE_SZ*2 是最大长度, 64bit=0x70 32bit=0x38  
### small bin  
	chunk size 小于 512 byte  
	每个 chunk 的大小与其所在的 bin 的 index 的关系为：chunk_size = 2 * SIZE_SZ *index。  
	small bins 中一共有 62 个循环双向链表(索引从2-63)，每个链表中存储的 chunk 大小都一致.每个链表都有链表头结点，这样可以方便对于链表内部结点的管理。此外，small bins 中每个 bin 对应的链表采用 FIFO 的规则，所以同一个链表中先被释放的 chunk 会先被分配出去。两个相邻索引的 small bin 链表中的 chunk 大小相差的字节数为2个机器字长。   
	fastbin 与 small bin 中 chunk 的大小会有很大一部分重合，但是fast bin 中的 chunk 是有可能被放到 small bin 中去的。
### large bin  
	large bins 中一共包括 63 个 bin。每个 bin 中的 chunk 的大小不一致，而是处于一定区间范围内，其中的chunk 按 fd 指针的顺序从大到小排列。相同大小的chunk同样按照最近使用顺序排列。这 63 个 bin 被分成了 6 组，每组 bin 中的 chunk 大小之间的公差一致。 
### unsorted bin  
	unsorted bin 可以视为空闲 chunk 回归其所属 bin 之前的缓冲区。它只有一个链表，处于bin数组下标1处。进行内存分配查找时先在fastbins，small bins中查找，之后会在unsorted bin中进行查找。如果取出来的 chunk 大小刚好满足，就会直接返回给用户，否则整理unsorted bin中所有的chunk到bins中对应的bin中。  
	在头部插入新的节点。使用过程中，遍历顺序是FIFO。  
	unsorted bin 中的空闲 chunk 处于乱序状态，主要有两个来源:  
	1. 当一个较大的 chunk 被分割成两半后，如果剩下的部分大于 MINSIZE，就会被放到 unsorted bin 中。  
	2. 释放一个不属于 fast bin 的 chunk，并且该 chunk 不和 top chunk 紧邻时，该 chunk 会被首先放到 unsorted bin 中; 如果该chunk和 top chunk紧邻时，它会和top chunk 合并。
	3. 当进行 malloc_consolidate 时，可能会把合并后的 chunk 放到 unsorted bin 中，如果不是和 top chunk 近邻的话。
	fastbin 范围的 chunk 释放后会被置入 fastbin 链表中，而不处于这个范围的 chunk 被释放后会被置于 unsorted bin 链表中。    
### top chunk  
	程序第一次进行 malloc 的时候，heap 会被分为两块，一块给用户，剩下的那块就是 top chunk。 top chunk 就是处于当前堆的物理地址最高的 chunk。它的作用在于当所有的 bin 都无法满足用户请求的大小时，如果其大小不小于指定的大小，就进行分配，并将剩下的部分作为新的 top chunk。否则，就对 heap 进行扩展后再进行分配。在 main arena 中通过 sbrk 扩展 heap，而在 thread arena 中通过 mmap 分配新的 heap。top chunk 的 prev_inuse 比特位始终为 1。  
	初始情况下，我们可以将 unsorted chunk 作为 top chunk。  
	对应于 malloc_state 中的 top。    
### last remainder  
	分割之后的剩余部分称之为 last remainder chunk，unsort bin 也会存这一块。top chunk 分割剩下的部分不会作为 last remainer.  
	对应于 malloc_state 中的 last_remainder。  

## heap overflow  
	堆溢出是指程序向某个堆块中写入的字节数超过了堆块本身可使用的字节数.因而导致了数据溢出，并覆盖到物理相邻的高地址的下一个堆块。  
	覆盖临近chunk的prev_size,size,chunk content.  
	利用前提:  
	- 程序向堆上写入数据。  
	- 写入的数据大小没有被良好地控制。  
  
## off by one  
	程序向缓冲区中写入时，写入的字节数超过了这个缓冲区本身所申请的字节数并且只越界了一个字节.  
	x86_64是小端覆盖。  

## chunk extend and overlapping(malloc)  
	ptmalloc 通过 chunk header 的数据判断 chunk 的使用情况和对 chunk 的前后块进行定位.chunk extend 就是通过*控制 size 和 pre_size 域*来实现跨越块操作从而导致 overlapping 的。  类似也有 chunk shrink.  
	可以控制 chunk 中的内容。如果 chunk 存在字符串指针、函数指针等，就可以利用这些指针来进行信息泄漏和控制执行流程。  
	- 对inuse fastbin 进行extend  
	ptmalloc 通过 chunk header 的数据判断 chunk 的使用情况和对 chunk 的前后块进行定位.chunk extend 就是通过*控制 size 和 pre_size 域*来实现跨越块操作从而导致 overlapping 的。  类似也有 chunk shrink.  
	可以控制 chunk 中的内容。如果 chunk 存在字符串指针、函数指针等，就可以利用这些指针来进行信息泄漏和控制执行流程。  
	- 对inuse fastbin 进行extend  
	可以控制 chunk 中的内容。如果 chunk 存在字符串指针、函数指针等，就可以利用这些指针来进行信息泄漏和控制执行流程。  

	- 对inuse fastbin 进行extend
```c  
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
```

- inuse smallbin 进行 extend  
```
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
```

- 对free的small bin 进行extend  
```
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
```

- 通过extend 前向overlapping  
通过修改 pre_inuse 域和 pre_size 域实现合并前面的块.  
前向 extend 利用了 smallbin 的 unlink 机制，通过修改 pre_size 域可以跨越多个 chunk 进行合并实现 overlapping。  

- 通过extend 前向overlapping  
通过修改 pre_inuse 域和 pre_size 域实现合并前面的块.  
```
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
```

## exploitation
- 堆中存有地址，可以覆盖成我们想要的地址，泄露信息。 e.g. babyfengshui

## 保护机制
### Safe Linking
  2.32中引入的新的保护机制。也可以参考[聊聊glibc 2.32 malloc新增的保護機制-Safe Linking](https://publicki.top/2020/08/25/SoSafeMinePool/[https://medium.com/@ktecv2000/%E8%81%8A%E8%81%8Aglibc-2-32-malloc%E6%96%B0%E5%A2%9E%E7%9A%84%E4%BF%9D%E8%AD%B7%E6%A9%9F%E5%88%B6-safe-linking-9fb763466773](https://medium.com/@ktecv2000/%E8%81%8A%E8%81%8Aglibc-2-32-malloc%E6%96%B0%E5%A2%9E%E7%9A%84%E4%BF%9D%E8%AD%B7%E6%A9%9F%E5%88%B6-safe-linking-9fb763466773 "libc.2.32")
```c
/* Caller must ensure that we know tc_idx is valid and there's room
   for more chunks.  */
static __always_inline void
tcache_put (mchunkptr chunk, size_t tc_idx)
{
  tcache_entry *e = (tcache_entry *) chunk2mem (chunk);

  /* Mark this chunk as "in the tcache" so the test in _int_free will
     detect a double free.  */
  e->key = tcache;

  e->next = PROTECT_PTR (&e->next, tcache->entries[tc_idx]);
  tcache->entries[tc_idx] = e;
  ++(tcache->counts[tc_idx]);
}

/* Caller must ensure that we know tc_idx is valid and there's
   available chunks to remove.  */
static __always_inline void *
tcache_get (size_t tc_idx)
{
  tcache_entry *e = tcache->entries[tc_idx];
  if (__glibc_unlikely (!aligned_OK (e)))
    malloc_printerr ("malloc(): unaligned tcache chunk detected");
  tcache->entries[tc_idx] = REVEAL_PTR (e->next);
  --(tcache->counts[tc_idx]);
  e->key = NULL;
  return (void *) e;
}
// 参考https://publicki.top/2020/08/25/SoSafeMinePool/#more
```

---

## UAF
	1. 内存块释放后，被置为0但又被使用。
	2. 内存块释放后，未被置为0，但是下一次使用前没有代码对这块内存进行修改。 e.g. double free
	3. 内存块释放后，未被置为0，但是下一次使用前有代码对这块内存进行修改。 
### 利用原理
	内存块释放后被重新分配给新的对象，从而实现内存块共用。

### exploitation
	1. 修改堆块代码，用这块空间进行正常操作。

### reference
	1. PPT：20191114-UAF漏洞检测及利用


## fastbin attack
	指所有基于 fastbin 机制的漏洞利用方法, 有如下常见的漏洞。
	* Fastbin Double Free
	* House of Spirit
	* Alloc to Stack
	* Arbitrary Alloc
	前两种主要漏洞侧重于利用 free 函数释放真的 chunk 或伪造的 chunk，然后再次申请 chunk 进行攻击，后两种侧重于故意修改 fd 指针，直接利用 malloc 申请指定位置 chunk 进行攻击。
### fastbin_double_free
	当一个内存被释放之后再次被释放，就是Free()了同一块内存多次，其精髓在于多次分配可以从 fastbin 链表中取出同一个堆块，相当于多个指针指向同一个堆块，结合堆块的数据内容可以实现类似于类型混淆 (type confused) 的效果。
####前提
	存在堆溢出、use-after-free等能控制chunk内容的漏洞。
#### 原理
	Fastbin Double Free 能够成功利用主要有两部分的原因：
	1. fastbin 的堆块被释放后 next_chunk 的 pre_inuse 位不会被清空
	2. fastbin 在执行 free 的时候仅验证了 main_arena 直接指向的块，即fastbin链表指针头部的块。对于链表后面的块，并没有进行验证。通俗的讲就是当我们申请的一块chunk被释放后，它将以单链的形式被串在fastbin中，然后会有一个fast指针指向最后一个链上来了的chunk，当下一个chunk被释放后，将被链在上一个chunk的前面，fast指针向前移动.

#### exploitation
	1. add(0);add(1) : 创建两个chunk
	2. del(0);del(1);del(0) : double free ->>   fastbin头 -> 0<->1
	3. add(2,"修改chunk0的fd"); : fastbin头->1->0->"修改后的地址“ 
	chunk0的fd指向的chunk得满足fastbin的chunk规则，因为_int_malloc会对欲分配位置的 size 域进行验证，如果其 size 与当前 fastbin 链表应有 size 不符就会抛出异常（memory corruption (fast)）
```
/* offset 2 to use otherwise unindexable first 2 bins 
*/   
//求在fastbinsY中的index  
#define fastbin_index(sz) ((((unsigned int) (sz)) >> (SIZE_SZ == 8 ? 4 : 3)) - 2)  
so, 0x7f-> index=7-2=5                                                                                                                                                                                                                                                                                             

//验证申请的chunk与fastbinsY中的chunk是不是一致的，也就是判断index是不是相等。
if (__builtin_expect (fastbin_index (chunksize (victim)) != idx, 0))        
{    
	errstr = "malloc(): memory corruption (fast)";   
errout:
	malloc_printerr (check_action, errstr, chunk2mem (victim), av);       
	return NULL;
}                                                                                                                                                                                                                                                                                                                                                                   
```
---
### House Of Spirit
	该技术的核心在于在目标位置处伪造 fastbin chunk，并将其释放，从而达到分配指定地址的 chunk 的目的。
#### 前提
	要想构造 fastbin fake chunk，并且将其释放时，可以将其放入到对应的 fastbin 链表中，需要绕过一些必要的检测：
	// 通用
	* fake chunk 的 ISMMAP 位不能为1，因为 free 时，如果是 mmap 的 chunk，会单独处理。
	* fake chunk 地址需要对齐， MALLOC_ALIGN_MASK=MAX (2 * sizeof(INTERNAL_SIZE_T)-1,__alignof__ (long double)-1)
	//fastbin
	* fake chunk 的 size 大小需要满足对应的 fastbin 的需求，同时也得对齐。
	* fake chunk 的 next chunk 的大小不能小于 2 * SIZE_SZ，同时也不能大于av->system_mem 。
	* fake chunk 对应的 fastbin 链表头部不能是该 fake chunk，即不能构成 double free 的情况。
#### summary
	想要使用该技术分配 chunk 到指定地址，其实并不需要修改指定地址的任何内容，关键是要能够修改指定地址的前后的内容使其可以绕过对应的检测。

---
### Alloc to Stack
	该技术的核心点在于劫持 fastbin 链表中 chunk 的 fd 指针，把 fd 指针指向我们想要分配的栈上，从而实现控制栈中的一些关键数据，比如返回地址等。
#### stack chunk
	把 fake_chunk 置于栈中称为 stack_chunk.

---
### Arbitrary Alloc
	只要满足目标地址存在合法的 size 域（这个 size 域是构造的，还是自然存在的都无妨），我们可以把 chunk 分配到任意的可写内存中，比如bss、heap、data、stack等等。
#### summary
	可以利用字节错位等方法来绕过 size 域的检验，实现任意地址分配 chunk，最后的效果也就相当于任意地址写任意值。
---
 
## unsortedbin attack
	释放离散的chunk，才会在unsortedbin中。
### exploitation
	1. 创建chunk0, 大小保证free后能够放进unsortedbin,紧接着创建chunk1。
	2. free(chunk0), 此时chunk0的fd,bk都指向unsortedbin。
		若存在堆溢出或uaf，能够修改chunk0的bk。
		a. 那么我们将bk修改为target_addr - sizeof(ptr)*2。
		b. 我们再申请一个和chunk0相同大小的空间，目标地址(bk)就会写入unsortedbin的地址，即达到写任意地址的目的，但修改的值不受我们可控，唯一可以知道得是，这个值很大。
### summary
	1. 通过这种攻击修改循环的次数来使得程序可以执行多次循环。	
	2. 修改 heap 中的 global_max_fast 来使得更大的 chunk 可以被视为 fast bin，这样我们就可以去执行一些 fast bin attack了。

## Tcache attack
### Tcache poisoning
	**在2.27的环境下是可以直接做到任意地址写的**，这一点非常nice, 这种利用方法，在也被叫做tcache  poisoning。同时，在double free领域，Tcache可以直接double free，而不需要像fastbin那样，需要和链上上一个堆块不一样,也就是下面这个样子。
	通过覆盖 tcache 中的 next，不需要伪造任何 chunk 结构即可实现 malloc 到任何地址。
	可以看出 tcache posioning 这种方法和 fastbin attack 类似，但因为没有 size 的限制有了更大的利用范围。
```
/*
 heap0 ----> heap1 ----> heap0 (fastbin YES)
 heap0 ----> heap0 (fastbin NO)
 heap0 ----> heap0 (Tcahce YES)
 */
```
	还有一点不同，就是在Tcache中，fd指向的并不是堆头，而是堆内容，这一点也是需要我们注意的。
#### leak libc
	常用方法如下：
	1. 申请8个大堆块，释放8个，这里堆块大小，大于fastbin范围，就是填满tcache（tcachebin默认7)。
		a. 2.27版本 可以是释放同一个的堆块。
	2. 有double free的情况下，连续free 8次同一个堆块，这里堆块大小，大于fastbin范围。
	3. 申请大堆块，大于0x410。
	4. 修改堆上的Tcache管理结构
#### Tcache double free
	* 2.27的环境
		实现任意地址写。
		1. add(0);
		2. free(0);free(0)
		3. add(0, target_addr);add(0, target_addr);add(0, any_value);
### tcache dup
	类似 fastbin dup，不过利用的是 tcache_put() 的不严谨.
```
static __always_inline void
tcache_put (mchunkptr chunk, size_t tc_idx)
{
  tcache_entry *e = (tcache_entry *) chunk2mem (chunk);
  assert (tc_idx < TCACHE_MAX_BINS);
  e->next = tcache->entries[tc_idx];
  tcache->entries[tc_idx] = e;
  ++(tcache->counts[tc_idx]);
}
```
	可以看出，tcache_put() 的检查也可以忽略不计（甚至没有对 tcache->counts[tc_idx] 的检查），大幅提高性能的同时安全性也下降了很多。
	因为没有任何检查，所以我们可以对同一个 chunk 多次 free(double free)，造成 cycliced list。
#### tcache check
	2.29后加入Tcache 的 double free 的 check。
##### 主要的修改
	tcache的tcache_entry增加 struct tcache_perthread_struct *key域 检测double free.
```
tcache_put{
...
+	e->key = tcache;
...
}

tcache_get{
...
+	e->key = NULL;
...
}

_int_free{
...
+    /* Check to see if it's already in the tcache.  */
+    tcache_entry *e = (tcache_entry *) chunk2mem (p); // next=fd, key=bk.
+
+    /* This test succeeds on double free.  However, we don't 100%
+       trust it (it also matches random payload data at a 1 in
+       2^<size_t> chance), so verify it's not an unlikely coincidence
+       before aborting.  */
+    if (__glibc_unlikely (e->key == tcache && tcache))
+      {
+       tcache_entry *tmp;
+       LIBC_PROBE (memory_tcache_double_free, 2, e, tc_idx);
+       for (tmp = tcache->entries[tc_idx];
+            tmp;
+            tmp = tmp->next)
+         if (tmp == e)
+           malloc_printerr ("free(): double free detected in tcache 2");
+       /* If we get here, it was a coincidence.  We've wasted a few
+          cycles, but don't abort.  */
+      }
+
...
}
```
##### bypass 思路
	新增保护主要还是用到e->key这个属性，因此绕过想绕过检测进行double free，这里也是入手点。绕过思路有以下两个：
	1. 	如果有UAF漏洞或堆溢出，可以修改e->key为空，或者其他非tcache_perthread_struct的地址。这样可以直接绕过_int_free里面第一个if判断。不过如果UAF或堆溢出能直接修改chunk的fd的话，根本就不需要用到double free了。
	2. 	利用堆溢出，修改chunk的size，最差的情况至少要做到off by null。留意到_int_free里面判断当前chunk是否已存在tcache的地方，它是根据chunk的大小去查指定的tcache链，由于我们修改了chunk的size，查找tcache链时并不会找到该chunk，满足free的条件。虽然double free的chunk不在同一个tcache链中，不过不影响我们使用tcache poisoning进行攻击。
	3. 	绕过tcache double free ，使用 fastbin double free.

### tcache perthread corruption
	tcache_perthread_struct 是整个 tcache 的管理结构，如果能控制这个结构体，那么无论我们 malloc 的 size 是多少，地址都是可控的。
```
tcache_    +------------+<---------------------------+
\perthread |......      |                            |
\_struct   +------------+                            |
           |counts[i]   |                            |
           +------------+                            |
           |......      |          +----------+      |
           +------------+          |header    |      |
           |entries[i]  |--------->+----------+      |
           +------------+          |target    |------+
           |......      |          +----------+
           |            |          |          |
           +------------+          +----------+
```
	两次 malloc 后我们就返回了 tcache_prethread_struct 的地址，就可以控制整个 tcache 了。
	因为 tcache_prethread_struct 也在堆上，因此这种方法一般只需要 partial overwrite 就可以达到目的。

### tcache house of spirit
	在栈上伪造一个chunk，然后把它给free进tcache中，再malloc，就可以控制栈上的空间。

### smallbin unlink
	在 smallbin 中包含有空闲块的时候，会同时将同大小的其他空闲块，放入 tcache 中，此时也会出现解链操作，但相比于 unlink 宏，缺少了链完整性校验( __glibc_unlikely (bck->fd != victim) )。因此，原本 unlink 操作在该条件下也可以使用。
#### tcache stashing unlink attack
这种攻击利用的是 tcache bin 有剩余 (数量小于 `TCACHE_MAX_BINS` ) 时，同大小的 small bin 会放进 tcache 中 (这种情况可以用 `calloc` 分配同大小堆块触发，因为 calloc 分配堆块时不从 tcache bin 中选取)。在获取到一个 `smallbin` 中的一个 chunk 后, 如果 tcache 仍有足够空闲位置，会从smallbin的最后一个元素开始将剩余的 small bin 链入 tcache ，在这个过程中只对第一个 bin 进行了完整性检查( __glibc_unlikely (bck->fd != victim) )，后面的堆块的检查缺失。当攻击者可以写一个 small bin 的 bk 指针时，其可以在任意地址上写一个 libc 地址 (类似 `unsorted bin attack` 的效果)，另外可以分配任意地址。 构造得当的情况下，tcache插入处于任意地址的fake_chunk, tcache分配未检查size。
##### exploitation
###### 前提
	1. 对应大小的tcachebin可继续插入。
	2. smallbin不为空（count可能需要>=2）。
	3. 使用calloc触发攻击。
###### implementation
	smallbin fd: chunk0->chunk1->bin
	smallbin bk: chunk1->chunk0->target_addr->0x0
	tcachebin: chunk2->chunk3->0x0
    -------calloc-------->>>>>>
	tcachebin: target_addr->chunk0->chunk1->chunk2->chunk3->0x0
	其中，target_addr(fake chunk) 的fd = bin.
	-------malloc-------->>>>>>>
	控制target_addr.
	任意地址写固定值（smallbin的地址）
### reference
- https://www.freebuf.com/articles/system/234219.html : 解释性文章。
- http://q1iq.top/GeekPwn%E7%83%AD%E8%BA%AB%E8%B5%9B2020-wp/ ： 题

## fake chunk
- hook-19附近有一个size为0x7f的fakechunk.
- io附近有个0x7f的fakechunk, _IO_2_1_stderr_+160 该处错位能构造。
- 没开pie的got附近有个0x40/0x60的fakechunk.
- bss段构建fakechunk.

---
## other
### dangling pointer
	一些被free但未置0的悬挂指针。
### main_arena
	这是malloc()实现过程中的一个结构体，存储在libc段。
```c
	struct malloc_state {
  /* Serialize access.  */
  mutex_t mutex;
 
  /* Flags (formerly in max_fast).  */
  int flags;
 
#if THREAD_STATS
  /* Statistics for locking.  Only used if THREAD_STATS is defined.  */
  long stat_lock_direct, stat_lock_loop, stat_lock_wait;
#endif
 
  /* Fastbins */
  mfastbinptr      fastbins[NFASTBINS];
 
  /* Base of the topmost chunk -- not otherwise kept in a bin */
  mchunkptr        top;
 
  /* The remainder from the most recent split of a small request */
  mchunkptr        last_remainder;
 
  /* Normal bins packed as described above */
  mchunkptr        bins[NBINS * 2 - 2];
 
  /* Bitmap of bins */
  unsigned int     binmap[BINMAPSIZE];
 
  /* Linked list */
  struct malloc_state *next;
 
  /* Memory allocated from the system in this arena.  */
  INTERNAL_SIZE_T system_mem;
  INTERNAL_SIZE_T max_system_mem;
};
```
#### 利用
	unsorted bins的第一个chunk的bk指向main_arena附近。
	1. 获取bk的值。
	2. 获取main_arena偏移量。 
	3. 获取调试阶段的libc基址。
	4. 它们的差值就是偏移量。

### glibc
- 2.23最经典
- 2.26加入tcache
- 2.29增加tcache限制，几乎不能offbynull,也不能unsortedbin attack
- 2.31修了largebin attack.

### TLS段
	通常会有一些有用的东西会放在 .tls 段， 像是主分配区（main_arena） 的地址， canary （栈保护值） ，还有一个奇怪的栈地址（stack address），它指向栈上的某个地方，每次运行可能不一样，但它具有固定的偏移量。
	默认情况下，当 malloc 或者 new 操作一次性分配大于等于 128KB 的内存时，会使用 mmap 来进行，而在小于 128KB 时，使用的是 brk 的方式。
	使用 malloc 的 mmap方式来分配内存m, 这些页面将放在 .tls 段之前的地址，与.tls段紧挨。

### 使用printf触发malloc和free.
```
#define EXTSIZ 32
enum { WORK_BUFFER_SIZE = 1000 };

if (width >= WORK_BUFFER_SIZE - EXTSIZ)
{
    /* We have to use a special buffer.  */
    size_t needed = ((size_t) width + EXTSIZ) * sizeof (CHAR_T);
    if (__libc_use_alloca (needed))
        workend = (CHAR_T *) alloca (needed) + width + EXTSIZ;
    else
    {
        workstart = (CHAR_T *) malloc (needed);
        if (workstart == NULL)
        {
            done = -1;
            goto all_done;
        }
        workend = workstart + width + EXTSIZ;
    }
}
```
	大多数时候，触发 malloc 和 free 的最小 width 是 65537。
---

### to-do list
- https://www.anquanke.com/post/id/222948 : house-of系列

