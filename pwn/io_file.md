# file operation
  进程中的FILE结构会通过_chain域彼此连接形成一个链表，链表头部用全局变量_IO_list_all表示，通过这个值我们可以遍历所有的FILE结构。
  在标准I/O库中，每个程序启动时有三个文件流是自动打开的：stdin、stdout、stderr。因此在初始状态下，_IO_list_all指向了一个有这些文件流构成的链表，但是需要注意的是这三个文件流位于libc.so的数据段。而我们使用fopen创建的文件流是分配在堆内存上的。
  typedef struct _IO_FILE FILE;
  _IO_FILE结构外包裹着另一种结构_IO_FILE_plus，其中包含了一个重要的指针vtable指向了一系列函数指针,需要注意参数。在libc2.23版本下，32位的vtable偏移为0x94，64位偏移为0xd8。
```c
// libio.h
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

extern struct _IO_FILE_plus *_IO_list_all;

extern struct _IO_FILE_plus _IO_2_1_stdin_;
extern struct _IO_FILE_plus _IO_2_1_stdout_;
extern struct _IO_FILE_plus _IO_2_1_stderr_; 
#define _IO_stdin ((_IO_FILE*)(&_IO_2_1_stdin_))
#define _IO_stdout ((_IO_FILE*)(&_IO_2_1_stdout_))
#define _IO_stderr ((_IO_FILE*)(&_IO_2_1_stderr_))

int
putchar (int c)
{
  int result;
  _IO_acquire_lock (_IO_stdout);
  result = _IO_putc_unlocked (c, _IO_stdout);
  _IO_release_lock (_IO_stdout);
  return result;
}
```
   由上述代码可知，_IO_stdin/out/err 空间存储着_IO_2_1_stdin_/out_/err_的地址，因此我们可以伪造FILE结构体覆盖它。 相关io函数都是用的_IO_stdout/in/err.
   用gdb打印出_chain的偏移的方法： p &((struct _IO_FILE*)0)->_chain
```c
//每个元素都是IO_jump_t类型，其实是一个hook。
void * funcs[] = {
   1 NULL, // "extra word"
   2 NULL, // DUMMY
   3 exit, // finish, _IO_finish_t
   4 NULL, // overflow, _IO_overflow_t
   5 NULL, // underflow, 
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
  **伪造vtable劫持程序流程**的中心思想就是针对_IO_FILE_plus的vtable动手脚，通过把vtable指向我们控制的内存，并在其中布置函数指针来实现。
  vtable劫持分为两种，一种是直接改写vtable中的函数指针，通过任意地址写就可以实现,需要libc支持libc数据段可修改。另一种是覆盖vtable的指针指向我们控制的内存，然后在其中布置函数指针。
  vtable中的函数调用时会把对应的_IO_FILE_plus指针作为第一个参数传递。eg: memcpy(fp,"sh",3);
  如果程序中不存在fopen等函数创建的_IO_FILE时，也可以选择stdin\stdout\stderr等位于libc.so中的_IO_FILE，这些流在printf\scanf等函数中就会被使用到。在libc2.23之前，这些vtable是可以写入并且不存在其他检测的。

# FSOP(File Stream Oriented Programming)
  根据前面对FILE的介绍得知进程内所有的_IO_FILE结构会使用_chain域相互连接形成一个链表，这个链表的头部由_IO_list_all维护。
  FSOP的核心思想就是**劫持_IO_list_all的值**来伪造链表和其中的_IO_FILE项，但是单纯的伪造只是构造了数据还需要某种方法进行触发。FSOP选择的触发方法是调用_IO_flush_all_lockp，这个函数会刷新_IO_list_all链表中所有项的文件流，相当于对每个FILE调用fflush，也对应着会调用_IO_FILE_plus.vtable中的_IO_overflow。

  _IO_flush_all_lockp不需要攻击者手动调用，在一些情况下这个函数会被系统调用：
- 当libc执行abort流程时
	- 当 glibc 检测到内存错误时，会依次调用这样的函数路径：malloc_printerr ->libc_message->__GI_abort -> _IO_flush_all_lockp -> _IO_OVERFLOW
- 当执行exit函数时
	- 执行exit()时，系统会调用_IO_flush_all_lockp
- 当执行流从main函数返回时

## _IO_flush_all_lockp中的_IO_overflow的触发约束
  要让正常控制执行流，还需要伪造一些数据，我们看下代码:
```
if (((fp->_mode <= 0 && fp->_IO_write_ptr > fp->_IO_write_base)   
#if defined _LIBC || defined _GLIBCPP_USE_WCHAR_T
       || (_IO_vtable_offset (fp) == 0
           && fp->_mode > 0 && (fp->_wide_data->_IO_write_ptr
                    > fp->_wide_data->_IO_write_base))
#endif
       )
      && _IO_OVERFLOW (fp, EOF) == EOF)
```
- fp->mode <=0
- fp->_IO_write_ptr > fp->_IO_write_base 

### usage
	1. 利用unsortedbin attack修改bk并且malloc分割unsortedbin, 因此触发fsop -> 修改_IO_list_all的值为 main_arena+n ->  (_IO_FILE_plus*)(main_arena+n)->_chain = 剩下的unsortedbin空间的地址。 -> 剩下的unsortedbin空间要改成 io_file(其vtable改成伪造的空间)  -> 触发_IO_file_overflow. 

# 新版本libc下IO_FILE的利用
  在_IO_FILE中_IO_buf_base表示操作的起始地址，_IO_buf_end表示结束地址，通过控制这两个数据可以实现控制读写的操作。经测试，_IO_2_1_stdin+56的内存空间内容=全局缓冲区buf的地址。
## libc2.23
  在目前 libc2.23 版本下，位于 libc 数据段的 vtable 是不可以进行写入的。不过，通过在可控的内存中伪造 vtable 的方法依然可以实现利用。
    
## libc2.24
  在最新版本的glibc中(2.24)，全新加入了针对IO_FILE_plus的vtable劫持的检测措施，glibc 会在调用虚函数之前首先检查vtable地址的合法性。
```
IO_validate_vtable (const struct _IO_jump_t *vtable)
{
  /* Fast path: The vtable pointer is within the __libc_IO_vtables
     section.  */
  uintptr_t section_length = __stop___libc_IO_vtables - __start___libc_IO_vtables;
  const char *ptr = (const char *) vtable;
  uintptr_t offset = ptr - __start___libc_IO_vtables;
  if (__glibc_unlikely (offset >= section_length))
    /* The vtable pointer is not in the expected section.  Use the
       slow path, which will terminate the process if necessary.  */
    _IO_vtable_check ();
  return vtable;
}

```
  新版本（libc2.24以上）的防御机制会检查vtable的合法性，不能再像之前那样改vtable为堆地址，但是除了_IO_file_jumps 虚表，还有_IO_str_jumps是一个符合条件的 vtable，改 vtable为 _IO_str_jumps即可绕过检查。

```
#define JUMP_INIT_DUMMY JUMP_INIT(dummy, 0), JUMP_INIT (dummy2, 0)
const struct _IO_jump_t _IO_str_jumps libio_vtable =
{
  
  JUMP_INIT_DUMMY,
  JUMP_INIT(finish, _IO_str_finish),
  JUMP_INIT(overflow, _IO_str_overflow),
  JUMP_INIT(underflow, _IO_str_underflow),
  JUMP_INIT(uflow, _IO_default_uflow),
  JUMP_INIT(pbackfail, _IO_str_pbackfail),
  JUMP_INIT(xsputn, _IO_default_xsputn),
  JUMP_INIT(xsgetn, _IO_default_xsgetn),
  JUMP_INIT(seekoff, _IO_str_seekoff),
  JUMP_INIT(seekpos, _IO_default_seekpos),
  JUMP_INIT(setbuf, _IO_default_setbuf),
  JUMP_INIT(sync, _IO_default_sync),
  JUMP_INIT(doallocate, _IO_default_doallocate),
  JUMP_INIT(read, _IO_default_read),
  JUMP_INIT(write, _IO_default_write),
  JUMP_INIT(seek, _IO_default_seek),
  JUMP_INIT(close, _IO_default_close),
  JUMP_INIT(stat, _IO_default_stat),
  JUMP_INIT(showmanyc, _IO_default_showmanyc),
  JUMP_INIT(imbue, _IO_default_imbue)
};
  
```
### 利用思路
#### 方法一 _IO_str_jumps -> overflow -> 劫持控制流
   如果我们能设置文件指针的 vtable 为 _IO_str_jumps 么就能调用不一样的文件操作函数。这里以_IO_str_overflow为例子：
```
int
_IO_str_overflow (_IO_FILE *fp, int c)
{
  int flush_only = c == EOF;
  _IO_size_t pos;
  if (fp->_flags & _IO_NO_WRITES)// pass
      return flush_only ? 0 : EOF;
  if ((fp->_flags & _IO_TIED_PUT_GET) && !(fp->_flags & _IO_CURRENTLY_PUTTING))
    {
      fp->_flags |= _IO_CURRENTLY_PUTTING;
      fp->_IO_write_ptr = fp->_IO_read_ptr;
      fp->_IO_read_ptr = fp->_IO_read_end;
    }
  pos = fp->_IO_write_ptr - fp->_IO_write_base;
  if (pos >= (_IO_size_t) (_IO_blen (fp) + flush_only))// should in 
    {
      if (fp->_flags & _IO_USER_BUF) /* not allowed to enlarge */ // pass
    return EOF;
      else
    {
      char *new_buf;
      char *old_buf = fp->_IO_buf_base;
      size_t old_blen = _IO_blen (fp);
      _IO_size_t new_size = 2 * old_blen + 100;
      if (new_size < old_blen)//pass 一般会通过
        return EOF;
      // 劫持控制流的位置 target
      new_buf
        = (char *) (*((_IO_strfile *) fp)->_s._allocate_buffer) (new_size);//target [fp+0xe0]
      if (new_buf == NULL)
        {
          /*      __ferror(fp) = 1; */
          return EOF;
        }
      if (old_buf)
        {
          memcpy (new_buf, old_buf, old_blen);
          (*((_IO_strfile *) fp)->_s._free_buffer) (old_buf);
          /* Make sure _IO_setb won't try to delete _IO_buf_base. */
          fp->_IO_buf_base = NULL;
        }
      memset (new_buf + old_blen, '\0', new_size - old_blen);

      _IO_setb (fp, new_buf, new_buf + new_size, 1);
      fp->_IO_read_base = new_buf + (fp->_IO_read_base - old_buf);
      fp->_IO_read_ptr = new_buf + (fp->_IO_read_ptr - old_buf);
      fp->_IO_read_end = new_buf + (fp->_IO_read_end - old_buf);
      fp->_IO_write_ptr = new_buf + (fp->_IO_write_ptr - old_buf);

      fp->_IO_write_base = new_buf;
      fp->_IO_write_end = fp->_IO_buf_end;
    }
    }

  if (!flush_only)
    *fp->_IO_write_ptr++ = (unsigned char) c;
  if (fp->_IO_write_ptr > fp->_IO_read_end)
    fp->_IO_read_end = fp->_IO_write_ptr;
  return c;
}
libc_hidden_def (_IO_str_overflow)
```
   代码中，有一个是利用字段调用函数，可用来劫持程序流程：
```
new_buf
        = (char *) (*((_IO_strfile *) fp)->_s._allocate_buffer) (new_size);
```
   几个条件需要bypass:
   1. fp->_flags & _IO_NO_WRITES为假
   2. (pos = fp->_IO_write_ptr - fp->_IO_write_base) >= ((fp->_IO_buf_end - fp->_IO_buf_base) + flush_only(1))
   3. fp->_flags & _IO_USER_BUF(0x01)为假
   4. 2*(fp->_IO_buf_end - fp->_IO_buf_base) + 100 不能为负数
   5. new_size = 2 * (fp->_IO_buf_end - fp->_IO_buf_base) + 100; 应当指向/bin/sh字符串对应的地址
   6. fp+0xe0指向system地址
   
   构造：
```
_flags = 0
_IO_write_base = 0
_IO_write_ptr = (binsh_in_libc_addr -100) / 2 +1
_IO_buf_base = 0
_IO_buf_end = (binsh_in_libc_addr -100) / 2 

_freeres_list = 0x2
_freeres_buf = 0x3
_mode = -1

vtable = _IO_str_jumps
```
   FILE+0xd8时vtable, IO_str_overflow 函数会调用 FILE+0xe0处的地址。这时只要我们将虚表覆盖为 IO_str_jumps将偏移0xe0处设置为one_gadget即可。
	
#### 方法二 _IO_str_jumps -> finish -> 劫持控制流
   还有一种就是利用io_finish函数，原理同上面的类似， io_finish会以 IO_buf_base处的值为参数跳转至 FILE+0xe8处的地址。执行 fclose（ fp）时会调用此函数，但是大多数情况下可能不会有 fclose（fp），这时我们还是可以利用异常来调用 io_finish，异常时调用 IO_OVERFLOW是根据IO_str_overflow在虚表中的**偏移**找到的， 我们可以设置vtable为IO_str_jumps-0x8异常时会调用io_finish函数。 即 修改io_finish的配置 -> 调用 IO_OVERFLOW, 即调用io_finish。
```
void
_IO_str_finish (_IO_FILE *fp, int dummy)
{
  if (fp->_IO_buf_base && !(fp->_flags & _IO_USER_BUF))
    (((_IO_strfile *) fp)->_s._free_buffer) (fp->_IO_buf_base);  //[fp+0xe8]
  fp->_IO_buf_base = NULL;

  _IO_default_finish (fp, 0);
}
```
   利用条件：
   1. _IO_buf_base 不为空
   2. _flags & _IO_USER_BUF(0x01) 为假
   构造如下：
```py
_flags = (binsh_in_libc + 0x10) & ~1
_IO_buf_base = binsh_addr

_freeres_list = 0x2
_freeres_buf = 0x3
_mode = -1
vtable = _IO_str_finish - 0x18
fp+0xe8 -> system_addr
```

#### 方法3 fileno 与缓冲区的相关利用
   _IO_FILE 在使用标准 IO 库时会进行创建并负责维护一些相关信息，其中有一些域是表示调用诸如 fwrite、fread 等函数时写入地址或读取地址的，如果可以控制这些数据就可以实现任意地址写或任意地址读。
   在_IO_FILE 中_IO_buf_base 表示操作的起始地址，_IO_buf_end 表示结束地址，通过控制这两个数据可以实现控制读写的操作。

## pwntools
	FileStructure() 返回 FILE 结构体

# other
## exit函数()分析
### 利用思路一 - rtld
	exit() -> _dl_fini() -> _dl_rtld_unlock_recursive和 _dl_rtld_lock_recursive两个函数。
    这_dl_rtld_unlock_recursive和 _dl_rtld_lock_recursive两个函数是_rtld_global结构体变量中的函数指针。
    _rtld_global结构位于ld.so中，所以需要先计算ld_base。 在Libc-2.27中，libc_base+0x3f1000=ld_base。
    似乎只有dl_rtld_unlock_recursive 才有合适的one_gadget。
### 利用思路二 - exit_hook 
	exit_hook.
### 利用思路三 - __exit_funcs
  exit调用过程： exit() -> __run_exit_handlers()
```
struct exit_function_list
{
	struct exit_function_list *next;
	size_t idx;
	struct exit_function fns[32];
}；

extern struct exit_function_list *__exit_funcs attribute_hidden;
extern struct exit_function_list *__quick_exit_funcs attribute_hidden;

struct exit_function结构体 存储着函数指针。



exit (int status)
{
    __run_exit_handlers (status, &__exit_funcs, true, true);
}

__run_exit_handlers(int status, struct exit_function_list **listp,
					bool run_list_atexit, bool run_dtors)
{
......
    while (*listp != NULL)
    {
        struct exit_function_list *cur = *listp;
		while (cur->idx > 0)	
		{
			//根据idx执行相应的 exit_function类型 的函数
		}
    }
......
}
```

由上述代码可知：
	\__exit_funcs是一个 exit_function_list * 类型的全局变量。因此，若我们能够用自定义的空间地址覆盖libc中的 \__exit_funcs 指针变量，就可以让 __run_exit_handlers 函数使用我们伪造的exit_function_list，从而执行任意代码。
	