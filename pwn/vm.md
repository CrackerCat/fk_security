# vm
## vm pwn
	1. 程序实现运算指令模拟程序的运行（汇编类）。
		* 难点在于逆向出伪汇编指令，漏洞多为越界造成的任意地址读写。
		* 此类程序会接收一段OPCODE(机器码), 之后通过OPCODE转换为伪汇编指令模拟一台PC.
	2. 在程序中自定义运算指令的程序（编译类）。
### 常见设计 -- 需要识别的数据
	1. 初始化分配模拟寄存器reg空间。   To 数据交换
		* 根据函数调用关系猜测 rdi, rsi,rdx等寄存器。
	2. 初始化分配模拟栈stack空间。  To 函数调用
	3. 初始化分配模拟数据存储（buffer) 空间。 To 输入输出
	4. 初始化分配模拟OPCODE（机器指令）空间。  To 指令翻译

## qemu pwn 
	在ctf比赛中，主要针对pci设备。 qemu逃逸类题目基本上都是直接修改了qemu的源码。一般是在 _libc_csu_init 中注册了pci设备。
	
### base
#### interface
- cpu_physical_memory_rw() : 它是使用的物理地址作为源地址或目标地址.

#### I/O interaction
	与qemu的虚拟设备进行I/O交互通常有以下两种方式，分别是MMIO和PMIO，区别在于是否与设备共享内存。
	1. MMIO 内存映射
		直接操作I/O设备的共享内存空间，以此来交互，实现方法就是直接调用mmap映射内存，然后直接通过指针读写。
		mmap的fd参数为open以下两个文件之一，flags参数需要传递MAP_SHARED属性。
			a. 设备内存（据说有些题目用不了这种）: /sys/devices/pci0000:00/0000:00:??.?/resource0
			b. 整个物理内存: /dev/mem
	2. 端口映射(resource1)
		不共享内存空间，需要调用inx和outx函数来进行交互（要先调用iopl(3)来提权）。

### vulnerability
#### oob
#### basic framework
	基本的代码框架如下：

	1. 编译内核模块，在内核态访问mmio空间。
```
#include <asm/io.h>
#include <linux/ioport.h>

long addr=ioremap(ioaddr,iomemsize);
readb(addr);
readw(addr);
readl(addr);
readq(addr);//qwords=8 btyes

writeb(val,addr);
writew(val,addr);
writel(val,addr);
writeq(val,addr);
iounmap(addr);
```
	2. 在用户态访问mmio空间，通过映射resource0文件实现内存的访问。
```
#include <stdio.h>
#include <unistd.h>
#include <stdlib.h>
#include <stdint.h>
#include <string.h>
#include <errno.h>
#include <signal.h>
#include <fcntl.h>
#include <ctype.h>
#include <termios.h>
#include <assert.h>

#include <sys/types.h>
#include <sys/mman.h>
#include <sys/io.h>

#define MAP_SIZE 4096UL
#define MAP_MASK (MAP_SIZE - 1)

#define DMA_BASE 0x40000


#define PAGE_SHIFT  12
#define PAGE_SIZE   (1 << PAGE_SHIFT)
#define PFN_PRESENT (1ull << 63)
#define PFN_PFN     ((1ull << 55) - 1)

char* pci_device_name = "/sys/devices/pci0000:00/****/resource0";
unsigned char* mmio_base;

unsigned char* getMMIOBase(){
    
    int fd;
    if((fd = open(pci_device_name, O_RDWR | O_SYNC)) == -1) {
        perror("open pci device");
        exit(-1);
    }
    mmio_base = mmap(0, MAP_SIZE, PROT_READ | PROT_WRITE, MAP_SHARED, fd, 0);
    if(mmio_base == (void *) -1) {
        perror("mmap");
        exit(-1);
    }
    return mmio_base;
}

// 获取页内偏移
uint32_t page_offset(uint32_t addr)
{
    // addr & 0xfff
    return addr & ((1 << PAGE_SHIFT) - 1);
}

uint64_t gva_to_gfn(void *addr)
{
    uint64_t pme, gfn;
    size_t offset;

    int fd;
    fd = open("/proc/self/pagemap", O_RDONLY);
    if (fd < 0) {
        perror("open");
        exit(1);
    }

    // printf("pfn_item_offset : %p\n", (uintptr_t)addr >> 9);
    offset = ((uintptr_t)addr >> 9) & ~7;

    ////下面是网上其他人的代码，只是为了理解上面的代码
    //一开始除以 0x1000  （getpagesize=0x1000，4k对齐，而且本来低12位就是页内索引，需要去掉），即除以2**12, 这就获取了页号了，
    //pagemap中一个地址64位，即8字节，也即sizeof(uint64_t)，所以有了页号后，我们需要乘以8去找到对应的偏移从而获得对应的物理地址
    //最终  vir/2^12 * 8 = (vir / 2^9) & ~7 
    //这跟上面的右移9正好对应，但是为什么要 & ~7 ,因为你  vir >> 12 << 3 , 跟vir >> 9 是有区别的，vir >> 12 << 3低3位肯定是0，所以通过& ~7将低3位置0
    // int page_size=getpagesize();
    // unsigned long vir_page_idx = vir/page_size;
    // unsigned long pfn_item_offset = vir_page_idx*sizeof(uint64_t);

    lseek(fd, offset, SEEK_SET);
    read(fd, &pme, 8);
    // 确保页面存在——page is present.
    if (!(pme & PFN_PRESENT))
        return -1;
    // physical frame number 
    gfn = pme & PFN_PFN;
    return gfn;
}

uint64_t gva_to_gpa(void *addr)
{

    uint64_t gfn = gva_to_gfn(addr);
    assert(gfn != -1);
    return (gfn << PAGE_SHIFT) | page_offset((uint64_t)addr);
}

void mmio_write(uint64_t addr, uint64_t value)
{
    *((uint64_t*)(mmio_base + addr)) = value;
}

uint64_t mmio_read(uint64_t addr)
{
    return *((uint64_t*)(mmio_base + addr));
}

int main(int argc, char const *argv[])
{
    getMMIOBase();
    printf("mmio_base Resource0Base: %p\n", mmio_base);
	
	// to-do:
	// 1. leak binary_base_addr.
	// 2. hyjack the control flow.
	return 0;
}
```
	3. 编译内核模块，在内核空间访问pmio空间.
```
#include <asm/io.h> 
#include <linux/ioport.h>

inb(port);  //读取一字节
inw(port);  //读取两字节
inl(port);  //读取四字节

outb(val,port); //写一字节
outw(val,port); //写两字节
outl(val,port); //写四字节
```
	4. 用户空间访问则需要先调用iopl函数申请访问端口。
```
#include <sys/io.h >

iopl(3); 
inb(port); 
inw(port); 
inl(port);

outb(val,port); 
outw(val,port); 
outl(val,port);
```