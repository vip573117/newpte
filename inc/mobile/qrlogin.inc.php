<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    //var_dump($_GPC);die();
    $token = $_GPC['token'];
    $data= pdo_fetch("SELECT * FROM ".tablename('pte_logintoken')." WHERE token = :token", array(':token' => $token));
    $openid = $data['openid'];
    if($data['status']==1){
        $this->my_message(0,'', '该二维码已被使用');
    }else if(empty($openid)){
        $this->my_message(0,'', '没有openid');
    }else{
        $user = pdo_fetch("SELECT * FROM ".tablename('pte_teacher')." WHERE openid = :openid", array(':openid' => $openid));
        if(!$user){
            $user = pdo_fetch("SELECT * FROM ".tablename('pte_student')." WHERE openid = :openid", array(':openid' => $openid)); 
        }
        if(!$user){
            $user = pdo_fetch("SELECT * FROM ".tablename('pte_admin')." WHERE openid = :openid", array(':openid' => $openid)); 
        }
        if($user){
                $member = array(
                    'uid'=>$user['userid']
                );
                if (_mc_login($member)) {
                    $update_data = array(
                        'status'=>1
                    );
                    $res = pdo_update('pte_logintoken', $update_data, array('token' => $token));
                    $this->my_message(1,'', '登录成功');
                }else{
                    $this->my_message(0,'', '登录失败'); 
                }
                // 验证管理员表里是否有该用户
        }else{
            $this->my_message(0,'', '出现错误');
        }
    }
}
if($operation == 'shaomiao'){
    $token = $_GPC['token'];
    $openid = $_W['openid'];
    $update_data = array(
        'openid' =>$openid,
    );
    
    $res = pdo_update('pte_logintoken', $update_data, array('token' => $token));
    
    $user = pdo_fetch("SELECT * FROM ".tablename('pte_teacher')." WHERE openid = :openid", array(':openid' => $openid));
    
    if(!$user){
        $user = pdo_fetch("SELECT * FROM ".tablename('pte_student')." WHERE openid = :openid", array(':openid' => $openid)); 
    }
    
    if(!$user){
        $user = pdo_fetch("SELECT * FROM ".tablename('pte_admin')." WHERE openid = :openid", array(':openid' => $openid)); 
    }
    
    if($user){
        $retu = "success";
        $data = "扫描成功！";
    }else{
        $retu = "error";
        $data = "没有权限！";
    }
    include $this->template('admin/successpage');
}
