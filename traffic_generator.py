import requests
import random
import time

urls = [
    "http://127.0.0.1:8000/api/normal",
    "http://127.0.0.1:8000/api/slow",
    "http://127.0.0.1:8000/api/error"
]

while True:
    url = random.choice(urls)

    try:
        requests.get(url)
    except:
        pass

    time.sleep(random.uniform(0.2,1.5))