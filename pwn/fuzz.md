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
# 将testcases放入testcase_dir的文本中。
$ ./afl-fuzz -i testcase_dir -o findings_dir /path/to/program @@
```
#### fuzz results
	从界面上主要注意以下几点:
	1. last new path 如果报错那么要及时修正命令行参数，不然继续fuzz也是徒劳（因为路径是不会改变的）；
	2. cycles done 如果变绿就说明后面即使继续fuzz，出现crash的几率也很低了，可以选择在这个时候停止
	3. uniq crashes 代表的是crash的数量
	4. queue：存放所有具有独特执行路径的测试用例。
	5. hangs：导致目标超时的独特测试用例。
	6. crashes/README.txt：保存了目标执行这些crash文件的命令行参数。
	7. map density : 插桩分支哈希表的密度。 越大，碰撞的概率越大。
#### crash 分析
	xxd命令的作用就是将一个文件以十六进制的形式显示出来。
	crash文件，那么分析的话只需要将其作为之前vuln文件的输入，使用gdb调试分析就可以得到详细结果了，但是在这之前可以使用xxd看一下其中数据的内容做一个初步的判断。
### theory
	主要是AFL(American Fuzzy Lop)。
	afl-gcc.c 和 afl-fuzz.c
#### AFL内部实现细节
##### 代码插桩
	使用 afl-gcc/afl-clang等工具编译目标，在这个过程中会对其进行插桩。
	afl-gcc.c本质上是一个 gcc 的 wrapper。
	
	对于编译过程，源代码 -编译-> 汇编代码 -汇编-> 二进制。
	在linux下的汇编器是 as. 
	因此，代码插桩，就是在源代码编译成汇编代码后，通过 `afl-as` 完成。 即 afl-as.c。
	其大致逻辑： 处理汇编代码，在分支处插入桩代码，并最终调用 `as` 汇编成二进制文件。
```
//将格式化字符串添加到汇编文件的相应位置
fprintf(outf, use_64bit ? trampoline_fmt_64 : trampoline_fmt_32, R(MAP_SIZE));
// 对于trampoline_fmt_32 格式化字符串 -- 汇编代码
// 1. 保存edi等寄存器。
// 2. 将ecx的值设置R(MAP_SIZE)。
// 3. 调用方法__afl_maybe_log()。 作为插桩代码所执行的实际内容。 插桩后的target，会记录执行过程中的分支信息；随后，fuzzer便可以根据这些信息，判断这次执行的整体流程和代码覆盖情况。
// 4. 恢复寄存器。

// 对于 R(MAP_SIZE)
// R(x)的定义是(random() % (x))。
// R(MAP_SIZE)即为0到MAP_SIZE之间的一个随机数。
// 在处理到某个分支，需要插入桩代码时，afl-as会生成一个随机数，作为运行时保存在ecx中的值。而这个随机数，便是用于标识这个代码块的key。
```
##### fork server
	编译完 target 后，可以通过 `afl-fuzz` 开始fuzzing。
	大致思路： 对输入的seed文件不断地变异，并将这些 mutated input 输入target执行，检查是否造成崩溃。
	因此，fuzzing涉及大量的fork和执行target的过程。
	
	为了更高效地执行上述过程，AFL实现了一套 fork server 机制。
	基本思路如下。
		1. 启动target进程。 target是由fork server控制；
		2. fuzzer不负责fork子进程，而是与这个fork server通信，并由fork server来完成fork及继续执行目标的操作。
		注： 这样设计的最大好处，就是不需要调用execve()，从而节省了载入目标文件和库、解析符号地址等重复性工作。  
		
		进程结构图？
		fuzzer -> fork server -> process1
							  -> process2
							  -> ...							  
	
	fork server 具体运行原理如下。
	1. 首先，fuzzer 执行 fork() 得到父进程和子进程。 这里的父进程仍然为fuzzer，子进程则为target进程，即将来的fork server。
		a. 父子进程之间，是通过管道进行通信。具体使用了2个管道，一个用于传递状态，另一个用于传递命令。
		b. 对于子进程（fork server），会进行一系列设置，其中包括将上述两个管道分配到预先指定的fd，并最终执行target。
		c. 对于父进程（fuzzer），则会读取状态管道的信息，如果一切正常，则说明fork server创建完成。
	2. fork server与fuzzer的通信方法。
		a. fork server侧的具体操作，即插桩的__afl_maybe_log()。 
			i. 首先，通过写入状态管道，fork server会通知fuzzer，其已经准备完毕，可以开始fork了，而这正是上面提到的父进程等待的信息；
			ii. fork server进入等待状态__afl_fork_wait_loop，读取命令管道，直到fuzzer通知其开始fork；
			iii. 一旦fork server接收到fuzzer的信息，便调用fork()，得到父进程和子进程；
			iV. 子进程是实际执行target的进程，其跳转到__afl_fork_resume。在这里会关闭不再需要的管道，并继续执行；
			V. 父进程则仍然作为fork server运行，其会将子进程的pid通过状态管道发送给fuzzer，并等待子进程执行完毕；一旦子进程执行完毕，则再通过状态管道，将其结束状态发送给fuzzer；之后再次进入等待状态__afl_fork_wait_loop。
		b. fuzzer侧的具体操作。
			i. 在fork server启动完成后，一旦需要执行某个测试用例，则fuzzer会调用run_target()方法。在此方法中，便是通过命令管道，通知fork server准备fork；并通过状态管道，获取子进程pid；
			ii. 随后，fuzzer再次读取状态管道，获取子进程退出状态，并由此来判断子进程结束的原因，例如正常退出、超时、崩溃等，并进行相应的记录。
##### 共享内存
	AFL使用共享内存，来完成以上信息在fuzzer和target之间的传递。
	具体实现方式如下。
	1. fuzzer在启动时，会执行setup_shm()方法进行配置。其首先调用shemget()分配一块共享内存，大小MAP_SIZE为64K；
	2. 分配成功后，该共享内存的标志符会被设置到环境变量中，从而之后fork()得到的子进程可以通过该环境变量，得到这块共享内存的标志符：
	3. fuzzer本身，会使用变量trace_bits来保存共享内存的地址；
	4. 在每次target执行之前，fuzzer首先将该共享内容清零；
	5. target是如何获取并使用这块共享内存的, 相关代码同样也在上面提到的方法__afl_maybe_log()中；
		i. 首先，会检查是否已经将共享内存映射完成：
		ii. __afl_area_ptr中保存的就是共享内存映射到target的内存空间中的地址，如果其不是NULL，便保存在ebx中继续执行；否则进一步跳转到__afl_setup;
		iii. __afl_setup处会做一些错误检查，然后获取环境变量AFL_SHM_ENV的内容并将其转为整型,这里获取到的，便是之前fuzzer保存的共享内存的标志符;
		iV. 最后，通过调用shmat()，target将这块共享内存也映射到了自己的内存空间中，并将其地址保存在__afl_area_ptr及edx中。由此，便完成了fuzzer与target之间共享内存的设置。 
		注: 如果使用了fork server模式，那么上述获取共享内存的操作，是在fork server中进行；随后fork出来的子进程，只需直接使用这个共享内存即可。
##### 分支信息的记录
	AFL是根据二元tuple(跳转的源地址和目标地址)来记录分支信息，从而获取target的执行流程和代码覆盖情况。伪代码如下。
```
cur_location = <COMPILE_TIME_RANDOM>; //R(MAP_SIZE)
prev_location = cur_location >> 1; // 解决 A->B and B->A 和 A->A and B->B。
shared_mem[cur_location ^ prev_location]++;  
```
	AFL为每个代码块生成一个随机数，作为其“位置”的记录；随后，对分支处的”源位置“和”目标位置“进行异或，并将异或的结果作为该分支的key，保存每个分支的执行次数。用于保存执行次数的实际上是一个哈希表，大小为MAP_SIZE=64K，当然会存在碰撞的问题；但根据AFL文档中的介绍，对于不是很复杂的目标，碰撞概率还是可以接受的。
	由上述分析可知，之前提到的共享内存，被用于保存一张哈希表，target在这张表中记录每个分支的执行数量。随后，当target执行结束后，fuzzer便开始对这张表进行分析，从而判断代码的执行情况。
##### 分支信息的分析
	1. 首先，fuzzer对trace_bits（共享内存）进行预处理。
		i. target是将每个分支的执行次数用1个byte来储存，而fuzzer则进一步把这个执行次数归入以下的buckets中。举个例子，如果某分支执行了1次，那么落入第2个bucket，其计数byte仍为1；如果某分支执行了4次，那么落入第5个bucket，其计数byte将变为8，等等。	这样处理之后，对分支执行次数就会有一个简单的归类。例如，如果对某个测试用例处理时，分支A执行了32次；对另外一个测试用例，分支A执行了33次，那么AFL就会认为这两次的代码覆盖是相同的。当然，这样的简单分类肯定不能区分所有的情况，不过在某种程度上，处理了一些因为循环次数的微小区别，而误判为不同执行结果的情况。
```
static const u8 count_class_lookup8[256] = {

  [0]           = 0, 
  [1]           = 1, 
  [2]           = 2, 
  [3]           = 4, 
  [4 ... 7]     = 8, 
  [8 ... 15]    = 16,
  [16 ... 31]   = 32,
  [32 ... 127]  = 64,
  [128 ... 255] = 128

};
```
	2. 对于某些mutated input来说，如果这次执行没有出现崩溃等异常输出，fuzzer还会检查其是否新增了执行路径。
		i. 是对trace_bits计算hash并来实现。 通过比较hash值，就可以判断trace_bits是否发生了变化，从而判断此次mutated input是否带来了新路径，为之后的fuzzing提供参考信息。
##### conclusion
	1. AFL是如何判断一条路径是否是favorite的、如何对seed文件进行变化，等等
	2. 如果需要在AFL的基础上进一步针对特定目标进行优化，那么了解AFL的内部工作原理就是必须的了。

#### AFL文件变异一览
	AFL维护了一个队列(queue)，每次从这个队列中取出一个文件，对其进行大量变异，并检查运行后是否会引起目标崩溃、发现新路径等结果。变异的主要类型如下。
	* bitflip，按位翻转，1变为0，0变为1
	* arithmetic，整数加/减算术运算
	* interest，把一些特殊内容替换到原文件中
	* dictionary，把自动生成或用户提供的token替换/插入到原文件中
	* havoc，中文意思是“大破坏”，此阶段会对原文件进行大量变异
	* splice，中文意思是“绞接”，此阶段会将两个文件拼接起来得到一个新的文件
	注: 前四项是非dumb mode（-d）和主fuzzer（-M）会进行的操作，由于其变异方式没有随机性，所以也称为deterministic fuzzing。 havoc和splice则存在随机性，是所有状况的fuzzer（是否dumb mode、主从fuzzer）都会执行的变异。
##### bitflip
###### 生成effector map
	effector map几乎贯穿了整个deterministic fuzzing的始终。
	实现逻辑： 在对每个byte进行翻转时，如果其造成执行路径与原始路径不一致，就将该byte在effector map中标记为1，即“有效”的，否则标记为0，即“无效”的。
	这样做的逻辑是：如果一个byte完全翻转，都无法带来执行路径的变化，那么这个byte很有可能是属于"data"，而非"metadata"（例如size, flag等），对整个fuzzing的意义不大。所以，在随后的一些变异中，会参考effector map，跳过那些“无效”的byte，从而节省了执行资源。
	由此，AFL对文件格式进行了启发式的判断。
	
##### arithmetic
	在bitflip变异全部进行完成后，便进入下一个阶段：arithmetic。
	* arith 8/8
	* arith 16/8
	* arith 32/8
	加减变异的上限，在config.h中的宏ARITH_MAX定义，默认为35。
	此外，AFL还会使用 effector map 智能地跳过某些arithmetic变异。
##### interest
	下一个阶段是interest。
	interest 8/8, 16/8, 32/8。
	从 config.h 中，可以看到，用于替换的基本都是可能会造成溢出的数。
	因此，effector map仍然会用于判断是否需要变异；此外，如果某个interesting value，是可以通过bitflip或者arithmetic变异达到，那么这样的重复性变异也是会跳过的。
##### dictionary
	进入到这个阶段，就接近deterministic fuzzing的尾声。
	* user extras (over)，从头开始，将用户提供的tokens依次替换到原文件中
	* user extras (insert)，从头开始，将用户提供的tokens依次插入到原文件中
	* auto extras (over)，从头开始，将自动检测的tokens依次替换到原文件中
###### 自动监测token
	在进行bitflip 1/1变异时，对于每个byte的最低位(least significant bit)翻转还进行了额外的处理：如果连续多个bytes的最低位被翻转后，程序的执行路径都未变化，而且与原始执行路径不一致，那么就把这一段连续的bytes判断是一条token。
	为了控制这样自动生成的token的大小和数量，AFL还在config.h中通过宏定义了限制。
##### havoc
	对于非dumb mode的主fuzzer来说，完成了上述deterministic fuzzing后，便进入了充满随机性的这一阶段；对于dumb mode或者从fuzzer来说，则是直接从这一阶段开始。
##### splice
	文件的变异也进入到了最后的阶段：splice。如其意思所说，splice是将两个seed文件拼接得到新的文件，并对这个新文件继续执行havoc变异
##### cycle
	上面的变异完成后，AFL会对文件队列的下一个进行变异处理。当队列中的全部文件都变异测试后，就完成了一个"cycle"，这个就是AFL状态栏右上角的"cycles done"。而正如cycle的意思所说，整个队列又会从第一个文件开始，再次进行变异，不过与第一次变异不同的是，这一次就不需要再进行deterministic fuzzing了。
	如果用户不停止AFL，那么seed文件将会一遍遍的变异下去。
#### reference
- [1] AFL 官方文档
- https://paper.seebug.org/496/ : AFL原理介绍

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

export DRRUN_PATH=/home/extra_space/ctf/drAFL-master/build_dr/bin64/drrun  # 定义DRRUM_PATH值来指向drrun启动工具

export LIBCOV_PATH=/home/extra_space/ctf/drAFL-master/build/libbinafl.so  # 设置LIBCOV_PATH来指向libbinafl.so代码覆盖库

export AFL_NO_FORKSRV=1 # 设置AFL的fork服务器

export AFL_SKIP_BIN_CHECK=1

afl/afl-fuzz -m none -i in -o out -- target_binary @@
```
### afl-other-arch
	This is a simple patch to AFL to make other-arch (non-x86 based) support easy.
```sh
$ afl-fuzz -Q -i ./testcases -o ./outputs -- target_binary
```

## 适用场景
	1. 可以控制pc寄存器。
	2. 导致程序逻辑混乱。

## reference
- [1] https://www.freebuf.com/articles/system/203302.html : 在没有源代码的情况下对Linux二进制代码进行模糊测试, drAFL。
- [2] http://zeroyu.xyz/2019/05/15/how-to-use-afl-fuzz/ : fuzz的使用教程。
- [3] https://github.com/shellphish/afl-other-arch : 异构fuzz