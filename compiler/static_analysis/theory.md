# theory
## CFL context free language
---
## CFI
	控制流完整性 (Control-Flow Integrity) 是一种针对控制流劫持攻击的防御方法。
	控制流的转移是以跳转指令为基础的。
	按寻址方式分类：
	- 直接跳转指令，e.g. CALL 0x1060000F
	- 间接跳转指令，e.g. JMP EBX
	按转移方向分类：
	- 前向转移,指的是将控制权定向到程序中一个新位置的转移方式，比如使用 CALL 指令调用函数。
	- 后向转移是将控制权返回到先前位置，最常见的就是RET指令。

&emsp;对于控制流完整性策略而言，直接跳转指令的地址在编译时就已固定，难以被攻击者更改，无需消耗大量资源对其进行检查。而间接跳转指令的地址存在被攻击者恶意篡改的可能，因此是检测的重点。间接跳转又分为前向间接跳转 (如通过指针的函数调用) 和后向间接跳转 (如RET指令) ，几乎所有的控制流完整性策略都是针对这两者进行检验。
### 核心思想
	CFI防御机制的核心思想是限制程序运行中的控制流转移，使其始终处于原有的控制流图所限定的范围内。

### reference
- Control-Flow Integrity, CCS 2005. (控制流完整性机制的首次提出)
- Control-flow integrity: Precision, security, and performance[J]. ACM Computing Surveys (CSUR), 2017, 50(1): 16. （对现有CFI机制的安全性和开销作出了系统的评价）

## SMT Solver
	SMT是寻找公式满足性（对变量取值使得某个公式成立），很多形式化验证问题可以转化为公式可满足性问题。
### reference
- z3工具.