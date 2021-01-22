<?php
require_once (__DIR__.'/init.php');
if(!isset($_GET['method'])){
    exitWithApiJson('Param \'method\': json, get',false);
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
                'url'=>$item['url']
            ];
        }
        exitWithApiJson($obj);
        break;
    case 'get':
        if(isset($_GET['id'])){
            $id=intval($_GET['id']);
            $url=$db->cell('select url from images where tg_id=? and url is not null',$id);
        }else{
            $url=$db->cell('select url from images where url is not null order by random() limit 1');
        }
        if(empty($url)){
            exitWithApiJson('No result matches this ID',false);
        }
        header('Location: '.$url);
        break;
    default:
        exitWithApiJson('Param \'method\': json, get',false);
        break;
}
