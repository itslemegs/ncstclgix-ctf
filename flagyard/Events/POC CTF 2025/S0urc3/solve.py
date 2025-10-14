encoded = [71,105,102,104,88,120,107,108,49,54,103,55,52,45,104,110,51,98,60,58,52,98,103,106,55,97,105,110,50,96,55,59,99,49,60,61,56,54,130]
out = []
for i,b in enumerate(encoded):
    if i % 4 == 0: out.append(b ^ 0x01)
    elif i % 4 == 1: out.append((b + 3) & 0xFF)
    elif i % 4 == 2: out.append((b - 5) & 0xFF)
    else: out.append(b ^ 0x0F)
print(bytes(out).decode('latin-1'))
