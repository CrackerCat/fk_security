.py     
   p.interactive() 接下来自动交互。    
   %num$x 在linux下有效，win下无效。    
基础知识    
x86 按字节编制，字节序：低字节放低地址    
  
context.arch = ''; flat([]); // no p32 p64 + for payload.    
sendlineafter() =  recvuntil() + sendline()    
  
随机数（伪随机数)利用： seed不变,随机数序列不变。    
  
dll = ctypes.cdll.LoadLibrary() 引用动态库。    
  
NX保护：栈内代码不可执行。    
canary found是，触发check_failed(),ROP失效。 可以写超出当前ebp的范围. 触发*** stack smashing detected ***    
  
整型溢出： __int8等赋值修改变量。    
  
'\x41' ='a'    
  
system的参数，可以通过gets，read读取任意字符串设置。    
  
strings -t x 可以查看在文件中字符串的0x偏移量    
libc 中 /bin/sh 是一个字符串，next(libc.search('/bin/sh')) / libc.search('/bin/sh').next()    
readelf -s  查找elf中的符号表。    
  
elf模块： 静态加载ELF文件    
所谓的动态链接在linux中是延迟绑定技术，涉及了got表和plt表。     
plt表(程序链接表)：跳板，跳转到一个地址来加载libc库。文件中会对每个用到的外部函数分配一个plt函数(函数入口地址),可从ida中读出。不能修改。第一次调用外部函数，会进行解析函数。    
got表(全局偏移量的表，数据段.data中)：经过plt表的跳转会跳转会在got表上写入地址，这个地址是函数调用或变量的**内存真实地址**.可以修改。     
注意：plt表只在程序调用函数之前有用，调用函数之后第二次执行这个函数就不会经过plt表。    
加载：动态链接文件加载时有时候会重新改变基地址但是偏移(8位地址的后4位是一样的)是不变的(寻址方式是基地址+偏移量)    
address 获取ELF的基址    
symbols 获取函数的地址(跟是否开启PIE有关) ,未开启就是偏移量    
  
网络流传过来的需要u32解开?    
  
可执行文件往往是第一个被加载的文件，它可以选择一个固定空间的地址，比如Linux下一般都是0x0804000,windows下一般都是0x0040000    
共享的指令可以使用地址无关代码技术(PIC)，装载地址不变，跟地址相关部分放到数据段里面。    
  
linux 延迟绑定PLT    
动态链接器需要某个函数来完成地址绑定工作，这个函数至少要知道这个地址绑定发生在哪个模块 哪个函数，如lookup(module,function)。    
  
在glibc中，lookup的函数真名叫做_dl_runtime_reolve()    
  
当我们调用某个外部模块时，调用函数并不直接通过GOT跳转，而是通过一个叫做PLT项的结构来进行跳转，每个外部函数在PLT中都有一个相应的项，比如bar()函数在PLT中的项地址叫做bar@plt，具体实现    
bar@plt：    
 jmp *(bar@GOT)    
 push n    
 push moduleID    
 jump _dl_runtime_resolve    
  
第一条指令是一条通过GOT间接跳转指令，bar@GOT表示GOT中保存bar()这个函数的相应项。    
但是为了实现延迟绑定，连接器在初始化阶段没有将bar()地址填入GOT,而是将push n的地址填入到bar@GOT中，所以第一条指令的效果是跳转到第二条指令，相当于没有进行任何操作。第二条指令将n压栈，接着将模块ID压栈，跳转到_dl_runtime_resolve。实际上就是lookup(module,function)的调用。    
_dl_runtime_resolve（）在工作完成后将bar()真实地址填入bar@GOT中。    
一旦bar（）解析完毕，再次调用bar@plt时，直接就能跳转到bar()的真实地址。    
  
PLT的真正实现要更复杂些，ELF将GOT拆分成两个表.got和".got.plt",前者用来保存全局变量引用的地址，后者用来保存函数引用的地址。    
  
数组越界漏洞利用。    
  
## static link    
静态编译的代码在同一架构上都能运行。IDA 红色部分为外部函数    
函数符号需要重新签名.    
static link 可以使用ROPgadget 生成 ROP chain    
  
## gdb调试    
context.terminal = ['tmux','splitw','-h'] //当无图形时    
gdb.attach(p, 'gdb cmd') :     
context.log_level = 'debug'
log.success()
  
## 栈溢出的简化计算：    
cyclic(0x100):生成0x100大小的pattern    
cyclic_find(0x61616161/'aaaa')：查找该数据在pattern的位置    
  
## ROPGadget 查看特殊代码段的工具    
ROPGaget --binary exe --only/--string "pop | ret(instruction)"    
ROPgadget --binary binary --ropchain 获取static execute ROP chain.  
  
## 系统调用获取shell    
当没有system()函数时  
linux: int 0x80 用于系统调用。    
只要我们把对应获取shell的系统调用的参数放到对应的寄存器中(指令地址+pop栈元素)，我们就能执行对应的系统调用。    
当存在栈溢出ROP时，可以将返回地址指向int 0x80指令的地址，再修改相应寄存器的地址(通过ROPgadget获得)    
![](image/ROP_syscall.png "ROP syscall")    
![](image/syscall.png "disassemble syscall")    
  
## shellcode: 填入某个位置充当指令。    
     https://www.exploit-db.com/shellcodes    
     pwntools  asm(shellcraft.sh())      
     asm(shellcraft.linux.sh()) getshell 注：shellcraft.linux.sh()是getshell的汇编指令,asm进行汇编，返回字符串。  
       
## other  
函数指针，可以使用shellcode的地址    
