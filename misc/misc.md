# MISC
## tools
- pdf2word ：http://app.xunjiepdf.com/pdf2word
- convert ： gif -> png
- stegsolve : 图片隐写查看器
  - File Format:文件格式，这个主要是查看图片的具体信息
  - Data Extract:数据抽取，图片中隐藏数据的抽取
  - Frame Browser:帧浏览器，主要是对GIF之类的动图进行分解，动图变成一张张图片，便于查看
  - Image Combiner:拼图，图片拼接
- 十六进制串转字符串 : https://www.bejson.com/convert/ox2str/
- crc校验码计算 ： http://www.ip33.com/crc.html

## file
### rar
  RAR Archive (rar)， 文件头：52617221
  RAR每个块的开头都是 HEAD_CRC(2byte) + HEAD_TYPE(1byte)
#### HEAD_TYPE
- 0x74 : FILE_HEAD file header.
### png
  PNG (png)， 　　 文件头：89504E47　　文件尾：AE 42 60 82
### gif
  GIF (gif)， 　　 文件头：47494638　　文件尾：00 3B ZIP 
### zip
  ZIP支持基于对称加密系统的一个简单的密码,已知明文攻击，字典攻击和暴力攻击。
  一个 Zip文件由三个部分组成：压缩源文件数据区+压缩源文件目录区+压缩源文件目录结束标志 
  压缩源文件数据区：50 4B 03 04：这是头文件标记（0x04034b50） 
  压缩源文件目录区：
  - 50 4B 01 02：目录中文件文件头标记
  - 3F 00：压缩使用的 pkware 版本 
  - 14 00：解压文件所需 pkware 版本
  - 00 00：全局方式位标记（有无加密，这个更改这里进行伪加密，改为09 00打开就会提示有密码了）
  压缩源文件目录结束标志 ：50 4B 05 06：目录结束标记
  
#### zip属性隐藏
  注意文件属性一栏，往往有时候，加密者会把密码放在属性里面，可能在注释里。
#### zip 伪加密
  伪加密是在未加密的zip文件基础上修改了它的压缩源文件目录区里的全局方式位标记的比特值，使得压缩软件打开它的时候识别为加密文件，提示输入密码.
- ZipCenOp.jar : java -jar ZipCenOp.jar r xxx.zip
or
- 使用WinRAR,压缩修复文件，修复完后压缩包就可以打开了。
or
- 用winhex打开，搜索504B,把解压字段(1400)后面的一字节改为00
#### force crack
  Windows下我使用的是ARCHPR
- 暴力破解
- 字典破解
- 掩码破解
