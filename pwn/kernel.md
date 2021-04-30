# linux kernel
## base
### Ring Model
	intel CPU 将 CPU 的特权级别分为 4 个级别：Ring 0, Ring 1, Ring 2, Ring 3。
	Ring0 只给 OS 使用，Ring 3 所有程序都可以使用，内层 Ring 可以随便使用外层 Ring 的资源。
	大多数的现代操作系统只使用了 Ring 0 和 Ring 3, 提升系统安全性。
### Loadable kernel modules (LKMs)
	包括驱动程序（device drivers）和内核扩展模块（Modules)。
	LKMs 的文件格式和用户态的可执行程序相同，Linux 下为 ELF，Windows 下为 exe/dll，mac 下为 MACH-O，因此我们可以用 IDA 等工具来分析内核模块。
	模块可以被单独编译，但不能单独运行。它在运行时被链接到内核作为内核的一部分在内核空间运行，这与运行在用户控件的进程不同。
	模块通常用来实现一种文件系统、一个驱动程序或者其他内核上层的功能。
### 机制
#### kalsr
	内核地址随机化，kernel image会按照vmlinux链接脚本中的链接地址去映射虚拟地址，如果开启kaslr，则会重新再映射一次，映射到 链接地址 + offset的新地址上去。如果offset每次开机都随机生成，那么每次开机后，kernel image最后映射的虚拟地址都不一样，这就是内核地址随机化。
	开启klaslr后，.text/.rodata/.init/.data/.bss这些段相对于链接地址都加上了一个offset。
##### bypass
	可以通过某些方法泄露内核全局变量的真正地址并打印到dmesg中，这样就可以破解offset的值了。
##### kalsr
	1. 支持kaslr之前，kernel加载到system RAM的某个位置，它之前的内存kernel是无法管理的，所以一般将kernel加载到system RAM的 起始位置+TEXT_OFFSET（0x080000）处，因为kaslr修改成可以随意加载到system RAM的任何位置，只要满足对齐要求就可以；
	2. 支持kaslr之前，kernel image是映射到线性映射区域的，因为kaslr才修改成映射到vmalloc区域；
	3. 为了支持kaslr，内核要编译成PIE(Position Independent Execuable)，才能重映射。
##### reference
- https://blog.csdn.net/weixin_29662775/article/details/112143028 : kalsr机制详解

### 状态切换
#### user space to kernel space
	当发生 系统调用，产生异常，外设产生中断等事件时，会发生用户态到内核态的切换，具体的过程为：
	1. 通过 swapgs 切换 GS 段寄存器，将 GS 寄存器值和一个特定位置的值进行交换，目的是保存 GS 值，同时将该位置的值作为内核执行时的 GS 值使用。
	2. 将当前栈顶（用户空间栈顶）记录在 CPU 独占变量区域里，将 CPU 独占区域里记录的内核栈顶放入 rsp/esp。
	3. 通过 push 保存各寄存器值。 //1-3保存并切换cpu上下文。
	4. 通过汇编指令判断是否为 x32_abi。
	5. 通过系统调用号，跳到全局变量 sys_call_table 相应位置继续执行系统调用。
#### kernel space to user space
	退出时，流程如下：
	1. 通过 swapgs 恢复 GS 值
	2. 通过 sysretq 或者 iretq 恢复到用户控件继续执行。如果使用 iretq 还需要给出用户空间的一些信息（CS, eflags/rflags, esp/rsp 等。
```
// 内核态返回用户态
shellcode[19] = iret;              
// rip                                                                                                                                                          shellcode[20] = (size_t)get_shell; 
//恢复上下文                                                                                                                                                    shellcode[21] = user_cs;                                                                                                                                                                     shellcode[22] = user_eflags;                                                                                                                                                                 shellcode[23] = user_sp;                                                                                                                                                                     shellcode[24] = user_ss; 
```
```c
// intel flavor assembly
size_t user_cs, user_ss, user_rflags, user_sp;
void save_status()
{
    __asm__("mov user_cs, cs;"
            "mov user_ss, ss;"
            "mov user_sp, rsp;"
            "pushf;"
            "pop user_rflags;"
            );
    puts("[*]status has been saved.");
}

// at&t flavor assembly
void save_stats() {
asm(
    "movq %%cs, %0\n"
    "movq %%ss, %1\n"
    "movq %%rsp, %3\n"
    "pushfq\n"
    "popq %2\n"
    :"=r"(user_cs), "=r"(user_ss), "=r"(user_eflags),"=r"(user_sp)
    :
    : "memory"
);
}
```
### struct cred
	每个进程中都有一个 cred 结构，这个结构保存了该进程的权限等信息（uid，gid 等），如果能修改某个进程的 cred，那么也就修改了这个进程的权限。
### kallsyms
	在2.6版的内核中，为了更方便的调试内核代码，开发者考虑将内核代码中所有函数以及所有非栈变量的地址抽取出来，形成是一个简单的数据块(data blob:符号和地址对应)，并将此链接进 vmlinux 中去。
	kallsyms抽取了内核用到的所有函数地址(全局的、静态的)和非栈数据变量地址，生成一个数据块，作为只读数据链接进kernel image，相当于内核中存了一个System.map。
	要在一个内核中启用 kallsyms 功能。须设置 CONFIG_KALLSYMS 选项为y；如果要在 kallsyms 中包含全部符号信息，须设置 CONFIG_KALLSYMS_ALL 为y。
### 内核态函数
	相比用户态库函数，内核态的函数有了一些变化：
	* printf() -> printk()， printk() 不一定会把内容显示到终端上，但一定在内核缓冲区里，可以通过 dmesg 查看效果。
	* memcpy() -> copy_from_user()/copy_to_user()。
		* copy_from_user() 实现了将用户空间的数据传送到内核空间
		* copy_to_user() 实现了将内核空间的数据传送到用户空间
	* malloc() -> kmalloc()，内核态的内存分配函数，使用的是 slab/slub 分配器。
	* free() -> kfree()。
	* kernel 中有两个可以方便的改变权限的函数。
		* int commit_creds(struct cred *new)
		* struct cred* prepare_kernel_cred(struct task_struct* daemon)
			* 执行 commit_creds(prepare_kernel_cred(0)) 即可获得 root 权限，0 表示 以 0 号进程作为参考准备新的 credentials。 是最常用的提权手段。
			* 两个函数的地址都可以在 /proc/kallsyms 中查看（较老的内核版本中是 /proc/ksyms）。
			* 一般情况下，/proc/kallsyms 的内容需要 root 权限才能查看。 查找vmlinux的基址：cat /tmp/kallsyms|grep stext。
			* 如果是没开启PIE的vmlinux，可以通过elf.symbols['func'] 查询函数地址。
			* echo 0 > /proc/sys/kernel/kptr_restrict： 能通过 /proc/kallsyms 查看函数地址
			* echo 0 > /proc/sys/kernel/dmesg_restrict ： 能通过 dmesg 查看 kernel 的信息
### Mitigation
	canary, dep, PIE, RELRO 等保护与用户态原理和作用相同.
	* smep: Supervisor Mode Execution Protection，当处理器处于 ring0 模式，执行 用户空间 的代码会触发页错误。（在 arm 中该保护称为 PXN）
	* smap: Superivisor Mode Access Protection，类似于 smep，通常是在访问数据时。
	* mmap_min_addr:	
	
### start
	一般会给以下三个文件:
	1. boot.sh: 一个用于启动 kernel 的 shell 的脚本，多用 qemu，保护措施与 qemu 不同的启动参数有关.
	2. bzImage: kernel binary
	3. rootfs.cpio: 文件系统映像
```boot.sh
qemu-system-x86_64 \  
-m 256M \  
-kernel ./bzImage \  
-initrd  ./rootfs.cpio \  
-append "root=/dev/ram rw console=ttyS0 oops=panic panic=1 quiet kaslr" \  
-s  \  
-netdev user,id=t0, -device e1000,netdev=t0,id=nic0 \  
-nographic  \  
```
解释一下 qemu 启动的参数：
	1. -initrd rootfs.cpio，使用 rootfs.cpio 作为内核启动的文件系统。 gzip/gunzip 打包。
	2. -kernel bzImage，使用 bzImage 作为 kernel 映像
	3. -cpu kvm64,+smep，设置 CPU 的安全选项，这里开启了 smep
	4. -m 64M，设置虚拟 RAM 为 64M，默认为 128M 其他的选项可以通过 --help 查看
	5. 如果题目没有给 vmlinux，可以通过 extract-vmlinux 从 bzImage 中提取。
	6. 通过 -gdb tcp:port 或者 -s 来开启调试端口。
	7. -append 内核启动配置命令行。 
		* panic: 当内核遇到严重错误的时候，内核panic，立马崩溃,死机。
		* oops=panic panic=1 : Oops可以看成是内核级的Segmentation Fault。如果内核自己犯了这样的错误，则会打出Oops信息。 oops会触发panic。
		* oops=panic : oops是内核遇到错误时发出的提示“声音”，oops有时候会触发panic，有时候不会，而是直接杀死当前进程，系统可以继续运行。如果错误发生在中断上下文，oops也会触发panic。如果错误只是发生在进程上下文，这个时候只需要kill当前进程。【中断上下文包括以下情况：硬中断、软中断、NMI】。 oops的时候内核还可以运行，只是可能不稳定，这个时候，内核会调用printk打印输出内核栈的信息和寄存器的信息。
		* quiet ： 表示在启动过程中只有重要信息显示，类似硬件自检的消息不回显示。
		* rhgb ： 表示redhat graphics boot，就是会看到图片来代替启动过程中显示的文本信息 
		
### other
	1. vmlinux。 是静态编译的elf文件，未经过压缩的 kernel 文件.
	2. vmlinux.bin。 The same as vmlinux, but in a bootable raw binary file format. All symbols and relocation information is discarded. Generated from vmlinux by objcopy -O binary vmlinux vmlinux.bin 
	3. vmlinuz。 The vmlinux file usually gets compressed with zlib. 
	4. zImage (make zImage)。 This is the old format for small kernels (compressed, below 512KB). At boot, this image gets loaded low in memory (the first 640KB of the RAM).
	5. bzImage (make bzImage)。 The big zImage (this has nothing to do with bzip2), was created while the kernel grew and handles bigger images (compressed, over 512KB). The image gets loaded high in memory (above 1MB RAM). As today's kernels are way over 512KB, this is usually the preferred way.

----
## knowledge related to driver
### base
- module_init(hello_init);//用宏来指定入口 加载模块时里面的加载函数会被调用。
- module_exit(hello_exit);//卸载模块时候会调用。
- 自定义open等函数的函数签名需要符合内核要求，详情见 linux/fs.h. 初始化const struct file_operations。
```c
struct file_operations  {
    struct module *owner;
    loff_t (*llseek) (struct file *, loff_t, int);
    ssize_t (*read) (struct file *, char __user *, size_t, loff_t *);
    ssize_t (*write) (struct file *, const char __user *, size_t, loff_t *);
    ssize_t (*aio_read) (struct kiocb *, const struct iovec *, unsigned long, loff_t);
    ssize_t (*aio_write) (struct kiocb *, const struct iovec *, unsigned long, loff_t);
    int (*readdir) (struct file *, void *, filldir_t);
    unsigned int (*poll) (struct file *, struct poll_table_struct *);
    int (*ioctl) (struct inode *, struct file *, unsigned int, unsigned long);
    long (*unlocked_ioctl) (struct file *, unsigned int, unsigned long);
    long (*compat_ioctl) (struct file *, unsigned int, unsigned long);
    int (*mmap) (struct file *, struct vm_area_struct *);
    int (*open) (struct inode *, struct file *);
    int (*flush) (struct file *, fl_owner_t id);
    int (*release) (struct inode *, struct file *);
    int (*fsync) (struct file *, struct dentry *, int datasync);
    int (*aio_fsync) (struct kiocb *, int datasync);
    int (*fasync) (int, struct file *, int);
     ... 
};
```
#### busybox
	busybox是一个集成了一百多个最常用linux命令和工具的软件,他甚至还集成了一个http服务器和一个telnet服务器,而所有这一切功能却只有区区1M左右的大小。
	- setsid cttyhack sh : Starting interactive shell from boot shell script.
	- setuidgid USER PROG ARGS : 用指定的用户USER的uid和gid来运行某个程序。

## ctf kernel pwn
	本地写好 exploit 后，可以通过 base64 编码等方式把编译好的二进制文件保存到远程目录下，进而拿到 flag。同时可以使用 musl, uclibc 等方法减小 exploit 的体积方便传输。
	本地调试建议用root。
### bypass smep
#### smep
	smep: 为了防止 ret2usr 攻击，内核开发者提出了 smep 保护，smep 全称 Supervisor Mode Execution Protection，是内核的一种保护措施，作用是当 CPU 处于 ring0 模式时，执行 用户空间的代码 会触发页错误；这个保护在 arm 中被称为 PXN。
	查询是否开启smep：
	1. 通过 qemu 启动内核时的选项可以判断是否开启了 smep 保护。
	2. grep smep /proc/cpuinfo。
##### 原理与绕过
	系统根据 CR4 寄存器（保存CPU的标志位）的值判断是否开启 smep 保护，当 CR4 寄存器的第 20 位是 1 时，保护开启；是 0 时，保护关闭。
	 CR4 寄存器是可以通过 mov 指令修改的，因此只需要从 vmlinux 中提取出的 gadget，很容易就能达到这个目的。为了关闭 smep 保护，常用一个固定值 0x6f0，即 mov cr4, 0x6f0。
### vulnerability
#### ptmx
	当我们open("/dev/ptmx")的时候，会分配一个tty_operation的结构体,覆盖该结构体可以将控制流劫持到我们的代码中。
#### kernel rop
#### kernel uaf
#### kernel ret2usr
	进入内核态后，不需构造调用链，使用应用层的代码，从而完成一些功能。
##### reference
- https://www.jianshu.com/p/b59b44afe3e8?utm_campaign=maleskine&utm_content=note&utm_medium=seo_notes&utm_source=recommendation : core 例题
#### double fetch
	Double Fetch 从漏洞原理上属于条件竞争漏洞，是一种内核态与用户态之间的数据访问竞争。
	通常情况下，用户空间向内核传递数据时，内核先通过通过 copy_from_user 等拷贝函数将用户数据拷贝至内核空间进行校验及相关处理，但在输入数据较为复杂时，内核可能只引用其指针，而将数据暂时保存在用户空间进行后续处理。此时，该数据存在被其他恶意线程篡改风险，造成内核验证通过数据与实际使用数据不一致，导致内核代码执行异常。
	一个典型的 Double Fetch 漏洞原理。一个用户态线程准备数据并通过系统调用进入内核，该数据在内核中有两次被取用，内核第一次取用数据进行安全检查（如缓冲区大小、指针可用性等），当检查通过后内核第二次取用数据进行实际处理。而在两次取用数据之间，另一个用户态线程可创造条件竞争，对已通过检查的用户态数据进行篡改，在真实使用时造成访问越界或缓冲区溢出，最终导致内核崩溃或权限提升。
### gadget
	建议使用 Ropper 来寻找 gadget.
```sh
	ropper --file binary
```

## 漏洞分析
### Linux Kernel 4.20rc1-4.20rc4，BPF模块整数溢出
	a. 漏洞触发路径：bpf->map_create->find_and_alloc_map->queue_stack_map_alloc。
	b. 达到漏洞函数的条件。
```
	1. 设置 attr->map_type 为 22。
	2. 通过 map_alloc_check 的检查。
	3. attr->map_ifindex 为空。
```
	c. 漏洞函数 queue_stack_map_alloc 的漏洞位置。
```
bpf_map_area_alloc{
......
  struct bpf_queue_stack *qs;   
  u32 size, value_size;
  u64 queue_size, cost;
 
  // bugs-> integer overflow. -> 0
  size = attr->max_entries + 1;
  value_size = attr->value_size;
 
  queue_size = sizeof(*qs) + (u64) value_size * size;
  ......
qs = bpf_map_area_alloc(queue_size, numa_node);
......
bpf_map_init_from_attr(&qs->map, attr);
  ......
qs->size = size;
  return &qs->map;
}
```
	d. 最后从 find_and_alloc_map(attr) 中返回我们的map（struct bpf_map *）。
	---->
	由于整数溢出，导致只分配了struct bpf_queue_stack 的空间，而没有分配map对应的空间。（个人理解这个bpf_queue_stack类似报文头部，而map对应的空间类似payload），但是map->max_entries = attr->max_entries。
	
	e. 接下来，我们需要找到一块可以造成堆溢出的位置。我们将视角移出 map_create 函数。然而在BPF_MAP_UPDATE_ELEM 分支中我们对此对象进行更新等操作。	
	map_update_elem -> queue_stack_map_push_elem.
```
queue_stack_map_push_elem(struct bpf_map *map, void *value,
                     u64 flags){
......
 dst = &qs->elements[qs->head * qs->map.value_size]; //qs->head代表当前是第几个entry
 memcpy(dst, value, qs->map.value_size); //堆溢出位置     
......
}
```
	所以，对于0x100大小的map header+map payload，如果我们要拷贝的大小（value_size）大于（0x100-map header）的大小，就会造成堆溢出。	value_size可控。

	f. 漏洞利用
```
struct bpf_queue_stack {
    struct bpf_map map;
    raw_spinlock_t lock;
    u32 head, tail;
    u32 size; /* max_entries + 1 */
 
    char elements[0] __aligned(8);
};

struct bpf_map {
    /* The first two cachelines with read-mostly members of which some
     * are also accessed in fast-path (e.g. ops, max_entries).
     */
    const struct bpf_map_ops *ops ____cacheline_aligned;
  ......
}
```
	看到其第一个成员就是虚表指针 ops ，换句话说，在我们kamlloc出的slab中的第一个位置就是指向当前map虚表的指针，如果我们能通过上方的slab堆溢出，劫持下方slab的虚表指针，再fake相应的vtable，就可以实现一套内核的执行流劫持。
### 5.11.10 bug分析
#### ethernet/realtek/r8169_main.c
	1. 漏洞点-- 安全检查函数缺失：
		安全检查函数： rtl_set_def_aspm_entry_latency(struct rtl8169_private *tp) -> rtl_csi_access_enable(struct rtl8169_private *tp, u8 val)。
```
static void rtl_csi_access_enable(struct rtl8169_private *tp, u8 val)
{
	struct pci_dev *pdev = tp->pci_dev;
	u32 csi;
	//默认 ECAM 访问方式。若不支持的话，使用CSI方式。
	/*Some chips have a non-zero function id, however instead of hardcoding
	the id's (CSIAR_FUNC_NIC and CSIAR_FUNC_NIC2) we can get them
	dynamically via PCI_FUNC(pci_dev->devfn). This way we can get rid
	of the csi_ops.
	In general csi is just a fallback mechanism for PCI config space
	access in case no native access is supported. Therefore let's
	try native access first.
	I checked with Realtek regarding the functionality of config space
	byte 0x070f and according to them it controls the L0s/L1
	entrance latency.
	*/
	/* According to Realtek the value at config space address 0x070f
	 * controls the L0s/L1 entrance latency. We try standard ECAM access
	 * first and if it fails fall back to CSI.
	 */
	if (pdev->cfg_size > 0x070f &&
	    pci_write_config_byte(pdev, 0x070f, val) == PCIBIOS_SUCCESSFUL)
		return;

	netdev_notice_once(tp->dev,
		"No native access to PCI extended config space, falling back to CSI\n");
	csi = rtl_csi_read(tp, 0x070c) & 0x00ffffff;
	rtl_csi_write(tp, 0x070c, csi | val << 24);
}

static void rtl_set_def_aspm_entry_latency(struct rtl8169_private *tp)
{
	rtl_csi_access_enable(tp, 0x27);
}

```
	2. 缺失安全检查函数的函数
		rtl_hw_start_8401(struct rtl8169_private *tp)
		
	3. 漏洞点可达路径分析
		rtl_hw_start_8125(struct rtl8169_private *tp) -> rtl_hw_config() ->
		注： Do note that you can't use any RTL 8125 chip with a 4.x kernel, and you should also note that the latest RTL8125B revision requires Linux 5.9.x.		

		rtl_hw_start_8168(struct rtl8169_private *tp) -> rtl_hw_config() ->
		todo:
			qemu 模拟 rtl8169。
	4. 需要补充的知识
		a. Realtek RTL 8125
			The RTL 8125 is a 2.5 GiB Ethernet network chip for 2.5 Gigabit networking over standard CAT5e/CAT6 wiring.
## reference
- https://zh.wikipedia.org/wiki / 内核
- https://zh.wikipedia.org/wiki / 分级保护域
- https://zh.wikipedia.org/wiki/Ioctl
- http://www.freebuf.com/articles/system/54263.html
- https://blog.csdn.net/zqixiao_09/article/details/50839042 : 字符设备
- https://yq.aliyun.com/articles/53679 : kallsyms
- https://blog.csdn.net/pwl999/article/details/106931608/ ： Linux 死机复位(oops、panic)问题定位指南