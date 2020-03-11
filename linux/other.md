# network  
## nmap  
nmap -sn ip/netmask_bits  扫描网段主机状态  

## netfilter  
netfilter 提供一系列表，表中有多个chain,chain中有多个rule。netfilter系统缺省的表是filter，该表包含INPUT,FORWARD和OUTPUT.  INPUT表示数据包已经进入主机用户空间。

- iptables是一个内核包过滤工具，最终执行这些过滤规则的是netfilter.  
- iptable -I INPUT -s ip -d des_ip -p protocol --dport port -j DROP  //insert rule  
- iptable -D INPUT -s ip -j DROP  //delete rule
- iptables -L -n 打印规则

## route
配置kernel ip table。

- route add/del -net destination gw output_ip 添加路由项 
- route -n 打印路由表

# ssh -X  
## windows connect to linux  

1. install XMing, X Window server.  
2.  install PuTTY, remote login program.  
3.  configure sshd_config.  

# Typora  
markdown editor.  

# file from windows to linux  
linux下用的编码一般是utf-8，而 windows 一般是gb2312  
```
iconv -f gb2312 -t utf-8 1.txt> 2.txt
```
# fs  
## extend disk volume  
1. vmware setting中扩展磁盘容量  
2. fdisk /dev/sda 物理格式化  
3. partprobe 更新分区表  
4. mkfs.ext3 /dev/sda* 创建ext3 fs  
5. 根据blkid输出值，修改/etc/fstab  
