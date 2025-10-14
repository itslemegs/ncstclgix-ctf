import re
import sys

if len(sys.argv) < 2:
    print("Usage: safe_deob.py <file>", file=sys.stderr)
    sys.exit(1)

path = sys.argv[1]
with open(path, "r", encoding="utf-8", errors="ignore") as f:
    src = f.read()

m = re.search(r'"([^"]+)"\s*-f', src, flags=re.S)
if m:
    fmt = m.group(1)
    afterf = src[m.end():]
else:
    quotes = re.findall(r'"([^"]+)"', src, flags=re.S)
    if quotes:
        fmt = max(quotes, key=len)
        afterf = src
    else:
        print(src)
        sys.exit(0)

args = []
for m in re.finditer(r"(?:'([^']*)'|\"([^\"]*)\")", afterf, flags=re.S):
    arg = m.group(1) if m.group(1) is not None else m.group(2)
    args.append(arg)

result = fmt

for i, a in enumerate(args):
    
    placeholder = r"\{" + str(i) + r"\}"
    
    result = re.sub(placeholder, lambda _m, repl=a: repl, result)

result = re.sub(r"Cvx", "'", result)
result = re.sub(r"vx\+", "", result)
result = re.sub(r"Cv", "", result)
result = re.sub(r"\s{2,}", " ", result)

sys.stdout.write(result)
