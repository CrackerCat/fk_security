# assembly language
主流的两种汇编语言是 intel Assembly Language 和 AT&T Assembly language.

---
## assemble instruction  
* nop : 空操作指令 延时一个机器周期。  
* push ax;  送入栈中，sp - 1  
* pop ax; $sp -> ax,sp + 1  
* movl src dst   四字节  
* movl $-8192, %eax  将栈底四字节 移到 eax   $ = 0xffffffff  
* movl %eax, 4(%edx) 将%edx + 4地址中的内容 移入 %eax
* lea 计算一个表达式的结果.  LEA EAX, [123 + 4*EBX + ESI]  
* mov dword ptr [12345678],eax  把内存地址12345678中的双字型（32位）数据赋给eax  
* jnz=jne: ZF标志位不为0时jmp; jz=je刚好相反  
* jg 前大于后
* jge 前大于等于后
* cmp des src: 比较整数，des-src,修改标志位，不修改任何操作数  
* add des src
* rep ret 解决ret的分支预测问题。 [rep详解](http://repzret.org/p/repzret/ "rep")

---
## disaseembly syntax
- $ 当前正汇编到的指令在代码段中的偏移量; 也可用于表达式。

---
## disassemble  
32bit: leave = mov esp ebp; pop ebp; 清除栈空间，弹出ebp  
retn = pop ip; pc 是非intel厂家对IP的称呼，和 CS:IP一样  
return 0; =  leave ; retn  

---
## linux下的段：  
* .text,存放编译后的机器指令。  
* .bss段(rw),数据不包含再可执行程序中，在文件中不占据空间，存放未初始化的全局变量和局部静态变量，而.data段相反，且必须被初始化,存放已初始化的全局变量和局部静态变量。  
* .rodata段(r), 存放字符串常量。  
* 进程逻辑地址低到高地址：其他 .text  .rodata  .data 其他 HEAP 其他  STACK  

---
## linux assemble:  
* as AT&T assembler
  * as -o file.o file.s --64
  * ld -m elf_x86_64 file.o -o file 
* ld  
* nasm Intel assembler
  * nasm -f elf64 file.asm
  * ld -s -o file file.o

---
## data type  
* dq 四字单元  
* db 字节单元  
* dw 单字单元  
* dd 双字单元  
数据访问方式:immediate, register, memory.  

---
## architecture  
x86_64(ia32的64bit扩展): return value 使用寄存器的值当做返回值，减少从内存读取和写操作， 使用栈存储返回地址。  
ARM：使用R0 传递函数返回值。 LR寄存器 存储 函数结束之后的返回地址。  
ia32: intel architeture 32 bit  

## ia32_register  
![](image/IA32_register.png "IA32 register")  
方便处理byte,word,double word类型的数据,eax, ax, al 都不一样  

## x86_i64 register
![](image/x86_64_register.png "x86_64 register")
64bit: %rax, %rdx, %rdx, %rbx, %rsi, %rdi, %rsp, %rbp, %r8-%r15.
访问低32bit/16bit/8bit register可以直接访问, 跟ia32类似,例如 %eax/%ax/%al.
 

---
## other  
### shift  
left shift: fill 0  
logical right shift: fill 0  
arithmetic right shift: fill 最高位.  
对于无符号数，右移填充0.对于有符号数，右移可能是logical shift也或者是 arithmetic shift，大多数情况是算术右移，Java中明确规定了，c语言未明确规定右移采取哪种方式。  

### function return value  
* 32bit: 存放在eax中  
* 64bit: 高位存放在edx,低位存放在eax  

### data alignment
* 存储地址是2的倍数意味着地址最低一位为0.存储地址是4的倍数意味着地址最低两位为0.其他同理。
* linux中，short 2字节对齐；int,int*,float,double 四字节对齐。
* windows中，大多数情况，k字节的对象的地址必须是k的倍数。
* 汇编语法: .align number 
```c
struct S2 {
  int i;
  int j;
  char c;
};
struct S1 e;
struct S2 d[4];
```
  在上述代码中，e占9字节，而d数组中元素占12字节，需要考虑每个元素的对齐。

