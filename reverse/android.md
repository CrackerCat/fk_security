# android
##android disasseble
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

## AOSP - android open source project
	Android=AOSP+GMS（google mobile service)，所以国内的AOSP需要定制修改。
### android 系统架构
![](https://qiangbo-workspace.oss-cn-shanghai.aliyuncs.com/2016-09-05-AndroidAnatomy_Introduction/Android_Architecture.png "")
	整个Android操作系统分为五层。它们分别是：
	1. 内核层(Linux Kernel以及Android定制的一些改动）: Android在Linux增加了一些定制的驱动，这些驱动通常与硬件无关，而是为了上层软件服务的，它们包括：
		a. Binder  进程间通讯（IPC）基础设施。
		b. Ashmem 匿名共享内存。
		c. lowmemorykiller 进程回收模块。
		d. logger 日志相关。
		e. wakelock 电源管理相关。
		f. Alarm 闹钟相关，为AlarmManager服务。
	2. 硬件抽象层，该层为硬件厂商定义了一套标准的接口。
	3. Runtime和公共库层，这一层包含了虚拟机以及基本的运行环境。早期Dalvik，后来是ART。
	4. Framework 层，这一层包含了一系列重要的系统服务。对于App层的管理以及App 使用的API基本上都是在这一层提供的。
		a. ActivityManagerService：负责四大组件的管理（Activity，Service，ContentProvider，BroadcastReceiver）以及App进程管理
		b. WindowManagerService：负责窗口管理
		c. PackageManagerService：负责APK包的管理，包括安装，卸载，更新等
		d. NotificationManagerService：负责通知管理
		e. PowerManagerService：电源管理
		f. LocationManagerService：定位相关
	5. 应用层，这是与用户直接接触的一层。
		