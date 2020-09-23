# reverse
  花指令，代码混淆，代码加壳加固(把真正的代码逻辑进行加密)，反调试,
---

## tools
- z3 线性规约求解器 
- sage 求公式
- PEID 查壳
- IAT 导入表重建工具
- ltrace ： 库追踪,也可以查看系统调用
- strace ：系统调用追踪
- ptrace系统调用 ：用于附加到进程上，并访问进程的代码、数据、堆栈和寄存器。 
---

## topic
### maze
  *#的组合构成maze,需要推出上下左右是哪几个字符代替。
---

## packer
### upx
- upx -d file #unpack
---

## checksum
1. 查表法实现校验
2. 阅读源码，思考破解
---

## hash
### md5
  128bit,每四位转一个16进制数，就一共32个数字。有时候有16个数字的hash值，是将32位md5去掉前八位，去掉后八位得到的。
#### openssl c
1. MD5_Init(MD5_CTX *c) : 初始化 MD5 Contex, 成功返回1,失败返回0
2. MD5_Update(MD5_CTX *c, const void *data, size_t len); : 循环调用此函数,可以将不同的数据加在一起计算MD5,成功返回1,失败返回0.
3. MD5_Final(unsigned char *md, MD5_CTX *c); : 输出MD5结果数据,成功返回1,失败返回0
