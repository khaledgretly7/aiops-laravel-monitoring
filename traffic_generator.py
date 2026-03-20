import requests
import time

BASE_URL = "http://127.0.0.1:8000"

def spike_latency():
    print("Generating latency spike...")
    for _ in range(5):
        try:
            requests.get(f"{BASE_URL}/api/slow", timeout=10)
        except Exception as e:
            print(f"Ignored error: {e}")
        time.sleep(0.2)

def spike_errors():
    print("Generating error spike...")
    for _ in range(5):
        try:
            requests.get(f"{BASE_URL}/api/error")
        except Exception as e:
            print(f"Ignored error: {e}")
        time.sleep(0.2)

def spike_traffic():
    print("Generating traffic spike...")
    for _ in range(50):
        try:
            requests.get(f"{BASE_URL}/api/normal")
        except Exception as e:
            print(f"Ignored error: {e}")
        time.sleep(0.05)

if __name__ == "__main__":
    print("Starting traffic generation...")
    spike_latency()
    spike_errors()
    spike_traffic()
    print("Traffic generation complete.")