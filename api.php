<?php
require_once (__DIR__.'/init.php');
if(!isset($_GET['method'])){
    exitWithApiJson('Param \'method\': json, get, redirect',false);
}
$method=$_GET['method'];
switch ($method){
    case 'json':
        $count=1;
        if(isset($_GET['count'])){
            $count=(intval($_GET['count'])>0)?intval($_GET['count']):1;
            $count=($count>API_MAX_COUNT)?API_MAX_COUNT:$count;
        }
        $result=$db->run('select * from images order by random() limit ?',$count);
        $obj=[];
        foreach ($result as $item){
            $obj[]=[
                'id'=>intval($item['tg_id']),
                'raw_url'=>$item['url'],
                'proxy_url'=>(isSSL()?'https://':'http://').$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']
                    .'?method=get&id='.$item['tg_id']
            ];
        }
        exitWithApiJson($obj);
        break;
    case 'get':
        if(isset($_GET['id'])){
            $id=intval($_GET['id']);
            $url=$db->cell('select url from images where tg_id=?',$id);
        }else{
            $url=$db->cell('select url from images order by random() limit 1');
        }
        if(empty($url)){
            exitWithApiJson('No result matches this ID',false);
        }
        $imgRaw=$guzzle->get($url)->getBody();
        header('Content-Type: image/jpeg');
        echo $imgRaw;
        break;
    case 'redirect':
        if(isset($_GET['no_proxy'])){
            $url=$db->cell('select url from images order by random() limit 1');
            header('Location: '.$url);
        }else {
            $tgId = $db->cell('select tg_id from images order by random() limit 1');
            header('Location: ' . (isSSL() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']
                . '?method=get&id=' . $tgId);
        }
        break;
    default:
        exitWithApiJson('Param \'method\': json, get, redirect',false);
        break;
}
