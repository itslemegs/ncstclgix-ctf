```
                    ▗▄▄▄▖▗▞▀▜▌ ▄▄▄  ■      ▗▄▄▄▖▗▞▀▜▌▗▞▀▘   ■   ▄▄▄   ▄▄▄ 
                    ▐▌   ▝▚▄▟▌▀▄▄▗▄▟▙▄▖    ▐▌   ▝▚▄▟▌▝▚▄▖▗▄▟▙▄▖█   █ █    
                    ▐▛▀▀▘     ▄▄▄▀ ▐▌      ▐▛▀▀▘           ▐▌  ▀▄▄▄▀ █    
                    ▐▌             ▐▌      ▐▌              ▐▌             
                                   ▐▌                      ▐▌             
```

**Category:** Crypto
> One number binds them both.

**Flag:** FlagY{!_S33_You'r3_a_Cryptogrpaher_Ex3pert}

# WHAT EVEN IS THIS

Two RSA moduli were siblings—they share one prime—and somebody helpfully printed a weird linear combo of them. The server prints `n1 = p*q`, `n2 = q*z`, `c1 = m1^e mod n1`, `c2 = m2^e mod n2`, and `x = n1*E + n2`. Because `x` contains `n2` plus a multiple of `n1`, the greatest common divisor of `n1` and `x` is exactly the shared prime `q`. Once you have `q`, the rest is standard RSA surgery: factor, invert, decrypt. Files you saw: `server.py` (the generator) and `output.txt` (the printed values).

# WHY THIS IS A PROBLEM (TL;DR)

RSA security collapses if two moduli share a prime. Normally, factoring a 1024-bit RSA modulus is hard; but if two moduli share the same prime, a single `gcd(n1,n2)` reveals that prime instantly. The authors of this challenge made it even easier by printing `x = n1*E + n2`—so you don’t even need to compute `gcd(n1,n2)` directly; `gcd(n1, x)` gives the shared prime just as well. Once you know the shared prime `q`, factoring `n1` gives `p`, and standard modular-inverse + pow do the decryption. In one line: `gcd → factor → compute d → decrypt`.

# SOLUTION

First things first, let's check out the challenge files provided.

From `server.py`, we see that `x` is literally `n1 * E + n2`.

```python
n1, n2 = p*q, q*z
c1, c2 = pow(bytes_to_long(f1), e, n1), pow(bytes_to_long(f2), e, n2)
print(... n1 ...)
print(... c1 ...)
print(... c2 ...)
print(f"x = {n1*E + n2}")
```

Because `x = n1*E + n2`, any common divisor of `n1` and `x` must divide `n2` as well. But `n1` and `n2` share exactly the prime `q` (by construction: `n1 = p*q, n2 = q*z`). Therefore: `q=gcd(n1,x)`. Which is the key recovery step. Once we have `q`, compute `p = n1 // q`. We now have both primes for `n1`.

Next, we recover private exponent for `n1` and decrypt `c1`. We do so by computing Euler’s totient: `phi = (p-1)*(q-1)`, `d = e^{-1} mod phi`, and `m1 = c1^d mod n1`. We then convert `m1` from integer to bytes (`long_to_bytes`) that yields `flag1`.

Next, we try to recover `m2`. For `n2 = q*z`, we only know `q` (not `z`), but we can still recover the plaintext if the plaintext is smaller than `q`. We then compute `d_q = e^{-1} mod (q-1)` and `m2 = c2^{d_q} mod q`. Because `m2 < q`, this value is the true message bytes. Next, we convert to bytes to get `flag2`.

Finally, we concatenate `long_to_bytes(m1)` and `long_to_bytes(m2)`, and decode the flag.

*(See solve.py)*

![well-hello-beautiful](/assets/images/well-hello-beautiful.gif)