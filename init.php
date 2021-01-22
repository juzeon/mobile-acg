<?php
require_once(__DIR__ . '/vendor/autoload.php');
define('API_MAX_COUNT', 1000);
$guzzle = new \GuzzleHttp\Client([
    'timeout' => 15,
    'proxy' => (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? '127.0.0.1:7890' : ''
]);
$db = new \ParagonIE\EasyDB\EasyDB(new PDO('sqlite:' . __DIR__ . '/database.db'));


function println($msg) {
    echo $msg . PHP_EOL;
}

function exitWithApiJson($data, $isSuccessful = true) {
    header('Content-Type: application/json');
    echo pretty_json_encode([
        'status' => $isSuccessful,
        'data' => $data
    ]);
    exit;
}

function pretty_json_encode($raw) {
    return json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

function isSSL() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
}

function uploadRemoteImage($imgUrl){
    global $guzzle;
    $resp=backoff(function () use ($imgUrl,$guzzle){
        return $guzzle->get($imgUrl);
    });
    $rawImage=$resp->getBody();
    $imageType=$resp->getHeader('Content-Type')[0];
    $filename='';
    switch($imageType){
        case 'image/jpeg':
            $filename='file.jpg';
            break;
        case 'image/png':
            $filename='file.png';
            break;
        case 'image/gif':
            $filename='file.gif';
            break;
    }
    $resp=backoff(function () use ($rawImage,$filename,$guzzle){
        return $guzzle->post('https://image.kieng.cn/upload.html?type=jd',[
            'multipart' => [
                [
                    'name'     => 'image',
                    'contents' => $rawImage,
                    'filename' => $filename
                ]
            ]
        ]);
    });
    $link=json_decode($resp->getBody())->data->url;
    return $link;
}