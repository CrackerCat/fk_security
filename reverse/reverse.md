# reverse
	花指令，代码混淆，代码加壳加固(把真正的代码逻辑进行加密)，反调试,
## 花指令
	如果程序中包含的花指令IDA Pro无法识别或识别到了后一条指令中，将会对后续部分指令的分析和函数识别产生严重影响。简单的说就是在代码中混入一些垃圾数据阻碍你的静态分析。
### 可执行花指令
	执行前后不改变任何寄存器的值(当然eip这种除外)，同时这部分代码也会被反汇编器正常识别。 这种花指令可以破坏反编译的分析,使得栈指针在反编译引擎中出现异常(栈指针实际上是没有问题的,只不过反编译引擎还有待完善的空间)。
### 不可执行花指令
	利用反汇编器线性扫描算法的缺陷使得静态分析的时候会看到一些错误的代码。
### 特殊花指令
	不会影响反汇编和反编译，只是单纯的混淆视听。 这些指令相当于等价替换。
### 基本的花指令插入方法
	1. 使用jnz + jz方法跳过垃圾字节的方式，来插入花指令。
		1.1 应对措施：patch方法 a. 将无法解析的位置转化为数据，然后把跳转的位置转化为code,其他部分nop掉。
## 反调试 - 对抗gdb,strace,ltrace等调试工具
### linux
#### ptrace
	ptrace有个规定是，每个进程只能被PTRACE_TRACEME一次。
	因此，只要在程序的开头就先执行一次 ptrace(PTRACE_TRACEME, 0, 0, 0)，判断是否为-1。
##### 绕过思路
	1. 打patch。
	2. 利用hook技术，把ptrace函数给替换成自定义的ptrace函数。
	3. 利用gdb的catch命令，修改ptrace的返回值。
#### 利用getppid
	如果一个程序的父进程不是意料之中的bash等（而是gdb，strace之类的），那就说明它被跟踪了。
#### 检测/proc/self/status
	检查 /proc/self/status 中的 TracerPID - 正常运行时为0，在有debugger挂载的情况下变为debugger的PID。因此通过不断读取这个值可以发现是否存在调试器，进行对应处理。
#### 检测/proc/self/cmdline
	这种操作本质上就是在检测输入的命令内容。
#### 设置时间间隔
	在程序启动时，通过alarm设置定时，到达时则中止程序 ，这样就不能长时间调试程序。
---
## re 技巧
### 调试法
	所需的信息在内存中。

## tools
- z3 线性规约求解器 
- sage 求公式
- PEID 查壳
- IAT 导入表重建工具
- ltrace ： 库追踪,也可以查看系统调用
- strace ：系统调用追踪
- ptrace系统调用 ：用于附加到进程上，并访问进程的代码、数据、堆栈和寄存器。 
---

## topic
### maze
  *#的组合构成maze,需要推出上下左右是哪几个字符代替。

---
## packer
	壳：一段专门负责保护软件不被非法修改或反编译的程序。一般都是先于程序运行，拿到控制权，然后完成它们保护软件的任务。 EXE文件 --加壳-->>  Loader(外壳) + 压缩数据(原EXE文件)。
	壳分成两类：
		a. 压缩壳。常见的压缩壳有：Upx、ASpack、PECompat
		b. 加密壳/保护壳，保护 PE 免受代码逆向分析。常见的加密壳有：ASProtector、Armadillo、EXECryptor、Themida、VMProtect
	加载过程：
		a. 保存入口参数。
			保存入口参数，执行外壳，恢复入口参数。
		b. 获取壳所需函数的API
			一般外壳的输入表IAT主要是GetProcAddress，GetModuleHandle和LoadLibrary这几个API函数。
		c. 解密原程序各个区块的数据
			出于保护源程序代码和数据的目的，一般会加密源程序文件的各个区块。在程序执行时外壳将这些区块数据解密，并将解密的数据放回在合适的内存位置，以让程序正常运行。
		d. IAT(Import Address Table)的初始化
			IAT类似plt表。
			加壳时，壳会自己构造一个输入表IAT，并将PE头中的输入表指针指向自建的输入表。PE装载器对自建的输入表进行填充，原来的输入表由壳来填充。
		e. 处理重定位项
			加壳的DLL比加壳的EXE修正时多一个重定位表, 因为DLL没有占住原来的imagebase地址。
		f. HOOK-API
			壳一般都修改了原程序的输入表，自己模仿装载器来填充输入表。在填充过程中，外壳就可以填充HOOK-API的代码地址，间接获得程序的控制权。
		g. 跳到程序的原入口点(OEP,original entry point), (EP(entry point)，即带壳程序的入口点)。
### uppack
#### win平台常规脱壳方法
	1. 单步跟踪法
		单步向下，尽量实现向下的跳转；跳过体量较大的循环；如果函数载入时不远处就是一个 call(近 call), 那么我们尽量不要直接跳过, 而是进入这个 call； 一般跳转幅度大的 jmp 指令, 都极有可能是跳转到了原程序入口点 (OEP)。
		执行完外壳，就可以看到正确的汇编指令。
	2. ESP定律
		原理：利用堆栈平衡快速找到OEP。
		不少壳会先将当前寄存器状态压栈, 如使用pushad，就设对 ESP 寄存器设硬件断点，在解压结束后, 会将之前的寄存器值出栈, 如使用popad，因此在寄存器出栈时, 往往程序代码被恢复, 此时硬件断点触发。
		补充：
			a. 内存断点：给内存页增加一个禁止访问的属性，访问该内存页时会触发异常，通过捕获异常并判断异常位置是否与下断地址相同来来触发断点，如果是就中断。
			b. 内存访问一次性断点:是一次性断点，当所在段被读取或执行时就会中断。
	3. 一步到达法
		原理：根据所脱壳的特征, 寻找其距离 OEP 最近的一处汇编指令, 然后下 int3 断点, 在程序走到 OEP 的时候 dump 程序。只适用于极少数压缩壳。例如，搜索 popad 指令。
	4. 内存镜像法
		对程序资源段和代码段下断点, 一般程序自解压或者自解密时, 会首先访问资源段获取所需资源, 然后在自动脱壳完成后, 转回程序代码段. 这时候下内存一次性断点, 程序就会停在 OEP 处。
	5. 最后一次异常法
		程序在自解压或自解密过程中, 可能会触发无数次的异常. 如果能定位到最后一次程序异常的位置, 可能就会很接近自动脱壳完成位置. 
	6. SFX
		利用 Ollydbg 自带的 OEP 寻找功能, 可以选择直接让程序停在 OD 找到的 OEP 处, 此时壳的解压过程已经完毕, 可以直接 dump 程序。
	补充：
		a. 内存dump: 在找到程序 OEP 后, 我们需要将程序 dump 出来, 并重建IAT(Import Address Table),其表项指向函数的实际地址。 
			i. 使用Ollydump插件dump程序
			ii. 使用ImportREC软件重建函数表
		b. 手动查找IAT表：利用模块间的调用关系和IAT表的连续性。
#### upx
- upx -d file #unpack

#### vmprotect
	x86_64架构？
	VMProtect是一个堆栈虚拟机，它的一切操作都是基于堆栈传递的。在VMP中，每一个伪指令就是一个handler，VM中有一个核心的Dispatch部分，它通过读取程序的bytecode，然后在DispatchiTable里面定位到不同的handler中执行。绝大多数情况下，在一个handler中执行完成后，程序将回到Dispatch部分，然后到next handler中执行。
##### VMP
	虚拟机保护技术，是指将代码翻译为机器和人都无法识别的一串伪代码字节流；在具体执行时再对这些伪代码进行一一翻译解释，逐步还原为原始代码并执行。例如，java的JVM、.NET或其他动态语言的虚拟机都是靠解释字节码来执行的。
	虚拟机（CPU）的体系架构可分为3种，基于堆栈的(Stack based)，基于寄存器的(Register based)和3地址机器(只操作内存)。
		a. 基于堆栈的虚拟机。参数和返回都是在堆栈里。
	虚拟机执行概述如下。
```
Virtual Machine Loop Start
...
...
...
--> Decode VM Instruction's
...
--> Execute the Decoded VM Instruction's
...
...
--> Check Special Conditions
...
...
...
Virtual Machine Loop End
```
###### VMContext
		VMContext是虚拟机VM使用的虚拟环境结构。各种寄存器。
		自定义指令 = 自定义指令操作码 <操作数在VMcontext中的偏移> <操作数在VMcontext中的偏移> <操作数在VMcontext中的偏移>;
###### VStartVM
		VStartVM是虚拟机的入口，负责保存运行环境(各个寄存器的值)、以及初始化堆栈(虚拟机使用的变量全部在堆栈中)。
			1. VStartVM首先将所有的寄存器的符号压入堆栈，然后esi指向字节码的起始地址，ebp指向真实的堆栈，edi指向VMContext，esp再减去40h(这个值可以变化)就是VM用的堆栈地址了。
			2. 因为堆栈是变化的，在执行完跟堆栈有关的指令时总应该检查一下真实的堆栈是否已经接近自己存放的数据了，如果是，那么再将自己的结构往更地下移动。VCheckEsp函数。
			3. 然后从 movzx eax, byte ptr [esi]这句开始，读字节码，然后在jump表中寻找相应的handler，并跳转过去继续执行。
			注：在整个虚拟机代码执行过程中，必须要遵守一个事实。
				a. 不能将edi,esi,ebp寄存器另做他用
				b. edi指向的VMContext存放在栈中而没有存放在其他固定地址或者申请的堆空间中，是因为考虑到多线程程序的兼容
###### VMDispatcher
	VMDispatcher负责调度这些Handler，Handler可以理解为一个个的子函数(功能代码)，它是每一个伪指令对应的执行功能代码。dispatcher往往是一个类while结构，不断的循环读取伪指令，然后执行。
	handler分两大类：
		a. 辅助handler，指一些更重要的、更基本的指令，如堆栈指令
		b. 普通handler，指用来执行普通的x86指令的指令，如运算指令
	对于不可识别指令：
		在这里任何不能识别的指令都可将其划分为不可模拟指令，碰到这类指令时，只能与vcall使用相同的方法，即先退出虚拟机，执行这个指令，然后再压入下一字节码(虚拟指令)的地址，重新进入虚拟机。
		
###### 逆向思路
	手动逆向思路：找到虚拟指令和原始汇编的对应关系，然后重写出原始程序的代码，完成算法的逆向和分析。
##### vmprotect 运行过程
	在VMP的VM运行过程中，各个寄存器的基本用途是：
		a. EBP和EDI是VM堆栈指针（不是常规的堆栈）；
		b. ESI是伪指令指针（相当于常规的EIP）；
		c. EAX是VM解密数据的主运算寄存器；
		d. EBX是VM解密数据的辅运算寄存器；
		e. ECX是常规的循环计数器；
		f. ESP是常规的堆栈栈顶指针;
		g. EDX是读取伪指令表数据。
##### VM堆栈
	1. 向正常栈push所有真实寄存器。(vmp运算区域）
	2. 意图在正常栈中构建出一个给vmp使用的空间。
	3. 此空间原则上一分为二。
		a. 上部分称为context区，16个4字节空间，每1个代表一个vmp虚拟寄存器,EDI记录着它的上边界。
		b. 下部分称为运算区(或者叫vmp运算栈)，是vmp指令执行用的栈，使用EBP记录该vmp运算栈的栈顶。
	4. 第一步的push所有真实寄存器操作完成后，这些寄存器值正位于vmp运算栈中。
	5. 给ESI一个指向，后面会用这个指向去加载vmp的字节码。
##### 伪指令
- vPop : 将vmp运算栈存放的一堆真实寄存器值移动到vm context区，一个vPop移动一个值。
- 对move指令的模拟：先 vPush, 再vPop。
- vPush : vmContext -> vm运算区
- vRet : 将压入vmp运算栈中vmp虚拟寄存器值，更新到真实cpu寄存器中。

###### 常见伪指令组合
	在VMP的伪指令的执行中有一些常见的组合套路，熟悉它们能让我们在跟踪VMP时更加的得心应手。
##### reference
- https://link.zhihu.com/?target=https%3A//www.tuicool.com/articles/bIFrMz ： VMProtect原理简介

#### Armadillo 穿山甲
	加密壳。
##### 保护机制
	Armadillo主要采用了Debug-Blocker，CopyMem-II， Enable Import Table Elimination，Enable Nanomites Processing，Enable Memory-Patching Protections保护手段。同时也有单双进程之分，造成了保护手段的多样性。
		a. Debug-Blocker反调试，基本只要开插件都可以过，打开IsProcessDebug去反调试选项和忽略异常。
		b. CopyMem-II：双进程保护，两个进程互相监听,当一个进程被终止时,另一个进程会立即将其恢复。
		c. Enable Import Table Elimination：IAT保护，修改Magic_Jmp。
		d. Enable Nanomites Processing就是CC保护，也是Armadillo最强大的保护机制。原理就是就是将程序中的部分代码改写为int3或者向其中插入int3代码。
##### 前期侦壳
	侦壳工具：
		a. exepeinfo是用于查壳的。
		b. 任务管理器是用于判断是单进程还是双进程，如果是双进程就需要双转单。
		c. ArmaFP是用于判断其保护模式，是标准模式，还是全保护模式(专业模式)。
##### Armadillo单进程脱壳
###### 标准单进程Armadillo 3.78 - 4.xx 脱壳
	最简单的加密方法，只需要修改Magic_Jmp就可以了，因为这个版本单进程防护只是加密了IAT，(1)只需要绕过加密，(2)并让其解压压缩区段即可。
###### 单进程Armadillo v4.x脱壳
	首先需要判断加壳版本是否是http://4.xxx。关于这点如何判断呢，主要下硬件断点 HE OutputDebugStringA 。在堆栈窗口出现%s%s%s%s的标志，说明这是4.X的壳。
	第一，使用Magic_Jmp避过IAT加密保护，对GetCurrentThreadId下断点找到OEP。
##### Armadillo双进程脱壳
###### 标准保护
	双进程保护的原理，利用互斥体来判断进程列表是否存在相同的进程(即多开)。思路：首先是利用CreateMutex创建一个互斥体。然后在利用OpenMutex打开那个互斥体，如果OpenMutex成功返回互斥体句柄，说明已经存在一个进程。如果不存在则在CreateProcess一个进程。
	双转单思路： 如果提前创建了一个即将被打开的互斥体。那么程序就不会去创建新的进程。
###### CopyMem-II
	去除CopyMem-ll 保护通常有两个方法。
		a. 首先寻找OEP，然后对WaitForDebugEvent下断点bp WaitForDebugEvent,接着运行程序，看堆栈，当出现pDebugEvent字符的时候，选择在数据窗口跟随，然后对WriteProcessMemory下断bp WriteProcessMemory，中断后，在数据窗口发现OEP。
		b. 不会。
##### reference
- https://zhuanlan.zhihu.com/p/66029373 : Armadillo 原理简介 

---
## checksum
1. 查表法实现校验
2. 阅读源码，思考破解


---
## hash
### md5
  128bit,每四位转一个16进制数，就一共32个数字。有时候有16个数字的hash值，是将32位md5去掉前八位，去掉后八位得到的。
#### openssl c
1. MD5_Init(MD5_CTX *c) : 初始化 MD5 Contex, 成功返回1,失败返回0
2. MD5_Update(MD5_CTX *c, const void *data, size_t len); : 循环调用此函数,可以将不同的数据加在一起计算MD5,成功返回1,失败返回0.
3. MD5_Final(unsigned char *md, MD5_CTX *c); : 输出MD5结果数据,成功返回1,失败返回0

## other
### 函数以endbr指令开始
- 运行fxxk_cet.py脚本破解，恢复函数签名。
