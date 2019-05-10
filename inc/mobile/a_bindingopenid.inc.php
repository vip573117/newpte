<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation== 'display') {
    $openid = $_W['openid'];
    $user = pdo_fetch("SELECT * FROM ".tablename('pte_teacher')." WHERE openid = :openid", array(':openid' => $openid));
    if(empty($user)){
    $user = pdo_fetch("SELECT * FROM ".tablename('pte_student')." WHERE openid = :openid", array(':openid' => $openid)); 
    }else if(empty($user)){
    $user = pdo_fetch("SELECT * FROM ".tablename('pte_admin')." WHERE openid = :openid", array(':openid' => $openid)); 
    }
    if($user){
            $retu = "error";
            $data = "该微信已经绑定！";
    }else{
        $userinfo = mc_oauth_userinfo();
        $id = $_GPC['id'];
        if($openid){
            $update_data=array(
                'nickname' => $userinfo['nickname'],
                'openid' => $openid,
                'handurl'=> $userinfo['avatar']
            );
            $result = pdo_update('pte_admin', $update_data, array('id' =>$id));
            if($result ){
                $retu = "success";
                $data = "绑定成功！";
            }else{
                $retu = "error";
                $data = "绑定失败！";
            }
            }
    }
  
    include $this->template('admin/successpage');
}