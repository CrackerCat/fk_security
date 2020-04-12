# IDA的使用  
## 基本命令  
- f5反汇编  
- n修改函数名,变量名  
- y修改类型  
- x跟踪变量引用,查看哪些地方用到了它  
- shift + f5 导入外部库。  
- shift + f12 打开string window。  
- ctrl+alt+k 修改汇编，打patch,再点击edit应用patch到input file.  

## flirt  
IDA用于识别库代码序列的一项技术，解决静态链接的问题。模式匹配算法  
IDA自带的签名文件保存在 IDADIR/sig目录，大多数是windows编译器自带的库。  
如果二进制去除了符号，只要拥有相关签名，IDA仍然能够识别库函数。  
一般应用libc、libcrypto、libkrb5、libresolv及其他库的签名。  
### sig步骤  
# pelf 生成模式文件  
./pelf static_lib_file lib.pat  
# sigmake 生成签名文件  
./sigmake lib.pat lib-arch.sig  
注： sigmake 出错时，修改exc文件，将上面的注释删掉，取部分冲突函数，在它们前面加 '+'  
### 查找版本信息  
strings -a binary |grep GCC  
