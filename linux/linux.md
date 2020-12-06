# linux  
## mechanism

### 用户身份
	real uid/gid : 标记用户是谁。
	euid/egid/supplementary group ids : 用于文件访问权限检查
	saved set-user-ID / saved set-group-ID : 被exec函数(替换进程映像)保存。

### DAC
- SUID
	- 只对二进制文件有效,调用者对该文件有执行权,在执行过程中，调用者会暂时获得该文件的所有者权限,该权限只在程序执行的过程中有效。 对于带S的二进制程序，调用system后，在fork进程过程中，会使用原来的euid，所以euid还原了。

### fork
	对于多进程，子进程继承了父进程的：
		a. uid gid euid egid
		b. 环境变量。
		c. 打开文件描述符。
		d. 子进程不继承父进程的进程正文（text），数据和其他锁定内存（memory locks）。经过fork()以后，父进程和子进程拥有相同内容的代码段、数据段和用户堆栈，父进程只复制了自己的PCB块。由于copy on write, 代码段，数据段和用户堆栈只有在改变的时候才分配空间。
		e. 不继承异步输入和输出。

## Ubuntu  
### dpkg  
一种原始的包管理工具，主要由Debian系列的系统使用。类似的工具，在centos系列中有rpm。  
- dpkg -i install  
- dpkg -r remove  
- dpkg -l list all  
- dpkg -L list file of deb  
- dpkg -S list deb owner of file
  
---  
### apt  
基于dpkg支持自动解决依赖的包管理工具。  
- /etc/apt/source.list 软件包来源  
- /etc/apt/apt.conf.d  

---
### systemctrl
- systemctrl daemon-reload; systemctrl restart service; 先重新加载服务，再重启服务。
