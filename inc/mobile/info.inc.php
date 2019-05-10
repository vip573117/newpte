<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    include $this->template('admin/update_user');
}if ($operation == 'updateuser') {
    if($_W['isajax'] && $_W['ispost']){
      $user = $this->user;
      $password =  $_GPC['password'];
      $newpassword =  $_GPC['newpassword'];
      $new2password =  $_GPC['new2password'];
      $u = pdo_fetch("SELECT * FROM " . tablename('mc_members')." where uid = :uid",array(':uid'=> $user['uid']));
      $password = md5($password . $u['salt'] . $_W['config']['setting']['authkey']);
      if( $newpassword == $new2password ){
            if($password==$u['password']){
                $newpassword =  md5($newpassword . $u['salt'] . $_W['config']['setting']['authkey']);
                $updata_data = array(
                    'password' => $newpassword
                );
                $result = pdo_update('mc_members', $updata_data, array('uid' =>  $user['uid']));

                $this->my_message(200,'','修改成功！');
            }else{
                $this->my_message(201,'','原密码错误！');
            }
          }else{
            $this->my_message(201,'','两场输入不对');
          }
    }
}