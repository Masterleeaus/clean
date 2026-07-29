#!/usr/bin/env python3
"""Generate a QR SVG to stdout using the vendored BSD-licensed qrcode package."""
from __future__ import annotations

import pathlib
import sys

BASE = pathlib.Path(__file__).resolve().parent
if str(BASE) not in sys.path:
    sys.path.insert(0, str(BASE))

import qrcode
from qrcode.image.svg import SvgPathImage


def main() -> int:
    if len(sys.argv) != 2 or not sys.argv[1]:
        print("A non-empty QR payload is required.", file=sys.stderr)
        return 2

    image = qrcode.make(
        sys.argv[1],
        image_factory=SvgPathImage,
        error_correction=qrcode.constants.ERROR_CORRECT_H,
        box_size=10,
        border=4,
    )
    image.save(sys.stdout.buffer)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
