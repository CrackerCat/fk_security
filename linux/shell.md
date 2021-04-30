# shell  
---
## string operation  
* ${variable##*string} 从左向右截取最后一个string后的字符串  
* ${variable:pos:length} substring  
* ${#variable/argument_number} length  
* $(shell command) 执行shell命令
* for ele in list;\ do command; done  遍历
---
## common cmd
### egrep
#### 基本正则表达式
- . ： 匹配任意单个字符
- [] ： 匹配指定范围内的任意单个字符
- [^] ：匹配指定范围外的任意单个字符
- * ：匹配前面的字符任意次，包括0次
- .* ：任意长度的任意字符
- \{m\} ：匹配前面的字符m次
- \{m,n\} ：匹配前面的字符至少m次，至多n次
- \{,n\} ：匹配前面的字符至多n次
- \{m,\} ：匹配前面的字符至少m次
- ^ ：行首锚定，用于模式的最左侧
- $ ：行尾锚定，用于模式的最右侧

### chmod
-- 4755: 4表示其他用户执行文件时，具有与所有者相当的权限。

### set
  shell中所有变量
- set -/+x: 开启/关闭详细的日志输出(xtrace).

### printf
	打印格式化字符串。

### time
	计算命令执行时间。

### lsof 
- 通过某个进程号显示该进行打开的文件 : lsof -p process_number 

### setsid PROG ARGS
	Run PROG in a new session. 

### grep
- grep -r "str" --exclude-dir={xxxx} . : 在某个目录递归搜索，但是排除xxxx目录。

### awk
- awk '{print $1}'

### cut


---
## bash
### envirenment variable
- BASH_CMDS: 含有命令的内部hash的数组。
- SHELLOPTS: 记录了处于开状态的shell选项列表，它是一个只读变量。 e.g.add history # set -o history
- PS4: 控制脚本调试显示信息的环境变量
---
## other
- $@/$* : 传入脚本的所有参数
- $# : 传入脚本的参数个数
- $(()) : 进行数字运算
- getopt : 不仅支持短参-s，还支持–longopt的长参数，甚至支持-longopt的简化参数。相较于getopts ，getopts 不但支持长短选项，其还支持选项和参数放在一起写。
	- -a : 使getopt长参数支持”-“符号打头，必须与-l同时使用
	- -l : 后面接getopt支持长参数列表
	- -o：后面接短参数列表，这种用法与getopts类似
---
