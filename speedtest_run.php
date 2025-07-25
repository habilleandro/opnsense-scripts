#!/usr/local/bin/php
<?php
date_default_timezone_set("UTC");

function is_int_val($n) {
    return is_numeric($n) && intval($n) == $n;
}

function json_output($data) {
    echo json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;
    exit;
}

$speedtest = "speedtest";
$csvfile = "/usr/local/opnsense/scripts/OPNsense/speedtest/speedtest.csv";
$arg = $argv[1] ?? '';

$fields = ['Timestamp', 'ClientIp', 'ServerId', 'ServerName', 'Country', 'DlSpeed', 'UlSpeed', 'Latency', 'Link'];

if (!file_exists($csvfile)) {
    file_put_contents($csvfile, implode(",", $fields) . PHP_EOL);
}

switch ($arg) {
    case 'log':
        $rows = array_slice(array_reverse(file($csvfile)), 1, 50);
        $array = array_map(function($line) {
            $row = str_getcsv(trim($line));
            $row[0] = date(DATE_ISO8601, floatval($row[0]));
            return $row;
        }, $rows);
        json_output($array);

    case 'recent':
        $lines = file($csvfile);
        $last = str_getcsv(trim(end($lines)));
        $last[0] = date(DATE_ISO8601, floatval($last[0]));
        $out = [
            'date' => $last[0],
            'server' => $last[2] . " " . $last[3],
            'download' => $last[5],
            'upload' => $last[6],
            'latency' => $last[7],
            'url' => $last[8]
        ];
        json_output($out);

    case 'stat':
        $lines = file($csvfile);
        array_shift($lines); // remove header
        $latency = $dl = $ul = $time = [];
        foreach ($lines as $line) {
            $row = str_getcsv(trim($line));
            $time[] = date(DATE_ISO8601, floatval($row[0]));
            $dl[] = floatval($row[5]);
            $ul[] = floatval($row[6]);
            $latency[] = floatval($row[7]);
        }
        if (empty($dl)) {
            $dl = $ul = $latency = [0];
        }
        $out = [
            'samples' => count($dl),
            'period' => [
                'oldest' => min($time),
                'youngest' => max($time)
            ],
            'latency' => [
                'avg' => round(array_sum($latency)/count($latency), 2),
                'min' => min($latency),
                'max' => max($latency)
            ],
            'download' => [
                'avg' => round(array_sum($dl)/count($dl), 2),
                'min' => min($dl),
                'max' => max($dl)
            ],
            'upload' => [
                'avg' => round(array_sum($ul)/count($ul), 2),
                'min' => min($ul),
                'max' => max($ul)
            ]
        ];
        json_output($out);

    case 'version':
        exec("$speedtest --version 2>&1", $output, $ret);
        if ($ret !== 0) {
            json_output(['version' => 'none', 'message' => 'No speedtest installed']);
        }
        $bin = stripos(implode("", $output), "Ookla") !== false;
        json_output(['version' => $bin ? 'binary' : 'cli', 'message' => $output[0]]);

    case 'list':
        exec("$speedtest --accept-license --accept-gdpr --servers -fjsonl 2>&1", $output, $ret);
        $servers = [];
        foreach ($output as $line) {
            $data = json_decode($line, true);
            $servers[] = [
                'id' => strval($data['id']),
                'name' => $data['name'],
                'location' => $data['location'],
                'country' => $data['country']
            ];
        }
        json_output($servers);

    default:
        $cmd = '';
        if ($arg === '' || $arg === '0') {
            $cmd = "$speedtest --accept-license --accept-gdpr -fjson";
        } elseif (is_int_val($arg)) {
            $cmd = "$speedtest --accept-license --accept-gdpr -fjson -s $arg";
        } else {
            json_output(['error' => "$arg is invalid server id"]);
        }

        exec($cmd . " 2>&1", $output, $ret);
        if ($ret !== 0) {
            json_output(['error' => 'Speedtest execution failed']);
        }
        $result = json_decode(implode("\n", $output), true);
        if (!$result) {
            json_output(['error' => 'Invalid JSON output from speedtest']);
        }

        $out = [
            'timestamp' => $result['timestamp'],
            'clientip' => $result['interface']['externalIp'],
            'serverid' => $result['server']['id'],
            'servername' => $result['server']['name'] . ", " . $result['server']['location'],
            'country' => $result['server']['country'],
            'latency' => round($result['ping']['latency'], 2),
            'download' => round($result['download']['bandwidth'] / 125000, 2),
            'upload' => round($result['upload']['bandwidth'] / 125000, 2),
            'link' => $result['result']['url']
        ];

        $csvtime = strtotime($result['timestamp']);
        $line = [
            $csvtime, $out['clientip'], $out['serverid'], $out['servername'],
            $out['country'], $out['download'], $out['upload'], $out['latency'], $out['link']
        ];
        file_put_contents($csvfile, implode(",", $line) . PHP_EOL, FILE_APPEND);
        json_output($out);
}
?>
