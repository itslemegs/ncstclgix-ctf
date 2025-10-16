s = "FwlrY{72mo70ml8p0881onp0q72mm5752lm13l}"
pad = "abcdefghijklmnopqrstuvwxyz"
shift = 1337 % 26  # 11

out = []
for c in s:
    if c in pad:
        out.append(pad[(pad.index(c) - shift) % 26])
    else:
        out.append(c)
print(''.join(out))
