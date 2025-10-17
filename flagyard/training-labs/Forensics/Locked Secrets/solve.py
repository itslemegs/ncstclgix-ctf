CPASSWORD_B64 = "Xpk4DyKDjYQQ/EUHClHlP+m6AKRQL5cC+JABNiw5C9aUMrmXIH2Mkky4a8tKUxIq"
GPP_AES_KEY_HEX = "4e9906e8fcb66cc9faf49310620ffee8f496e806cc057990209b09a433b66c1b"

from base64 import b64decode
import sys, subprocess
try:
    from Crypto.Cipher import AES
except Exception:
    subprocess.check_call([sys.executable, "-m", "pip", "install", "pycryptodome"])
    from Crypto.Cipher import AES

def decrypt_gpp_cpassword(cpassword_b64: str, key_hex: str) -> str:
    key = bytes.fromhex(key_hex)
    iv = b"\x00" * 16
    ct = b64decode(cpassword_b64)
    cipher = AES.new(key, AES.MODE_CBC, iv)
    pt = cipher.decrypt(ct)

    pad_len = pt[-1]
    if isinstance(pad_len, int) and 1 <= pad_len <= 16:
        pt = pt[:-pad_len]
    try:
        return pt.decode("utf-16le")
    except Exception:
        return repr(pt)

if __name__ == "__main__":
    try:
        plaintext = decrypt_gpp_cpassword(CPASSWORD_B64, GPP_AES_KEY_HEX)
        print("Decrypted password:", plaintext)
    except Exception as e:
        print("Error:", e)