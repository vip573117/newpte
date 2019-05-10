<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation== 'selectopenid') {
    $user = $_GPC['user'];
    $id = $user['id'];
    $resut = pdo_fetch("SELECT * FROM ".tablename('pte_teacher')." WHERE id = :id", array(':id' => $id));
    if($resut['openid']){
        $this->my_message(1, '', '绑定成功');
    }else{
        $this->my_message(0, '', '绑定失败');
    }
}if($operation== 'deleteopenid'){
    $id = $_GPC['user']['id'];
    $update_data=array(
        'openid' => ''
    );
    $result = pdo_update('pte_teacher', $update_data, array('id' =>$id));
    if($result){
        $this->my_message(1, $resultdata, '提交成功');
    }else{
        $this->my_message(0, $resultdata, '提交失败');
    }
}
