# cmake  
## command  
- file 文件操作命令 例如 file(COPY files DESTINATION dir)  
- add_library ( <library name> [STATIC | SHARED | MODULE] [EXCLUDE_FROM_ALL] source1... ) 其中EXCLUDE_FROM_ALL表示默认不会构建，除非被其他组件依赖. 主要作用是将指定源文件生成链接文件。  
- link_directories() 指定要链接的库文件的路径  
- target_link_libraries() 将库文件与库文件链接  
- add_executable(name source1...) 为工程引入一个可执行文件 , 一般后面紧跟target_link_libraries()  
- message() 打印  
- add_subdirectory(source_dir [binary_dir] [EXCLUDE_FROM_ALL]) 将外部项目文件夹(含有CMakeLists.txt)加入build任务列表中。若未指定binary_dir(输出文件的位置)，则它等于source_dir。  
---  
## macro  
usage: ${macro name}。在if中直接使用变量名。  
- PROJECT_SOURCE_DIR 源代码 <- cmake ..  
- PROJECT_BINARY_DIR 二进制 <- 当前路://cmake.org/documentation径  
- <projectname>_BINARY_DIR 和<projectname>_SOURCE_DIR 两个隐式变量。  
- EXECUTABLE_OUTPUT_PATH 二进制存放位置  
- LIBRARY_OUTPUT_PATH 目标链接文件存放位置  
- CMAKE_C_FLAGS = ADD_DEFINITIONS() 设置C编译选项  
- CMAKE_CXX_FLAGS = ADD_DEFINITIONS() 设置C++编译选项  
## logical expression  
logical expression 用来创建条件输出。  
- $<TARGET_OBJECT:objLib> objLib构建过程产生的目标文件列表。objLib 必须是OBJECT_LIBRARY的一个目标文件。这个表达式只能在add_library或者add_executable()中使用。  
---  
## documentation  
url: https://cmake.org/documentation  

