# file operation
  进程中的FILE结构会通过_chain域彼此连接形成一个链表，链表头部用全局变量_IO_list_all表示，通过这个值我们可以遍历所有的FILE结构。
  在标准I/O库中，每个程序启动时有三个文件流是自动打开的：stdin、stdout、stderr。因此在初始状态下，_IO_list_all指向了一个有这些文件流构成的链表，但是需要注意的是这三个文件流位于libc.so的数据段。而我们使用fopen创建的文件流是分配在堆内存上的。
  typedef struct _IO_FILE FILE;
  _IO_FILE结构外包裹着另一种结构_IO_FILE_plus，其中包含了一个重要的指针vtable指向了一系列函数指针,需要注意参数。在libc2.23版本下，32位的vtable偏移为0x94，64位偏移为0x228。
```c
struct _IO_FILE {
  int _flags;       /* High-order word is _IO_MAGIC; rest is flags. */
  /* The following pointers correspond to the C++ streambuf protocol. */
  /* Note:  Tk uses the _IO_read_ptr and _IO_read_end fields directly. */
  char* _IO_read_ptr;   /* Current read pointer */
  char* _IO_read_end;   /* End of get area. */
  char* _IO_read_base;  /* Start of putback+get area. */
  char* _IO_write_base; /* Start of put area. */
  char* _IO_write_ptr;  /* Current put pointer. */
  char* _IO_write_end;  /* End of put area. */
  char* _IO_buf_base;   /* Start of reserve area. */
  char* _IO_buf_end;    /* End of reserve area. */
  /* The following fields are used to support backing up and undo. */
  char *_IO_save_base; /* Pointer to start of non-current get area. */
  char *_IO_backup_base;  /* Pointer to first valid character of backup area */
  char *_IO_save_end; /* Pointer to end of non-current get area. */

  struct _IO_marker *_markers;

  struct _IO_FILE *_chain;

  int _fileno;
  int _flags2;
  _IO_off_t _old_offset; /* This used to be _offset but it's too small.  */
};
struct _IO_FILE_plus
{
    _IO_FILE    file;
    IO_jump_t   *vtable;
}
```
```c
void * funcs[] = {
   1 NULL, // "extra word"
   2 NULL, // DUMMY
   3 exit, // finish
   4 NULL, // overflow
   5 NULL, // underflow
   6 NULL, // uflow
   7 NULL, // pbackfail
   8 NULL, // xsputn  #printf
   9 NULL, // xsgetn
   10 NULL, // seekoff
   11 NULL, // seekpos
   12 NULL, // setbuf
   13 NULL, // sync
   14 NULL, // doallocate
   15 NULL, // read
   16 NULL, // write
   17 NULL, // seek
   18 pwn,  // close
   19 NULL, // stat
   20 NULL, // showmanyc
   21 NULL, // imbue
};
```
## fread
  fread的代码位于/libio/iofread.c中，函数名为_IO_fread，但真正的功能实现在子函数_IO_sgetn中。在_IO_sgetn函数中会调用_IO_XSGETN，而_IO_XSGETN是_IO_FILE_plus.vtable中的函数指针，在调用这个函数时会首先取出vtable中的指针然后再进行调用。
## fwrite
  fwrite的代码位于/libio/iofwrite.c中，函数名为_IO_fwrite。 在_IO_fwrite中主要是调用_IO_XSPUTN来实现写入的功能。根据前面对_IO_FILE_plus的介绍，可知_IO_XSPUTN位于_IO_FILE_plus的vtable中，调用这个函数需要首先取出vtable中的指针，再跳过去进行调用。
## fopen
  在fopen内部会创建FILE结构并进行一些初始化操作，下面来看一下这个过程。
  首先在fopen对应的函数__fopen_internal内部会调用malloc函数，分配FILE结构的空间。因此我们可以获知FILE结构是存储在堆上的
  之后会为创建的FILE初始化vtable，并调用_IO_file_init进一步初始化操作。
  在_IO_file_init函数的初始化操作中，会调用_IO_link_in把新分配的FILE链入_IO_list_all为起始的FILE链表中。
  之后__fopen_internal函数会调用_IO_file_fopen函数打开目标文件，_IO_file_fopen会根据用户传入的打开模式进行打开操作，总之最后会调用到系统接口open函数。
- 使用malloc分配FILE结构
- 设置FILE结构的vtable
- 初始化分配的FILE结构
- 将初始化的FILE结构链入FILE结构链表中
- 调用系统调用打开文件
## fclose
  fclose首先会调用_IO_unlink_it将指定的FILE从_chain链表中脱链。
  之后会调用_IO_file_close_it函数，_IO_file_close_it会调用系统接口close关闭文件。
  最后调用vtable中的_IO_FINISH，其对应的是_IO_file_finish函数，其中会调用free函数释放之前分配的FILE结构。
## printf/puts
  printf和puts是常用的输出函数，在printf的参数是以'\n'结束的纯字符串时，printf会被优化为puts函数并去除换行符。
  puts在源码中实现的函数是_IO_puts，这个函数的操作与fwrite的流程大致相同，函数内部同样会调用vtable中的_IO_sputn，结果会执行_IO_new_file_xsputn，最后会调用到系统接口write函数。

# 伪造vtable劫持程序流程
  伪造vtable劫持程序流程的中心思想就是针对_IO_FILE_plus的vtable动手脚，通过把vtable指向我们控制的内存，并在其中布置函数指针来实现。
  vtable劫持分为两种，一种是直接改写vtable中的函数指针，通过任意地址写就可以实现,需要libc支持libc数据段可修改。另一种是覆盖vtable的指针指向我们控制的内存，然后在其中布置函数指针。
  vtable中的函数调用时会把对应的_IO_FILE_plus指针作为第一个参数传递。eg: memcpy(fp,"sh",3);
  如果程序中不存在fopen等函数创建的_IO_FILE时，也可以选择stdin\stdout\stderr等位于libc.so中的_IO_FILE，这些流在printf\scanf等函数中就会被使用到。在libc2.23之前，这些vtable是可以写入并且不存在其他检测的。

# FSOP(File Stream Oriented Programming)
  根据前面对FILE的介绍得知进程内所有的_IO_FILE结构会使用_chain域相互连接形成一个链表，这个链表的头部由_IO_list_all维护。
  FSOP的核心思想就是劫持_IO_list_all的值来伪造链表和其中的_IO_FILE项，但是单纯的伪造只是构造了数据还需要某种方法进行触发。FSOP选择的触发方法是调用_IO_flush_all_lockp，这个函数会刷新_IO_list_all链表中所有项的文件流，相当于对每个FILE调用fflush，也对应着会调用_IO_FILE_plus.vtable中的_IO_overflow。
  _IO_flush_all_lockp不需要攻击者手动调用，在一些情况下这个函数会被系统调用：
- 当libc执行abort流程时
- 当执行exit函数时
- 当执行流从main函数返回时

# 新版本libc下IO_FILE的利用
  在最新版本的glibc中(2.24)，全新加入了针对IO_FILE_plus的vtable劫持的检测措施，glibc 会在调用虚函数之前首先检查vtable地址的合法性。
  在_IO_FILE中_IO_buf_base表示操作的起始地址，_IO_buf_end表示结束地址，通过控制这两个数据可以实现控制读写的操作。经测试，_IO_2_1_stdin+56的内存空间内容=全局缓冲区buf的地址。
