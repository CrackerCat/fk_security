# crypto
## number theory
### 有限域GF
	若p为素数，GF(p)={0,1,2,...,p-1}
	生成元g的有限域(阶为q) = {0,g^0,g^1,...,g^(q-2)}

### division algorithm
	divide a by n -> a = q*n + r -> 0<=r<n; q=floor(a/n)

### Euler's Theorem
	phi(n) : 在[1,n-1]范围内，与n互素的自然数个数。
	a^(phi(n)) 恒等于 1 (mod n), 其中a与n互质。

### primitive root 本原根
	素数p的原根定义：如果a是素数p的原根，则数a mod p, a^2 mod p, … , a^(p-1) mod p 是不同的并且包含1到p-1的整数的某种排列。
### gcd
	gcd(a,b) = gcd(a,-b) = gcd(-a,b) = gcd(-a,-b)=max[k, such that k | a and k | b]	

### 扩展欧几里得算法（Extended Euclid Algorithm）
	给予二整数 a 与 b, 必存在有整数 x 与 y 使得ax + by = gcd(a,b)。
	a存在模b的乘法逆元的充要条件是gcd（a,b）= 1。
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

---
## asymmetric cipher
### EIGamal encryption
	encrypt: (R,S)=(g^r mod p, m*B^r mod p)

---
## stream cipher
	一次加密一字节或一位数据。

---
## application of cryptography
### digital certificate
	将用户id和公钥进行关联的技术。

### 非对称密码和对称密码的差异
	1. 对称密码适合对大数据进行加密，效率高，通常采用非对称加密对秘钥加密。
---