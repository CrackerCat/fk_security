# PA
## theory
### reference

## A Principled Approach to Selective Context Sensitivity for Pointer Analysis
### reference
- https://zhuanlan.zhihu.com/p/140400355 : 南大软件分析课程8——指针分析-上下文敏感

## application
### CFLSteensAA
	这是一种基于控制流不敏感上下文不敏感的别名分析技术。
	目前的实现,对于函数内的别名分析,其较为精准，而对于过程间的分析，别名结果都是MayAlias。
#### 自定义过程间分析
	基于调用流图和函数内的CFLSteens别名分析结果，我们实现了一种路径优化的过程间分析方法。
#### 目前的问题
	1. 函数内需存在赋值语句。
	2. 普通变量被识别问题。
	3. 什么时候合并到项目里。
	4. arm编译
		a. 交叉编译。
		b. 使用模拟环境，进行编译。