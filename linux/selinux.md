# SeLinux
  Selinux就是通过安全上下文来判断一个进程对客体的访问，更为重要的是，由于SElinux最主要的访问控制手段是类型强制（TE：type enforcement）。当SeLinux开启时，SeLinux和Linux的访问控制同时生效。
## concept
### 安全上下文
  格式：user:role:type
  进程的type被称为域。
### TE (Type Enforcement)
  可以编写te文件，编译成pp文件，最后加载进内核。
#### TE grammar
- allow source_type target_type : object_class {permissions};
### 查看标识符
  使用 "-Z"

## common command
- seinfo -u/r : query u/r
- sesearch -t : search access vector
- semanage : policy management
- getenforce/setenforce
- semodule : selinux module management
