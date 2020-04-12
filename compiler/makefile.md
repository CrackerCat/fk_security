# makefile
## grammar
- .PHONY: object object如果不是文件,则会出现"up to date".因此需要这个声明。
- 规则所在行必须以tab开头。
- $(info/warning/error $(var)) 打印
- @echo "" 打印
- $(foreach ele,list,expression) 遍历返回 expression(ele) 字符串
