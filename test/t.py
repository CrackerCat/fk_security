#!/usr/bin/env python
#mov esp,0x080F6CC0
from pwn import *
p = asm('''
mov esp,0x080F6CC0
ret
''', arch='x86_64', os='linux')
print(repr(p))
#print shellcraft.linux.sh()
#print asm(shellcraft.linux.sh())
print disasm("\x89\xcc\xc3")
