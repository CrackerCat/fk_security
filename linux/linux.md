# linux  
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
