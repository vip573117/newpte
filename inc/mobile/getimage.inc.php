<?php
    global $_W, $_GPC;
    $media_id = $_GPC['media_id'];
    $acc = WeAccount::create($_W['acid']);
    $token = $acc->getAccessToken();
    if(is_error($token)){
        return $token;
    }
    //$sendapi = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$token}&media_id={$media_id}";
    $photo = $acc->downloadMedia(array('type'=>"image","media_id"=>$_GPC['media_id'])); 
    $file = ['path'=>$photo];
    //$response = ihttp_get($sendapi);
    // var_dump($file );die();
    // header('Content-type: image/jpeg');
    echo  $file['path'];