case "$1" in
    download)
        jq -r '.download' /tmp/speedtest_result.json 2>/dev/null | tr -d '[:space:]'
        ;;
    upload)
        jq -r '.upload' /tmp/speedtest_result.json 2>/dev/null | tr -d '[:space:]'
        ;;
    ping)
        jq -r '.ping' /tmp/speedtest_result.json 2>/dev/null | tr -d '[:space:]'
        ;;
esac
