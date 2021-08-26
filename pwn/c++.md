# C++ syntax
## 类
	1. 初始化列表初始化的顺序和变量声明的顺序一致，和初始化中的顺序无关。
	2. 拷贝构造函数参数一定需要以const形式，引用传递。值传递会导致递归调用。
## STL
### std::priority_queue
	1. priority_queue<int> q; : 大顶堆
	2. priority_queue<int, vector<int>, greater<int> > q; ： 小顶堆
	3. empty() ： 判空
	4. pop() ： 删除第一个元素
	5. push() 加入一个元素
	6. size() 返回优先队列中拥有的元素的个数
	7. top() 返回优先队列中有最高优先级的元素
## C++ 11
### shared_ptr 类
	1. shared_ptr 允许多个指针指向同一个对象，引用计数，智能指针也是模板。
	2. 智能指针的使用方式与普通指针类似。
	3. get() : 返回p中保存的指针，要小心使用。
	4. swap(p) : 和智能指针 p 交换指针。
	5. make_shared<T>() : 最安全的分配和使用动态内存的方法,在动态内存中分配一个对象并初始化它，返回指向此对象的shared_ptr。
	6. 原子操作解决了引用计数的计数的线程安全问题，但是当智能指针发生拷贝的时候，标准库的实现是先拷贝智能指针，再拷贝引用计数对象，导致智能指针指向的对象的线程安全问题。
### unique_ptr 类
	1. unique_ptr 独占所指向的对象。某个时刻只能有一个unique_ptr指向一个给定对象。当unique_ptr被销毁时，它所指向的对象也被销毁。
	2. make_unique<T>() : 最安全的分配和使用动态内存的方法,在动态内存中分配一个对象并初始化它，返回指向此对象的unique_ptr。
	3. release() : u放弃对指针的控制权，返回指针，并将u置空
	4. reset() : 释放u指向的对象
	5. reset() : 如果提供了内置指针q，令u指向这个对象；否则将u置空.
	6. get() : 返回p中保存的指针，要小心使用。
	7. swap(p) : 和智能指针 p 交换指针。
	8. 非线程安全。
### array 类
	array 是对定长数组的封装。
	1. array和数组属于定长容量。
### vector 类
	array 是对变长数组的封装。
	1. vector属于变长容器。
# pwn in c++
## virtual function table
![](image/virtual_function_table_in_c++.jpg "")
### exploit
	Vtable 劫持，使vfptr指向伪造的虚表。

## vector
	分配在heap段。
	比⼀一般 c 中的陣列列更更有彈性，當空間不夠⼤大時，會重新兩兩倍⼤大的的⼩小來來放置新的 vector ，再把原本的空間還給系統。
```c
Vector
• member
	• _M_start : vector 起始位置
		• vector::begin()，迭代器/二级指针。
	• _M_finish : vector 結尾位置
		• vector::end(), 因此push_back()/pop_back()就是移动这个成员变量。
	• _M_end_of_storage ：容器最後位置
		• if _M_finish == _M_end_of_storage in push_back
			• It will alloca a new space for the vector
			• 把原来的元素复制过来，再将其delet掉
			• 以這個來來判斷空間是否⾜足夠放元素
```

## string 
	g++<5 的一种实现方式：
```
String
• member
	• size ：字串串的長度
	• Capacity : 該 string 空間的容量量, 不斷以⼆二的指數倍增長.
	• reference count : 引⽤用計數
		• 只要有其他元素引⽤用該字串串就會增加
		• 如果其他元素不引⽤用了了，也會減少
			• 當 reference == 0 時就會，將空間 delete 掉
	• value : 存放字串串內容
```
	g++>5
```
String
• g++ > 5 之後取消了了 Copy-on-Write 機制
	• 所以少掉了了 reference count 這個欄欄位
• 在 data length
	• <= 15 時會⽤用 local buffer
	• > 15 時則會在 heap allocate 空間給 data 使⽤用

----->
String
• data pointer : 指向 data 位置
• size : 分配出去 string 的長度
• union
	• local buffer : 在 size <= 15 時會直接把這欄欄位拿來來存data
	• allocated capacity : size > 15 時會拿來來紀錄 capcity
```

## New & Delete
	• new 大致上流程
		• operator new
			• 與 malloc 類似，單純配置記憶體空間，但配置失敗會進入 exception, ⽽ malloc 則是返回 null ，有點像在 malloc 上⼀一層 wrapper
		• constructor

	• delete ⼤致上流程
		• destructor
		• operator delete
			• 與 free 類似，釋放⼀一塊記憶體空間，有點像是在 free 上⼀一層 wrapper

## copy constructor & assignment operator
![](image/copy_constructor&assignment_operator.jpg "")
这个代码导致了double free.
### copy constructor
	• c++ 在進⾏行行複製 object 會使⽤用 copy constructor
		• 通常 class 的 member 有指標時，就需要⾃行去實作
	• 若未特別定義則會使⽤用 default copy constructor
		• 只做 shallow copy

	何時會使⽤用 copy constructor
	• func(Stu stu) 函数传递对象。
	• return stu
	• vector 等 STL 容器
### assignment operator
	c++ 在進⾏行行 “=“ 這個 operator 時 object 會使⽤用assignment operator 這個 function 去 assign object 的值
		• 通常 class 的 member 有指標時，就需要⾃行去實作
	• 若若未特別定義則會使⽤用 default assignment operator
		• 只做 shallow copy

	何時會使⽤用 assignment operator
	• stuA = stuB
	• vector 等 STL 容器
	• e.g. vector.erase()
	• …

## STL
### 智能指针
	在使用智能指针时，防止其和原始指针的混用，否则可能导致对象生命周期问题，例如 UAF 等安全风险。
	例如。
```
fool_u_ptr = make_unique<Foo>(1);
// 从独占智能指针中获取原始指针,<Foo>(1)，调用高危函数。
pfool_raw_ptr = fool_u_ptr.get();

// 独占智能指针重新赋值后会释放内存
fool_u_ptr = make_unique<Foo>(2);
// 通过原始指针操作会导致UAF，pfool_raw_ptr指向的对象已经释放
// 使用 pfool_raw_ptr;
```
	关联漏洞：高风险-内存破坏。