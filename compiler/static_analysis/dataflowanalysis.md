# data flow analysis
	1. 数据流分析是一种基于格（lattice）理论的、用来获取相关数据（如：变量及其取值）沿着程序执行路径流动的程序分析技术，常用于源代码的编译优化过程，能够获得变量在某个程序点上的性质、状态、取值等信息. 
	2. IN(s), OUT(s), 顺序，分流，汇聚。
	3. 数据流分析就是对程序中所有的statements的input points和output points关联一个dataflow value, 这个solution通过解析一系列的safe-approximation-deriected constraints(约束规则)。
		约束规则基于：1. semantics of statements（转化函数） 2. flows of control(控制流信息)
			i. transfer function 就是input point的state 转化成 output point的state。
			ii. control flow's constraints: 分流和汇聚。
## AST & IR
	AST 适合做快速的类型检测。AST仅能够表示源代码的结构信息。
	IR 适合做静态分析。
	CFG 能够给出源代码语句之间的控制依赖关系，无法提供数据依赖关系。
	DDG，数据依赖图，
	PDG，程序依赖图，
	CPG，代码属性图，
## CSSA & SSA
	CSSA 需要做数据流分析，即到达-定义分析，。
	SSA 每个变量只能做一次赋值，可以快速变量读。方便优化。
	CSSA -> SSA: 将每个被赋值的变量用一个新的变量来代替，同时将每次使用的变量替换为这个变量到达该程序点的“版本”。 对于控制流的问题，引入φ判断变量的来源。
## theory
### fix point
	f(x) = x.
### partial order 偏序
	partial order set = poset 需满足：
		1. 自反性。x<=x
		2. 传递性。x<=y, y<=z -> x<=z
		3. 反对称性。 x<=y, y<=x -> x=y
		注：<= 约等于 运算关系
### upper and lower bounds
	1. poset subset S 的 upper bounds 是，S中所有元素 <= poset中某些元素，那么  poset中某些元素 就是上界。
	2. poset subset S 的 lower bounds 是，poset中某些元素 <= S中所有元素 ，那么  poset中某些元素 就是下界。
	3. least upper bound(lub or join) of poset subset S是，某个上界 <= 所有上界，那么这个某个上界就是lub。
	4. greatest lower bound(glb or meet) of poset subset S是，所有下界 <= 某个下界， 那么这个某个下界是glb.
	5. Not every poset has lub or glb. 例如没有空串。
	6. if a poset has lub or glb, it will be unique。
### lattice 格
	Given a poset (P, ⊑), ∀a, b ∈ P, if a ⊔ b（ab的lub） and a ⊓ b（ab的glb）exist, then (P, ⊑) is called a lattice.
	解释：A poset is a lattice if every pair of its elements has a least upper bound and a greatest lower bound。
### semilattice 半格
	Given a poset (P, ⊑), ∀a, b ∈ P,
	if only a ⊔ b exists, then (P, ⊑) is called a join semilattice
	if only a ⊓ b exists, then (P, ⊑) is called a meet semilattice
### complete lattice 全格
	Given a lattice (P, ⊑), for arbitrary subset S of P, if ⊔S and ⊓S exist, then (P, ⊑) is called a complete lattice。
	Every finite lattice (P is finite) is a complete lattice。
### product lattice 乘积格
	Given lattices L1 = (P1, ⊑1), L2 = (P2, ⊑2), …, Ln = (Pn, ⊑n), then we can have a product lattice L^n = (P, ⊑) that is defined by:
	a. P = P1 ×… × Pn
	b. (x1, …, xn) ⊑ (y1, …, yn)⟺(x1 ⊑ y1) ∧…∧ (xn ⊑ yn)
	c. (x1, …, xn) ⊔ (y1, …, yn) = (x1 ⊔1 y1, …, xn ⊔n yn)
	d. (x1, …, xn) ⊓ (y1, …, yn) = (x1 ⊓1 y1, …, xn⊓n yn)
	e. A product lattice is a lattice
	f. If a product lattice L is a product of complete lattices, then L is also complete.
## DFA framework via Lattice
	A data flow analysis framework (D, L, F) consists of:
	a. D: a direction of data flow: forwards or backwards
	b. L: a lattice including domain of the values V and a meet ⊓ or join ⊔ operator.
		i. merge的时候，多个分支就是求子集合的上界，即合并的集合。
	c. F: a family of transfer functions from V to V
	
	理论角度的理解：Data flow analysis can be seen as iteratively applying transfer functions and meet/join operations on the values of a lattice。
---
## taint analysis
	污点分析通过对程序中的敏感数据进行标记，跟踪标记数据在程序中的传播，从而检测系统中存在的安全问题。
	1. 污点分析被定义为三元组<Source, Sink, Sanitizer>，建立在SC: {Tainted, Untainted}集合的格上. 
	2. 如果信息从Tainted类型的变量（图中标记为“污点变量”）传递到Untainted类型的变量（图中标记为“变量”），则Untainted类型的变量将被标记为Tainted类型；如果在Tainted类型变量的传播过程中，经过净化处理，则当其传递到Tainted类型或Untainted类型的变量时，不会改变目标变量的污染状态. 在污点分析过程中，如果Tainted类型的变量能够传递到Sink，则说明当前程序存在安全问题.

## application
### Reaching Definitions Analysis
	1. RDA是针对程序中的每个程序点，分析出能到达此处的变量定义。
	2. 方法：给定一个程序点p和变量定义d，判断在CFG上是否存在一条从d到p的路径，该路径上没有对d进行重新定义的指令. 如果存在这么一条路径，则称定义d可以到达程序点p；否则，定义d不能到达程序点p. 
### Liveness analysis
	1. LA是针对程序中的每个程序点(通常是一条指令的前后处，即IN(p)和OUT(P))，分析出活跃于此处的所有变量。
	2. 方法：给定一个程序点p和变量v，判断在CFG上是否存在一条从p开始的路径，该路径上有使用了v的指令. 如果存在这么一条路径，则称变量v在程序点p处是存活的；否则，变量v在程序点p处不存活. 
#### what is Liveness?
	变量v在程序点p处是活跃的，要满足以下两点：
	a. 变量在程序点p`处被使用，且程序p和p`存有一条路径(p->p`)。
	b. 变量v在上述路径没有被定义过。
    由此，可知LA属于may分析。
#### usage
	a. 死代码去除。
	b. 优化寄存器分配。
#### how to implement?
	在LA分析之前，我们就知道结束程序点（可能有多个）处的活跃变量集合是空，因为没有任何变量在此后被使用过。
	而入口程序点处的活跃变量集合则需要分析。因此应以结束程序点作为分析的起点。
	此外，要确定某程序点处的活跃变量集合，必须先把此程序点到所有结束程序点的所有可能路径上的指令都扫描一遍。这也表明LA应采用backward分析。
1. 变量在通过指令时的传播规则  
	变量v活跃在指令p之前的紧邻程序点q, 需要满足以下条件：
	a. 变量v在指令p中被使用。
	或者
	b. 变量v在程序点p之后的紧邻程序点中是活跃的，且在p处未被重新赋值(旧的活跃变量得以顺利传递)
    形式化表述如下:
	IN(P) = (OUT(P)-{v}) U vars(E). 
	其中，vars(E)是表达式E中所使用的变量， v是被重新赋值的变量。

2. 变量在汇聚时的传播规则
	当来自不同分支的活跃变量集合在某程序点发生汇聚时，此程序点处的活跃变量集合应是各集合的并集。形式化表述如下所示：
	OUT(P) = U(IN(p<sub>s</sub>), p<sub>s</sub>∈succ(P). 

3. 基于上述传播规则，求解活跃变量

#### reference
- [数据流分析之Liveness Analysis](https://blog.csdn.net/lunaticzhg/article/details/105383645 "数据流分析之Liveness Analysis")  

### Available Expression Analysis
	可用表达式分析AEA是针对每个程序点，分析可使用的表达式。
	方法：给定一个程序点p和表达式x op y（其中，op表示operator，指操作符），判断在CFG上从ENTRY节点到p的所有路径上，是否都执行了x op y，且最后一次执行x op y后，没有对x和y进行重新定义。
### Constant Propagation Analysis
	常量传播分析CPA: 针对每个程序点，分析指令中变量的值是否为常数。
	方法：判断在给定程序点p处指令i中的变量v的值是否为常数.
---
## framework
### soot
#### Jimple IR
## other
---
