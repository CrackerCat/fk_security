nop : 空操作指令 延时一个机器周期。

32bit: leave = mov esp ebp; pop ebp; 清除栈空间，弹出ebp
       retn = pop ip;
return 0; =  leave ; retn

x86 : return value 使用寄存器的值当做返回值， 使用栈存储返回地址。
ARM：使用R0 传递函数返回值。 LR寄存器 存储 函数结束之后的返回地址。

push ax;  送入栈中，sp - 1
pop ax; $sp -> ax,sp + 1

linux下的段：
.text,存放编译后的机器指令。
.bss段(rw),数据不包含再可执行程序中，在文件中不占据空间，存放未初始化的全局变量和局部静态变量，而.data段相反，且必须被初始化,存放已初始化的全局变量和局部静态变量。
.rodata段(r), 存放字符串常量。
进程逻辑地址低到高地址：其他 .text  .rodata  .data 其他 HEAP 其他  STACK

linux assemble: as ld

movl src dst   四字节
movl $-8192, %eax  将栈底四字节 移到 eax   $ = 0xffffffff

dq 四字单元
db 字节单元
dw 单字单元
dd 双字单元
