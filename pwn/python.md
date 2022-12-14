# python
## number transform  
* int(str, base) str -> int(Dec)  decimal  
* chr(num) num to character.  
* ord(ch) 将一个字符转换为它的整数值  
* hex(num) 将一个整数转换为一个十六进制字符串
* sys.maxsize/-sys.maxsize -1 : int 最大值/最小值

## str  
	str[begin:end]   str[(-begin):(-end)]  output str   元素不可修改.

	''' 
    '''支持换行的字符串。   
	格式化变量(sprintf): s='%s %d' %(str,integer) 
	upper(), lower() ： 大小写
### str2bytes
	1. str.encode(s) : str->bytes
	2. bytes.decode(b) : bytes->str
	注意，带上encoding.
### str reverse
	''.join(reversed(str)) ： reversed()返回的是迭代器
	s[::-1]
### format
	"{}{}...".format(str(num),ord(i)) : 自动识别变量类型。
## list  
- list: ls = [1,2,4] ls[:] output list  ls[index]; output 元素。  
- 修改列表中元素：ls[begin:end] = 'ele'  
- str2list: list(str) return list  
- list2str:''.join(list) return str  
- list 添加元素 ：append 方法  
- [(),()] 循环遍历，可以 for one, two.... in 容器:  
- index(ele) : 查找元素
- 列表推导式：[ expr(item) for item in iterable <if condition> ] , expr(item) 放入列表中。 
- reverse() ： 反向列表
- insert(index, value)
- sort(key=None, reverse=False) : 排序
	- 自定义排序，sort(key=functools.cmp_to_key(my_compare))，my_compare满足 -1,0,1
- copy() : 返回复制后的新列表。
- list() : 其他类型转list，有点强大。

### stack
	1. append() : 实现入栈
	2. pop() : 实现出栈
	
## tuple  
	tuple: 元素不可修改  (,,,,,)  

## set  
	set: {,,,,,,}, 无下标索引, 无相同元素。  
	set也是存储的键值对，只是默认键和值是相同的。Python中的dict和set都是通过散列表来实现的。查找时间为O(1)，以空间换时间。

	python变量内存：值相同的变量地址相同(常量)  

## dict
	map={key:value,,,,,} key 唯一。  = dict(key=value,,,)  
	添加元素：map[key] = value
	键是否在字典dict里: key in dict
	items() ： 返回pair对列表
	pop(key) : 删除字典给定键 key 所对应的<key, value> 

## computing
- x**y x的y乘方  
- 逻辑运算符： and  or not  
- x is y : 是否相同地址  is not  
- x in y : x 是否在可迭代对象中  not in  
- x+y : 序列x 与 序列y 拼接  
- x*n ： 序列x重复n次  

## if statement
```python  
if con1:  
elif con2:  
elif con3:  
....  
else:  
```
pass 空操作，只起一个占位符
  
## loop statement
for val in 可迭代对象:  
语句  

break  
continue  

while con1:  
语句  
else:  
语句  

range([beg,]end[,step])  生成可迭代对象。  

enumerate() 返回索引序列对象,同时获得每个元素的索引和值   for index,value in enumerate(ls):  

eval() 返回表达式的值。  

## function
	def function()  支持默认参数   *arg 不定长参数, **kw 用于字典。  
	语句  
	没有显示返回值，默认有return。
 
	注释的一般规则是参数名后跟一个冒号（：），然后再跟一个expression，这个expression可以是任何形式。
	返回值的形式是 -> int，annotation可被保存为函数的attributes。
	
	数字、字符或者元组等不可变对象类型都属于值传递，而字典dict或者列表list等可变对象类型属于引用传递。
	如果要想修改新赋值后原对象不变，则需要用到python的copy模块，即对象拷贝。

## module
	import module1 (as name)  
	模块中标识符，可module.标识符  
	__name__   当前模块的名称， 每个模块都有一个__name__  , __name__ = '__main__' 是直接运行的。  
	sys.argv  
	from module import * / 标识符n  
### struct模块  
	struct.pack(fmt, v1, v2,...) 用于将python的值根据格式化符,转换为字符串。  
	struct.unpack(fmt, string) 将字节流转换为python数据类型，返回一个元组。  
- I unsigned int  
- i int  
- b signed char  
- B unsigned char  
- f float  
- d double  

### random
- choice(str) : 从str中随机选择一个字符。
__init__.py  empty / 包含包的初始化代码或者设置__all__列表  

### heapq
	堆队列算法。
	小顶堆。
	1. heappop(heap) ：出堆
	2. heappush(heap, val) : 入堆
### set
	add() : 添加进集合
### queue
	队列。该模块也衍生出一些基本队列不具有的功能。
	Queue : 单向队列
	put : 存数据
	get : 取数据
	empty : 判断队列是否为空
	full : 检查队列是否已满
### math
	fatorial() : 以一个整数返回x 的阶乘。
### other
	支持函数指针  var = function  
	
	第三方工具安装： pip install module  
	
	global 声明变量是全局变量。在一个函数中要修改全局变量的值，必须使用global关键字声明使用该全局变量。  
	
	lambda para1,[para2,para3,...]:表达式  function  

	@function 装饰器注入代码。  
	def function(x,y):  
		def inner(x,y):  
			pass  
		return inner  
### file operation  
	open() 返回文件对象也是可迭代对象(按行迭代） encoding='utf-8' 读取中文。  
	如果是a+模式，第一次读取是在bof,若你写了文件，读指针就到了最后。  

### 自定义迭代器--生成器函数  
	函数定义中出现yield语句，是构造生成器对象的生成器函数。  
	使函数构造的生成器返回表达式，但它不结束而是停在这里，可以被唤醒继续工作。  

### 闭包  
	如果内层函数使用了外层函数中定义的局部变量，并且外层函数的返回值是内层函数的引用，就构成了闭包。  
	函数定义嵌套，nonlocal 可以使用外层函数定义的变量。  

### 类  
	基本数据也是类。  
	关键字。 class name:  
	创建类对象: classname()  
	支持动态的为已经创建的对象绑定新的属性。  
	类的普通方法：第一个参数需要对应调用方法的所使用的实例对象（一般是self），调用时不需要传入self，必须通过实例对象调用。  
	私有属性：以__开头的变量。类外访问时，访问时在变量前加上_类名。  
	构造函数：方法名为—__init__.  
析构函数：__del__.del删除对象会触发。  
__str__函数(返回值为字符串)：调用str、format、print对对象处理时触发.  
内置方法：__name__(self,...)  
继承： class name(parent class1, parent class2,...):  
存在方法覆盖。多态含义与c++、java等语言中的多态并不是同一含义。  
super方法获取父类的代理对象。super(parent class/object, super class)  
isinstance()判断一个对象所属的类是否是指定类或指定类的子类。  
issubclass()判断一个类是否是另一个类的子类。  
type()获取对象所属的类。  
类方法：@classmethod修饰的方法，其第一个参数是类本身。通过类对象或类调用。  
静态方法：@staticmethod修饰的方法，没有类方法的第一个参数。  
动态绑定方法：types模块的MethodType(function, object)，跟动态绑定属性类似，只能用这个对象调用。  
__slots__变量：所做的动态扩展属性限制只对__slots__所在类的实例对象有效.__slots__=('variable')  
@property装饰器：将类中**属性的访问和赋值操作**自动**转化为方法调用，可以做一些条件限定。直接使用property就可以定义getter.setter需要使用@属性名.setter的装饰器。只有getter而无setter，则只读。在setter和getter中使类内用self访问属性。
python类内部调用自己的成员函数必须加self，不论是私有（标志“__”）、保护(标志“_”)、公有(没有下划线)及系统成员__xxx__,访问内部的成员对象时，一定要加上self.xxx。

### python 线程、进程、协程
#### GIL Gloabla Interpreter Lock 全局解释器锁
	一段python代码可以通过CPython(默认)，PyPy，Psyco等不同的Python执行环境来执行。CPython 引入了 GIL 概念。GIL并不是Python的特性，Python完全可以不依赖于GIL。
	In CPython, the global interpreter lock, or GIL, is a mutex(互斥锁）that prevents multiple native threads from executing Python bytecodes at once. This lock is necessary mainly because CPython’s memory management is not thread-safe. (However, since the GIL exists, other features have grown to depend on the guarantees that it enforces.)
	
---
## python & c
- id() 函数用于获取对象的内存地址。
- ctypes.c_char_p(str) str的地址。

## python 沙盒
	以一定的方法模拟 Python 终端，实现用户对 Python 的使用。
	正常的 Python 沙箱会以黑名单的形式禁止使用一些模块如 os 或以白名单的形式只允许用户使用沙箱提供的模块，用以阻止用户的危险操作。
### python 沙箱逃逸
	绕过模拟的 Python 终端，最终实现命令执行。
#### 导入模块
	任意命令执行的函数：
```
os.system() os.popen()
commands.getstatusoutput() commands.getoutput()
commands.getstatus()
subprocess.call(command, shell=True) subprocess.Popen(command, shell=True)
pty.spawn()
timeit.timeit()
platform.popen().read()
```
	在 Python 中导入模块的方法通常有三种（xxx 为模块名称）：
	a. import xxx
	b. from xxx import *
	c. __import__('xxx')
	d. 通过路径引入模块
```
>>> import sys
>>> sys.modules['os']='/usr/lib/python2.7/os.py'
>>> import os
```
	e. 其他的危险函数举例 如 execfile 文件执行,timeit, exec, eval. 

---
## other  
- repr() 将对象转化为供解释器读取的形式，比如存在不可打印字符的字符串。  
- 右侧对齐(左侧填充)： str.rjust(width, char)    str.zfill(width)  
- 左侧对齐(右侧填充)： str.ljust(width, char)  
- str(int_data): 将整数转成字符串。
- 按位取反： num^0xff...
- 字符串16进制显示 : [bytes] 中hex()方法
- bin(x) : 返回字符串，一个整数 int 或者长整数 long int 的二进制表示。
- map(func, iterator) : 根据提供的函数对指定序列做映射。e.g, set(map(tuple, list[list]))
- from copy import deepcopy : deepcopy(); [].copy() 深拷贝对象 
