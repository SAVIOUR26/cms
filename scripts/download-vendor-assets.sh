#!/usr/bin/env bash
# KandaNews — Download vendor assets for local bundling (P1)
# Run once on server setup or after a clean clone.
# Files go to assets/vendor/ which is gitignored (binary/generated).

set -e
VENDOR="$(dirname "$0")/../assets/vendor"
mkdir -p "$VENDOR/webfonts"

echo "► Downloading Swiper 11..."
curl -sL "https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" -o "$VENDOR/swiper.min.css"
curl -sL "https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"  -o "$VENDOR/swiper.min.js"

echo "► Downloading FontAwesome 6.4..."
curl -sL "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" \
  | sed 's|\.\./webfonts/|webfonts/|g' \
  > "$VENDOR/fa.min.css"
curl -sL "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-solid-900.woff2"   -o "$VENDOR/webfonts/fa-solid-900.woff2"
curl -sL "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-brands-400.woff2"  -o "$VENDOR/webfonts/fa-brands-400.woff2"
curl -sL "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-regular-400.woff2" -o "$VENDOR/webfonts/fa-regular-400.woff2"

echo "✓ Vendor assets ready in $VENDOR"
ls -lh "$VENDOR"
