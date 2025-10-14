import re
import sys
import math
from Crypto.Util.number import long_to_bytes
from Crypto.Util.number import inverse

def parse_numbers_from_text(text):
    labels = {}
    pattern = re.compile(r'^\s*(n1|c1|c2|x)\s*=\s*([0-9]+)\s*$', re.MULTILINE | re.IGNORECASE)
    for m in pattern.finditer(text):
        labels[m.group(1).lower()] = int(m.group(2))

    if all(k in labels for k in ("n1","c1","c2","x")):
        return labels["n1"], labels["c1"], labels["c2"], labels["x"]

    ints = re.findall(r'\b([0-9]{50,})\b', text)
    if len(ints) >= 4:
        n1 = int(ints[0])
        c1 = int(ints[1])
        c2 = int(ints[2])
        x  = int(ints[3])
        return n1, c1, c2, x

    raise ValueError("Couldn't parse n1, c1, c2, x from file. Make sure the file contains them labeled or as big integers.")

def recover_flags(n1, c1, c2, x, e=65537):
    q = math.gcd(n1, x)
    if q == 1 or q == n1:
        raise ValueError(f"Unexpected gcd result: q={q} (gcd failed)")

    p = n1 // q
    print(f"[+] Found shared prime q (decimal, truncated): {str(q)[:60]}...") 
    print(f"[+] p = n1 // q (decimal, truncated): {str(p)[:60]}...")

    phi = (p - 1) * (q - 1)
    try:
        d = inverse(e, phi)
    except Exception as exc:
        raise ValueError("Failed to invert e mod phi(n1).") from exc

    m1 = pow(c1, d, n1)

    try:
        d_q = inverse(e, q - 1)
    except Exception as exc:
        raise ValueError("Failed to invert e mod (q-1).") from exc
    m2 = pow(c2, d_q, q)

    return m1, m2, p, q

def pretty_bytes(b):
    try:
        return b.decode('utf-8')
    except Exception:
        try:
            return b.decode('latin-1')
        except Exception:
            return repr(b)

def main():
    if len(sys.argv) != 2:
        print("Usage: python solve.py <output.txt>")
        sys.exit(2)

    path = sys.argv[1]
    txt = open(path, 'r', encoding='utf-8', errors='ignore').read()

    try:
        n1, c1, c2, x = parse_numbers_from_text(txt)
    except ValueError as e:
        print("ERROR:", e)
        sys.exit(1)

    print("[*] Parsed values from file.")
    print(f"    n1 (digits) = {len(str(n1))}")
    print(f"    c1 (digits) = {len(str(c1))}")
    print(f"    c2 (digits) = {len(str(c2))}")
    print(f"    x  (digits) = {len(str(x))}")

    try:
        m1, m2, p, q = recover_flags(n1, c1, c2, x)
    except Exception as e:
        print("Exploit failed:", e)
        sys.exit(1)

    b1 = long_to_bytes(m1)
    b2 = long_to_bytes(m2)

    print("\n--- Recovered plaintext parts (raw bytes) ---")
    print(f"m1 ({len(b1)} bytes):")
    print(b1)
    print(f"\nm2 ({len(b2)} bytes):")
    print(b2)

    print("\n--- Best-effort decodes ---")
    print("m1 decoded:", pretty_bytes(b1))
    print("m2 decoded:", pretty_bytes(b2))

    try:
        combined = b1 + b2
        print("\nCombined (bytes):", combined)
        print("Combined decoded:", pretty_bytes(combined))
    except Exception:
        pass

if __name__ == "__main__":
    main()
