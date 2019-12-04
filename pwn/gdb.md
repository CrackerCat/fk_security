# gdb commad:
- finish ： 执行完整个函数
- c : 继续运行
- x/64 w(四字节)/g(八字节)/c(字符格式) x/i(显示指令) $寄存器/地址
- r : 运行
- ni : 单步跳过
- si : 单步进入
- distance addr1 addr2  = addr2 - addr1 
- search -s "target string"
- vmmap 查看进程中的权限
- bt backtrace 函数调用路径,可查看当前运行到哪一行
- set var $reg/addr=value  修改变量值
- list <linenum>/<function>/<first>,<last>/,<last>/-/+/empty  显示源代码(-g)

