# IDA的使用
## theory
	在反汇编过程中，首先会按照程序字节地址顺序得到汇编代码，再根据汇编指令识别函数边界，进行自动的函数划分。  
## 基本命令  
- f5反汇编  
- n修改函数名,变量名  
- y修改类型,调用方式
- x跟踪变量引用,查看哪些地方用到了它  
- shift + f5 导入外部库。  
- shift + f12 打开string window。  
- ctrl+alt+k 修改汇编，打patch,再点击edit应用patch到input file.  
- tab 切换反编译窗口和汇编窗口
- shift + E 导出数据
- packer + apply patches to input file : 修改二进制文件

## 识别汇编中的C代码结构
### 反汇编数组
	数组是通过使用一个基地址作为起始点来进行访问。 每一个元素的大小通常并不总是明显的，但是它可以通过看这个数组是如何被索引的来进行判断。
```
mov dword_40A000[ecx*4], eax # int a[]
mov [ebp+ecx*4+var_14], edx # int b[]
```
### 识别结构体
	结构体通过一个作为起始指针的基地址进行访问。
	要判断附近的数据字节类型是同一个结构的组成部分，还是只是凑巧相互挨着是比较困难的，这依赖这个结构体的上下文。 通过加载和赋值，判断每个字段的类型。
```
mov ecx, [ebp+arg_0] # 结构体基址
fstp qword ptr [ecx+18h] # 18h处是double类型
mov byte ptr [eax+14h], 61h # 14h处是字符类型
```
#### 手动创建结构体
	1. 打开创建结构体的 Subview，点击工具栏 View->Open Subview->Structures
	2. 按键盘 I 弹出结构体的创建窗口，输入 Structure name
	3. 在结构体的 ends 行，按键盘 d 键，创建新的结构体成员。
	4. 在结构体成员初按 d 键，修改数据类型（db dw dd dq），右键点击 Array 可以创建数组。
	OR
	1. 打开 Local Types subview：View->Open Subviews->Local Types
	2. 输入结构体定义. Insert...
	OR
	1. 打开创建结构体的 Subview，点击工具栏 View->Open Subview->Structures
	2. 导入标准结构体。 Add structure type... -> Add standard structure. 
	最后一步。
	5. 最后在反编译代码中修改变量类型。 -- set item type...
## flirt  
	IDA用于识别库代码序列的一项技术，解决静态链接的问题。模式匹配算法  
	IDA自带的签名文件保存在 IDADIR/sig目录，大多数是windows编译器自带的库。  
	如果二进制去除了符号，只要拥有相关签名，IDA仍然能够识别库函数。  
	一般应用libc、libcrypto、libkrb5、libresolv及其他库的签名。  
### sig步骤  
#### pelf 生成模式文件  
	./pelf static_lib_file lib.pat  
#### sigmake 生成签名文件  
	./sigmake lib.pat lib-arch.sig  
	注： sigmake 出错时，修改exc文件，将上面的注释删掉，取部分冲突函数，在它们前面加 '+'  
#### 查找版本信息  
	strings -a binary |grep GCC  

## IDApython
	ida默认安装了idapython。
### 配置python
1. 利用msi安装python27/38，配置python路径。
	* 测试
		* 利用sys.path打印路径。
	* 安装库文件
		* ida中python.exe -m pip install lib
		* 需要安装到ida内的python的Lib(2.7/3.8)\site-packages下。
		* idapythonswitch.exe 换python。 结合资料。
2. script file 选项
	* 运行插件脚本。
3. 安装插件
	* 将插件复制到IDA的plugins目录中。

### 重要函数解析
- MakeCode(ea) #分析代码区，相当于ida快捷键C
- ItemSize(ea) #获取指令或数据长度
- GetMnem(ea) #得到addr地址的操作码
- GetOperandValue(ea,n) #返回指令的操作数的被解析过的值
- PatchByte(ea, value) #修改程序字节
- Byte(ea) #将地址解释为Byte
- MakeUnkn(ea,0) #MakeCode的反过程，相当于ida快捷键U
- MakeFunction(ea,end) #将有begin到end的指令转换成一个函数。如果end被指定为BADADDR（-1），IDA会尝试通过定位函数的返回指令，来自动确定该函数的结束地址

## other
### 修改数据类型
	点击数据类型，然后按d修改数据格式，然后右键，可以选择array，转换成数组。