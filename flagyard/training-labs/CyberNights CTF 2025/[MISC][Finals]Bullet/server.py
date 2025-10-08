#!/usr/bin/env python3

print("Welcome to Bullet!")
print("Goal: Can you execute commands?")

while True:
    try:
        user_input = input(">>> ")
        result = eval(user_input, {"__builtins__": {}})
        print("Result:", result)
    except Exception as e:
        print("Error:", e) 