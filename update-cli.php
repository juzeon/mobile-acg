<?php
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
    $isAdded=false;
    foreach ($m[1] as $key=>$brokenUrl){
        $url='https://cdn'.$brokenUrl.'.jpg';
        $tgId=$m[3][$key];
        $rowAffected=$db->safeQuery('insert or ignore into images (tg_id,url,is_saved) values(?,?,0)',
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