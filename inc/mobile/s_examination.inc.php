<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$sign_type = !empty($_GPC['sign_type']) ? $_GPC['sign_type'] : 'display';
$infolist = $this->authentication();
if ($operation == 'display') {
    //$openid = $_W['openid'];
    $openid = 'oNajf04IZ_IxBAODt-VB4O0YWOXc';
    $student_num = pdo_fetch("SELECT student_num FROM " . tablename('pte_student') . " where openid='" . $openid . "'")['student_num'];
    $examination_list = pdo_fetch("SELECT * FROM " . tablename('pte_examination_detail') . " where status=1 and student_num='" . $student_num . "'");

    include $this->template('wx/final');
}
if ($operation == 'upload') {
    
    $openid = 'oNajf04IZ_IxBAODt-VB4O0YWOXc';
    $student_data = pdo_fetch("SELECT * FROM " . tablename('pte_userinfo') . " where openid='" . $openid . "'");
    $name = $_GPC['name'];
    $s_id = $_GPC['s_id'];
    $senior_school = $_GPC['senior_school'];
    $email = $_GPC['email'];
    $purpose_school = $_GPC['purpose_school'];
    $purpose_major = $_GPC['purpose_major'];

    // $name = '测试一号';
    // $s_id = '500102199406058582';
    // $senior_school = '重庆一中';
    // $email = '12786868@qq.com';
    // $purpose_school = "哈弗大学";
    // $purpose_major = "视觉传达"; 


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
