# obfuscation
## custom obfuscation
### 控制流混淆
	基本控制流主要有三种，分别是顺序、循环和条件控制流。逆向工程师总是试图找到决定性的跳转或循环的条件，只要能够确定这些关键部分，则其他部分就会相当清楚。
#### 虚假控制流
	通过添加不透明谓词和虚假控制块，进一步增大程序分析时的测试用例数量和程序控制流的复杂度。
	虚假控制流通过插入条件跳转来修改普通的顺序控制流，条件跳转的后继块可以为程序中原本的基本块，也可以是添加的虚假块，通过添加条件跳转修改函数调用图。条件跳转一般通过不透明谓词进行实现。
	不透明谓词可以分为静态不透明谓词和动态不透明谓词，静态或动态可证明为恒真或者恒假的条件判别式。
	
	虚假块的构造,首先根据程序中原有基本块，进行指令复制(增加迷惑性)，之后添加花指令。
	不透明谓词的实现。
		a. 动态不透明谓词, 是由一系列相关的谓词组成的，这些谓词在一个给定的执行中呈现相同的值，但是在另一个执行中值可能会发生变化。
		b. 静态不透明谓词，是由恒真或恒假的条件语句构成。
#### 控制流扁平化
	纵向的程序执行流转换为横向的 switch 选择流。不改变基本块数量，横向拉伸基本块。
#### 循环展开
	通过处理循环头和循环体，隐藏原本的循环状态，模糊化循环的退出逻辑。目前的实现方式是直接展开。C->B->A ----->   C->B->A -> C->B->A -> C->B->A ->....-> C
#### 直接跳转间接化
	通过添加跳转块，可以进一步增加逆向的难度。A->B --->  A->C->B
### 指令混淆
	将原始的代码指令转换为功能上等价但更为复杂、难以理解的指令，从而尽可能地提高分析和破解的成本。
#### 指令替换
	在保证程序执行结果不变的前提下，对原始指令进行等价的指令替换（比如a = b + c 和a = b − (−c) 是等效的），这种混淆方法所用到的指令序列基本上是功能等效但是更复杂的指令序列，以此去替换标准的二元运算符。
#### 指令加花
	代码指令层级的保护，防止反汇编程序的逆向处理。
### 字符串混淆
	标识符重命名和重要字符串的加解密。
#### 字符串加密
	为了防止明文字符串出现在静态存储数据中，通过字符串加密的手段，可在一定程度上增加逆向的难度，提高程序安全性。
#### 标识符重命名
	更改开发者对程序中使用到的有意义的变量名、常量名、函数名、类名等标识符进行重命名。
### VMP
#### 基本块层混淆
	以基本块为基本单位，对于基本块内的每一条指令，将其包装成单独的基本块，然后对其绑定一个操作码，然后设计好的解释器对原有指令进行解释执行。通过操作码和 switch 指令实现所有指令所在基本块和解释器之前的跳转关系，从而控制了整个程序的执行流程。
	
	操作码设计。
		基于块内指令有序执行的特点，操作码存放的顺序和指令执行的顺序保持一致，将操作码存储在IR中的一个全局数组内，通过数组的索引递增寻找到下一条需要执行的指令操作码。
		
	指令重组。
		通过解释器实现块间跳转，从而控制程序的执行逻辑，因此需要将原本的指令包装成为基本块。
		a)	得到原始基本块内的每一条指令。
		b)	为该指令创建一个新块。
		c)	将该指令加入此新块。
		d)	将该指令从原始基本块中移除。
		e)	将该新的基本块与一个操作码绑定。
		
	解释器设计。
		解释器的主要工作是，根据当前的操作码索引，然后找到当前需要执行的操作码，然后计算下一次要执行的指令操作码的索引。
#### 函数层级混淆
	整个函数只有一个解释器，解释器控制了整个函数的所有基本块中指令的执行。
	对于基本块内部指令的处理，同样也是将基本块内的每一条指令都包装成单独的基本块，并对应着一个操作码，然后通过解释器按照程序原有的指令执行顺序进行解释执行。
	解释器通过switch指令，根据当前的操作码，实现指令所在基本块和解释器之间的跳转关系，从而控制整个程序的执行流程。
	
	操作码设计。
		考虑到块间跳转，指令顺序打乱的情况我们将通过解释器部分来解决。操作码只需要控制块内指令的有序性即可。
			
	指令重组。
		解释器就可以通过操作码来找到对应的基本块，然后跳转到该基本块，执行其中的指令。操作码是标识基本块的唯一数字。
		
	解释器设计。
		通过当前指令操作码的索引得到下一次即将执行的指令对应的操作码，然后跳转到下一条指令所在基本块去执行。 跳转关系主要是在拆分阶段实现，各个基本块本身就存在跳转关系。