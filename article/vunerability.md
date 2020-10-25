# vulnerability
## vulnerability detection
	漏洞检测主要分为静态检测和动态检测。
	* 静态检测
		* 源代码漏洞检测
			主要包括基于中间表示的漏洞检测和基于逻辑推理的漏洞检测.
			* 基于模式的漏洞检测
			* 基于代码相似性的漏洞检测
				核心思想是相似的代码很可能含有相同的漏洞，只需要根据漏洞代码实例就可以检测目标程序中是否含有相同的漏洞。
		* 二进制漏洞检测
			* 二进制程序分析
			* 基于二进制相似性的漏洞检测
				*	函数代码特征
				*	函数与文件内其他函数的交互关系特征
				*	函数与其他文件的交互关系特征
			* 基于模式的漏洞检测
	* 动态检测
		动态检测技术指在真实环境或模拟环境中实际运行程序,通过观察程序运行过程中的状态来发现漏洞。
		难点在于如何生成覆盖率高的测试用例,或者生成触发应用程序漏洞发生的测试用例。
		根据测试用例生成方式不同,模糊测试可分为基于变异的模糊测试和基于生成的模糊测试。
		* 基于变异的模糊测试
			* 种子优化
				对种子文件进行变异,触发目标应用程序的潜在崩溃以发现新的漏洞。
				* 如何获取高质量的初始种子文件
					常用的收集方法包括使用现有的POC代码、使用基准测试用例以及从互联网爬取文件等方法。
				* 如何从种子池选取高质量的种子文件进行变异生成测试用例.
			* 测试用例生成
				测试用例的质量直接影响模糊测试的性能.高质量测试用例在更短的时间内覆盖更多的程序路径或触发更多的潜在漏洞。
			* 测试用例选择
				需要对测试用例进行筛选,选择高质量的测试用例（例如：触发漏洞或到达新路径）,过滤掉无效的测试用例,从而进一步提高测试性能。
		* 动态符号执行
			使用符号值代替具体值执行程序，多款工具应用于漏洞检测，包括KLEE, SAGE等.
			* 约束生成
### Sys:A static/symbolic tool for finding good bugs in good (browser) code.
	本文介绍了一个将静态检查和符号执行结合起来的漏洞检测工具Sys。Sys将漏洞检测分为两个步骤：首先利用静态检查将可能出现错误的代码进行标记；然后利用符号执行判断标记的代码是否存在bug。 作者利用Sys对Chrome，Firefox以及FreeBSD进行了测试，总共发现51个bug，其中有43个被确认（并且作者获得了很多奖金）。
#### Goal
	目的：自动检测浏览器中的安全漏洞。

	问题1: browsers check a log.
		* static checkers : 在源码中查找"buggy patterns". 可以考虑提供自动修复功能？？
	问题2: static checkers 不能找到很多bug.
	问题3: 符号执行 难且慢.
		* 全程序符号执行开销巨大，效率太低。对于浏览器或者操作系统这样大规模的代码，基本不可用。
	
#### 新方法
	static checking + underconstrained 符号执行。

	static checking: 寻找"buggy patterns"，确定潜在的漏洞点。
		* 尽量高的recall（tp/(tp+fn)）(找得全)，即Sys需要尽量减少漏报，把降低误报的任务交给符号执行。
	符号执行: 遍历所有可能的值，直接跳转到候选错误点并执行，降低误报。
		* 对可能存在bug的路径(从static checker获取的)进行符号执行。
		* 将用户提供的symbolic checker应用到路径上。
	underconstrained： 从任何地方开始，保证符号执行可以从程序的任意一点开始进行，从而降低符号执行的开销。 
		* constraints 就是把许多代码表示成逻辑公式。
		* SMT Solver.

##### using Sys to find bugs
##### Uninitialized memory
	static extension:
		对于每一个栈上分配的内存对象，extension会对allocation后续的所有路径进行流敏感的遍历。如有没有显式的store，extension会将第一个出现的load标记为潜在的未初始化。extension没有追踪指针偏移，而是把每一个偏移都看作是一个新的追踪位置。
	symbolic checker:
		Sys用shadow memory来检测未初始化内存的使用。Sys会对static pass标记的每条路径运行symbolic checker，起始位置是每一个可能未初始化使用的栈变量s。对于s的每一个bit，Sys会在shadow memory里标记一个对应的bit，用1和0表示uninit和not-uninit。对于每一个store，Sys会修改shadow memory里对应的bit为0。在s被读取时，checker会检查shadow memory里对应的bit是否为1，以确认是有存在未初始化内存的使用.
##### Heap out-of-bounds
	Concrete out-of-bounds: 是指索引为常量的越界访问。
	static extension：
		主要对三种操作进行标记：
			1. phi节点，可以给操作数引入常量。
			2. 编译器生成的undef常量，undef可能为任意值，会造成潜在的越界读写。
			3. 索引为常量的getelementptr指令。
		static pass会确认上述1，2的常量是否可以到达3，并把这个信息传给symbolic checker。但是pass也会忽略一些情况：比如父类的对象（可能与子类对象的布局不同）以及动态大小的结构体成员变量等等。
	symbolic checker:
		由于是对常量的检测，符号执行的作用是过滤不可达路径。

#### 与其他静态检测工具的对比
	作者选择了Clang Static Analysis和Semmle与Sys进行对比，进行了未初始化内存的测试。上述两个工具的扩展性足够好，并且已经被应用在Mozilla的代码上。
	误报的情况多数是因为静态分析无法对变量的取值进行判断。
	另外Sys还存在其他工具能检测的漏报。
		1. 4个是因为Sys跳过了某些函数；
		2. 4个是因为分析的block超过了Sys的阈值；
		3. 2个是因为编译器优化消除了bug。

#### 与其他符号执行工具的对比
	作者表示angr和KLEE都无法直接对Firefox进行处理。（angr跑了24小时被作者手动停掉）。

#### 贡献
	1. 实现了一个结合静态分析和符号执行的漏洞检测框架，提供了五个checker（包括uninitialized memory, out-of-bounds access, use-after-free以及taint analysis），检测出51个浏览器相关的bug；
	2. 提出了将符号执行扩展到大型codebase的方法；
	3. 提供了一个基于Haskell，用户可自行扩展的检测系统。

#### reference
- http://hackdig.com/10/hack-155064.htm : 解释性文章 
- 李正 等:深度学习在漏洞检测研究中的应用进展 ： 漏洞检测综述
---

## other
- recall  ： 找得全。
- precision ： 找得对。
- word2vec模型将程序切片转换成向量表示。
- PDG ： program dependency graph.
- 高质量的漏洞数据集是应用深度学习实现漏洞检测的关键.在软件漏洞领域,尚未形成标准的漏洞数据集,研究者通常需要自己去构造数据集.有部分研究者公开自己的数据集。