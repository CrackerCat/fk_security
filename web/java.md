# java
## base knowledge
### accessor & mutator
	get and set.
### this
	class本身。
### aggregation & composition
	aggregation:  A directional association between objects. When an object ‘has-a’ another object, then you have got an aggregation between them. Direction between them specified which object contains the other object. Aggregation is also called a “Has-a” relationship -> 父类 有其他类的对象（0个或多个)。 从属对象可以不存在父对象而存在。
	composition: 是一种特殊的aggregation, 但是必须存在，是不可分割的一部分。 没有父对象就不能存在从属对象。
### Inheritance
	extends关键字。
	一个覆盖父类方法的同名方法应该比父类方法拥有相同或更高的透明度。
	在child class当中， 可以拥有weaker（定义域更为广阔）的precondition.
	在child class当中， 可以拥有strong（定义域更为狭窄）的postcondition.
### Generic
	Upper bound指的是任何的subclass可以使用. e.g. List<? extends Number> list <-(传递)- List<Integer> list1.
	Lower bound指的是任何的supeclass可以使用. e.g. List<? super Integer> list <-(传递)- List<Object> list1.
### javadoc
	书写规范：
	1. 功能。
	2. 描述参数。 @param var
		Parameter:
	3. 描述返回值。 @return ***.
		Returns:
	4. 描述异常。 @throws ***.
		Throws:
	5. @pre 成功执行的前提。
		@pre
### Exception
	If an overidden method throws an exception, the super method also must throw the same or higher level of exception because of polymorphism.
- BadInputException.
- IllegalArgumentException.
- RuntimeException.
- IndexOutOfBoundsException.

### abstract
	抽象函数没函数体。
	抽象类既有抽象和非抽象函数。
### special function.
- equals(): java.lang.Object 类的方法。
	- Object类中的equals方法是用来比较“地址”的。 对于非字符串来说是比较其指向的对象是否相同的。
	- 字符串类比较所包含的内容是否相同。
	- 它的实现： 判断变量地址是否相同，目标对象是否为空，getClass()是否相同，最后判断属性。
- == : 比较两个变量本身的值。