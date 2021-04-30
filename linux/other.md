# network  
## nmap  
nmap -sn ip/netmask_bits  扫描网段主机状态  

## od
  以各种数据格式显示文本。

## netfilter  
netfilter 提供一系列表，表中有多个chain,chain中有多个rule。netfilter系统缺省的表是filter，该表包含INPUT,FORWARD和OUTPUT.  INPUT表示数据包已经进入主机用户空间。

- iptables是一个内核包过滤工具，最终执行这些过滤规则的是netfilter.  
- iptable -I INPUT -s ip -d des_ip -p protocol --dport port -j DROP  //insert rule  
- iptable -D INPUT -s ip -j DROP  //delete rule
- iptables -L -n 打印规则

### ufw
	UFW 是标准 Ubuntu 20.04 安装过程中的一部分.
```sh
ufw enable/disable：打开/关闭防火墙
ufw reload：重启防火墙
ufw status：查看已经定义的ufw规则
ufw default allow/deny：外来访问默认允许/拒绝
ufw allow/deny 20：允许/拒绝访问20端口，20后可跟/tcp或/udp，表示tcp或udp封包。
sudo ufw allow proto tcp from 192.168.0.0/24 to any port 22：允许自192.168.0.0/24的tcp封包访问本机的22端口。
ufw delete allow/deny 20：删除以前定义的"允许/拒绝访问20端口"的规则
ufw allow from 10.0.0.163 允许此IP访问所有的本机端口
```
## libpcap
  unix/linux下网络数据包捕获函数库，是大多数网络监控软件的基础。

## route
配置kernel ip table。

- route add/del -net destination gw output_ip 添加路由项 
- route -n 打印路由表

## ifconfig
  网络配置
- ifconfig dev-name ip/掩码位数 up/down

# ssh -X  
## windows connect to linux  

1. install XMing, X Window server.  
2. install PuTTY, remote login program.  
3. configure sshd_config.  

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
5. 根据blkid输出值，修改/etc/fstab，添加/dev/sda* mount_point ext4 defaults 0 0

## extend swap volume
1. vmware setting中扩展磁盘容量  
2. fdisk /dev/sda 物理格式化  
3. partprobe 更新分区表  
4. mkswap /dev/sda*
5. swapon /dev/swap
6. 修改/etc/fstab，添加/dev/sda* swap swap defaults 0 0  
还有一种临时申请空间的方法，如下。
1. dd if=/dev/zero of=/tmp/mem.swap bs=1M count=4096   (4G)
2. mkswap /tmp/mem.swap
3. swapon /tmp/mem.swap
