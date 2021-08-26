# qemu
	虚拟操作系统模拟器。
	引入KVM(OS的模块)的辅助，将CPU和内存虚拟化的工作移交给KVM。
	qemu内置了gdbserver，所以我们可以用gdb调试qemu虚拟机上执行的代码，而且不受客户机系统限制。

	运行的每个qemu虚拟机都相应的是一个qemu进程，从本质上看，虚拟出的每个虚拟机对应 host 上的一个 qemu 进程，而虚拟机的执行线程（如 CPU 线程、I/O 线程等）对应 qemu 进程的一个线程。
![](image/qemu_overview.jpg "overview")
## base
### virtualization
	* 完全虚拟化。 虚拟机内核不作任何修改，单纯由软件模拟。
	* 半虚拟化。 加入VT-X技术，对内核进行一定程度的修改。 
		* VT-X -> VMX root operation 和 VMX non-root operation 两种模式，都支持Ring0-Ring3, 这样VMM可以运行在VMX root operation模式下，Guest OS运行在VMX non-root operation模式下。 KVM基于x86硬件虚拟化技术，要求VT-X。 现在QEMU/KVM分为两部分，分别是运行在kernel模式的KVM内核模块和运行在User模式的Qemu模块（指的是VMX root模式下的ring0和ring3)。 QEMU/KVM作为guest os的 VMM, 而对应的虚拟机运行在VMX non-root模式下，又称guest模式。 QEMU线程与KVM内核模块之间的交互以 ioctl 的方式进行交互，而Guest os和KVM内核模块之间的交互通过 VM Exit 和 VM entry 操作进行交互。启动流程如下：
			* QEMU线程以ioctl的方式指示 KVM 内核模块进行vcpu的创建和初始化操作，在初始化完成后，QEMU线程以ioctl的方式向KVM内核模块发出运行vcpu的指示，KVM内核模块执行VM entry操作，进入 VMX non-root模式，中断host软件，运行Guest软件。 在Guest运行时，如果发生发生异常或外部中断事件，或执行I/O操作，可能会导致 VM exit,切换回 VMX root模式，KVM内核模块会检查 VM exit的原因。 
				* 如果是由 I/O 操作导致，就执行系统调用返回操作，将I/O操作交给 ring3 的QEMU线程来处理，QEMU线程处理完毕后再次执行ioctl，指示KVM切换到 VMX non-root, 恢复Guest的运行；
				* 如果VM exit是其他原因导致，则由 KVM 内核模块负责，并在处理完切换到 VMX non-root 模式，恢复Guest的运行。
		* virtio设备，没有对应的物理设备，需要guest自行安装，数据会先发给virtio设备，经过特殊处理在发送给真正的硬件。
		* vhost. 
	* 设备直通。 物理硬件直接挂载在虚拟机上，尽可能减少开销。

### PCI总线与设备
	PCI设备挂在桥上，桥挂在pci主桥上，并且给设备固定映射一个IO基地址。
#### PCI结构
	每一个PCI设备都对应一段内存空间，里面按照地址位置放置PCI设备的信息，包括厂家信息，bar信息，中断等等。
	lspci的输出： xx:yy.z的格式为总线:设备.功能的格式。例如， [0000:00]-+-00.0  Intel Corporation 440FX - 82441FX PMC [Natoma]。
		* 其中[0000]表示pci的域， PCI域最多可以承载256条总线。 每条总线最多可以有32个设备，每个设备最多可以有8个功能。
	在LInux中使用lspci -x 看到PCI设备的相关内存数据信息。
	PCI设备都有一个配置空间（PCI Configuration Space），其记录了关于此设备的详细信息。大小为256字节，其中头部64字节是PCI标准规定的。
#### QEMU的PCI总线 
	QEMU在初始化硬件的时候，最开始的函数就是pc_init1。在这个函数里面会相继的初始化CPU，中断控制器，ISA总线，然后就要判断是否需要支持PCI，如果支持则调用i440fx_init初始化我们伟大的PCI总线。
	i440fx_init函数主要参数就是之前初始化好的ISA总线以及中断控制器，返回值就是PCI总线，之后我们就可以将我们自己喜欢的设备统统挂载在这个上面。
	使用lspci -t来看PCI总线的结构图(树状的形式)。 
```
->pci_register_root_bus
	->pci_root_bus_new
		->qbus_create
		->pci_root_bus_init
			->bus->address_space_mem = address_space_mem;
			->bus->address_space_io = address_space_io;
			->pci_host_bus_register
```
#### QEMU的 PCI-PCI 桥 
	在QEMU中，所有的设备包括总线，桥，一般设备都对应一个设备结构，通过register函数将所有的设备链接起来，就像Linux的模块一样，在QEMU启动的时候会初始化所有的QEMU设备，而对于PCI设备来说，QEMU在初始化以后还会进行一次RESET，将所有的PCI bar上的地址清空，然后进行统一分配。
	QEMU（x86）里面的PCI的默认PCI设都是挂载主总线上的，而桥的作用一般也就是连接两个总线，然后进行终端和IO的映射。
#### QEMU的PCI设备
	每个 PCI 设备有一个总线号, 一个设备号, 一个功能号标识。 Linux 现在支持 PCI 域。每个 PCI 域可以占用多达 256 个总线. 每个总线占用 32 个设备, 每个设备可以是 一个多功能卡有最多 8 个功能。
	一般的PCI设备和桥很像，关键区分桥和一般设备的地方就是class属性和bar(Base Adress Registers)地址。

	设备可以申请两类地址空间，memory space和I/O space，它们用BAR的最后一位区别开来。可以通过下列命令查看。
```sh
$ lspci -v -s xx:yy.z
```
	当BAR最后一位为0表示这是映射的I/O内存，为1是表示这是I/O端口。
		* 当是I/O内存的时候1-2位表示内存的类型，bit 2为1表示采用64位地址，为0表示采用32位地址。bit1为1表示区间大小超过1M，为0表示不超过1M。bit3表示是否支持可预取。 通过memory space访问设备I/O的方式称为memory mapped I/O，即MMIO，这种情况下，CPU直接使用普通访存指令即可访问设备I/O。
		* 当最后一位为1时表示映射的I/O端口。I/O端口一般不支持预取。 通过I/O space访问设备I/O的方式称为port I/O，或者port mapped I/O，这种情况下CPU需要使用专门的I/O指令如IN/OUT访问I/O端口。
	
	在MMIO中，内存和I/O设备共享同一个地址空间。它使用相同的地址总线来处理内存和I/O设备，I/O设备的内存和寄存器被映射到与之相关联的地址。 每个I/O设备监视CPU的地址总线，一旦CPU访问分配给它的地址，它就做出响应，将数据总线连接到需要访问的设备硬件寄存器。 为了容纳I/O设备，CPU必须预留给I/O一个地址区域，该地址区域不能给物理内存使用。
	在PMIO中，内存和I/O设备有各自的地址空间。 端口映射I/O通常使用一种特殊的CPU指令，专门执行I/O操作。在Intel的微处理器中，使用的指令是IN和OUT。这些指令可以读/写1,2,4个字节（例如：outb, outw, outl）到IO设备上。 I/O设备有一个与内存不同的地址空间，为了实现地址空间的隔离，要么在CPU物理接口上增加一个I/O引脚，要么增加一条专用的I/O总线。

	给bar分配IO地址，调用函数如下：
```
//其中第一个参数是设备；第二个参数是bar的编号，每个PCI设备又5个bar，对应0-5，这个我们也可以在上面的PCI基本结构中看到这6个bar，这个也是后文中提到的6个region，我们这里设置第一个也就是0；第三个参数是分配的IO地址空间范围；第四个参数是表示IO类型是PIO而不是MMIO；最后一个参数是IO读写映射函数。
pci_register_bar(&s->dev,0,0x800,PCI_BASE_ADDRESS_SPACE_IO,fpga_ioport_map);
//没有给设备分配IO空间的基地址，只有一个空间长度而已,说明PCI设备在QEMU中一般是随机动态分配空间的，通过不断的updatemapping来不断更新IO空间的映射。
```
	PCI设备的初始化函数主要是 pci_qdev_realize, 最后调用子设备的pc->realize函数完成子类中的初始化逻辑。
	qemu查看设备模拟列表：
```
// https://www.humblec.com/list-qemu-device-options-using-command-line-kvm-environment/
qemu-system-arch -device ?
```
#### resource 文件
	我们通过lspci 查看到pci设备，然后用 cat /sys/devices/pci0000\:00/设备/resource 打开, 查看I/O信息。
	resource文件内容的格式为start_address end_address flag。根据flag最后一位0可知存在一个MMIO的内存空间(1是PMIO的内存空间)，地址为 start_address, size为 end_address-start_address+1。 每行分别表示相应空间的起始地址（start-address）、结束地址（end-address）以及标识位（flags）。
	resource N file : PCI resource N, if present (binary, mmap, rw1)
		* resource0（MMIO空间）
		* resource1（PMIO空间）

#### PCI/PCIe config space
	一个与Memory空间和IO空间并列的独立的空间。
	* 对Legacy PCI来讲，Configuration Space有256 Bytes
	* 对于PCIe, Configuration Space有4096 Bytes
##### 访问方式
	对于x86架构的CPU而言，有定义Memory和IO的指令，但没有配置空间相关的指令。所以需要有一个译码器把配置命令翻译一下，这个译码器一般是在北桥里面，现在Intel的CPU已经自动集成北桥，从而CPU可以直接完成翻译工作。具本而言，有以下两种方式可以完成对配置空间的访问。
	* IO方式(CF8h/CFCh)
		* 配置空间控制寄存器 CF8h-CFBh
		* 配置空间数据寄存器 CFCh-CFFh
	* Memory方式(ECAM)
		Memory方式访问PCI/PCIe配置空间需要知道MMCFG的基本地址。

#### reference
- https://blog.csdn.net/yearn520/article/details/6576875 : KVM虚拟机代码揭秘——QEMU的PCI总线与设备

### timer
- timer_init_tl(ts, timer_list, scale, cb, opaque) : 初始化一个定时器并将其与timer_list关联。
- timer_mod ≈ timer_mod_ns {scale} : 修改当前timer，以至于当current_time >= expire_time or the current deadline时激活该timer。  

#### reference
- https://github.com/m943040028/qemu/blob/master/qemu-timer.c : 源码分析

### mips
  mips是big-endian的mips架构。
  mipsel是little-endian的mips架构。

### qemu内存结构
	qemu虚拟机内存所对应的真实内存结构如下：
```
                        Guest' processes
                     +--------------------+
Virtual addr space   |                    |
                     +--------------------+
                     |                    |
                     \__   Page Table     \__
                        \                    \
                         |                    |  Guest kernel
                    +----+--------------------+----------------+
Guest's phy. memory |    |                    |                |
                    +----+--------------------+----------------+
                    |                                          |
                    \__                                        \__
                       \                                          \
                        |             QEMU process                 |
                   +----+------------------------------------------+
Virtual addr space |    |                                          |
                   +----+------------------------------------------+
                   |                                               |
                    \__                Page Table                   \__
                       \                                               \
                        |                                               |
                   +----+-----------------------------------------------++
Physical memory    |    |                                               ||
                   +----+-----------------------------------------------++
```
	qemu进行会为虚拟机mmap分配出相应虚拟机申请大小的内存，用于给该虚拟机当作物理内存（在虚拟机进程中只会看到虚拟地址）。 

### QOM编程模型 (QEMU Object Module)
	QEMU提供了一套面向对象编程的模型——QOM（QEMU Object Module），几乎所有的设备如CPU、内存、总线等都是利用这一面向对象的模型来实现的。
	有几个比较关键的结构体，TypeInfo、TypeImpl、ObjectClass以及Object。其中ObjectClass、Object、TypeInfo定义在include/qom/object.h中，TypeImpl定义在qom/object.c中。
	* TypeInfo
		TypeInfo是用户用来定义一个Type的数据结构，用户定义了一个TypeInfo，然后调用type_register(TypeInfo )或者type_register_static(TypeInfo )函数，就会生成相应的TypeImpl实例，将这个TypeInfo注册到全局的TypeImpl的hash表中。
```
struct TypeInfo
{
    const char *name;
    const char *parent;
    size_t instance_size;
    void (*instance_init)(Object *obj);
    void (*instance_post_init)(Object *obj);
    void (*instance_finalize)(Object *obj);
    bool abstract;
    size_t class_size;
    void (*class_init)(ObjectClass *klass, void *data);
    void (*class_base_init)(ObjectClass *klass, void *data);
    void (*class_finalize)(ObjectClass *klass, void *data);
    void *class_data;
    InterfaceInfo *interfaces;
};
```
	TypeImpl的属性与TypeInfo的属性对应，实际上qemu就是通过用户提供的TypeInfo创建的TypeImpl的对象。

	* ObjectClass
		当所有qemu总线、设备等的type_register_static执行完成后，即它们的TypeImpl实例创建成功后，qemu就会在type_initialize函数中去实例化其对应的ObjectClasses。
		每个type都有一个相应的ObjectClass所对应，其中ObjectClass是所有类的基类。 ObjectClass <- DeviceClass <- PCIDeviceClass。 类的定义中父类都在第一个字段，使得可以父类与子类直接实现转换。当所有的父类都初始化结束后，TypeInfo::class_init就会调用以实现虚函数的初始化。
```
/* include/qom/object.h */
typedef struct TypeImpl *Type;
typedef struct ObjectClass ObjectClass;
struct ObjectClass
{
        /*< private >*/
        Type type;       /* points to the current Type's instance */
        ...
/* include/hw/qdev-core.h */
typedef struct DeviceClass {
        /*< private >*/
        ObjectClass parent_class;
        /*< public >*/
        ...
/* include/hw/pci/pci.h */
typedef struct PCIDeviceClass {
        DeviceClass parent_class;
        ...
		vendor_id;
		device_id;
		...
```
	* Object
		Type以及ObjectClass只是一个类型，而不是具体的设备。TypeInfo结构体中有两个函数指针：instance_init以及class_init。 class_init是负责初始化ObjectClass结构体的，instance_init则是负责初始化具体Object结构体的。
		Object类的构造函数与析构函数（在Objectclass构造函数中注册的）只有在命令中-device指定加载该设备后才会调用（或者它是该系统的默认加载PCI设备）。
		QOM会为设备Object分配instace_size大小的空间，然后调用instance_init。
```
struct Object
{
    /*< private >*/
    ObjectClass *class;
    ObjectFree *free;
    GHashTable *properties;
    uint32_t ref;
    Object *parent;
};

// an example.
/* include/qom/object.h */
typedef struct Object Object;
struct Object
{
        /*< private >*/
        ObjectClass *class; /* points to the Type's ObjectClass instance */
        ...
/* include/qemu/typedefs.h */
typedef struct DeviceState DeviceState;
typedef struct PCIDevice PCIDevice;
/* include/hw/qdev-core.h */
struct DeviceState {
        /*< private >*/
        Object parent_obj;
        /*< public >*/
        ...
/* include/hw/pci/pci.h */
struct PCIDevice {
        DeviceState qdev;
      ...
struct YourDeviceState{
    PCIDevice pdev;
    ...
```
	* PCI的内存空间 MemoryRegion
		qemu使用MemoryRegion来表示内存空间，在include/exec/memory.h中定义。
		使用MemoryRegionOps结构体来对内存的操作进行表示，如PMIO或MMIO。对每个PMIO或MMIO操作都需要相应的MemoryRegionOps结构体，该结构体包含相应的read/write回调函数。
		首先使用memory_region_init_io函数初始化内存空间（MemoryRegion结构体），记录空间大小，注册相应的读写函数等；然后调用pci_register_bar来注册BAR等信息。需要指出的是无论是MMIO还是PMIO，其所对应的空间需要显示的指出（即静态声明或者是动态分配），因为memory_region_init_io只是记录空间大小而并不分配。
#### reference
- https://ray-cp.github.io/archivers/qemu-pwn-basic-knowledge#%E8%AE%BF%E9%97%AEpmio : qemu pwn 基础。

---
## cmd
- qemu-mips 大端user模式模拟运行; 用户模式没系统模式强大，功能有限。 该模式不支持 ALSR.
- qemu-system-mips 大端system模式模拟运行


### example
- 使用qemu启动下载的镜像: qemu-system-mips -M malta -kernel vmlinux-3.2.0-4-4kc-malta -hda debian_wheezy_mips_standard.qcow2 -append "root=/dev/sda1 console=tty0" -netdev tap,id=tapnet,ifname=tap0,script=no -device rtl8139,netdev=tapnet -nographic

### user para
- -D -d : 生成日志文件
- -L : 设置lib路径

### system para
- -L dir : 指向BIOS和VGA BIOS所在目录(一般我们使用”-L .”)
- -hda/-hdb/-hdd/-hdc “文件名” :虚拟机系统安装文件
- -cdrom “文件名” :使用“文件名”作为光盘镜象（文件应该是ISO类型）
- -fda/-fdb “文件名” :使用“文件名”作为磁盘0/1镜像
- -boot [a|b|c] :使用磁盘a，光盘d，或者硬盘c启动.
- -m 容量 :指定内存的大小，单位是MB.
- -soundhw c1,…: 使用声卡设备. -soundhw ? :列出所有可使用的声卡 -soundhw all 使用全部声卡
- -usb :允许使用usb设备. -usbdevice :名字 添加一个usb设备“名字”.
- -net nic :创建一块新的网卡. -net nic[,vlan=n][,macaddr=addr][,model=type]
- -net tap[,vlan=n][,fd=h][,ifname=name][,script=file]。 类似vmware的host-only
- -user-net=-net user id='',hostfwd=tcp::port-:22：类似vmware里的nat。
- -snapshot：快照功能
- -nographic：通常，QEMU使用SDL显示VGA输出，使用这个选项，使qemu成为简单的命令行应用程序
- -smp n：cpu个数
- -kernel bzImage： 内核镜像
- -append cmdline：内核启动配置命令行。 root为root路径
- -initrd file：使用文件作为ram盘
- -device driver : 
- -gdb -S : 暂停模式，需要用attach进行。 freeze CPU at startup (use 'c' to start execution)。
- -s/-gdb dev: 调试模式,等待连接dev。
- -enable-kvm : 开启kvm
 

## install
### 源码安装
	编译配置： ./configure --target-list=x86_64-softmmu,aarch64-softmmu,aarch64-linux-user --enable-debug --prefix=/usr/local/bin/ --enable-system --enable-linux-user --enable-pie --enable-modules  --tls-priority=@QEMU,SYSTEM   ： 编译用户模式和系统模式的二进制。
#### reference
- https://kojipkgs.fedoraproject.org//vol/fedora_koji_archive05/packages/qemu/4.0.0/1.fc31/data/logs/aarch64/build.log : 编译日志参考链接

## pwn
---
