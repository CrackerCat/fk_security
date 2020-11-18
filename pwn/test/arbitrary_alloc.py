from pwn import *
LIBC='/lib64/libc.so.6'
libc_elf = ELF(LIBC)
print("__malloc_hook:"+hex(libc_elf.symbols['__malloc_hook']))
