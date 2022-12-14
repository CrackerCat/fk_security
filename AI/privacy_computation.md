# 隐私计算 - 数据安全
	在大数据时代中，海量的数据的交叉计算和人工智能的发展为各行各业提供了更好的支持，但这些被使用的数据往往包含用户的隐私数据，或企业/机构的内部数据。这些数据由于数据安全和隐私的考虑，往往是不对外开发。因此形成了一个个数据孤岛，数据之间不能互通，数据的价值无法体现。
	如何应用海量的数据，实现数据流动，同时能够保护数据隐私安全、防止敏感信息泄露是当前大数据应用中的重大挑战。隐私计算就是为了解决这些问题应运而生。隐私计算，广义上是指面向隐私保护的计算系统与技术，涵盖数据的生产、存储、计算、应用等信息流程全过程。
	目前在对数据隐私的保护方面，隐私计算技术的应用主要可以分为可信硬件, 多方安全计算，联邦学习三个主要流派。除了以上三大门派外，还有差分隐私、K匿名算法、L多样性等隐私相关的技术，这些技术不是相互替代关系，而是可以相互结合，产生更强大的威力。
## 可信硬件
	基于可信硬件方面技术，即通过硬件技术来对数据进行隔离保护。通用的应用方法包括安全屋，可信执行计算环境等。好处是不用深入研究算法和密码学，缺点是受限制较多，数据需要先集中后处理。
### TEE可信计算环境
	可信计算的基本思想是，首先在计算机系统中构建一个信任根，信任根的可信性由物理安全、技术安全和管理安全共同确保；再建立一条信任链，从信任根开始到软硬件平台，到操作系统、再到应用、一级度量认证一级、一级信任一级、把这种信任扩展到整个计算机系统，从而确保整个计算机系统的可信。
### 安全沙箱/安全屋
	安全屋主要是通过物理方式对数据的所有权和使用权进行分离，通常使用中心化和分布式相结合的混合架构，即各个数据提供方按照主控平台的接入规范统一接入平台，而所有管理权限由主控平台统一提供，各个参与的数据源方提供数据区的维护能力，通过这种方式来确保数据的整个流通过程安全可控的一种技术方案。
## 多方安全计算
	秘密分享。不经意传输。混淆电路。
	多方安全计算(Secure Multi-Party Computation)是指在无可信第三方情况下，通过多方共同参与，安全地完成某种协同计算。即在一个分布式的网络中，每个参与者都各自持有秘密输入，希望共同完成对某个函数的计算，但要求每个参与者除计算结果外均不能得到其他参与实体的任何输入信息。多方安全计算主要基于密码学的一些隐私技术，包括有同态加密(Homomorpgic Encryption)，不经意传输(Oblivious Transfer)，混淆电路(Garbled Circuit)，秘密共享(Secret Sharing)等。
### 秘密共享
	秘密共享是在一组参与者中共享秘密的技术，它主要用于保护重要信息，防止信息被丢失、被破坏、被篡改。
	基于Shamir秘密共享理论的方法中，秘密共享的机制主要由秘密的分发者D、团体参与者P{P1，P2，…，Pn}、接入结构、秘密空间、分配算法、恢复算法等要素构成。
	秘密共享通过把秘密进行分割，并把秘密在n个参与者中分享，使得只有多于特定t个参与者合作才可以计算出或是恢复秘密，而少于t个参与者则不可以得到有关秘密。
	秘密共享体系还具有同态的特性。
### 不经意传输 Oblivious Transfer - OT
	在Rabin [1] 的OT协议中，发送者Alice发送一个信息m给接收者Bob，接收者Bob以1/2的概率接受信息m。所以在协议交互的结束的时候，发送者Alice并不知道Bob是否接受了消息，而接收者Bob能确信地知道他是否得到了信息m，从而保护了接收者的隐私性，同时保证了数据传输过程的正确性。该方法主要是基于RSA加密体系构造出来。
	在实际应用中，不经意传输OT的一种实施方式是基于RSA公钥加密技术。一个简单的实施流程如下：首先，发送者生成两对不同的公私钥，并公开两个公钥，称这两个公钥分别为公钥1和公钥2。假设接收人希望知道m1，但不希望发送人知道他想要的是m1。接收人生成一个随机数k，再用公钥1对k进行加密，传给发送者。发送者用他的两个私钥对这个加密后的k进行解密，用私钥1解密得到k1，用私钥2解密得到k2。显然，只有k1是和k相等的，k2则是一串毫无意义的数。但发送者不知道接收人加密时用的哪个公钥，因此他不知道他算出来的哪个k才是真的k。发送人把m1和k1进行异或，把m2和k2进行异或，把两个异或值传给接收人。显然，接收人只能算出m1而无法推测出m2（因为他不知道私钥2，从而推不出k2的值），同时发送人也不知道他能算出哪一个。
### 混淆电路
	通过布尔电路的观点构造安全函数计算，达到参与者可以针对某个数值来计算答案，而不需要知道他们在计算式中输入的具体数字。
	混淆电路则通过加密和扰乱这些电路的值来掩盖信息，而这些加密和扰乱是以门为单位，每个门都有一张真值表。
### 同态加密
### 隐私信息检索(PIR)
### 零知识证明
	零知识证明实质上是一种涉及两方或更多方的协议，即两方或更多方完成一项任务所需采取的一系列步骤。证明者向验证者证明并使其相信自己知道或拥有某一消息，但证明过程不能向验证者泄漏任何关于被证明消息的信息。
## 联邦学习
	联邦学习结合密码学和分布式计算，实现了多方协作的机器学习。
	主要厂家：Google的TensorFlow Federated、微众的Fate、百度的PaddleFL、富数科技Avatar，蚂蚁Morse。
	联邦学习作为分布式的机器学习新范式，以帮助不同机构在满足用户隐私保护，数据安全，和政府法规的要求下，可以进行数据联合使用和建模为目的。
	主要解决的问题就是，在企业各自数据不出本地的前提下，通过加密机制下的参数交换与优化，建立虚拟的共有模型。这个共有模型的性能和传统方式将各方数据聚合到一起使用机器学习方法训练出来的模型性能基本一致。
	通过这种方式，可以从技术上有效解决数据孤岛问题，让参与方在不泄露用户隐私数据的基础上实现联合建模，实现AI协作。
	联邦学习通过加密机制下的参数交换方式保护用户数据隐 私，加密手段包括同态加密等，其数据和模型本身不会进行传输，因此在数据层面上不存在泄露的可能，也不违反更严格的数据保护法案如 GDPR 等。
	
	联邦学习主要分纵向联邦学习和横向联邦学习。
	传统分布式机器学习涵盖了多个方面，包括把机器学习中的训练数据分布式存储、计算任务分布式运行、模型结果分布式发布等，参数服务器（Parameter Server）是传统分布式机器学习的一个重要组成部分。参数服务器作为加速机器学习模型训练过程的一种工具，它将数据存储在分布式的工作节点上，通过一个中心式的调度节点调配数据分布和分配计算资源，以便更高效的获得最终的训练模型。而对于联邦学习而言，首先在于横向联邦学习中的工作节点代表的是模型训练的数据拥有方，其对本地的数据具有完全的自治权限，可以自主决定何时加入联邦学习进行建模，相对地在参数服务器中，中心节点始终占据着主导地位，因此联邦学习面对的是一个更复杂的学习环境；其次，联邦学习则强调模型训练过程中对数据拥有方的数据隐私保护，是一种应对数据隐私保护的有效措施，能够更好地应对未来愈加严格的数据隐私和数据安全监管环境。
## reference
- [1] https://www.36kr.com/p/727146931849347 : 隐私保护概述