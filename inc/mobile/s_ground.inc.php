<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$sign_type = !empty($_GPC['sign_type']) ? $_GPC['sign_type'] : 'display';
$infolist = $this->authentication();
$openid =$_W['openid'];
$openid = 'oNajf04IZ_IxBAODt-VB4O0YWOXc';
if ($operation == 'display') {
    $student_info = pdo_fetch("SELECT * FROM " . tablename('pte_student') . " where openid='".$openid."'");
    $ground = pdo_fetchall("SELECT * FROM " . tablename('pte_ground') . " where status=1");
    foreach($ground as $k => $item){
        $examination_list = pdo_fetchall("SELECT * FROM " . tablename('pte_ground_detail') . " where  ground_id='" . $item['id']."'");
        $ground[$k]['detail'] =  $examination_list;
    }
//   pdo_debug();
    include $this->template('wx/tesr');
}
if ($operation == 'upload') {
    $student_data = pdo_fetch("SELECT * FROM " . tablename('pte_userinfo') . " where openid='" . $openid . "'");
    $name = $_GPC['name'];
    $s_id = $_GPC['s_id'];
    $senior_school = $_GPC['senior_school'];
    $email = $_GPC['email'];
    $purpose_school = $_GPC['purpose_school'];
    $purpose_major = $_GPC['purpose_major'];

    $name = '测试一号';
    $s_id = '500102199406058582';
    $senior_school = '重庆一中';
    $email = '12786868@qq.com';
    $purpose_school = "哈弗大学";
    $purpose_major = "视觉传达"; 

    $inset_data = array(
        'name' => $name,
        's_id' => $s_id,
        'senior_school' => $senior_school,
        'email' => $email,
        'purpose_school' => $purpose_school,
        'purpose_major' => $purpose_major,
        'createtime' => time(),
    );

    if( $student_data){
        $res = pdo_insert('pte_userinfo', $inset_data);
    }else{
        $res = pdo_update('pte_userinfo', $inset_data);
    }
  
    if ($res) {
        $this->my_message(200, '', '保存成功');
    } else {
        $this->my_message(201, '', '保存失败');
    }
    // var_dump( $file);die();

}
