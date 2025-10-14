```
                     ▗▄▄▖▗▞▀▜▌▄▄▄▄  ▗▞▀▚▖ ▗▄▄▖█  ▄▄▄  █  ▐▌▐▌
                    ▐▌   ▝▚▄▟▌█ █ █ ▐▛▀▀▘▐▌   █ █   █ ▀▄▄▞▘▐▌
                    ▐▌▝▜▌     █   █ ▝▚▄▄▖▐▌   █ ▀▄▄▄▀   ▗▞▀▜▌
                    ▝▚▄▞▘                ▝▚▄▄▖█         ▝▚▄▟▌
```

**Category:** Web
**Instance:** http://bmnzdgnsz2l4.playat.flagyard.com
> Ever wanted to hack the cloud and buy your way to glory? Welcome to GameCloud!

**Flag:** FlagY{d6ce936f901dfdd68aabdca3de03dadc}

# WHAT EVEN IS THIS

We’re given a “gaming cloud” Flask app with endpoints for games, store, leaderboards, profiles, daily rewards, and—juicy bit—premium purchases. One premium “service” is `admin_access`, whose `description` is literally the dynamic flag value pulled from an environment variable. If you can buy it, the API reply echoes the flag right back to you.

Key artifacts from the challenge files:

- `app.secret_key = "verysecurekeythatwenevergetto"` (client-side sessions signed with a known secret)
- `PREMIUM_SERVICES['admin_access']['description'] = os.getenv('DYN_FLAG', 'FlagY{test_flag}')`
- `/api/premium/purchase` returns each purchased service’s name + description in JSON

# WHY THIS IS A PROBLEM (TL;DR)

- Flask uses client-side sessions signed with the app’s `SECRET_KEY`. If the secret is public/guessable, we can mint whatever session we want.
- The premium purchase route returns the service `description` for whatever we buy.
- Combine them: forge a session with tons of coins → buy `admin_access` → flag appears in the JSON response. EZPZ.

# SOLUTION

Checking out the challenge file, we find out that `app.secret_key = "verysecurekeythatwenevergetto"`. This means anyone can create a valid Flask session cookie (because Flask saves session data on the client and just signs it).

Diving a little bit deeper, in `PREMIUM_SERVICES`:

```python
'admin_access': {
    'name': 'Developer Console Access',
    'price': 999,
    'bonus_eligible': False,
    'description': str(os.getenv('DYN_FLAG','FlagY{test_flag}')),
    ...
}
```

And the purchase route returns:

```python
purchased_services.append({
    'service': service['name'],
    'description': service['description'],
    'quantity': item['quantity']
})
```

So if we can buy `admin_access`, we get the flag in `description`.

In `/api/premium/purchase`, we found out how the purchase math worked. First, it validates `quantity` (blocks NaN/inf/≤0). Then, it computes `bonus_coins` only for `bonus_eligible` items (not `admin_access`). Next, it computes `available_balance = session['coins'] + bonus_coins`. Finally, it requires `available_balance - total_cost >= 0`. So, just give yourself a huge `session['coins']` and skip the bonus shenanigans.

![stealth-mode](/assets/images/stealth-mode.gif)

Because some serializer quirks vary, the solid method is to use Flask’s own signing interface locally with the known secret. First, we build `session_dict = {"coins": 1000000000, "user": "Pwner", ...}`. Then, we use `SecureCookieSessionInterface().get_signing_serializer(app)` to `dumps()` that dict. Finally, we get a legit `session=<cookie>`.

Next, we send the cookie with our request to `/api/premium/purchase`. Then, the server thinks we're loaded, approves the purchase, and outputs the flag.

![i-own-a-boat](/assets/images/i-own-a-boat.gif)

*(See solve.py)*

![well-hello-beautiful](/assets/images/well-hello-beautiful.gif)