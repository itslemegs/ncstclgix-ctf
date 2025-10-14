# find_readable_improved.py
import zlib
import itertools
import string
import time

targets = [2508312701, 1231198871, 1473663577, 1022026391, 4277043751, 1684325040]

def find_crc_quick(target, known_candidates):
    for s in known_candidates:
        if zlib.crc32(s.encode()) & 0xffffffff == target:
            return s
    return None

def find_crc_bruteforce(target, charset, maxlen=5):
    target &= 0xFFFFFFFF
    for length in range(1, maxlen + 1):
        for tup in itertools.product(charset, repeat=length):
            s = ''.join(tup)
            if zlib.crc32(s.encode()) & 0xffffffff == target:
                return s
    return None

def main():
    quick_candidates = list(string.punctuation) + list(string.ascii_lowercase) + [" "]
    charset = string.ascii_lowercase
    maxlen = 6

    decoded = []
    for t in targets:
        print(f"Searching {hex(t)} ...", flush=True)
        s = find_crc_quick(t, quick_candidates)
        if s:
            print("  -> quick match:", repr(s))
            decoded.append(s); continue

        s = find_crc_bruteforce(t, charset, maxlen=maxlen)
        if s:
            print("  -> brute match:", repr(s))
            decoded.append(s); continue

        print("  -> not found")
        decoded.append("??")

    print("\nDecoded pieces:", decoded)
    print("FlagY{" + "_".join(decoded) + "}")

if __name__ == "__main__":
    main()
