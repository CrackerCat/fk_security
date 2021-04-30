# program slicing
	切片准则<n ,V> : n 指程序中的某个点（一般指某条语句），V 表示在n 定义或使用变量(def,use)的集合。
	这说明切片准则sc 代表感兴趣的变量以及感兴趣的变量的定义位置或使用位置，一般是一个二元组<n ，V>。

	给定某个程序P和程序切片准则<n ，V>， 程序P关于切片准则<n ，V>的程序切片（如静态后向切片）是由程序P 中所有在n点对变量v ∈ V 的值有影响的语句和控制谓词构成。
## 基于数据流方程的程序切片
	程序控制流图的基础上建立了数据流方程， 然后， 通过求解数据流方程来获得相应的程序切片。

## 基于PDG/SDG的程序切片
	程序依赖图： 根据程序中存在的各种依赖关系抽象出来的一种程序中间表示，它的节点表示程序语句或控制谓词， 边表示依赖关系。
	基于程序依赖图计算切片的过程大致如下：
	1. 构造某个程序的PDG 或SDG ；
	2. 给定某个程序切片准则， 在PDG 上运用图可达性算法， 或在SDG 上运用两步遍历图可达性算法， 获得与切片相对应的依赖图（我们称之为依赖图切片）；
	3. 把依赖图切片映射到源程序中， 与依赖图切片相对应的语句和控制谓词构成的集合就构成了该程序关于此切片准则的程序切片。

## concept
### 静态切片准则<n, V>
	在计算这类程序切片的时候， 并没有考虑程序的具体输入是什么，把程序的各种可能的输入都考虑进去。
	静态切片技术一般用于程序理解与软件维护方面。
### 动态切片准则<n, V, I0>
	动态切片准则是一个三元组<n，V，I0>，要计算程序 P 的动态切片就是要计算程序P中在某个特定输入 I0 的情况下所有影响 V 在 n 点的值的语句和谓词组成的集合。
	动态切片技术多用于程序调试、测试方面。
### 有条件切片准则<n, V, Ic>
	计算切片时考虑某些满足某个谓词表达式的所有可能输入。
### 后向切片
	寻找的都是程序P 中那些对某个兴趣变量有影响的语句和控制谓词，构造集合 affect(V/n)。
### 前向切片
	构造集合 affect-by(V/n), 该集合由程序P中所有受到变量V 在n 点的值影响的语句和控制谓词构成。

## article
### Understanding Program Slices
	Program slicing is a useful analysis for aiding different software engineering activities. By now program slicing has numerous applications in software maintenance, program comprehension, reverse engineering, program integration, and software testing. Usability of program slicing for real world programs depends on many factors such as precision, speed, and scalability, which have already been addressed in the literature.
    然而， 对于大规模的程序而言， 如何理解某个特定的元素包含在结果切片(包含很多程序指令)中。这篇论文，给出了一种关于静态程序切片的元素推断方法。
#### 1. introduction
	1. 介绍程序切片的最初的概念。
	2. 程序切片的进展。
	3. Program slicing allows the users to focus on the selected aspects of semantics by breaking the whole program into smaller pieces, and when these slices are small they can be more easily maintained. However, lager program slices, but even slices containing only some tens of program instructions can be very difficult to understand.
	4. 介绍元素推断的作用。
	5. 文章结构。

### Partial Slices in Program Testing
	Program slicing is widely used as an aid in program analysis. In several cases, it is observed that the static slices contain a large number of program statements. Due to this increased size of the static slice, they are of little use in many practical applications. Moreover, the static slices may be less precise compared to dynamic slices.
	Partial slicing is suggested as a method for program testing in order to eliminate the disadvantages of static slicing.
#### introduction
	本文partial slices: 后向查找影响变量的语句所生成的切片集合(直到遍历完所有程序点就结束了)。  
	partial slices criterion = (statement, variable, program points)
#### conclusion & future work
	partial slices 可以减少静态切片的数量。
	在partial slices中， 条件和约束的分析可以通过分析paritial slices中的约束来简化程序测试。	
#### reference
- 2012 IEEE 35th Software Engineering Workshop.

### Automatic Testing of Program Slicers
#### reference
- Scientific Programming Volume 2019, Article ID 4108652, 15 pages

###  一种基于程序切片相似度匹配的脆弱性发现方法
#### 3 框架与方法
##### 3.1 基于切片相似度匹配的脆弱性分析框架
	1. 在脆弱性切片准备部分,首先对脆弱性样本进行程序切片,获取与脆弱性密切相关的程序片段,然后通过特征提取算法获得脆弱性代码的特征,并将其映射到向量空间;
	2. 在脆弱性检测应用部分,待检测代码采取和脆弱性样本切片**相同的程序切片准则、特征抽取和向量空间映射算法**,然后计算其与脆弱性切片的向量距离,向量距离越近则相似程度越高,该程序越有可能存在脆弱性.
##### 3.2 基于切片相似度比对的脆弱性发现算法
###### 3.2.1 脆弱性上下文切片
	在脆弱性分析应用中,程序的复杂性导致脆弱性分析面临挑战.研究脆弱性发生上下文有助于降低待分析程序的规模,引入切片则使得分析更加关注于那些感兴趣的脆弱性上下文代码.
	由于脆弱性上下文在代码中具有的局部特性,切片首先需要确定脆弱性切片的头部和尾部,这对已经披露的CVE脆
	弱性通常是已知的.
	在仅仅标注了头部和尾部的情况下,基于图的可达性算法会生成很多切片,而其中大部分切片与脆弱性无关.
	为了解决这个问题,建立在对已知脆弱性的描述信息和人对脆弱性实例分析的基础上,通过人工标注或者建立的一些基于语法和语义的判定规则自动化地标注一些代码位置,这些位置的集合称为关键点集合K. 通过指定关键点集合K,可以大幅缩减产生的脆弱性切片的数量,降低了分析中不关心或者与脆弱性无关的程序代码干扰,从而提高了相似性比对中的特征和脆弱性的相关性.
	提出了基于头 - 关键点(集) - 尾模型的切片方法.

## reference
- https://research.cs.wisc.edu/wpis/html/ ： Wisconsin Program-Slicing Project
- Weiser, M. Program slicing. IEEE Trans. Software Eng., 10(4): 352–357,1984. ： 首次提出程序切片。
- https://www.geeksforgeeks.org/software-engineering-slicing/ : 切片例子

## TODO
 	- 能识别 check() 类型的 安全检查。 √   因为存在异常处理分支。
 	- 根据check函数提取安全检查的点。 doing!