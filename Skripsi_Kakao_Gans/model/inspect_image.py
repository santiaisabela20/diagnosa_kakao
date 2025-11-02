import sys
from PIL import Image
import traceback

path = sys.argv[1]
print('PATH:', path)
try:
    import os
    print('Exists:', os.path.exists(path))
    print('Size:', os.path.getsize(path))
    with open(path, 'rb') as f:
        head = f.read(64)
    print('Header (hex):', head[:32].hex())
except Exception as e:
    print('File info error:', repr(e))

try:
    img = Image.open(path)
    print('PIL format:', img.format)
    img.verify()
    print('verify() passed')
except Exception as e:
    print('PIL open/verify exception:', repr(e))
    traceback.print_exc()
    sys.exit(1)

print('OK')
