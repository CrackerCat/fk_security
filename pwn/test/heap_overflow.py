from pwn import *
io = process('./heap_overflow')
context.terminal = ['tmux', 'splitw', '-h']
gdb.attach(io, 'b main')
payload = 'A'*100
io.sendlineafter('input:', payload)
io.interactive()
