# linux 堆  
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
				version = 2.23
				a. 将用户的需求转化为实际分配的chunk大小。
				b. 若chunk_size <= max_fast？
					* 是则尝试 fastbin。 从中找用户需要的chunk。
				c. 尝试 smallbin, 判断 chunk_size <= 1024B?
					* 是，查找用户所需的chunk。
				d. 以上都不满足，说明需要一块大的内存。这里需要合并相邻的fastbin，并放入unsortedbin。
				e. 遍历 unsortedbin.
					* 若chunk.bck == unsorted_chunks (av), 大小属于smallbin的范围，这个chunk是last_remainder 并且 chunk的大小大于要分配的大小。
						* 满足这个条件，直接切割此chunk。 (实际测试，chunk>用户需求就切割....)
					* 如果chunk大小刚好与用户需要的一样，则返回它。 否则，根据chunk的大小放入smallbin，largebin中，直到找到合适的chunk。
				f. 遍历完成之后, 进入 large_bin 分配规则。
					* 按照 smallest-first, best-fit 原则，找到合适的chunk。
				g. 若fastbin, bins都不满足。
					* 判断 当前 top chunk 是否满足用户需求。满足则从 top chunk 中划分合适的chunk，否则，主分配区调用sbrk()/mmap以增大top_chunk的大小 (是否大于mmap的阈值); 非主分配区 调用mmap来分配新的sub_heap，增加top_chunk的大小。 （init时, 第一次生成heap空间，是用mmap)
				总结：
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
```
// get index
#define fastbin_index(sz) \
  ((((unsigned int) (sz)) >> (SIZE_SZ == 8 ? 4 : 3)) - 2)
// get pos
#define fastbin(ar_ptr, idx) ((ar_ptr)->fastbinsY[idx])
//arena所对应的malloc_state中fastbins数组相关的定义
mfastbinptr fastbinsY[NFASTBINS]

#define NFASTBINS  (fastbin_index (request2size (MAX_FAST_SIZE)) + 1)
```
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
		3. 检查大小是否符合smallbin,最后一个chunk的前一个chunk是否是unsortedbin，victim是否是av->last_remainder, chunk的size是否大于用户的正常的请求大小，则走另一个流程。
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
### largebin malloc
	当 fast bin、small bin 中的 chunk 都不能满足用户请求 chunk 大小时，就会考虑是不是 large bin。但是，其实在 large bin 中并没有直接去扫描对应 bin 中的 chunk，而是先利用 malloc_consolidate（参见 malloc_state 相关函数） 函数处理 fast bin 中的 chunk，将有可能能够合并的 chunk 先进行合并后放到 unsorted bin 中，不能够合并的就直接放到 unsorted bin 中，然后再在下面的大循环中进行相应的处理。
	注: 这是 ptmalloc 的机制，它会在分配 large chunk 之前对堆中碎片 chunk 进行合并，以便减少堆中的碎片。
```
    /*
       If this is a large request, consolidate fastbins before continuing.
       While it might look excessive to kill all fastbins before
       even seeing if there is space available, this avoids
       fragmentation problems normally associated with fastbins.
       Also, in practice, programs tend to have runs of either small or
       large requests, but less often mixtures, so consolidation is not
       invoked all that often in most programs. And the programs that
       it is called frequently in otherwise tend to fragment.
     */

    else {
        // 获取large bin的下标。
        idx = largebin_index(nb);
        // 如果存在fastbin的话，会处理 fastbin
        if (have_fastchunks(av)) malloc_consolidate(av);
    }
```
#### 大循环 - 遍历 unsorted bin
	1. 按照 FIFO 的方式逐个将 unsorted bin 中的 chunk 取出来
		1.1 当用户请求的一个 small chunk 并且最后剩余块是 unsorted bin 里唯一的块时，最后剩余块会分裂成两块，用户块会返回给用户同时剩余的那个块会添加到 unsorted bin。此外，它还会成为新的一个最后剩余块。
		1.2 从unsortedbin中移除该chunk。
		1.3 若chunk大小和申请大小相同，返回该chunk。
		1.3 如果chunk大小和申请大小不相同的话，放到对应的 bin 中(smallbin or largebin)。
	2. 尝试从 large bin 中分配用户所需的内存。根据大小进行分配，要么全分配要么split。
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
### free的具体步骤
	version = 2.23。 具体步骤如下。
	a. 判断指针是否为空。
	b. 判断所需的chunk是否是mmaped chunk.
		* 是，munmap() 释放。
	c. 判断chunk的位置和大小。
		* 是否属于fastbin的范畴。 是则放入。 
	d. 属于 unsortedbin的范畴。 判断前一个chunk是否空闲。
		* 是，合并。
		* 不是，判断当前chunk的下一个是不是top。
			* 是，和topchunk合并。
			* 否，则判断下一个chunk是否空闲。 若空闲则合并，并放入unsortedbin。
	e. 合并放入unsortedbin中之后，判断 chunk > FASTBIN_CONSOLIDATION_THRESHOLD（默认64KB/128KB)
		* 是，触发fastbin合并，放入unsortedbin。
	f. 判断top是否大于mmap的收缩阈值（默认128KB)
		* 是： 主分配区，回归还一部分给OS; 非主分配区，收缩sub_heap。
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
    1. 程序中存在一个全局变量,其地址是ptr,ptr指向伪造的chunk的地址, 即*ptr=P。 或未开PIE。
    2. *ptr指向的堆内存可由用户控制。
    3. got表可写。
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
	程序可能只是向操作系统申请很小的内存，但是为了方便，操作系统会把很大的内存分配给程序。这样的话，就避免了多次内核态与用户态的切换，提高了程序的效率。我们称这一块连续的内存区域为 arena。此外，我们称由主线程申请的内存为 main_arena。
	无论是 thread arena 还是 main arena，它们都只有一个 malloc state 结构。由于 thread 的 arena 可能有多个，malloc state 结构会在最新申请的 arena 中。 主分配区与非主分配区按环形链表的方式进行管理。 非主分区只能使用进程的mmap映射区域。
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
#### malloc_state 相关函数
##### malloc_init_state
```
/*
   Initialize a malloc_state struct.
   This is called only from within malloc_consolidate, which needs
   be called in the same contexts anyway.  It is never called directly
   outside of malloc_consolidate because some optimizing compilers try
   to inline it at all call points, which turns out not to be an
   optimization at all. (Inlining it in malloc_consolidate is fine though.)
 */

static void malloc_init_state(mstate av) {
    int     i;
    mbinptr bin;

    /* Establish circular links for normal bins */
    for (i = 1; i < NBINS; ++i) {
        bin     = bin_at(av, i);
        bin->fd = bin->bk = bin;
    }

#if MORECORE_CONTIGUOUS
    if (av != &main_arena)
#endif
        set_noncontiguous(av);
    if (av == &main_arena) set_max_fast(DEFAULT_MXFAST);
    // 设置 flags 标记目前没有fast chunk
    av->flags |= FASTCHUNKS_BIT;
    // 就是 unsorted bin
    av->top = initial_top(av);
}
```
##### malloc_consolidate
	该函数主要有两个功能:
	1. 若 fastbin 未初始化，即 global_max_fast 为 0，那就初始化 malloc_state。
		1.1 
```
  } else {
        malloc_init_state(av);
        // 在非调试情况下没有什么用，在调试情况下，做一些检测。
        check_malloc_state(av);
    }
```
	2. 如果已经初始化的话，就合并 fastbin 中的 chunk。 
		2.1 按照 fd 顺序遍历 fastbin 的每一个 bin，将 bin 中的每一个 chunk 合并掉。

	具体工作步骤如下：
	1. 判断fastbin是否初始化，如果未初始化，则进行初始化然后退出。
	2. 按照fastbin由小到大的顺序（0x20 ,0x30 ,0x40这个顺序）合并chunk，每种相同大小的fastbin中chunk的处理顺序是从fastbin->fd开始取，下一个处理的是p->fd，依次类推。
	3. 首先尝试合并pre_chunk。
	4. 然后尝试合并next_chunk：如果next_chunk是top_chunk，则直接合并到top_chunk，然后进行第六步；如果5. next_chunk不是top_chunk，尝试合并。
	5. 将处理完的chunk插入到unsorted bin头部。
	6. 获取下一个空闲的fastbin，回到第二步，直到清空所有fastbin中的chunk，然后退出。

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
	Tcache的引入是从Glibc2.26开始的, 是一个为了内存分配速度而存在的机制，当size不大（这个程度后面讲）堆块free后，不会直接进入各种bin，而是进入tcache，如果下次需要该大小内存，直接将tcache分配出去，跟fastbin蛮像的，但是其size的范围比fastbin大多了，他有64个bin链数组(按size_sz*2增长)，也就是(64+1)*size_sz*2(>64*size_sz*2)，在64位系统中就是0x410大小。也就是说，在64位情况下，tcache可以接受0x20~0x410大小的堆块。
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
	unsorted bin 可以视为空闲 chunk 回归其所属 bin 之前的缓冲区。它只有一个链表，处于bin数组下标1处。进行内存分配查找时先在fastbins，small bins中查找，之后会在unsorted bin中进行查找。 ***如果取出来的 chunk 大小刚好满足，就会直接返回给用户*** ，否则整理unsorted bin中所有的chunk到bins中对应的bin中。
	* 如果unsorted bins上只有一个chunk并且大于待分配的chunk，则进行切割，并且剩余的chunk继续扔回unsorted bins；
	* 如果unsorted bins上有大小和待分配chunk相等的，则返回，并从unsorted bins删除；
	* 如果unsorted bins中的某一chunk大小 属于small bins的范围，则放入small bins的头部；
	* 如果unsorted bins中的某一chunk大小 属于large bins的范围，则找到合适的位置放入。若未分配成功，转入下一步
	  
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
### exploitation
	- off by one: 可以overlopping. eg unsortedbin. 修改 prevsize 和 iuse位，然后free-> merge -> overlap. 往往对 unsortedbin 和 largebin 起作用，因为没有对size域的检查。
		- 增大size域。 
			假定 ver<=2.26（方法是通用的）, V-A-T 三个连续chunk，V有off-by-one漏洞，T是目标chunk，A不属于fastbin的范畴。 攻击流程是 free(A) -> 触发V的off-by-one 以增大 A 的size -> 分配 B chunk 以 重叠 A和T -> 修改B从而change T 的内容。
		- 缩小size域。
			与 增大的场景 类似。
		- 非邻近chunk合并->重叠
			假定 ver<=2.26（方法是通用的）, V-A-T-B 四个连续的chunk，V具有off-by-one漏洞，T是目标chunk，B不属于fastbin的范畴。 攻击流程是 free(B) ->  触发V的off-by-one 以增大 A 的size（=A+T) -> free(A) -> merge(A,T,B) -> 分配 C chunk 以重叠A和T -> 修改C从而change T 的内容
	- uaf : 也可以overlapping.

## 保护机制
### Safe Linking
  对于 tcache, 2.32中引入的新的保护机制。也可以参考[聊聊glibc 2.32 malloc新增的保護機制-Safe Linking](https://publicki.top/2020/08/25/SoSafeMinePool/[https://medium.com/@ktecv2000/%E8%81%8A%E8%81%8Aglibc-2-32-malloc%E6%96%B0%E5%A2%9E%E7%9A%84%E4%BF%9D%E8%AD%B7%E6%A9%9F%E5%88%B6-safe-linking-9fb763466773](https://medium.com/@ktecv2000/%E8%81%8A%E8%81%8Aglibc-2-32-malloc%E6%96%B0%E5%A2%9E%E7%9A%84%E4%BF%9D%E8%AD%B7%E6%A9%9F%E5%88%B6-safe-linking-9fb763466773 "libc.2.32")
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

#define PROTECT_PTR(pos, ptr)
  ((__typeof (ptr)) ((((size_t) pos) >> 12) ^ ((size_t) ptr)))

#define REVEAL_PTR(ptr)
    PROTECT_PTR (&ptr, ptr)

// 参考https://publicki.top/2020/08/25/SoSafeMinePool/#more
```
	对 next 域进行了保护。
##### bypass
	需要去知道一个堆地址才可以。

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
	2. free(chunk0), 此时chunk0的fd,bk都指向unsortedbin，即main_arena+88(这里存着chunk0.fd的地址)。
		若存在堆溢出或uaf，能够修改chunk0的bk。
		a. 那么我们将bk修改为target_addr - sizeof(ptr)*2。 
		b. 我们再申请一个和chunk0相同大小的空间chunk1，目标地址(bk)就会写入unsortedbin的地址，即达到写任意地址的目的，但修改的值不受我们可控，唯一可以知道得是，这个值很大。
		注: 若chunk1 和 chunk0 的大小不一样，会调用malloc_printerr报错，但是修改值成功，触发fsop！ 原因待研究？？
### summary
	1. 通过这种攻击修改循环的次数来使得程序可以执行多次循环。	
	2. 修改 heap 中的 global_max_fast 来使得更大的 chunk 可以被视为 fast bin，这样我们就可以去执行一些 fast bin attack了。
#### 利用global_max_fast进行相关的攻击
	如果可以改写global_max_fast为一个较大的值，然后释放一个较大的堆块时，由于fastbins数组空间是有限的，其相对偏移将会往后覆盖，如果释放堆块的size可控，就可实现往fastbins数组（main_arena）后的任意地址写入所堆块的地址。
	理论上来说只要是main_arena结构体后面的是函数指针或是结构体指针的地址都可以，目前很容易能够预想到的是：
	a. _IO_list_all
	b. stdout
	c. stdin
	d. stderr
	e. __free_hook
	
	解决方案：
	1. 复写前面四个就是使用IO_file攻击那一套方法，伪造结构体来实现任意读任意写或者伪造vtable来实现house of orange攻击。
	2. 复写__free_hook的话则需要一次uaf来修改释放进去的fd改成system或者one gadget，再将堆块申请出来，从而实现将__free_hook改写成system或者one gadget。
#### leak libc
	1. 生成一个unsortedbin，想办法打印。
		1. 对unsoredbin 进行打印。
		2. 从unsortedbin中分割一个chunk，然后进行打印。

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
	5. 当没有打印函数时，修改IO_2_1_stdout的_IO_write_base，这样，当再次调用puts或printf的时候，就会泄露出_IO_write_base~_IO_write_ptr之间的数据。 _flags需要满足 0xfbad2087 | 0x1000 | 0x800 = 0xfbad3887。因此，常将stdout的flags修改成0xfbad1800也可以，将_IO_write_base改小，就可以造成libc和stack的泄漏。
	6. 利用 off by one 泄露。chunk1 在 unsortedbin 里，切割unsortedbin修改chunk1，从而写入unsortedbin 的fd和bk。   
#### Tcache double free
	* 2.27的环境
		实现任意地址写。
		1. add(0);
		2. free(0);free(0)
		3. add(0, target_addr);add(0, target_addr);add(0, any_value);
##### tcache double free的使用
	* 制造overlap.
		1. 泄露heap部分地址。
		2. 使用double free修改fd，使fd指向某个任意堆地址。
		3. 利用 malloc函数 和 <edit>函数 修改目标chunk的prevsize和size。-> 生成任意大小的chunk。
			当 伪造的size >= 0x420 时：
			i. free 该伪造的chunk -> 生成一个任意大小的 unsortedbin -> overlap
			ii. malloc -> 修改堆上任意地址为 libc地址。
			iii. <edit> -> 部分写 tcache的fd指针指向 stdout -> 修改write_base -> stdout信息泄露。 
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
	1. 	如果有**UAF漏洞或堆溢出**，可以修改e->key为空，或者其他非tcache_perthread_struct的地址。这样可以直接绕过_int_free里面第一个if判断。不过如果UAF或堆溢出能直接修改chunk的fd的话，根本就不需要用到double free了。
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

## House of Orange
	House of Orange 核心就是通过漏洞利用获得 free 的效果。
	House of Orange 的核心在于在没有 free 函数的情况下得到一个释放的堆块 (unsorted bin)。 这种操作的原理简单来说是当前堆的 top chunk 尺寸不足以满足申请分配的大小的时候，原来的 top chunk 会被释放并被置入 unsorted bin 中，通过这一点可以在没有 free 函数情况下获取到 unsorted bins。
### 前提
	1. 需要目标漏洞是堆上的漏洞但是特殊之处在于题目中不存在 free 函数或其他释放堆块的函数。
### 原因
	我们假设目前的 top chunk 已经不满足 malloc 的分配需求。 首先我们在程序中的malloc调用会执行到 libc.so 的_int_malloc函数中，在_int_malloc函数中，会依次检验 fastbin、small bins、unsorted bin、large bins 是否可以满足分配要求，因为尺寸问题这些都不符合。接下来_int_malloc函数会试图使用 top chunk，在这里 top chunk 也不能满足分配的要求，因此会执行如下分支。
```
/*
Otherwise, relay to handle system-dependent cases
*/
else {
      void *p = sysmalloc(nb, av);
      if (p != NULL && __builtin_expect (perturb_byte, 0))
        alloc_perturb (p, bytes);
      return p;
}
```
	此时 ptmalloc 已经不能满足用户申请堆内存的操作，需要执行 sysmalloc 来向系统申请更多的空间。 但是对于堆来说有 mmap 和 brk 两种分配方式，我们需要让堆以 brk 的形式拓展，之后原有的 top chunk 会被置于 unsorted bin 中。 要实现 brk 拓展 top chunk，但是要实现这个目的需要绕过一些 libc 中的 check。 首先，malloc 的尺寸不能大于mmp_.mmap_threshold。
```
if ((unsigned long)(nb) >= (unsigned long)(mp_.mmap_threshold) && (mp_.n_mmaps < mp_.n_mmaps_max))
```
	sysmalloc 函数中存在对 top chunk size 的 check, 如下：
```
assert((old_top == initial_top(av) && old_size == 0) ||
     ((unsigned long) (old_size) >= MINSIZE &&
      prev_inuse(old_top) &&
      ((unsigned long)old_end & pagemask) == 0));
```
### 利用方式
	总结一下伪造的 top chunk size 的要求
	1. 伪造的 size 必须要对齐到内存页, 一般4kb
	2. size 要大于 MINSIZE(0x10)
	3. size 要小于之后申请的 chunk size + MINSIZE(0x10)
	4. size 的 prev inuse 位必须为 1
	
	之后原有的 top chunk 就会执行_int_free从而顺利进入 unsorted bin 中。
	往往是配合fsop一起使用，达到利用的目的。

## OOB
	1. off-by-one -> 修改size -> chunk 重叠 -> 修改fd/bk
	2. 多字节 -> 直接修改fd/bk

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
#### 在没有符号表情况下查找 main_arena
	用ida打开libc.so
	1. 通过malloc_trim函数定位
```
int
__malloc_trim (size_t s)
{
  int result = 0;
  if (__malloc_initialized < 0)
    ptmalloc_init ();
  mstate ar_ptr = &main_arena;
  do
    {
      __libc_lock_lock (ar_ptr->mutex);
      result |= mtrim (ar_ptr, s);
      __libc_lock_unlock (ar_ptr->mutex);
      ar_ptr = ar_ptr->next;
    }
  while (ar_ptr != &main_arena);
  return result;
}
```
	2. 通过malloc_hook定位
	3. 解引用一些这个地址malloc_hook,可以发现很多位置。
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

### scanf函数的利用
	When you input a very long string to scanf , it will malloc a buffer to handle it.  当你输入一个很长的字符串，即使使用setbuf()关闭了输入缓冲区，scanf会malloc一个largechunk来处理这个字符串。

### to-do list
- https://www.anquanke.com/post/id/222948 : house-of系列

# windows heap
## 堆管理机制
	1. Nt Heap: 默认使用的内存管理机制
	2. SegmentHeap：Win10中全新的内存管理机制
### Nt Heap Overview
```
                                                                                        (Front-End)
                                                                                        +-------------------------------+
                                                                                        |           ntdll.dll           |
                                                                                        +-------------------------------+
                                                                                        | +---------------------------+ |
                                                                      +-------------->  | |    RtlpLowFragHeapAlloc   | |
                                                                      |                 | |    RtlpLowFragHeapFree    | |
                                                                      |                 | +---------------------------+ |
                                                                      |                 +-------------------------------+
                                                                      |                                | 
                                                                      |                                | 
                                                                      |                     (Back-End) V  
+---------------+         +---------------------+         +-----------------------+         +-----------------------+
| msvcrt140.dll |         |     Kernel32.dll    |         |       ntdll.dll       |         |       ntdll.dll       |
+---------------+         +---------------------+         +-----------------------+         +-----------------------+
| +-----------+ |         | +-----------------+ |         | +-------------------+ |         | +-------------------+ |
| |   malloc  | |  ---->  | |    HeapAlloc    | |  ---->  | |  RtlAllocateHeap  | |  ---->  | |  RtlpAllocateHeap | | 
| |    free   | |         | |    HeapFree     | |         | |    RtlFreeHeap    | |         | |    RtlpFreeHeap   | |
| +-----------+ |         | +-----------------+ |         | +-------------------+ |         | +-------------------+ |
+---------------+         +---------------------+         +-----------------------+         +-----------------------+
                                                                                                       |
                                                                                                       |
                                                                                                       v
                                                                                                 +------------+
                                                                                                 |   Kernel   |
                                                                                                 +------------+
```
	从使用的角度来看，Win10堆可以分为两种：
		a. 进程堆：整个进程共享，都可以使用，会存放在_PEB结构中。
		b. 私有堆：单独创建的，通过HeapCreate返回的句柄hHeap来指定。
	前17个chunk地址间隔固定，是由Back-End直接分配的；而后面的chunk地址开始变得随机，是由Front-End分配的。也就是说，LFH机制是默认开启的，且只有在分配第18个chunk的时候才会开始启用。
	LFH(Low-fragmentation Heap), 低碎片化堆，系统根据需要使用低碎片堆（LFH）来对内存分配的请求提供服务。应用程序并不需要启用LFH堆。帮助减少堆碎片。
## Back-End
### heap结构 _HEAP
	每个HEAP有一个HEAP结构，一个heap结构有多个heap_segment。
	_HEAP结构体作为一个堆管理结构体，存放着许多的metadata，存在于每个堆的开头。其中一些在利用中比较重要的成员：
```
EncodeFlagMask(+0x7C: 4B)：用来标志是否要encode该heap中的chunk头，0x100000表示需要encode（加密状态)。
Encoding(+0x80: 16B)：用来和chunk头进行xor的cookie。
VirtualAllocdBlocks(+0x110: 16B)：一个双向链表的dummy head，存放着Flink和Blink，将VirtualAllocate出来的chunk链接起来。
BlocksIndex(+0x138: 8B)：指向一个_HEAP_LIST_LOOKUP结构（后面会进行介绍）。
FreeList(+0x138 8B)：一个双向链表的dummy head，同样存放着Flink和Blink，将所有的freed chunk给链起来，可以类比于linux ptmalloc下的unsorted bin进行理解；不同的是，它是有序的。
FrontEndHeap(+0x198: 8B)：指向管理Front-End heap的结构体。
FrontEndHeapUsageData(+0x1a8: 8B)：指向一个对应各个大小chunk的数组，该数组记录各种大小chunk使用的次数，达到一定数值的时候就会启用Front-End。
SegmentList(+0x0a4) 堆段的链表，前向指针指向0号堆段，后向指针指向最后一个堆段；
```
### heap Entry结构 _HEAP_ENTRY
	Heap Entry类似于linux下的chunk, 在Win10下就是_HEAP_ENTRY。
	前八个字节保存结构信息，类似chunk头，但是windows为了安全性，对前八个字节进行了加密。
		a. 加密方式：与_HEAP结构0x50偏移处八个字节（Encoding）异或（ps：此处HEAP 结构先理解为arena），可以有效防止堆溢出。
		b. * 0-1 bytes : size; 
		   * 2 byte : Flags; 
			   * 0×01 该块处于占用状态；判断是否是free。
			   * 0×02 该块存在额外描述
			   * 0×04 使用固定模式填充堆块
			   * 0×08 虚拟分配
			   * 0×10 该段最后一个堆块
		   * 3 byte : SmallTagIndex = 前三字节的异或结果（解密后校验等式）; 用于检测堆有效性的cookie，防止off-by-one单字节修改。
		   * 4-5 bytes : PreviousSize;
		   * 6 byte : LFHFlags; 堆块所在段的序号，未验证。
		   * 7 byte : UnusedBytes; 未用到的字节，如分配出来的堆块头部及最后的16字节填充未使用，故第八字节为0x18。
	还有其它重要的成员：
		a. PreviousBlockPrivateData(+0x0: 8B)：由于需要对齐0x10，所以这个地方存放的基本上是前一个堆块的数据，和linux ptmalloc类似，只是处于free状态的时候不会作为prev_size使用。
		b. SegmentOffset(+0xE: 1B)：某些情况下用来找segment。
	另外，用户数据区域在inuse的时候可以进行读写，在freed的时候存放Flink和Blink分别指向前一个和后一个freed chunk；与linux ptmalloc不同的是，这里Flink和Blink指向不是chunk头，而是数据区域。
### _HEAP_VIRTUAL_ALLOC_ENTRY
	维护通过VirtualAlloc分配出来的chunk，可以类比linux ptmalloc中的mmap chunk, 其中一些比较重要的成员：
		a. Entry(+0x0: 16B)：链表的Flink和Blink，分别指向上一个和下一个通过VirtualAlloc分配出来的chunk。
		b. BusyBlock(+0x30: 8B)：与普通的_HEAP_ENTRY头基本一样，不同在于这里的Size是没有使用的size，储存时也没有进行size >> 4的操作，UnusedBytes恒为4。
### _HEAP_LIST_LOOKUP
	_HEAP中BlocksIndex指向的结构体，方便快速寻找到合适的chunk。
### free chunk 的管理
```
+-----------------------+                     BlocksIndex                                 +----------------------+
|000001...1...1000000000|   +----------->+-------------------+                            | PreviousBlockPrivate |         
+-----------------------+   |            |        ...        |                            +----------------------+
           ^                |            +-------------------+                            |  Chunk header (0x70) |
           |                             |     ListHead      |-----+  +------------------>+----------------------+
           +--------+       |            +-------------------+     |  |                   |         Flink        |----+
       _HEAP        +-------+------------|  ListsInUseUlong  |--+  |  |                   +----------------------+    |
+------------------+        |            +-------------------+  |  |  |           +-------|         Blink        |    |
|        ...       |        |            |     ListHint      |  |  |  |           |  +--->+----------------------+    |
+------------------+        |            +-------------------+  |  |  |           |  |    |          ...         |    |
|  EncodeFlagMask  |        |                  +----------------+--+  |           |  |    +----------------------+    |
+------------------+        |                  |                |     |           |  |                                |
|     Encoding     |        |                  V                |     |           |  |    +----------------------+    |
+------------------+        |   +------->+-----------+<---------+-----|-----------+  |    | PreviousBlockPrivate |    |
|        ...       |        |   |        |   Flink   |          |     |              |    +----------------------+
+------------------+        |   |        +-----------+          |     |              |    | Chunk header (0x110) |    |
|   BlocksIndex    |--------+   |        |   Blink   |          |     |     +--------+--->+----------------------+<---+  
+------------------+            |     +->+-----------+<---------+-----|-----|-----+  |    |         Flink        |----+
|        ...       |            |     |        +----------------+     |     |     |  |    +----------------------+    |
+------------------+------------+     |        |                      |     |     |  +----|         Blink        |    |     
|     FreeList     |                  |        V                      |     |     |  +--->+----------------------+    |
+------------------+------------------+  +-----------+                |     |     |  |    |          ...         |    |
|        ...       |                     |    ...    |                |     |     |  |    +----------------------+    |
+------------------+                     +-----------+                |     |     |  |                                |
                              ListHint[7]|   Flink   |----------------+     |     |  |    +----------------------+    |
                                         +-----------+                      |     |  |    | PreviousBlockPrivate |    |
                                         |    ...    |                      |     |  |    +----------------------+    |      
                                         +-----------+                      |     |  |    | Chunk header (0x160) |    |
                             ListHint[17]|   Flink   |----------------------+  +--+--+--->+----------------------+<---+
                                         +-----------+                         |  |  |    |         Flink        |----+
                                         |    ...    |                         |  |  |    +----------------------+    |
                                         +-----------+                         |  |  +----|         Blink        |    |
                             ListHint[22]|   Flink   |-------------------------+  |       +----------------------+    |
                                         +-----------+                            |       |          ...         |    |
                                         |    ...    |                            |       +----------------------+    |
                                         +-----------+                            +-----------------------------------+
```
### 分配机制
#### heap function
	* HeapCreate
		HANDLE HeapCreate(DWORD flOptions ，DWORD dwInitialSize ， DWORD dwMaxmumSize)；
		创建一个只有调用进程才能访问的私有堆。进程从虚拟地址空间里保留出一个连续的块，并且为这个块特定的初始部分分配物理空间。
	* HeapAlloc
		LPVOID HeapAlloc(HANDLE hHeap, DWORD dwFlags, SIZE_T dwBytes);
		从堆中分配空间。
	* HeapFree
#### Allocate (RtlpAllocateHeap)
	根据size分为三种情况:
	1. size <= 0x4000
		基本都会通过RtlpAllocateHeap进行分配：
			a. 首先会看该size对应的FrontEndHeapStatusBitmap是否有启用LFH：
				i. 如果没有则在对应的FrontEndHeapUsageData += 0x21。
				ii. 如果FrontEndHeapUsageData > 0xff00 || FrontEndHeapUsageData & 0x1f > 0x10，那么启用LFH。
			b. 接下来会查看对应的ListHint中是否有值（也就是否有对应size的freed chunk）：
				i. 如果刚好有值，就检查该chunk的Flink是否是同样size的chunk：
					* 若是则将Flink写到对应的ListHint中。
					* 若否则清空对应ListHint，并最后将该chunk从Freelist中unlink出来。
				ii. 如果对应的ListHint中本身就没有值，就从比较大的ListHint中找：
					* 如果找到了，就以上述同样的方式处理该ListLink，并unlink该chunk，之后对其进行切割，剩下的重新放入Freelist，如果可以放进ListHint就会放进去，再encode header。
					* 如果没较大的ListHint也都是空的，那么尝试ExtendedHeap加大堆空间，再从extend出来的chunk拿，接着一样切割，放回ListHIint，encode header。
				iii. 最后将分配到的chunk返回给用户。
	2. 0x4000 < size <= 0xff000
		除了没有LFH相关操作外，其余都和第一种情况一样。
	3. size >= 0xff000
		直接调用ZwAllocateVirtualMemroy进行分配，类似于linux下的mmap直接给一大块地址，并且插入_HEAP->VirtualAllocdBlocks中。
#### Free (RtlpFreeHeap)
	根据size分为两种情况：
	1. size <= 0xff000
		a. 首先会检查地址对齐0x10，并通过unused bytes判断该chunk的状态（为0则是free状态，反之则为inuse状态）。
		b. 如果LFH未开启，会将对应的FrontEndHeapUsageData -= 1（并不是0x21）。
		c. 接着判断前后的chunk是否是freed的状态，如果是的话就将前后的freed chunk从Freelist中unlink下来（与上面的方式一样更新ListHint），再进行合并。
		d. 合并完之后更新Size和PreviousSize，然后查看是不是最前跟最后，是就插入，否则就从ListHint中插入，并且update ListHint；插入时也会对Freelist进行检查（但是此检查不会触发abort，原因在于没有做unlink写入）。
	2. size > 0xff000
		a. 检查该chunk的linked list并从_HEAP->VirtualAllocdBlocks中移除，接着使用RtlpSecMemFreeVirtualMemory将chunk整个munmap掉。
## Front-End
### _LFH_HEAP
	管理Front-End heap的结构体。
	其中比较重要的成员：
		a. Buckets(+0x2A4: 4B * 129)：一个存放129个_HEAP_BUCKET结构体的数组（_HEAP_BUCKET后面会分析），用来寻找配置大小对应到Block大小的阵列结构。
		b. SegmentInfoArrays(+0x4A8: 8B * 129)：一个存放129个_HEAP_LOCAL_SEGMENT_INFO结构体指针的数组（_HEAP_LOCAL_SEGMENT_INFO后面会分析），不同大小对应到不同的_HEAP_LOCAL_SEGMENT_INFO结构体，主要管理对应到的_HEAP_SUBSEGMENT的信息。
		c. LocalData：一个_HEAP_LOCAL_DATA结构体。
#### 整个LFH的结构布局
```
                                                                                                                         _HEAP_USERDATA_HEADER 
                                                                                                                    +-->+---------------------+
                                                                                                                    |   |      SubSegment     |
         _HEAP                                                   _HEAP_BUCKET                                       |   +---------------------+
+---------------------+                               +---->+---------------------+                                 |   |         ...         |
|         ...         |                               |     |      BlockUnits     |                                 |   +---------------------+
+---------------------+              _LFH_HEAP        |     +---------------------+          _HEAP_SUBSEGMENT       |   |    EncodedOffsets   |
|    EncodeFlagMask   |   +-->+---------------------+ |     |       SizeIndex     |    +->+---------------------+   |   +---------------------+
+---------------------+   |   |         ...         | |     +---------------------+  +-+--|      LocalInfo      |   |   |      BusyBitmap     |
|       Encoding      |   |   +---------------------+ |     |         ...         |  | |  +---------------------+   |   +---------------------+
+---------------------+   |   |        Heap         | | +-->+---------------------+  | |  |      UserBlocks     |---+   |         ...         |
|         ...         |   |   +---------------------+ | |                            | |  +---------------------+       +---------------------+
+---------------------+   |   |         ...         | | |                            | |  |         ...         |       |     chunk header    |
|     BlocksIndex     |   |   +---------------------+-+ |                            | |  +---------------------+----+  +---------------------+
+---------------------+   |   |      Buckets[0]     |   |                            | |  |    AggregateExchg   |    |  |         ...         |
|         ...         |   |   +---------------------+---+   _HEAP_LOCAL_SEGMENT_INFO | |  +---------------------+--+ |  +---------------------+
+---------------------+   |   |         ...         |   +-->+---------------------+  | |  |      BlockSize      |  | |  |     chunk header    |
|       FreeList      |   |   +---------------------+   | +-|      LocalData      |<-+ |  +---------------------+  | |  +---------------------+
+---------------------+   |   | SegmentInfoArray[x] |---+ | +---------------------+    |  |      BlockCount     |  | |  |         ...         |
|         ...         |   |   +---------------------+     | |   ActiveSubsegment  |----+  +---------------------+  | |  +---------------------+
+---------------------+   |   |         ...         |     | +---------------------+       |         ...         |  | |  |     chunk header    |
|     FrontEndHeap    |---+   +---------------------+     | |     CachedItems     |       +---------------------+  | |  +---------------------+
+---------------------+       |      LocalData      |<----+ +---------------------+       |      SizeIndex      |  | |  |         ...         |
|         ...         |       +---------------------+       |         ...         |       +---------------------+  | |  +---------------------+
+---------------------+       |         ...         |       +---------------------+       |         ...         |  | |  
|FrontEndHeapUsageData|       +---------------------+       |     BucketIndex     |       +---------------------+  | |       _INTERLOCK_SEQ
+---------------------+                                     +---------------------+                                | +->+---------------------+
|         ...         |                                     |         ...         |                                |    |        Depth        |
+---------------------+                                     +---------------------+                                |    +---------------------+
                                                                                                                   |    |    Hint(15 bits)    |
                                                                                                                   |    +---------------------+
                                                                                                                   |    |     Lock(1 bit)     |
                                                                                                                   +--->+---------------------+
```
### _HEAP_BUCKET
### _HEAP_LOCAL_SEGMENT_INFO
	一些比较重要的成员：
		a. ActiveSubsegment(+0x8: 8B)：非常重要的成员，一个_HEAP_SUBSEGMENT结构体指针，目的在于管理UserBlocks，记录剩余等多chunk、该UserBlocks最大分配数等信息。
### _HEAP_SUBSEGMENT
	一些比较重要的成员：
		a. UserBlocks(+0x8: 8B)：一个指向_HEAP_USERDATA_HEADER结构的指针（后面会对_HEAP_USERDATA_HEADER进行分析），也就是指向LFH chunk的内存分配池。该内存分配池包括一个_HEAP_USERDATA_HEADER，存放一些metatdata；紧跟着后面会有要分配出去的所有chunk。
### _INTERLOCK_SEQ
### _HEAP_USERDATA_HEADER
	一些比较重要的成员：
		a. EncodedOffsets(+0x18: 8B)：一个_HEAP_USERDATA_OFFSETS结构，用来验证chunk header是否被改过。
		b. BusyBitmap(+0x20: 10B)：记录该UserBlocks哪些chunk被使用了。
### _HEAP_ENTRY
### 分配机制
## exploitation
	相比较linux的堆漏洞利用，windows要多出一步信息泄露。
### Unlink利用
	虽然Windows下对freed chunk的管理比较复杂，但是unlink原理和linux ptmalloc十分类似，所以利用方法也是共通的。[3]
	主要有两点不同：
		a. 进行decode header然后进行完整性check的时候，需要保证其正确性，比如找到previous/next freed chunk，进行decode以及完整性check的操作的时候。
		b. windows下chunk的Flink和Blink直接指向数据区域而不是chunk header。
	整体的利用思路为：
		a. 在已知linux下unlink attack的基础上，以完全相同的方式，对windows heap进行unlink attack，可以实现将一个指针指向本身的效果。
		b. 利用这个指向自身的指针，我们可以控制周围的可能的指针，达到任意地址读写的效果。
		c. 不同于linux下的利用，windows下似乎不存在各种hook函数可以覆盖从而控制程序的执行流，所以只存在两条路，一是ROP，二是写shellcode。
		d. 不论如何，首先需要的是leak出text，各种dll，以及stack地址。
		f. 后面就可以覆盖返回地址做ROP，调VirtualProtect获得执行权限，然后jump到shellcode执行。（个人认为如果可以的话，也能修复Freelist的双向链表，然后ROP直接执行system("cmd.exe")）
### Reuse attack for LFH
	假如我们拥有UAF的漏洞可以利用，但是因为LFH分配的随机性，我们无法预测下一个那到的chunk是在哪个位置，也就是说现在我们free的chunk，下一次malloc不一定拿得到。
	那么此时可以通过填满UserBlocks的方式，再free掉目标chunk，这样下一次malloc就必然会拿到目标chunk（因为只剩下一个），然后可以利用这个特性构造chunk overlap做进一步利用, 这是一种利用思路。
## reference
- [1] https://n0nop.com/2021/04/15/Learning-Windows-pwn-Nt-Heap/#HEAP-VIRTUAL-ALLOC-ENTRY : windows heap 简介
- [2] https://www.slideshare.net/AngelBoy1/windows-10-nt-heap-exploitation-chinese-version ： AngelBoy1的ppt
- [3] https://zhuanlan.zhihu.com/p/44456002 ： unlink利用攻击
