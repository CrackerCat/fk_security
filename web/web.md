#web
## basic
### concept
- APTs: Advanced Persistent Threats
- Botnets: 使用 drive-by-download(浏览即下载) 的感染技术，在合法网站中安装恶意软件。
- Rootkits: modifies the OS to hide malicious activity of itself or other malware.
- Drive-by download: code executed by visiting a malicious website.
- Trojan: malicious program that provides some useful service in order to pose as legitimate.
- CSP(content security policy): Server send a response header that tells browser a whitelist of what resources can be loaded and what scripts can be executed, and from where.
- Nonce是一个在加密通信只能使用一次的数字,比如对称秘钥 K=AB中的A

### network
#### Linker layer
	frame, 封装IP数据报文。
	48bit MAC地址识别 source,destination.
	链路层在NIC or chip 中实现。
##### multiple access links
	Link type:
	1. point-to-point
		PPP protocol
	2. broadcast
		802.11 wireless LAN
	访问协议：
	a. TDMA: 时分复用。
	b. FDME: 频分复用
	c. Random access protocols:
		1. CSMA/CD: 冲突检测(适用于wired)，冲突则中止。
	d. taking turns MAC protocol
		主节点邀请其他节点轮流传输。
##### ARP
	ARP table: <IP, MAC, TTL> TTL:停留时间。
	ARP query: A 广播 ARP query(包含目标IP, 目的mac是ff-ff-ff-ff-ff-ff)，LAN中的节点都会收到。 目标IP向A回应 ARP response.
##### Ethernet
	bus: 电缆。
	Ethernet switch: 交换机。 存储和simultaneously转发 Ethernet frames，但不能向同一个端口转发。
		switch table: 记录发送者的mac与其interface。 
	Ethernet frame structure: preambel+dest_addr+src_addr+data+crc
	1. CRC: 接收端校验未通过，则drop。
##### flow control
##### error detection
##### error correction
##### half-duflex & full-duflex 半/全双工
	
#### network layer
	forward & route.
	两种构建 network control panal 的方式：
	1. per-router control: 根据每个路由器的路由表计算。
	2. logically centralized control (software defined networking)
##### AS
	1. AS中的路由器必须运行相同的域内路由协议（RIP, EIGRP, OSPF)， 不同的AS不受限制。
		a. OSPF: 每个路由器把OSPF link-state发送给AS内所有其他路由器。 每个路由器有全局的topology (Dijkstra)。 所有OSPF message 需要验证，防止恶意入侵。
	2. AS内有一个 gateway router.
	3. BGP. inter-AS routing. based on TCP.
		a. eBGP: 获取邻近AS的可达性。
		b. iBGP: 传播可达性到所有AS。  
##### ICMP
	ICMP message: type, code + first 8 bytes of IP datagram causing error.
##### security
	eavesdrop, impersonation, hijacking, denial of service
###### Digital signature
	Enc(H(m)){sk}.
###### TLS
	hierarchy: HTTP/2, TLS, TCP, IP.
	SSL handshake:
	1. client hello: supported cipher suites. Nc
	2. server hello: selected cipher, server-signed certificate. Ns, Certs
	3. client: 验证证书，取出公钥。产生 pre-masker secret,加密&发送。 ES(Kseed), {Hash1}KCS, Hash1 = #(NC,NS, ES(Kseed))
	4. server: {Hash2}KCS, Hash2 = #(NC,NS, ES(Kseed), {Hash1}KCS)
	5. Both: 独立产生 symmetric and mac keys. KCS is a session key based on Nc, Ns, Kseed.
###### Key Establishment Protocol
####### The Needham-Schroeder Public Key Protocol / NH protocol
	1. A → B : EB(Na, A)
	2. B → A : EA(Na,Nb)  --fix-->  B → A : EA(Na,Nb, B)  , forward secrecy.
	3. A → B : EB(Nb)
	Na 和 Nb can then be used to generate a symmetric key, but suffer from man-in-the-middle attack.
	The attacker C acts as a man-in-the-middle:
	1. A -> C : EC(Na, A) //C知道了Na和A， A replay attack.
			a. C(A) -> B : EB(Na, A) //C冒充A 
			b. B -> 	C(A) : EA(Na, Nb) //B不知情
	2. C -> A : EA(Na, Nb) //C与A进行正常秘钥交换
	3. A -> C : EC(Nb) //A与C完成秘钥交换
			a. C(A) -> B : EB(Nb) //C冒充A, A与B完成密钥交换。		
	因此， C知道了 A与B的会话对称秘钥。
####### Station-to-Station Protocol / STS
	1. A → B : gx
	2. B → A : gy, CertB, {SB(gy, gx)}gxy , has forward secrecy.
	3. A → B : CertA, {SA(gy, gx)}gxy
	4. B → A : {M}gxy

##### IPSec
	提供IP数据报文级别的加密，验证，完整性。
	a. transport mode
		只加密和验证payload。
	b. tunnel mode
		加密和验证整个报文。
	IPSec protocol: AH和ESP
	
#### Transfer layer
##### UDP
	是无连接的，尽最大可能交付，没有拥塞控制，面向报文。支持一对多通信。
	UDP 首部字段只有 8 个字节，包括源端口、目的端口、长度、检验和。
##### TCP
	面向连接的，提供可靠交付，有流量控制，拥塞控制，提供全双工通信，面向字节流。一对一通信。
	序号。一个 TCP 接收端可丢弃重复的报文段，记录以杂乱次序到达的报文段。
	确认号；一个确认字节号 N 的 ACK 表示所有直到 N 的字节（不包括 N）已经成功被接收了。
	数据偏移；
	控制位；
	窗口；
	检验和。
###### 三次握手
	假设 A 为客户端，B 为服务器端。
	首先 B 处于 LISTEN（监听）状态，等待客户的连接请求。Socket, bind, listen accept阻塞。

	* A 向 B 发送连接请求报文，SYN=1，ACK=0，选择一个初始的序号 x。 connect并阻塞。
	* B 收到连接请求报文，如果同意建立连接，则向 A 发送连接确认报文，SYN=1，ACK=1，确认号为 x+1，同时也选择一个初始的序号 y。 accept依然阻塞。
	* A 收到 B 的连接确认报文后，还要向 B 发出确认，确认号为 y+1，序号为 x+1。connect 返回。
	* B 收到 A 的确认后，连接建立。accept 返回。

	why 三次握手？
		第三次握手是为了防止失效的连接请求到达服务器，让服务器错误打开连接。需要两次确认。
###### 四次挥手
	1. 客户端发送一个 FIN 段，并包含一个希望接收者看到的自己当前的序列号 K. 同时还包含一个 ACK 表示确认对方最近一次发过来的数据。
	2. 服务端将 K 值加 1 作为 ACK 序号值，表明收到了上一个包。这时上层的应用程序会被告知另一端发起了关闭操作，通常这将引起应用程序发起自己的关闭操作。
	3. 服务端发起自己的 FIN 段，ACK=K+1, Seq=L。
	4. 客户端确认。进入 TIME-WAIT 状态，等待 2 MSL（最大报文存活时间）后释放连接。ACK=L+1。
	
	why 四次挥手？
		a. 全双工的方式，双方都可以同时向对方发送或接收数据。对方回我一个ACK，表示此时我方的连接关闭。但是对方仍然可以继续传输数据，等他发送完所有数据，会发送一个 FIN 段来关闭此方向上的连接，接收方发送 ACK确认关闭连接。
		
		b. 客户端发送了 FIN 连接释放报文之后，服务器收到了这个报文，就进入了 CLOSE-WAIT 状态。这个状态是为了让服务器端发送还未传送完毕的数据，传送完毕之后，服务器会发送 FIN 连接释放报文。
		
		c. 关闭连接时，当收到对方的 FIN 报文时，仅仅表示对方不再发送数据了但是还能接收数据，己方是否现在关闭发送数据通道，需要上层应用来决定，因此，己方 ACK 和 FIN 一般都会分开发。
		
		d. 客户端接收到服务器端的 FIN 报文后进入此状态，此时并不是直接进入 CLOSED 状态，还需要等待一个时间计时器设置的时间 2MSL(Maximum Segment Lifetime 最大报文存活时间）。i. 等待一段时间是为了让本连接持续时间内所产生的所有报文都从网络中消失，使得下一个新的连接不会出现旧的连接请求报文。 RFC 793中规定MSL为2分钟，实际应用中常用的是30秒，1分钟和2分钟等。
###### TCP 长连接和短连接
	短连接：Client 向 Server 发送消息，Server 回应 Client，然后一次读写就完成了，这时候双方任何一个都可以发起 close 操作，不过一般都是 Client 先发起 close 操作。
	长连接：Client 与 Server 完成一次读写之后，它们之间的连接并不会主动关闭，后续的读写操作会继续使用这个连接。
###### TCP粘包、拆包及解决办法
	UDP 是基于报文发送的，UDP首部采用了 16bit 来指示 UDP 数据报文的长度，因此在应用层能很好的将不同的数据报文区分开，从而避免粘包和拆包的问题。
	TCP 是基于字节流的，并没有把这些数据块区分边界，仅仅是一连串没有结构的字节流；另外从 TCP 的帧结构也可以看出，在 TCP 的首部没有表示数据长度的字段。
	
	why 会发生 TCP 粘包、拆包？
	a. 要发送的数据大于 TCP 发送缓冲区剩余空间大小，将会发生拆包。
	b. 待发送数据大于 MSS（最大报文长度），TCP 在传输前将进行拆包。
	c. 要发送的数据小于 TCP 发送缓冲区的大小，TCP 将多次写入缓冲区的数据一次发送出去，将会发生粘包。
	d. 接收数据端的应用层没有及时读取接收缓冲区中的数据，将发生粘包。
	
	粘包、拆包解决办法。
	只能通过上层的应用协议栈设计来解决，根据业界的主流协议的解决方案，归纳如下：
	a. 消息定长。
	b. 设置消息边界。服务端从网络流中按消息边界分离出消息内容。
	c. 将消息分为消息头和消息体：消息头中包含表示消息总长度（或者消息体长度）的字段。
	d. 更复杂的应用层协议比如 Netty 中实现的一些协议都对粘包、拆包做了很好的处理。
###### TCP 滑动窗口
	窗口是缓存的一部分，用来暂时存放字节流。发送方和接收方各有一个窗口，接收方通过 TCP 报文段中的窗口字段告诉发送方自己的窗口大小，发送方根据这个值和其它信息设置自己的窗口大小。发送窗口（已发送但未确认或允许发送但未发送的）；接收窗口（允许接收但未接收的或未按序收到的）。
###### TCP 流量控制
	流量控制是为了控制发送方发送速率，保证接收方来得及接收。
###### TCP 拥塞控制
	为了降低整个网络的拥塞程度。如果网络出现拥塞，分组将会丢失，此时发送方会继续重传，从而导致网络拥塞程度更高。因此当出现拥塞时，应当控制发送方的速率。
	
	TCP 主要通过四个算法来进行拥塞控制：
	慢开始、拥塞避免、快重传、快恢复。
		发送方需要维护一个叫做拥塞窗口（cwnd）的状态变量，注意拥塞窗口与发送方窗口的区别：拥塞窗口只是一个状态变量，实际决定发送方能发送多少数据的是发送方窗口。
		慢开始与拥塞避免。发送的最初执行慢开始，令 cwnd = 1，发送方只能发送 1 个报文段；当收到确认后，将 cwnd 加倍，因此之后发送方能够发送的报文段数量为：2、4、8 ...。设置一个慢开始门限 ssthresh，当 cwnd >= ssthresh 时，进入拥塞避免，每个轮次只将 cwnd 加 1。如果出现了超时，则令 ssthresh = cwnd / 2，然后重新执行慢开始。
		快重传与快恢复。在接收方，要求每次接收到报文段都应该对最后一个已收到的有序报文段进行确认。在发送方，如果收到三个重复确认，那么可以知道下一个报文段丢失，此时执行快重传，立即重传下一个报文段。在这种情况下，只是丢失个别报文段，而不是网络拥塞。因此执行快恢复，令 ssthresh = cwnd / 2 ，cwnd = ssthresh，注意到此时直接进入拥塞避免。
###### 提供网络利用率
	Nagle 算法。发送端即使还有应该发送的数据，但如果这部分数据很少的话，则进行延迟发送的一种处理机制。具体来说，就是仅在下列任意一种条件下才能发送数据。如果两个条件都不满足，那么暂时等待一段时间以后再进行数据发送。
		a. 已发送的数据都已经收到确认应答。
		b. 当可以发送最大段长度的数据时。
	延迟确认应答。接收方收到数据之后可以并不立即返回确认应答，而是延迟一段时间的机制。
		a. TCP 文件传输中，大多数是每两个数据段返回一次确认应答。
		b. 在没有收到 2*最大段长度的数据为止不做确认应答。其他情况下，最大延迟 0.5秒 发送确认应答。
	捎带应答。在一个 TCP 包中既发送数据又发送确认应答的一种机制，由此，网络利用率会提高。
##### reference
- https://zhuanlan.zhihu.com/p/108822858 : 简介

### Access Control
#### term
- authentication: username, password and additional factors.
- Session management: Keep track of authenticated users across sequence of requests.
	- Server generate session ID and gives it to browser.
		- Temporary token that identifies and authenticates user.
	- Browser returns session ID to server in subsequent requests.
- Authorization: Check and enforce permissions of authenticated users.

#### Session ID的实现
- Cookie: 在HTTP头中传递。 Set-Cookie 或者 Cookie.
	1. cookie 被所有浏览器 tabs 共享。
	2. cookie 自动被浏览器返回，有可能不是用户行为导致。
	3. HTTP response: Set-Cookie: adds a cookie. HTTP header: Cookie: gives a "cookie"
	4. Identify the user; Store user name, preferences etc; Track the user: time of last visit, etc;
- Get请求中的变量： SID
	1. 只有用户请求是才发送cookie.
- POST请求： SID.
	1. 只有用户请求时才发送cookie。
	2. 导航必须通过POST请求。
 
### 正则表达式
- ？ : 匹配前面的子表达式零次或一次，或指明一个非贪婪限定符。要匹配 ? 字符，请使用 \?。

### get和post请求
	GET 一般用于获取/查询资源信息；POST 一般用于更新资源信息。
	最直观的区别就是GET把参数包含在URL中，POST通过request body传递参数。
	* get把请求的数据放在url上，即HTTP协议头上，其格式为： 以?分割URL和传输数据，参数之间以&相连。数据如果是英文字母/数字，原样发送；如果是空格，转换为+；如果是中文/其他字符，则直接把字符串用BASE64加密；及“%”加上“字符串的16进制ASCII码”。
	
	对于GET方式的请求，浏览器会把http header和data一并发送出去，服务器响应200（返回数据）；
	而对于POST，浏览器先发送header，服务器响应100 continue，浏览器再发送data，服务器响应200 ok（返回数据）。 但并不是所有浏览器都会在POST中发送两次包，Firefox就只发送一次。
	它们的应用区别：
	1. GET在浏览器回退时是无害的，而POST会再次提交请求。
	2. GET产生的URL地址可以被Bookmark，而POST不可以。
	3. GET请求会被浏览器主动cache，而POST不会，除非手动设置。
	4. GET请求只能进行url编码，而POST支持多种编码方式。
	5. GET请求参数会被完整保留在浏览器历史记录里，而POST中的参数不会被保留。
	6. GET请求在URL中传送的参数是有长度限制的，而POST么有。
	7. 对参数的数据类型，GET只接受ASCII字符，而POST没有限制。
	8. GET比POST更不安全，因为参数直接暴露在URL上，所以不能用来传递敏感信息。
	9. GET参数通过URL传递，POST放在Request body中。
	
	get请求发送： 直接在URL中输入参数。
	post请求发送： 使用浏览器插件 hackbar 发送post请求。
### Robots协议
	/robots.txt文件 是搜索引擎中访问网站的时候要查看的第一个文件。当一个搜索蜘蛛访问一个站点时，它会首先检查该站点根目录下是否存在robots.txt，如果存在，搜索机器人就会按照该文件中的内容来确定访问的范围；如果该文件不存在，所有的搜索蜘蛛将能够访问网站上所有没有被口令保护的页面。
#### HTTP 
#### cookie
	Cookie是当主机访问Web服务器时，由 Web 服务器创建的，将信息存储在用户计算机上的文件。一般网络用户习惯用其复数形式 Cookies，指某些网站为了辨别用户身份、进行 Session 跟踪而存储在用户本地终端上的数据，而这些数据通常会经过**加密处理**。
#### XFF头（X-Forwarded-For）
	它代表客户端，也就是HTTP的请求端真实的IP，只有在通过了HTTP 代理或者负载均衡服务器时才会添加该项。
#### Referer
	HTTP Referer是header的一部分，当浏览器向web服务器发送请求的时候，一般会带上Referer，告诉服务器我是从哪个页面链接过来的。
	一般用来防盗链。
#### HTTPS
	HTTPS = http + tls, 发送消息会加密connection。
### php
- === 会同时比较字符串的值和类型, == 会先将字符串换成相同类型，再作比较，属于弱类型比较（PHP是一种弱类型的语言）。
- eval() - 执行函数(需要以分号结尾)。
- _FILES数组 - 二维数组，获取上传文件信息。
- preg_split(pattern, subject) - 用于正则表达式分割字符串。 
- \r回车，表示使光标下移一格。
- \n换行, 表示使光标到行首。
- 

#### web vulnerable
##### arbitrary file read
	1. 路径可控，可以修改为任意路径。
##### SQL Injection
	Elevation of privilege， Information disclosure， Tampering， Denial of service。
	* Blind SQLi. Try payloads that cause a delay in processing.
	* Second-order SQLi. User may submit payloads that are dangerous only on the second usage.
###### countermeasures
	Input filtering. Prepared statements. Stored procedures. Static/dynamic analysis of server-side code interfacing with database. Rate-limit requests to web server or database server. Web Application Firewall (WAF)(An IDS in front of the database, aware of the web application). Rely on a programming framework.
##### 文件上传漏洞
	上传一句话木马。
##### 文件包含漏洞
	文件包含漏洞是“代码注入”的一种，其原理就是注入一段用户能控制的脚本或代码，并让服务端执行。
	成功利用文件包含漏洞进行攻击，需要满足以下两个条件：
	1. 采用include()等文件包含函数通过动态变量的方式引入需要包含的文件。
	2. 用户能够控制该动态变量。 
##### csrf ( cross site request forgery )
	我们在用浏览器浏览网页时通常会打开好几个浏览器标签（或窗口），假如我们登录了一个站点A，站点A如果是通过cookie来跟踪用户的会话，那么在用户登录了站点A之后，站点A就会在用户的客户端设置cookie，假如站点A有一个页面siteA-page.php（url资源）被站点B知道了url地址，而这个页面的地址以某种方式被嵌入到了B站点的一个页面siteB-page.php中，如果这时用户在保持A站点会话的同时打开了B站点的siteB-page.php，那么只要siteB-page.php页面可以触发这个url地址（请求A站点的url资源）就实现了csrf攻击。
	Attacker’s site has script that issues a request on target site.
###### Mitigation
	1. use a cookie in combination with a post variable, called CSRF token.
	2. server 验证 cookie 和 CSRF token.
##### XSS
	将恶意代码注入进系统，让其触发。
	* DOM-based XSS
		A trusted script reads an attacker-controlled parameter and embeds it in the page。
		取出和执行恶意代码由浏览器端完成，属于前端 JavaScript 自身的安全漏洞。
	* Reflected XSS
		A an attacker-controlled URL parameter is embedded in the page by the server.
		攻击者通过特定手法（如电子邮件），诱使用户去访问一个包含恶意代码的 URL，当受害者点击这些专门设计的链接的时候，恶意代码会直接在受害者主机上的浏览器执行。
	* Stored XSS
		An attacker stores malicious data on a server, which later embeds it in user pages.
	* Self XSS
		Users can be tricked into injecting malicious JavaScript in the page.
	* Cross-channel scripting (XCS)
		Attack is triggered when user visits admin console using browser.
###### Mitigation
	1. whitelist
	2. blacklist
	3. Validate inputs
	4. XSS filters: htmlspecialchars() in PHP.
	5. Use templates or frameworks to validate inputs consistently.
	6. Browsers enforced defenses: X-XSS-Protection header
	7. Finding new XSSs

##### arbitrary command execution
##### replay attack
	重用报文数据，发起攻击。
##### ddos
	原理：攻击者通过代理服务器或者僵尸网络向攻击目标发送大量的高频合法请求，以达到消耗攻击目标带宽的目的。
###### 网络层ddos
	* SYN-Flood攻击。
	* ACK-Flood攻击。
	* UDP-Flood攻击。当攻击目标主机发现该应用程序端口并不存在时，会产生一个目的地址无法连接的ICMP数据包发送给源地址。
	* ICMP攻击。 利用大的流量给服务器带来较大的负载，影响服务器的正常服务。
###### 应用层ddos
	* DNS-Flood攻击。DNS查询。
	* 慢连接攻击。 post提交。
	* CC攻击。 CC攻击是基于页面攻击的，主要攻击目标是提供网页访问服务的服务器。模拟许多用户不间断的对服务器进行访问，而且CC攻击往往是攻击服务器上开销比较大的动态页面。
##### other
	- 扫目录脚本dirsearch(项目地址：https://github.com/maurosoria/dirsearch(https://github.com/maurosoria/dirsearch)) : 可以查看有哪些文件。
	- 利用加密函数绕过 字符过滤， e.g. base64_decode(target_base64)
### js
#### base
	* 对象
		* 对象可以有方法。方法是在对象上执行的动作。方法以函数定义被存储在属性中。
#### vulnerability
	1. 对象隐式强转成字符串，可以触发恶意代码执行。

### nginx
#### alias_traversal
##### Path traversal via misconfigured alias
	有以下配置：
```
location /i/ {
    alias /data/w3/images/;
}
```
	请求 /i../top.gif, /data/w3/images/../top.gif将被发送。
###### reference
- https://github.com/yandex/gixy/blob/master/docs/en/plugins/aliastraversal.md : 检测工具。

### wasm
	让 JavaScript 直接执行高级语言生成机器码的一种技术

## 流量安全
	传统基础安全领域，在通用产品能力上，一般分为三大件：网络层的防DDoS、主机层的防入侵和应用层的防漏洞。
	流量分析面对的是更底层的网络数据包，所包含的信息元素更多，但是分析起来也更加生涩。
	方向一：从网络流量层针对FW/IDS/IPS进行绕过的一些尝试研究。
	方向二：流量层也存在硬伤：加密问题。
### 工具
	网络流量一般都是传输的海量数据，需要一个强大的计算后台来支撑，同时策略上结合实时+离线，达到对分析性能和覆盖功能的平衡。其中有几个关键组件：
#### 高性能的网络IO
	eBPF -> XDP, 工作在网卡收包与协议栈之间，经过它分析处理之后，数据包可以直接丢弃、直接发送，或者是继续送到协议栈处理。
#### 特征匹配引擎
	流量分析可以分为两种类型，一种是DFI（深度流检测）、一种是DPI（深度包检测），前者注重量的统计、后者注重内容的分析。
	从实际现网来看，通过黑特征还是能解决很大一部分的通用性攻击威胁。传统的字符串匹配如KMP、Sunday和AC多模，正则匹配如Pcre、Re2和随DPDK广泛使用的Hyperscan。
	基于硬件加速的方案。比如多插一张FPGA卡，用来专门做匹配查找计算；另外还有Mellonax BlueField直接在网卡上加一个处理芯片（智能网卡），做协议卸载和匹配加速。
#### 流重组
	很多安全威胁都发生在TCP类的业务协议上（如Web漏洞、高危端口）。对于TCP的分段传输特性，流重组对提升安全对抗的检测覆盖能力起到重要作用。
### 场景
#### 网络层
	1. DDoS检测防护。
	2. Web漏洞防护。
		增加 waf 中间层，过滤恶意用户请求。
	3. 基于网络层的阻断。
		理论上，所有基于IP的流量管控拦截都可以通过这套体系实现。
#### 主机层
	1. 入侵回溯。
		传统的主机安全检测响应系统，通过Agent方式采集主机日志、命令操作等信息，然后上报到控制中心进行策略建模，从而发现主机入侵威胁。
			i. 单纯基于主机端数据仅能知道发生了什么，若同时能在流量层面针对这些强特征命令字进行检测，进一步关联，就能溯源到攻击者是如何利用的。
			ii. 流量层的检测能力，也能与主机层发现互补，形成多个铃铛的告警能力。
	2. 木马通信。
#### 应用层
	1. 网络资产搜集。
		安全工作的前置步骤是对资产大盘的全面搜集和掌控。
	2. 高危资产主动发现。
		高危端口、高危组件和高危服务的对外开放占据了漏洞攻击的很大一部分口子。
		i. 传统方法是靠主动扫描来感知，存在扫描周期和扫描被屏蔽的问题。
		ii. 通过流量则可以较好的解决上述问题, 响应速度快.
	3. 脆弱点/漏洞主动发现。
		对于业务层面的不合规行为或者逻辑类漏洞则覆盖不足，比如敏感信息明文传输、越权、管理后台类等，这些场景却是流量分析大显身手的地方。
		i. 传统扫描方法基于关键字，灵活性和可维护性不高.
		ii. 通过流量分析，同时引入AI算法，模型可以自动学习页面特征，效果远超过传统方案。同时，引入正负反馈机制配合AI模型训练，提升模型识别率。这不但可以监测管理后台的对外开放问题，而且也可以对存在弱口令的管理后台进行告警。
#### 云安全能力
	如果流量足够多、类型足够丰富，那基于流量层面进行威胁情报建设、pdns积累、0day发现等都有实践意义。

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
### intruder爆破
  可以用于字典爆破密码。

## wireshark
  抓包软件
### common command
- ip.src/dst  
- tcp.port

## sqlmap
- sqlmap -u url --batch -a : sql注入
- sqlmap -r post.txt --batch -a : post请求注入

## nmap
	扫描某个主机的端口。
## suricata
  一种一种基于规则的IDS工具，检测各种网络攻击手段，记录攻击流量。

## hping3
  一种命令行形式的用于生成和解析TCP/IP协议数据包汇编/分析，例如进行DoS攻击，伪造源IP等.

## JSON
### JSON format
  {"field1":value1,"field2":value2,...}

## other
	* 翻墙
		* 代理
		* api
		* 云服务器
## todo
### 邮件客户端安全
	邮件客户端对邮件内容的解析from字段或者content导致一些xss、权限绕过等漏洞。