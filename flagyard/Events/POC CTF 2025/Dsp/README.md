```
                    ▗▄▄▄   ▄▄▄ ▄▄▄▄  
                    ▐▌  █ ▀▄▄  █   █ 
                    ▐▌  █ ▄▄▄▀ █▄▄▄▀ 
                    ▐▙▄▄▀      █     
                               ▀     
```

**Category:** Reversing
<!-- **Level:** Easy -->
<!-- **Instance:** `nc 34.252.33.37 32034` -->
> Free flag for everyone.

**Flag:** FlagY{how_easy_peasy_is_this_?}

# WHAT EVEN IS THIS

We're given a Python disassembly that builds a 256-entry table using `poly = 0xEDB88320`, defines `zzz42(string)`—which starts with `value = 0xFFFFFFFF`, updates with `table[(ord(ch) ^ value) & 0xFF] ^ (value >> 8)` for each char, and returns `~value` (i.e., `-1 - value`)—and asks us for 6 strings, checks each `zzz42(s) & 0xFFFFFFFF` against the 6 numbers in `expected`. If everything matches, it prints `FlagY{<s1>_<s2>_..._<s6>}`.

# WHY THIS IS A PROBLEM (TL;DR)

Since the hash is CRC-32 (not a cryptographic hash), we can just recognize the algorithm as zlib’s CRC-32 and look for short strings whose CRCs match the target integers.

# SOLUTION

First things first, let's check out the challenge files provided.

We found that the polynomial `3988292384` is `0xEDB88320`, the reflected CRC-32 poly. The init (`0xFFFFFFFF`) and final XOR (`~value`) match the usual zlib flavor. Furthermore, we also found the targets from `expected`: `2508312701`, `1231198871`, `1473663577`, `1022026391`, `4277043751`, and `1684325040`. To solve this, we check small words with zlib’s crc32 until they match.

```bash
❯ python3 solve.py
Searching 0x9581d07d ...
  -> brute match: 'how'
Searching 0x49629a97 ...
  -> brute match: 'easy'
Searching 0x57d65259 ...
  -> brute match: 'peasy'
Searching 0x3ceae297 ...
  -> brute match: 'is'
Searching 0xfeee8227 ...
  -> brute match: 'this'
Searching 0x6464c2b0 ...
  -> quick match: '?'

Decoded pieces: ['how', 'easy', 'peasy', 'is', 'this', '?']
FlagY{how_easy_peasy_is_this_?}
```

*(See solve.py)*

![well-hello-beautiful](/assets/images/well-hello-beautiful.gif)