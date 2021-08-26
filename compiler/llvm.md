# llvm  
## llvm bitcode compilation
	编译类型分为单文件编译和多文件编译。

	多文件编译依靠一个wllvm工具，其编译流程如下。
```
$ export LLVM_COMPILER=clang
$ export LLVM_COMPILER_PATH=*llvm*/bin
$ 设置交换空间(可选)
$ CC=wllvm make
```
### kernel compilation
```
$ export LLVM_COMPILER=clang
$ export LLVM_COMPILER_PATH=*llvm*/bin
$ 设置交换空间(可选)
$ make defconfig
$ make -j8 CC=wllvm
```
	使用过程中遇到的问题：
	1. Compiler lacks asm-goto support
		直接删除相应Makefile中的检测。
	2. bcmp相关的问题
		在Makefile中添加 CLANG_FLAGS= -fno-builtin-bcmp

## clang
-  -Xclang -disable-O0-optnone -O0 : 生成SSA形式的IR.
- ./configure CFLAGS="para"  : CFLAGS 等价于 CFLAG="-S"
- --emit-llvm : 生成IR文件

## llvm pass  
	能够将代码进行转化和优化，所有pass都是Pass类的子类。
	通过覆盖Pass类的虚函数来实现功能。  
## cross compilation
### reference
- http://llvm.org/docs/HowToCrossCompileLLVM.html ： clang交叉编译使用文章
- http://clang.llvm.org/docs/CrossCompilation.html ： clang交叉编译的解释性文章
- https://www.thinbug.com/q/24204199 : 问题