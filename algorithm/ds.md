# data structure
## linear structure
### queue
#### example
	http1.0, http1.1, http2.0，解决因请求处理时间过长导致服务端队首阻塞问题。
### stack
#### example
	进制转换，括号匹配，栈混洗，中缀表达式, 后缀表达式（逆波兰表达式）等

## nonlinear structure
### tree
#### feature
	1. 如果树有 n 个顶点，那么其就有 n - 1 条边，这说明了树的顶点数和边数是同阶的。
	2. 任何一个节点到根节点存在唯一路径，路径的长度为节点所处的深度
#### example
	1. 游戏中的碰撞检测。
#### binary tree
##### true binary tree
	所有节点的度数只能是偶数，即只能为 0 或者 2.
##### heap
	一种优先级队列。
	需要注意的是优先队列不仅有堆一种，还有更复杂的，但是通常来说，我们会把两者做等价
###### feature
	在一个 最小堆 (min heap) 中，如果 P 是 C 的一个父级节点，那么 P 的 key（或 value) 应小于或等于 C 的对应值。 正因为此，堆顶元素一定是最小的，我们会利用这个特点求最小值或者第 k 小的值。
	最大堆类似。
##### binary search/sort tree
###### feature
	1. 若左子树不空，则左子树上所有节点的值均小于它的根节点的值；
	2. 若右子树不空，则右子树上所有节点的值均大于它的根节点的值；
	3. 左、右子树也分别为二叉排序树；
	4. 没有键值相等的节点。
	5. 中序遍历的结果是一个有序数组.
##### binary balance tree / AVL
	平衡指所有叶子的深度趋于平衡，更广义的是指在树上所有可能查找的均摊复杂度偏低。
	在 AVL 树中，任一节点对应的两棵子树的最大高度差为 1，因此它也被称为高度平衡树。查找、插入和删除在平均和最坏情况下的时间复杂度都是 O(logn)。增加和删除元素的操作则可能需要借由一次或多次树旋转，以实现树的重新平衡。
##### red black tree/ symmetric binary B tree
	红黑树是一种特殊的二叉查找树。红黑树是相对是接近平衡的二叉树。红黑树的每个节点上都有存储位表示节点的颜色，可以是红(Red)或黑(Black)。
###### feature
	a. 每个节点或者是黑色，或者是红色。
	b. 根节点是黑色。
	c. 每个叶子节点（NIL）是黑色。[注意：这里叶子节点，是指为空(NIL或NULL)的叶子节点！]
	d. 如果一个节点是红色的，则它的子节点必须是黑色的。
	e. 从一个节点到该节点的子孙节点的所有路径上包含相同数目的黑节点。
	红黑树的结构复杂，但它的操作有着良好的最坏情况运行时间。它可以在 O(logn) 时间内完成查找，插入和删除，这里的 n 是树中元素的数目。
###### 左旋，右旋，添加，删除
##### dictionary tree / prefix tree / Trie tree
	优点是：利用字符串的公共前缀来减少查询时间，最大限度地减少无谓的字符串比较，查询效率比哈希树高。
###### feature
	1. 根节点不包含字符，除根节点外每一个节点都只包含一个字符；
	2. 从根节点到某一节点，路径上经过的字符连接起来，为该节点对应的字符串；
	3. 每个节点的所有子节点包含的字符都不相同。
###### example
	用于统计，排序和保存大量的字符串（但不仅限于字符串），所以经常被搜索引擎系统用于文本词频统计。
##### 多路平衡查找树
###### B树
	为了提高磁盘或外部存储设备查找效率而产生的一种多路平衡查找树。
	阶数表示了一个结点最多有多少个孩子结点。
	我们将一个key和其对应的data称为一个记录。
	定义：
		a. 每个结点最多有m-1个关键字。
		b. 根结点最少可以只有1个关键字。
		c. 非根结点至少有Math.ceil(m/2)-1个关键字。
		d. 每个结点中的关键字都按照从小到大的顺序排列，每个关键字的左子树中的所有关键字都小于它，而右子树中的所有关键字都大于它。
		e. 所有叶子结点都位于同一层，或者说根结点到每个叶子结点的长度都相同。
###### B+树
	B树的变形结构，用于大多数数据库或文件系统的存储而设计。
	B+树特点：
		a. B+树包含2种类型的结点：内部结点（也称索引结点）和叶子结点。
		b. B+树与B树最大的不同是内部结点不保存数据，只用于索引，所有数据（或者说记录）都保存在叶子结点中。
		c. m阶B+树表示了内部结点最多有m-1个关键字, 阶数m同时限制了叶子结点最多存储m-1个记录。
		d. 叶子结点中的记录也按照key的大小排列。
		e. 每个叶子结点都存有相邻叶子结点的指针，叶子结点本身依关键字的大小自小而大顺序链接。
###### B和B+树的区别
	1. B树则所有节点都带有带有指向记录（数据）的指针（ROWID），B+树中只有叶子节点会带有指向记录(数据)的指针（ROWID）。
	2. B+树中每个叶子节点都包含指向下一个叶子节点的指针。所有叶子节点都是通过指针连接在一起，而B树不会。
	3. B+比B树更适合实际应用中操作系统的文件索引和数据库索引
		a. B+的磁盘读写代价更低。其内部结点相对B树更小，没有指向关键字具体信息的指针，从而页帧中索引项多 -> IO读写次数也就降低了。
		b. B+tree的查询效率更加稳定。B+树只要遍历叶子节点就可以实现整棵树的遍历。而且在数据库中基于范围的查询是非常频繁的，而B树不支持这样的操作（或者说效率太低）。
	
### graph
	一般的图题目第一步都是建图，第二步都是基于第一步的图进行遍历以寻找可行解。
	图的题目相对而言比较难，尤其是代码书写层面。但是就面试题目而言， 图的题目类型却不多，而且很多题目都是套模板就可以解决。
#### spanning tree
	一个连通图的生成树是指一个连通子图，它含有图中全部 n 个顶点，但只有足以构成一棵树的 n-1 条边。一颗有 n 个顶点的生成树有且仅有 n-1 条边，如果生成树中再添加一条边，则必定成环。在连通网的所有生成树中，所有边的代价和最小的生成树，称为最小生成树，其中代价和指的是所有边的权重和。
#### construction of graph
	一般图的题目都不会给你一个现成的图结构。当你知道这是一个图的题目的时候，解题的第一步通常就是建图。这里我简单介绍两种常见的建图方式。
##### 邻接矩阵
	使用一个 n * n 的矩阵来描述图 graph，其就是一个二维的矩阵，其中 graph[i][j] 描述边的关系。
##### 邻接表
	对于每个点，存储着一个链表，用来指向所有与该点直接相连的点。对于有权图来说，链表中元素值对应着权重。
#### traverse of graph
	不管是哪一种遍历， 如果图有环，就一定要记录节点的访问情况，防止死循环。当然你可能不需要真正地使用一个集合记录节点的访问情况，比如使用一个数据范围外的数据原地标记，这样的空间复杂度会是 \(O(1)\)。
##### DFS
	深度优先遍历图的方法是，从图中某顶点 v 出发， 不断访问邻居， 邻居的邻居直到访问完毕。
##### BFS
	广度优先搜索，可以被形象地描述为 “浅尝辄止”，它也需要一个队列以保持遍历过的顶点顺序，以便按出队的顺序再去访问这些顶点的邻接顶点。
##### common alg
###### shortest distance/path
####### dijkstra
	DIJKSTRA 算法主要解决的是图中单源到任意节点的最短距离。
	算法的基本思想是贪心，每次都遍历所有邻居，并从中找到距离最小的，本质上是一种广度优先遍历。这里我们借助堆这种数据结构，使得可以在 $logN$ 的时间内找到 cost 最小的点。
代码模板：
```python
import heapq

def dijkstra(graph, start, end):
    # 堆里的数据都是 (cost, i) 的二元祖，其含义是“从 start 走到 i 的距离是 cost”。
    heap = [(0, start)]
    visited = set()
    while heap:
        (cost, u) = heapq.heappop(heap)
        if u in visited:
            continue
        visited.add(u)
        if u == end:
            return cost
        for v, c in graph[u]:
            if v in visited:
                continue
            next = cost + c
            heapq.heappush(heap, (next, v))
    return -1

# test case
G = {
    "B": [["C", 1]],
    "C": [["D", 1]],
    "D": [["F", 1]],
    "E": [["B", 1], ["G", 2]],
    "F": [],
    "G": [["F", 1]],
}

shortDistance = dijkstra(G, "E", "C")
print(shortDistance)  # E -- 3 --> F -- 3 --> C == 6
```
####### Floyd_warshall 
	解决两个点距离的算法，只不过由于其计算过程会把中间运算结果保存起来防止重复计算，因此其特别适合求图中任意两点的距离。
	floyd_warshall 的基本思想是动态规划。该算法的时间复杂度是 (O(N^3))，空间复杂度是 (O(N^2))，其中 N 为顶点个数。
	简单来说就是： i 到 j 的最短路径 = i 到 k 的最短路径 + k 到 j 的最短路径的最小值。
代码模板：
```python
# graph 是邻接矩阵，v 是顶点个数
def floyd_warshall(graph, v):
    dist = [[float("inf") for _ in range(v)] for _ in range(v)]

    for i in range(v):
        for j in range(v):
            dist[i][j] = graph[i][j]

    # check vertex k against all other vertices (i, j)
    for k in range(v):
        # looping through rows of graph array
        for i in range(v):
            # looping through columns of graph array
            for j in range(v):
                if (
                    dist[i][k] != float("inf")
                    and dist[k][j] != float("inf")
                    and dist[i][k] + dist[k][j] < dist[i][j]
                ):
                    dist[i][j] = dist[i][k] + dist[k][j]
    return dist, v
```
###### A*寻路算法
	解决的问题是在一个二维的表格中找出任意两点的最短距离或者最短路径。常用于游戏中的 NPC 的移动计算，是一种常用启发式算法。一般这种题目都会有障碍物。除了障碍物，力扣的题目还会增加一些限制，使得题目难度增加。

	从起点开始，检查其相邻的四个方格并尝试扩展，直至找到目标。A 星寻路算法的寻路方式不止一种。
	公式表示为： f(n)=g(n)+h(n)。
	其中：
	f(n) 是从初始状态经由状态 n 到目标状态的估计代价，
	g(n) 是在状态空间中从初始状态到状态 n 的实际代价，
	h(n) 是从状态 n 到目标状态的最佳路径的估计代价。

	如果 g(n)为 0，即只计算任意顶点 n 到目标的评估函数 h(n)，而不计算起点到顶点 n 的距离，则算法转化为使用贪心策略的最良优先搜索，速度最快，但可能得不出最优解； 如果 h(n)不大于顶点 n 到目标顶点的实际距离，则一定可以求出最优解，而且 h(n)越小，需要计算的节点越多，算法效率越低，常见的评估函数有——欧几里得距离、曼哈顿距离、切比雪夫距离； 如果 h(n)为 0，即只需求出起点到任意顶点 n 的最短路径 g(n)，而不计算任何评估函数 h(n)，则转化为单源最短路径问题，即 Dijkstra 算法，此时需要计算最多的顶点；
	这里有一个重要的概念是估价算法，一般我们使用 曼哈顿距离来进行估价，即 H(n) = D * (abs ( n.x – goal.x ) + abs ( n.y – goal.y ) )。
###### 拓扑排序
	有向图的拓扑排序是对其顶点的一种线性排序，使得对于从顶点 u 到顶点 v 的每个有向边 uv， u 在排序中都在之前。当且仅当图中没有定向环时（即有向无环图），才有可能进行拓扑排序。 可判断有向图有无环。
####### Kahn 算法
	假设 L 是存放结果的列表，先找到那些入度为零的节点，把这些节点放到 L 中，因为这些节点没有任何的父节点。然后把与这些节点相连的边从图中去掉，再寻找图中的入度为零的节点。对于新找到的这些入度为零的节点来说，他们的父节点已经都在 L 中了，所以也可以放入 L。重复上述操作，直到找不到入度为零的节点。如果此时 L 中的元素个数和节点总数相同，说明排序完成；如果 L 中的元素个数和节点总数不同，说明原图中存在环，无法进行拓扑排序。
###### minimal spanning tree
####### Kruscal 
```
from typing import List, Tuple


def kruskal(num_nodes: int, num_edges: int, edges: List[Tuple[int, int, int]]) -> int:
    """
    >>> kruskal(4, 3, [(0, 1, 3), (1, 2, 5), (2, 3, 1)])
    [(2, 3, 1), (0, 1, 3), (1, 2, 5)]

    >>> kruskal(4, 5, [(0, 1, 3), (1, 2, 5), (2, 3, 1), (0, 2, 1), (0, 3, 2)])
    [(2, 3, 1), (0, 2, 1), (0, 1, 3)]

    >>> kruskal(4, 6, [(0, 1, 3), (1, 2, 5), (2, 3, 1), (0, 2, 1), (0, 3, 2),
    ... (2, 1, 1)])
    [(2, 3, 1), (0, 2, 1), (2, 1, 1)]
    """
    edges = sorted(edges, key=lambda edge: edge[2])

    parent = list(range(num_nodes))

    def find_parent(i):
        if i != parent[i]:
            parent[i] = find_parent(parent[i])
        return parent[i]

    minimum_spanning_tree_cost = 0
    minimum_spanning_tree = []

    for edge in edges:
        parent_a = find_parent(edge[0])
        parent_b = find_parent(edge[1])
        if parent_a != parent_b:
            minimum_spanning_tree_cost += edge[2]
            minimum_spanning_tree.append(edge)
            parent[parent_a] = parent_b

    return minimum_spanning_tree


if __name__ == "__main__":  # pragma: no cover
    num_nodes, num_edges = list(map(int, input().strip().split()))
    edges = []

    for _ in range(num_edges):
        node1, node2, cost = [int(x) for x in input().strip().split()]
        edges.append((node1, node2, cost))

    kruskal(num_nodes, num_edges, edges)
```
####### Prim
```python
import sys
from collections import defaultdict


def PrimsAlgorithm(l):  # noqa: E741

    nodePosition = []

    def get_position(vertex):
        return nodePosition[vertex]

    def set_position(vertex, pos):
        nodePosition[vertex] = pos

    def top_to_bottom(heap, start, size, positions):
        if start > size // 2 - 1:
            return
        else:
            if 2 * start + 2 >= size:
                m = 2 * start + 1
            else:
                if heap[2 * start + 1] < heap[2 * start + 2]:
                    m = 2 * start + 1
                else:
                    m = 2 * start + 2
            if heap[m] < heap[start]:
                temp, temp1 = heap[m], positions[m]
                heap[m], positions[m] = heap[start], positions[start]
                heap[start], positions[start] = temp, temp1

                temp = get_position(positions[m])
                set_position(positions[m], get_position(positions[start]))
                set_position(positions[start], temp)

                top_to_bottom(heap, m, size, positions)

    # Update function if value of any node in min-heap decreases
    def bottom_to_top(val, index, heap, position):
        temp = position[index]

        while index != 0:
            if index % 2 == 0:
                parent = int((index - 2) / 2)
            else:
                parent = int((index - 1) / 2)

            if val < heap[parent]:
                heap[index] = heap[parent]
                position[index] = position[parent]
                set_position(position[parent], index)
            else:
                heap[index] = val
                position[index] = temp
                set_position(temp, index)
                break
            index = parent
        else:
            heap[0] = val
            position[0] = temp
            set_position(temp, 0)

    def heapify(heap, positions):
        start = len(heap) // 2 - 1
        for i in range(start, -1, -1):
            top_to_bottom(heap, i, len(heap), positions)

    def deleteMinimum(heap, positions):
        temp = positions[0]
        heap[0] = sys.maxsize
        top_to_bottom(heap, 0, len(heap), positions)
        return temp

    visited = [0 for i in range(len(l))]
    Nbr_TV = [-1 for i in range(len(l))]  # Neighboring Tree Vertex of selected vertex
    # Minimum Distance of explored vertex with neighboring vertex of partial tree
    # formed in graph
    Distance_TV = []  # Heap of Distance of vertices from their neighboring vertex
    Positions = []

    for x in range(len(l)):
        p = sys.maxsize
        Distance_TV.append(p)
        Positions.append(x)
        nodePosition.append(x)

    TreeEdges = []
    visited[0] = 1
    Distance_TV[0] = sys.maxsize
    for x in l[0]:
        Nbr_TV[x[0]] = 0
        Distance_TV[x[0]] = x[1]
    heapify(Distance_TV, Positions)

    for i in range(1, len(l)):
        vertex = deleteMinimum(Distance_TV, Positions)
        if visited[vertex] == 0:
            TreeEdges.append((Nbr_TV[vertex], vertex))
            visited[vertex] = 1
            for v in l[vertex]:
                if visited[v[0]] == 0 and v[1] < Distance_TV[get_position(v[0])]:
                    Distance_TV[get_position(v[0])] = v[1]
                    bottom_to_top(v[1], get_position(v[0]), Distance_TV, Positions)
                    Nbr_TV[v[0]] = vertex
    return TreeEdges


if __name__ == "__main__":  # pragma: no cover
    # < --------- Prims Algorithm --------- >
    n = int(input("Enter number of vertices: ").strip())
    e = int(input("Enter number of edges: ").strip())
    adjlist = defaultdict(list)
    for x in range(e):
        l = [int(x) for x in input().strip().split()]  # noqa: E741
        adjlist[l[0]].append([l[1], l[2]])
        adjlist[l[1]].append([l[0], l[2]])
    print(PrimsAlgorithm(adjlist))
```
###### 二分图

## sort
### bucket sort 桶排序
	若干个桶；每个桶有容量；桶能够更匀称。
	思路：
	1. 将待排序的序列分到若干个桶中，每个桶内的元素再进行个别排序；
	2. 时间复杂度最好可能是线性O(n)，桶排序不是基于比较的排序；
	3. 桶排序是一种用空间换取时间的排序。
	