#web
## proxy
![](image/proxy.png "proxy")
- 正向代理中, proxy 和 client 在一个互联的网络中，对server透明。
- 反向代理中，proxy 和 server 在一个互联的网络中，对client透明, 也就是一个服务端的负载均衡器。
注: proxy在两种代理中做的事都是代为收发请求和响应,，不过从结构上来看正好左右互换了下，所以把后出现的那种代理方式叫成了反向代理。

## DVWA
  渗透测试的演练系统，用于web攻击学习。

## xampp(Apache + MySQL + PHP + PERL)
  建站集成软件包.

## burpsuit
  抓包软件

## wireshark
  抓包软件

### common command
- ip.src/dst  
- tcp.port

## sqlmap
- sqlmap -u url --batch -a : sql注入
- sqlmap -r post.txt --batch -a : post请求注入

## suricata
  一种一种基于规则的IDS工具，检测各种网络攻击手段，记录攻击流量。

## hping3
  一种命令行形式的用于生成和解析TCP/IP协议数据包汇编/分析，例如进行DoS攻击，伪造源IP等.

## JSON
### JSON format
  {"field1":value1,"field2":value2,...}

## attack
### XSS
	将恶意代码注入进系统，让其触发。
#### countermeasurement
	1. whitelist
	2. blacklist
