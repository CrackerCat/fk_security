# c language  
---
## syntax
### GCC Inline ASM
### initialization of array.
	对于C99, 可以以任意的顺序对数组元素初始化，只是需要给出数组元素所在的索引号。
```
int my_array[6] = { [4] = 29, [2] = 15 };
或者写成：
int my_array[6] = { [4] 29, [2] 15 };     //省略到索引与值之间的=，GCC 2.5 之后该用法已经过时了，但 GCC 仍然支持
两者均等价于：
int my_array[6] = {0, 0, 15, 0, 29, 0};
```
	GNU 还有一个扩展：在需要将一个范围内的元素初始化为同一值时，可以使用 [first ... last] = value 这样的语法：
```
int my_array[100] = { [0 ... 9] = 1, [10 ... 98] = 2, 3 };
```	
### macro
#### 用宏连接多个字符串
	“#”的功能是将其后面的宏参数进行字符串化操作,简单说就是在对它所引用的宏变量，通过替换后在其左右各加上一个双引号。
	“##”被称为连接符（concatenator），用来将两个Token连接为一个Token。注意这里连接的对象是Token就行，而不一定是宏的变量。
---

## union
  union中所有字段共用一块内存区域，内存区域长度是union中字段的最长长度。
---

## wchar_t
  一个宽字符占用多个字节。
- wprintf()
- fgetws()
- wcslen()
---

## main(int argc, const char **argv, const char **envp)
  envp : 环境变量
  回调：传递函数指针，对其赋值，在另一个函数中调用函数指针。
## linux c
### sprintf(const char*, format string, ...)  
### ftell(fp)
	对于二进制文件，则返回从文件开头到结尾的字节数。
	在随机方式存取文件时，由于文件位置频繁的前后移动，程序不容易确定文件的当前位置。使用fseek函数后再调用函数ftell()就能非常容易地确定文件的当前位置。
### __stpcpy_chk(dst, src, destlen)
	copy a string returning a pointer to its end, with buffer overflow checking.
### scanf()
  scanf函数接收整型数字要跳过所有非数字的字符专接收数字。那么如果输入了字符它就一直留在输入缓冲区；
  只要缓冲区有字符，scanf就要去读，一读不是数字，它就跳过。
  fflush(stdin);//此函数可以清除缓冲区中的字符 
  如果读取%s， 那么自动在末尾加上'\x00'。
### pipe()
  单向数据channel,可以被用于进程间通信。 
  pipe[0]指向pipe的读末尾;pipe[1]指向pipe的写末尾。
- 管道只允许具有血缘关系的进程间通信，如父子进程间的通信。
- 管道只允许单向通信。
- 管道内部保证同步机制，从而保证访问数据的一致性。
  - 如果一个管道的写端一直在写，而读端的引⽤计数(是否关闭pipe[0])是否⼤于0决定管道是否会堵塞，引用计数大于0，只写不读再次调用write会导致管道堵塞； 
  - 如果一个管道的读端一直在读，而写端的引⽤计数是否⼤于0决定管道是否会堵塞，引用计数大于0，只读不写再次调用read会导致管道堵塞； 
- 面向字节流
- 管道随进程，进程在管道在，进程消失管道对应的端口也关闭，两个进程都消失管道也消失。
### mkfifo()
  创建管道文件，以某种模式打开以满足同步需求,不需要满足父子关系。
### dup() 和 dup2()
	dup(fd) 复制一个现有的文件描述符, 指向同一个文件。
	dup2(fd, fd1) 文件描述符fd1重定向到fd。
### fork()
  创建进程,复制调用进程空间。
  在父进程中返回值是子进程的pid;在子进程中返回值是0.

### wait()
  挂起调用进程，直到它的一个子进程的运行状态发生改变。返回值是被终结子进程的pid.

### semaphore
#### sem_init()
	初始化一个定位在 sem 的匿名信号量。
#### sem_wait()
	资源减少1
#### sem_post()
	资源增加1
#### sem_destroy()
#### sem_getvalue()
### WIFEXITED()
  这个宏用来指出子进程是否为正常退出的，如果是，它会返回一个非零值。

#### WEXITSTATUS(status) 
  当WIFEXITED返回非零值时，我们可以用这个宏来提取子进程的返回值，如果子进程调用exit(5)退出，WEXITSTATUS(status)就会返回5.

### execlp()
  执行某个带参程序。

### mprotect()
  修改一段指定内存区域的保护属性。

### mmap(addr, length, prot, flags, fd, offset)
	mmap()  creates  a  new mapping in the virtual address space of the calling process.
	* 对于prot
		* PROT_READ  Pages may be read.
		* PROT_WRITE Pages may be written.

	* 对于flags
		* MAP_SHARED Share  this  mapping. 映射内容与文件内容一起更新。 
		* MAP_FIXED. 使用指定的映射起始地址，如果有start和len参数指定的内存区重叠于现存的映射空间，重叠部分将会被丢弃。如果指定起始地址不可用，操作将会失败。并且起始地址必须落在页的边界上。

### prctl()
	int prctl(int option, unsigned long arg2, unsigned long arg3, unsigned long arg4, unsigned long arg5);
	对进程进行操作。
	linux/prctl.h里面存储着prctl的所有参数的宏定义，prctl的五个参数中，其中第一个参数是你要做的事情，后面的参数都是对第一个参数的限定。
#### option
- PR_SET_NO_NEW_PRIVS=38 : prctl(38,1,0,0,0)表示禁用系统调用execve()函数，同时，这个选项可以通过fork()函数和clone()函数继承给子进程。
- PR_SET_SECCOMP=22：当第一个参数是PR_SET_SECCOMP,第二个参数argv2为1的时候，表示允许的系统调用有read，write，exit和sigereturn；当argv等于2的时候，表示允许的系统调用由argv3指向sock_fprog结构体定义，该结构体成员指向的sock_filter可以定义过滤任意系统调用和系统调用参数。 可通过seccomp-tools 查看禁用规则。
#### 沙箱规则
	通过prctl函数来实现的，它可以决定有哪些系统调用函数可以被调用，哪些系统调用函数不能被调用。
	沙箱(Sandbox)是程序运行过程中的一种隔离机制，其目的是限制不可信进程和不可信代码的访问权限。seccomp是内核中的一种安全机制，seccomp可以在程序中禁用掉一些系统调用来达到保护系统安全的目的，seccomp规则的设置，可以使用prctl函数和seccomp函数族。
	seccomp是内核中的一种安全机制，seccomp可以在程序中禁用掉一些系统调用来达到保护系统安全的目的，seccomp规则的设置，可以使用prctl函数和seccomp函数族。 
#### seccomp_rule_add()

### ioctl(fd, request, ...)
	ioctl 是设备驱动程序中设备控制接口函数，一个字符设备驱动通常会实现设备打开、关闭、读、写等功能，在一些需要细分的情境下，如果需要扩展新的功能，通常以增设 ioctl() 命令的方式实现。
	ioctl()[user space] -> sys_ioctl()[vfs] -> XXX-ioctl()[driver].
### proc_create(name, mode, parent, proc_fops)
	创建一个proc虚拟文件，应用层通过读写该文件，即可实现与内核的交互。
    mode 使用UGO模式，如 drwxrwxrwx=0x3ff
	proc_fops 是该文件的操作函数结构体，const struct file_operations 类型，需要初始化。
### remove_proc_entry(name)
	移除虚拟文件。
### glob()
  搜索所有匹配pattern的文件名，使用的是堆块保存文件名，globfree释放后可能保存着libc的信息。

### syscall(系统调用号，参数...)
	间接系统调用。

### signal
#### signal()
  将信号与信号处理函数进行绑定。
#### kill()
  向pid发送信号。
#### ctrl+c
  发送 SIGINT 信号（程序终止(interrupt)信号）给前台进程组中的所有进程。常用于终止正在运行的程序。
#### ctrl+z
  发送 SIGTSTP 信号（停止进程的运行, 但该信号可以被处理和忽略）.给前台进程组中的所有进程，常用于挂起一个进程。如果需要恢复到前台输入fg，恢复到后台输入bg.
#### ctrl+d
  表示一个特殊的二进制值，表示 EOF。
#### SIGFPE
	除零或者运算溢出。如 INT_MIN/-1。

### GNU c
- __attribute__ 可以设置函数属性(Function Attribute)、变量属性(Variable Attribute)和类型属性(Type Attribute)。
- __attribute__ 后面紧跟一对括号()，里面是相应的__attribute__参数。
	- __attribute__ 语法格式为：__attribute__ ((attribute-list)),位置为：放于函数声明尾部的 ; 之前。
	- __attribute__((regparm(3)))，那么就是说会用 3 个寄存器来传递参数(EAX, EDX, ECX)，其余的参数通过堆栈来传递。

### glibc
	1. (*__ctype_b_loc())[xb] & 0x2000) 对应于 isalnum,isalpha,isxdigit等等。
