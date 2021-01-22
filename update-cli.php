<?php
if(php_sapi_name() != 'cli'){
    echo 'This file can only be accessed via cli';
    exit;
}
require_once (__DIR__.'/init.php');
println('Getting First HTML...');
$resp=backoff(function () use ($guzzle){
    return $guzzle->get('https://t.me/s/MikuArt');
});
$firstHtml=$resp->getBody();
preg_match_all('/(\d+)\?single/',$firstHtml,$m);
$totalId=$m[1][array_key_last($m[1])];
for($i=$totalId;$i>0;$i-=24){
    println('Getting Second HTML with tg_before = '.$i);
    $resp=backoff(function () use ($guzzle,$i){
        return $guzzle->post('https://t.me/s/MikuArt?before='.$i);
    });
    $secondHtml=$resp->getBody();
    preg_match_all('/\'https:\/\/cdn(.*?)\.jpg\'(.*?)MikuArt\/(\d+)\?/',$secondHtml,$m);
    $promiseList=[];
    $imageMap=[];
    foreach ($m[1] as $key=>$brokenUrl){
        $url='https://cdn'.$brokenUrl.'.jpg';
        $tgId=$m[3][$key];
        $promiseList[]=$guzzle->getAsync($url)->then(function($resp) use ($guzzle,&$imageMap,$tgId){
            $guzzle->postAsync('https://mp.toutiao.com/upload_photo/?type=json',[
                'headers'=>[
                    'Accept'=>'application/json',
                    'X-Requested-With'=>'XMLHttpRequest',
                    'User-Agent'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:84.0) Gecko/20100101 Firefox/84.0'
                ],
                'multipart' => [
                    [
                        'name'     => 'photo',
                        'contents' => $resp->getBody(),
                        'filename' => 'image.jpg'
                    ]
                ]
            ])->then(function($resp2) use (&$imageMap,$tgId){
                $webUrl=json_decode($resp2->getBody())->web_url;
                if(!empty($webUrl)){
                    $imageMap[$tgId]=$webUrl;
                    println('Uploaded '.$imageMap[$tgId]);
                }else{
                    println('Upload failed');
                }
            });
        });
    }
    $pool = new \GuzzleHttp\Promise\EachPromise($promiseList,[
        'concurrency'=>25,
        'rejected'=>function($reason){
            println('Upload failed: '.$reason);
        }
    ]);
    $pool->promise()->wait();
    $isAdded=false;
    foreach ($imageMap as $tgId=>$url){
        $rowAffected=$db->safeQuery('insert or ignore into images (tg_id,url) values(?,?)',
            [$tgId,$url],
            \ParagonIE\EasyDB\EasyDB::DEFAULT_FETCH_STYLE,
            true,
            true);
        if($rowAffected>0){
            $isAdded=true;
        }
    }
    if(!$isAdded){
        println('Reached last process point, exit.');
        break;
    }
}