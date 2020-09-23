# qemu
  虚拟操作系统模拟器
## mips
  mips是big-endian的mips架构。
  mipsel是little-endian的mips架构。
---

## cmd
- qemu-mips 大端user模式模拟运行; 用户模式没系统模式强大，功能有限。
- qemu-system-mips 大端system模式模拟运行
### example
- 使用qemu启动下载的镜像: qemu-system-mips -M malta -kernel vmlinux-3.2.0-4-4kc-malta -hda debian_wheezy_mips_standard.qcow2 -append "root=/dev/sda1 console=tty0" -netdev tap,id=tapnet,ifname=tap0,script=no -device rtl8139,netdev=tapnet -nographic
---

