"""Disposable localhost fixture tests. Never invokes the application or database."""
from pathlib import Path
import json
import socket
import subprocess
import time
import urllib.request
import urllib.error

fixture = Path(__file__).resolve().parent
with socket.socket() as listener:
    listener.bind(("127.0.0.1", 0))
    port = listener.getsockname()[1]
server = subprocess.Popen(["php", "-S", f"127.0.0.1:{port}", "-t", str(fixture)],
                          stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
try:
    for _ in range(50):
        try:
            with socket.create_connection(("127.0.0.1", port), timeout=0.2):
                break
        except OSError:
            if server.poll() is not None:
                raise RuntimeError("Fixture server failed to start")
            time.sleep(0.1)
    cases = [
        ("valid JSON", b'{"admin_username":"test","admin_password":"test"}', "application/json", True, 200),
        ("malformed JSON", b'{', "application/json", True, 400),
        ("scalar JSON", b'"bad"', "application/json", True, 400),
        ("array JSON", b'[]', "application/json", True, 400),
        ("nested scalar", b'{"admin_username":{}}', "application/json", True, 400),
        ("answers array", b'{"answers":[]}', "application/json", True, 400),
        ("teachers object", b'{"teachers":{}}', "application/json", True, 400),
        ("wrong content type", b'{}', "text/plain", True, 415),
        ("missing CSRF", b'{}', "application/json", False, 403),
        ("oversized JSON", b' ' * (128 * 1024 + 1), "application/json", True, 413),
    ]
    for name, body, content_type, token, expected in cases:
        headers = {"Content-Type": content_type}
        if token:
            headers["X-CSRF-Token"] = "a" * 64
        request = urllib.request.Request(f"http://127.0.0.1:{port}/http_case.php?scenario=json_body",
                                         data=body, headers=headers, method="POST")
        try:
            response = urllib.request.urlopen(request, timeout=5)
        except urllib.error.HTTPError as error:
            response = error
        with response:
            status = response.status
            payload = json.loads(response.read())
            assert status == expected and payload["http"] == expected, (name, status, payload)
            assert response.headers.get("Cache-Control") == "no-store", name
            assert response.headers.get("X-Content-Type-Options") == "nosniff", name
        print("PASS HTTP " + name)
finally:
    server.terminate()
    try:
        server.wait(timeout=5)
    except subprocess.TimeoutExpired:
        server.kill()
        server.wait(timeout=5)
