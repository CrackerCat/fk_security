# crypto
## number theory
### 有限域GF
	若p为素数，GF(p)={0,1,2,...,p-1} = Zp
	生成元g的有限域(阶为q) = {0,g^0,g^1,...,g^(q-2)}
	Z*p 是与p互素且小于p的集合。
	在GF(2)上的多项式： a(x) = a0 + a1 * x + a2 * x^2 + ... + an * x^n, ai ∈ GF(2)
    GF(2^8)中的元素是由 a(x) = a0 + a1 * x + a2 * x^2 + ... + a7 * x^7, ai ∈ GF(2). 因此GF(2^8)由2^8个元素。
### 线性函数
	A function f from an Abelian group (A, +) to an Abelian group (B, +) is called linear if and only if f(x + y) = f(x) + f(y) for all x, y ∈ A

### 群
#### 循环群
	a∈群G,其他元素可以用a^k表示，其中a可以称作群的生成元或原根。
#### Abelian群
	满足 x*y∈A, *结合律， *交换律， 0*x=x, x*y=0， 其中*是操作符 

### division algorithm
	divide a by n -> a = q*n + r -> 0<=r<n; q=floor(a/n)

### Euler's Theorem
	phi(n) : 在[1,n-1]范围内，与n互素的自然数个数。
	a^(phi(n)) 恒等于 1 (mod n), 其中a与n互质。

### primitive root 本原根
	素数p的原根定义：如果a是素数p的原根，则数a mod p, a^2 mod p, … , a^(p-1) mod p 是不同的并且包含1到p-1的整数的某种排列。
	假设一个数g对于P来说是原根，那么g^i mod P的结果两两不同,且有 1<g<P, 1<i<P,那么g可以称为是P的一个原根.
	简单来说，g^i mod p ≠ g^j mod p （p为素数）,其中i≠j且i, j介於1至(p-1)之间,则g为p的原根。

### gcd
	gcd(a,b) = gcd(a,-b) = gcd(-a,b) = gcd(-a,-b)=max[k, such that k | a and k | b]	

### 扩展欧几里得算法（Extended Euclid Algorithm）
	给予二整数 a 与 b, 必存在有整数 x 与 y 使得ax + by = gcd(a,b)=gcd(b, a/b)。
	a存在模b的乘法逆元的充要条件是gcd（a,b）= 1 -> ax mod b = 1。
#### 求乘法逆元
```pseudocode
EXTENDED_EUCLID(m, b)
1. (A1, A2, A3) <- (1, 0, m); (B1, B2, B3) <- (0, 1, b)
2. if B3 = 0 return A3 = gcd(m, b); no inverse
3. if B3 = 1 return B3 = gcd(m, b); B2 = b^(-1) mod m
4. Q = floor(A3/B3)
5. (T1, T2, T3) <- (A1 - QB1, A2 - QB2, A3 - QB3)
6. (A1, A2, A3) <- (B1, B2, B3)
7. (B1, B2, B3) <- (T1, T2, T3)
8. goto 2 
B2 就是逆元
```

### 质数筛选算法：The Sieve of Eratosthenes(埃拉托色尼筛选法)
	计算某一范围的质数个数。埃拉托色尼筛选法基于一项基本性质：任何大于1的自然数，要么本身是质数，要么可以分解为几个质数之积，且这种分解是唯一的。
    
#### 思路
	假设从起点开始（起点可由要求指定）的所有数都是质数。从起点开始向前搜寻，若为质数，则将其倍数（不超过上界n）标记为非质数。例如2为质数，则标记4，6，8, ...这些2的倍数都为非质数，然后标记下一个……依此类推。
```
class Solution1 {
public:
    size_t countPrimes(size_t n) {
        bool *p = new bool[n+1];
        size_t i, j;
        for (i = 0; i <= n; ++i)
            p[i] = true;
        p[0] = p[1] = false;
        for (i = 2; i < n; ++i)
                if (p[i])
                    for (j = 2; i*j < n; ++j)
                        p[i*j] = false;
        size_t cnt = 0;
        for (i = 2; i < n; ++i)
            if (p[i])
                cnt++;
        delete []p;
        return cnt;
    }
};
```	

### Fermat’s Little Theorem
	a^p 恒等于 a (mod p), 其中p为质数，a为任意自然数。
    若p为素数且p不能整除a，a^(p-1) 恒等于 1 (mod p)，其中互素的意思是公约数为1。
	费马小定理通常用来检验一个数是否是素数，是素数的必要非充分条件。满足费马小定理检验的数未必是素数，这种合数叫做卡迈克尔数。

### Chinese Remainder Theorem (CRT)
	已知两个互不相同的素数，对于任意0<=x1<p, 0<=x2<q, 存在唯一的0<=x<n 使得：
	x1 = x mod p, and
	x2 = x mod q
	因此任意整数0<=x<n, 都可以用(x1, x2)表示。
### 二次探测
	如果p是一个素数，0<x<p,则 x^2 恒等于 1(mod p)的解为 x=1 或 x=p-1.

### Miller Rabin素数测试算法
	这个数又特别的大导致 O( sqrt(n) ) 的算法不能通过，这时候我们可以对其进行 Miller-Rabin 素数测试，可以大概率测出其是否为素数。
#### 算法流程
（1）对于偶数和 0，1，2 可以直接判断。

（2）设要测试的数为 x，我们取一个较小的质数 a，设 s,t，满足 2^s * t = x-1（其中 t 是奇数）。

（3）我们先算出 a^t，然后不断地平方并且进行二次探测( 进行 **s** 次，0 -> (s-1) )。
	1. a^t 不恒等于 1 (mod p). 若等于1，返回不确定。
	2. 对于任意0<= k <= s-1，a^((2^k)*t) 不恒等于 -1 (mod p). 若等于-1 or (p-1), 返回不确定。

（4）上述式子满足，则说明 x 为合数, 否则大概率是素数。

（5）多次取不同的 a 进行 Miller-Rabin 素数测试，这样可以使正确性更高。

### LFSR(线性反馈移位寄存器）
	LFSR通常是移位寄存器，其输入位由总移位寄存器值中某些位的XOR驱动。
	其输入位是先前状态的线性函数，单个位最常用的线性函数是异或。
	LFSR的初始值称为种子，寄存器产生的值流完全由其当前（或先前）状态决定，由于寄存器具有有限数量的可能状态，因此它最终必须进入重复周期。
```math
| ++ bit0 ++ bit1 ++ bit2 ++ bit3 ++|
|     |                |      |     |
|     |               <<-(x1) |     |
|     | <---func(x1,x2)   <<-(x2)-  |
|+++++++++++++++++++++++++++++++++++|
一次变换后就是 bit2^bit3 bit0 bit1 bit2,。多次变化后，会回到最初的值，周期性变换。
```
#### concept
- taps : 抽头，在LFSR中影响下一个状态的bit位的位置叫做抽头。
- 语义安全=不可区分性等价于IND-CPA-secure.
	- 用被挑战者(Challager，别人都翻译成挑战者，我觉得应该叫被挑战者更合适)和攻击者(Adversary)之间的安全游戏(这种游戏实际上是一种交互式证明，是证明哦，回想一下初中几何题)来理解，就是被挑战者给攻击者一个密文和两个明文，攻击者只有一半的机会从加密后的密文猜对对应的明文，几乎等同于瞎猜。
	- 语义安全是一种基本的安全，就是窃听者不能获取明文内容，但是窃听者除了窃听以外，其他什么也不能做，不能提问。满足语义安全，才是一个基本的现代加密方案。

---
## convention cipher
### OTP(one-time pad)
	它是一种明文与共享密钥(与明文等长或者更长) 进行模加法运算。
### shift cipher
#### caesar
  它是一种替换加密的技术，明文中的所有字母都在字母表上向后（或向前）按照一个固定数目进行偏移后被替换成密文。
### substitution cipher
#### simple substitution cipher
	简单替换密码是最常用的密码，包括为每个密文文本字符替换每个纯文本字符的算法。 在这个过程中，与凯撒密码算法相比，字母表是混乱的。

### transposition cipher
### affine cipher
	带系数的shift cipher.  (ax+b)mod n
### Rail Fence cipher
	a rail fence of depth 2.

---
## symmetric cipher
### DES
	基于Feistel结构。
	秘钥长度： 64bit, 有效长度 56bit
	块长： 64bit
	16轮变换。
### AES
	基于SPN网络。
	分组长度：128bit
	密钥长度/加密轮数 ：  128/10, 192/12, 256/14
	* 4个基本操作(一轮)
		* SubBytes 字节替换，查表。
		* ShiftRows 左循环移位操作
		* MixCol 通过矩阵相乘来实现
		* AddRoundKey 将128位轮密钥Ki同状态矩阵中的数据进行逐位异或操作
#### attack
##### timing attack
	这是一种测信道攻击的方法。
	如果一个算法的执行时间取决于用户的输入和密钥，那么TA可以获取该算法的密钥。
---
## asymmetric cipher
### EIGamal encryption
	encrypt: (R,S)=(g^r mod p, m*B^r mod p)， r是Zp-1中的随机数。
### RSA encryption
	已知两个大素数p,q,n=pq,phi(n)=(p-1)(q-1).
	1. 任取大整数e,满足gcd(e,phi(n))=1.  公钥=(n,e)
	2. 确定私钥d，(d*e)mod(phi(n))=1.
	3. 加密：c=m^e mod n
	4. 解密：m=c^d mod n
#### RSA-CRT
	运用CRT定理加速 c^d mod n 的计算。
```
dP = (1/e) mod (p-1)
dQ = (1/e) mod (q-1)
qInv = (1/q) mod p
m1 = c^dP mod p
m2 = c^dQ mod q
h = qInv*(m1 - m2) mod p
m = m2 + h*q
```
	private key as the quintuple (p, q, dP, dQ, qInv).
---
##### attack
###### Bellcore 
	q=gcd(s-s', n), p=n/q. 
	注： s是正确的签名，s'是错误的签名
###### lenstra
	q=gcd(s'^e - x, n), p=n/q.
	注： s'是错误的签名
##### reference
- https://www.di-mgt.com.au/crt_rsa.html : rsa-crt简介
- https://www.cryptologie.net/article/371/fault-attacks-on-rsas-signatures/ ： lenstra attack
- https://bham.cloud.panopto.eu/Panopto/Pages/Embed.aspx?id=45d3ffb8-0c86-4c84-b17b-ac72010a50f6 : fault injuction attack

## stream cipher
	一次加密一字节或一位数据。
### Vernam cipher
	Vernam密码是一种对称的流密码，其中，明文与相同长度的随机或伪随机数据流（“密钥流”）组合在一起，以使用布尔值 “ exclusive or”（ XOR）功能。

## hash
### birthday attack
	m位的hash值，至少经过2^(m/2)次碰撞才会冲突。
### MAC
	带密钥的hash函数。
---
## application of cryptography
### digital certificate
	将用户id和公钥进行关联的技术。
	大多数是Cert=(K(pub),ID,sig(K(pub),ID))
### certifying authority(CA)
	分发证书的可信组织。

### 非对称密码和对称密码的差异
	1. 对称密码适合对大数据进行加密，效率高，通常采用非对称加密对秘钥加密。

### replay attack
	重放攻击(Replay Attacks)又称重播攻击、回放攻击，是指攻击者发送一个目的主机已接收过的包，来达到欺骗系统的目的，主要用于身份认证过程，破坏认证的正确性。
---
## application
### base64
	一种基于64个可打印字符来表示二进制数据的方法。 输出内容中包括两个以上“符号类”字符（+, /, =)。
	Base64是一种任意二进制到文本字符串的编码方法，常用于在URL、Cookie、网页中传输少量二进制数据。
	Base64要求把每三个8Bit的字节转换为四个6Bit的字节（3*8 = 4*6 = 24），然后把6Bit再添两位高位0，组成四个8Bit的字节。
#### 加密规则
	关于这个编码的规则：
	①. 把3个字节变成4个字节。 在[A-Za-z0-9+/](ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/)中按下标查找对应字符。
	②. 每76个字符加一个换行符。
	③. 最后的结束符也要处理。
	特征是查表转化。
#### 解密规则
	解码是编码的反向过程，每次取出4个字节，然后将每个字节的字符转换成原始Base64索引表对应的索引数字，也就是编码时3字节转换成4字节的转换结果。然后使用位操作将每字节前2位去掉，重新转换成3字节。需要注意的是最后对于结尾“=”的处理。
	特征是char2index, >>4, >>2, <<6。

### tea算法
	属于流密码，每次对两个四字节数据，进行32轮变换。
	该算法得特征如下。
	1. 特征量：0x9e3779b9
	2. key: 4*32 = 128 bit {x1,x2,x3,x4}
	3. 传入两个32位无符号整数
	4. 三个累加量，其中最后赋值给传入的参数
	5. 存在<<4 , >>5 , xor等操作

```c
#include <stdio.h>
#include <stdint.h>
#include <string.h>
#include <stdlib.h>
#include <stdbool.h>

//加密函数
void encrypt (uint32_t* v, uint32_t* k) {
    uint32_t v0=v[0], v1=v[1], sum=0, i;           /* set up */
    uint32_t delta=0x9e3779b9;                     /* a key schedule constant */
    uint32_t k0=k[0], k1=k[1], k2=k[2], k3=k[3];   /* cache key */
    for (i=0; i < 32; i++) {                       /* basic cycle start */
        sum += delta;
        v0 += ((v1<<4) + k0) ^ (v1 + sum) ^ ((v1>>5) + k1);
        v1 += ((v0<<4) + k2) ^ (v0 + sum) ^ ((v0>>5) + k3);
    }                                              /* end cycle */
    v[0]=v0; v[1]=v1;
}

//解密函数
void decrypt (uint32_t* v, uint32_t* k) {
    uint32_t v0=v[0], v1=v[1], sum=0xC6EF3720, i;  /* set up */
    uint32_t delta=0x9e3779b9;                     /* a key schedule constant */
    uint32_t k0=k[0], k1=k[1], k2=k[2], k3=k[3];   /* cache key */
    for (i=0; i<32; i++) {                         /* basic cycle start */
        v1 -= ((v0<<4) + k2) ^ (v0 + sum) ^ ((v0>>5) + k3);
        v0 -= ((v1<<4) + k0) ^ (v1 + sum) ^ ((v1>>5) + k1);
        sum -= delta;
    }                                              /* end cycle */
    v[0]=v0; v[1]=v1;
}

int main()
{
    uint32_t k[4]={2,2,3,4};
    // v为要加密的数据是两个32位无符号整数
    // k为加密解密密钥，为4个32位无符号整数，即密钥长度为128位

    //exchange scale
    uint32_t flagLong[2];
    flagLong[0] = 0x67d7b805;
    flagLong[1] = 0x63c174c3;
    decrypt(flagLong,k);
    printf("flag{%x-%x}\n",flagLong[0],flagLong[1]);

    return 0;
}
```