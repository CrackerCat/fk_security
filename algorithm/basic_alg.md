# basic algorithm
## computation complexity
### Big-Oh Notation
	已知f(n)，g(n),存在c,n0,使得f(n)<=cg(n),n>=n0, 则f(n) = O(g(n)).

### Big-Omega Notation
	已知f(n)，g(n),存在c,n0,使得f(n)>=cg(n),n>=n0, 则f(n) = Ω(g(n)).

### big-Theta Notation
	已知f(n)，g(n),存在c1,c2,n0,使得c1g(n)>=f(n)>=c2g(n),n>=n0, 则f(n) = θ(g(n)).

## recursion
### Linear recursion
	1. 执行一次递归调用。
#### tail recursion
	从尾部开始递归。
### binary recursion
	两次递归运算之后，产生结果。
### multiple recursion
	多次递归调用，不仅仅是一两次。一般是循环内有递归调用。
## graph
### circle
- 可以使用**并查集**遍历边集合，判断边的两端点是否属于同一集合。 参考kruscal_mst的实现。

---

## other
---