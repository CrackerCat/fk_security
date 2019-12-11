int(str, base) str -> int(Dec)  decimal

## str
str[begin:end]   str[(-begin):(-end)]  output str   元素不可修改.

## list
list: ls = [1,2,4] ls[:] output list  ls[index]; output 元素。
修改列表中元素：ls[begin:end] = 'ele'
str2list: list(str) return list
list2str:''.join(list) return str
list 添加元素 ：append 方法
[(),()] 循环遍历，可以 for one, two.... in 容器:

## tuple
tuple: 元素不可修改  (,,,,,)

## set
set: {,,,,,,}, 无下标索引, 无相同元素。

python变量内存：值相同的变量地址相同(常量)

{key:value,,,,,} key 唯一。  = dict(key=value,,,)

格式化变量(sprintf): s='%s %d' %(str,integer)

x**y x的y乘方

逻辑运算符： and  or not

x is y : 是否相同地址  is not

x in y : x 是否在可迭代对象中  not in

x+y : 序列x 与 序列y 拼接

x*n ： 序列x重复n次

条件语句：
	if con1:
	elif con2:
	elif con3:
	....
	else:


pass 空操作，只起一个占位符

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

def function()  支持默认参数   *arg 不定长参数, **kw 用于字典。
    语句
没有显示返回值，默认有return

import module1 (as name)
模块中标识符，可module.标识符
__name__   当前模块的名称， 每个模块都有一个__name__  , __name__ = '__main__' 是直接运行的。
sys.argv
from module import * / 标识符n


__init__.py  empty / 包含包的初始化代码或者设置__all__列表


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
class name:
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
@property装饰器：将类中**属性的访问和赋值操作**自动**转化为方法调用，可以做一些条件限定。直接使用property就可以定义getter.setter需要使用@属性名.setter的装饰器。只有getter而无setter，则只读。在setter和getter中使用self访问属性时，需要在变量名前加上_.

## struct模块
struct.pack(fmt, v1, v2,...) 用于将python的值根据格式化符,转换为字符串。  
struct.unpack(fmt, string) 将字节流转换为python数据类型，返回一个元组。  
- I unsigned int
- i int
- b signed char
- B unsigned char
- f float
- d double

## other
repr() 将对象转化为供解释器读取的形式，比如存在不可打印字符的字符串。
右侧对齐(左侧填充)： str.rjust(width, char)    str.zfill(width)
左侧对齐(右侧填充)： str.ljust(width, char)
