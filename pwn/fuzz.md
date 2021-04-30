# fuzz
## base usage
### afl-gcc
	使用 afl 插桩编译程序。
	a. afl-gcc -g -o binary source_code.c
	b. ./configure CC="afl-gcc" CXX="afl-g++"
	c. 程序不是用autoconf构建，那么直接修改Makefile文件中的编译器为afl-gcc/g++。

	为了后期更好的分析crash，在此处可以开启Address Sanitizer(ASAN)这个内存检测工具，此工具可以更好的检测出缓存区溢出、UAF 等内存漏洞，开启方法如下:
```
AFL_USE_ASAN=1 ./configure CC=afl-gcc CXX=afl-g++ LD=afl-gcc--disable-shared
AFL_USE_ASAN=1 make
```
	不使用 AFL 编译插桩时，可使用以下方式开启 Address Sanitizer。
```
./configure CC=gcc CXX=g++ CFLAGS="-g -fsanitize=address"
make
```
### afl-fuzz
	1. 从stdin读取输入
```
$ ./afl-fuzz -i testcase_dir -o findings_dir /path/to/program […params…]
```
	2. 从文件读取输入, @@就是占位符，表示输入替换的位置
```sh
$ ./afl-fuzz -i testcase_dir -o findings_dir /path/to/program @@
```
#### fuzz results
	从界面上主要注意以下几点:
	1. last new path 如果报错那么要及时修正命令行参数，不然继续fuzz也是徒劳（因为路径是不会改变的）；
	2. cycles done 如果变绿就说明后面及时继续fuzz，出现crash的几率也很低了，可以选择在这个时候停止
	3. uniq crashes 代表的是crash的数量
	4. queue：存放所有具有独特执行路径的测试用例。
	5. hangs：导致目标超时的独特测试用例。
	6. crashes/README.txt：保存了目标执行这些crash文件的命令行参数。
#### crash 分析
	xxd命令的作用就是将一个文件以十六进制的形式显示出来。
	crash文件，那么分析的话只需要将其作为之前vuln文件的输入，使用gdb调试分析就可以得到详细结果了，但是在这之前可以使用xxd看一下其中数据的内容做一个初步的判断。
## common tools
### AFL
	原始版本的AFL支持使用QEMU模式来对待测目标进行黑盒测试。
### drAFL
	AFL配合DynamoRIO。在drAFL的帮助下，我们就可以在没有源代码的情况下对LInux二进制代码进行模糊测试了。
#### usage
	使用前，先参考[1]中搭建环境，再使用下列命令进行fuzz。
```sh
cd build

mkdir in

mkdir out

echo "AAAA" > in/seed

export DRRUN_PATH=/home/max/drAFL/build_dr/bin64/drrun # 定义DRRUM_PATH值来指向drrun启动工具

export LIBCOV_PATH=/home/max/drAFL/build/libbinafl.so # 设置LIBCOV_PATH来指向libbinafl.so代码覆盖库

export AFL_NO_FORKSRV=1 # 设置AFL的fork服务器

export AFL_SKIP_BIN_CHECK=1

afl/afl-fuzz -m none -i in -o out -- target_binary @@
```

## reference
- [1] https://www.freebuf.com/articles/system/203302.html : 在没有源代码的情况下对Linux二进制代码进行模糊测试, drAFL。
- [2] http://zeroyu.xyz/2019/05/15/how-to-use-afl-fuzz/ : fuzz的使用教程。