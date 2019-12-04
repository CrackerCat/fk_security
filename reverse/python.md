#python
## pyc->py
利用github上 uncompyle2工具,使用步骤.  
* uncompyle2 pyc_file/pyo_file > py_file

重新生成pyo文件.
* python -O -m py_compile py_file // 无-O生成pyc文件,-O是优化代码.
* pyo是源文件优化编译后的文件, pyc是源文件编译后的文件, pyd是其他语言写的python库.


