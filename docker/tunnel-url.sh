#!/bin/sh
# Prints the public trycloudflare.com link of the running tunnel.
# Waits up to 60 seconds, because Cloudflare needs a few seconds to create it.
# Usage (from the project folder): ./docker/tunnel-url.sh

cd "$(dirname "$0")/.." || exit 1

if ! docker compose --profile tunnel ps --status running --services 2>/dev/null | grep -qx tunnel; then
    echo "Tunelul nu rulează. Pornește-l cu:"
    echo "  docker compose --profile tunnel up -d --build"
    exit 1
fi

i=0
while [ $i -lt 30 ]; do
    url=$(docker compose --profile tunnel logs tunnel 2>/dev/null \
        | grep -oE 'https://[a-z0-9-]+\.trycloudflare\.com' | tail -1)
    if [ -n "$url" ]; then
        echo "$url"
        exit 0
    fi
    i=$((i + 1))
    sleep 2
done

echo "Linkul nu a apărut încă. Verifică: docker compose --profile tunnel logs tunnel"
exit 1
