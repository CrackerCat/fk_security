# git
## mechanism
- .gitignore 文件可以在任意文件夹中创建, .gitignore只能忽略那些原来没有被track的文件，如果某些文件已经被纳入了版本管理中，则修改.gitignore是无效的. 
   - 解决方法如下。  
	```c
	$ git rm -r --cached .  
	$ git add .
	$ git commit -m 'update .gitignore'	  
	```

---
## markdown syntax  
- 两个空格表示换行。  
- \# 一级标题、## 二级标题 以此类推。
- !\[](image path "text") 显示图片。  
- \- text 表示 * text 可以用来列举每个要点。  
- \*\*text** 加租字体  
- \```code block```
- \* 列表和缩进

---