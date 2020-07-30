## 列操作：  
- 可视模式, I + 修改内容 + ESE 两次。  
- ctrl+[ 退出插入模式  
- :(n,$/%) s/vivian/sky/(g) 替换 第n行/所有行/当前行 从开始到结束的字符串。  
- 行首：%s/^/your_word/  
- 行尾：%s/$/your_word/  
- 某几行: begin,ends/word/yourword/  
- ctrl + r : 撤销 u操作。  
- sp file; ctrl + w + j down;  ctrl +w +k up;  
- . 重复上一次命令  
  
## vim ctrl + p 自动补全  
- ctrl + p 上移   ctrl + n 下移  
  
## 查找  
- n 下移  shift + n 上移  

## normal mode  
- d$ 删除至行尾  
- d^ 删除至行首  
- gf 移动光标至文件处，再按。 ctrl+^ 会退回文件  
- . 重复上一次命令  
- gd 快速定位到函数定义部分  

## insert mode  
- ctrl + h 删除前一个字符  
- ctrl + w 删除前一个单词  

## view mode
- y 复制
- p 粘贴

## cscope
### common command
- cscope -Rbq 生成索引文件
- cs add cscope.out 加载cscope索引文件 可以写进~/.vimrc
- cs find {querytype} {name} 
 - 0或者s symbol   查找这个C符号
 - 1或者g globals  查找这个定义
 - 2或者d called  查找被这个函数调用的函数（们）
 - 3或者c calls  查找调用这个函数的函数（们）
 - 4或者t text   查找这个字符串
 - 6或者e egrep  查找这个egrep匹配模式
 - 7或者f file   查找这个文件
 - 8或者i includes   查找#include这个文件的文件（们）
- ctrl + ] 跳到光标所在变量或函数的定义处  Ctrl + T 返回

## ctags
### common command
- ctags -R 生成索引文件
- set tags=./tags 可以写进~/.vimrc
- ctrl + ] 跳到光标所在变量或函数的定义处  Ctrl + T 返回
