case "$1" in
    download)
        jq -r '.download' /usr/local/scripts/speedtest_result.json 2>/dev/null | tr -d '[:space:]'
        ;;
    upload)
        jq -r '.upload' /usr/local/scripts/speedtest_result.json 2>/dev/null | tr -d '[:space:]'
        ;;
    ping)
        jq -r '.ping' /usr/local/scripts/speedtest_result.json 2>/dev/null | tr -d '[:space:]'
        ;;
esac
