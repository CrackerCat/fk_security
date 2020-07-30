#android disasseble
## apktool
  逆向apk文件,它可以将资源解码，并在修改后可以重新构建它们。
### common commands
- java -jar apktool_jar_package d -f apk_package -o dir #解包
- java -b dir;用jarsigner签名 #生成apk,并签名

## dex2jar
  一个能操作Android的dalvik(.dex)文件格式和Java的(.class)的工具集合.
### procedure
- zip,解压apk
- java -jar d2j-dex2jar.jar classes.dex #classes.dex 对应java层源代码

### jd-gui
  可以将class/jar反编译成java源代码

### frida
  hook整个安卓的函数
