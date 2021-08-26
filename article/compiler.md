# compiler
## code generation and optimization
### Raising Binaries to LLVM IR with MCTOLL (WIP)
	只使用LLVM基础架构，llvm-mctoll 将 x86_64/arm32等 executables 翻译成 LLVM IR (bitcode/text)。另外，还有一个好处是，可以将 x86_64 binary 翻译成 其他 target ISA (如 ARM64 or RISC64)。
#### 个人理解
	对于 wllvm, 将 unmodified c or c++ 源代码 翻译成 LLVM IR。因此，wllvm 是 llvm-mctoll的子集。