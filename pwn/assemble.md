## assemble instruction  
* nop : 空操作指令 延时一个机器周期。  
* push ax;  送入栈中，sp - 1  
* pop ax; $sp -> ax,sp + 1  
* movl src dst   四字节  
* movl $-8192, %eax  将栈底四字节 移到 eax   $ = 0xffffffff  
* lea 计算一个表达式的结果.  LEA EAX, [123 + 4*EBX + ESI]  
* mov eax, dword ptr [12345678]  把内存地址12345678中的双字型（32位）数据赋给eax  
* jnz: ZF标志位不为0时jmp; jz刚好相反  
* cmp des src: 比较整数，des-src,修改标志位，不修改任何操作数  

## disassemble  
32bit: leave = mov esp ebp; pop ebp; 清除栈空间，弹出ebp  
retn = pop ip; pc 是非intel厂家对IP的称呼，和 CS:IP一样  
return 0; =  leave ; retn  

## linux下的段：  
* .text,存放编译后的机器指令。  
* .bss段(rw),数据不包含再可执行程序中，在文件中不占据空间，存放未初始化的全局变量和局部静态变量，而.data段相反，且必须被初始化,存放已初始化的全局变量和局部静态变量。  
* .rodata段(r), 存放字符串常量。  
* 进程逻辑地址低到高地址：其他 .text  .rodata  .data 其他 HEAP 其他  STACK  

## linux assemble:  
* as  
* ld  

## data type  
* dq 四字单元  
* db 字节单元  
* dw 单字单元  
* dd 双字单元  
数据访问方式:immediate, register, memory.  

## architecture  
x86_64(ia32的64bit扩展): return value 使用寄存器的值当做返回值， 使用栈存储返回地址。  
ARM：使用R0 传递函数返回值。 LR寄存器 存储 函数结束之后的返回地址。  
ia32: intel architeture 32 bit  

## ia32_register  
![](image/IA32_register.png "IA32 register")  
方便处理byte,word,double word类型的数据,eax, ax, al 都不一样  

## other  
### shift  
left shift: fill 0  
logical right shift: fill 0  
arithmetic right shift: fill 最高位.  
对于无符号数，右移填充0.对于有符号数，右移可能是logical shift也或者是 arithmetic shift，大多数情况是算术右移，Java中明确规定了，c语言未明确规定右移采取哪种方式。  

