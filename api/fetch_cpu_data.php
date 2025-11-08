<?php
// Simple security token
define('SECRET_TOKEN', 'change-this-to-a-random-secret-12345');

if (!isset($_GET['token']) || $_GET['token'] !== SECRET_TOKEN) {
    http_response_code(403);
    die(json_encode(['error' => 'Forbidden']));
}

header('Content-Type: application/json');

function fetchCpuBenchmarkData() {
    $agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.4 Safari/605.1.15";
    
    $ch = curl_init("https://www.cpubenchmark.net/CPU_mega_page.html");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_USERAGENT => $agent,
        CURLOPT_TIMEOUT => 10,
    ]);
    
    $response = curl_exec($ch);
    $curlErr = curl_error($ch);
    curl_close($ch);
    
    if (!$response || !preg_match("/PHPSESSID=([^;]+)/", $response, $matches)) {
        return ['error' => 'Failed to get PHPSESSID', 'details' => $curlErr];
    }
    
    $phpsessid = $matches[1];
    $timestamp = round(microtime(true) * 1000);
    
    $ch = curl_init("https://www.cpubenchmark.net/data/?_=$timestamp");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => $agent,
        CURLOPT_HTTPHEADER => [
            "Accept: application/json, text/javascript, */*; q=0.01",
            "Accept-Encoding: text",
            "Cookie: PHPSESSID=$phpsessid",
            "Referer: https://www.cpubenchmark.net/CPU_mega_page.html",
            "X-Requested-With: XMLHttpRequest",
        ],
        CURLOPT_TIMEOUT => 30,
    ]);
    
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);
    
    if (!$data) {
        return ['error' => 'Failed to get data', 'details' => $curlErr];
    }
    
    if ($httpCode !== 200) {
        return ['error' => "HTTP $httpCode"];
    }
    
    $json = json_decode($data, true);
    if (!isset($json['data'])) {
        return ['error' => 'No data field in response'];
    }
    
    return ['success' => true, 'data' => $json['data']];
}

echo json_encode(fetchCpuBenchmarkData());
?>