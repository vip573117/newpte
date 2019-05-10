<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
load()->func('tpl');
load()->func('file');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$sign_type = !empty($_GPC['sign_type']) ? $_GPC['sign_type'] : 'display';
$infolist = $this->authentication();
if ($operation == 'display') {
    //$openid = $_W['openid'];
    $openid = 'oNajf04IZ_IxBAODt-VB4O0YWOXc';
    $student_info = pdo_fetch("SELECT * FROM " . tablename('pte_student') . " where openid='" . $openid . "'");
    $student_data = pdo_fetch("SELECT * FROM " . tablename('pte_userinfo') . " where openid='" . $openid . "'");
    // var_dump($student_info);
    // var_dump($student_data);
    include $this->template('wx/mine');
}
if ($operation == 'upload') {
    $openid = 'oNajf04IZ_IxBAODt-VB4O0YWOXc';
    $student_id = pdo_fetch("SELECT id FROM " . tablename('pte_student') . " where openid='" . $openid . "'")['id'];
    $student_data = pdo_fetch("SELECT * FROM " . tablename('pte_userinfo') . " where openid='" . $openid . "'");
    if(!$student_data){
        //导入报名
       // pdo_fetch("SELECT * FROM " . tablename('pte_userinfo') . " where openid='" . $openid . "'");
    }
    //var_dump($_W['account']['jssdkconfig']);die();
    if ($_W['isajax']) {
        // var_dump( $_GPC);die();
        $name = $_GPC['name'];
        $s_id = $_GPC['s_id'];
        $senior_school = $_GPC['senior_school'];
        $email = $_GPC['email'];
        $purpose_school = $_GPC['purpose_school'];
        $purpose_major = $_GPC['purpose_major'];
        $sid_img_z = $_GPC['sid_img_z'];  
        $sid_img_b = $_GPC['sid_img_b'];
        $passport = $_GPC['passport'];
        $report = $_GPC['report'];
        $diploma = $_GPC['diploma'];
        $certificate = $_GPC['certificate'];

        $inset_data = array(
            'name' => $name,
            'openid' => $openid,
            'student_id' => $student_id,
            's_id' => $s_id,
            'senior_school' => $senior_school, 
            'email' => $email,
            'purpose_school' => $purpose_school,  
            'purpose_major' => $purpose_major,
            'sid_img_z' => $sid_img_z,
            'sid_img_b' => $sid_img_b,
            'passport' => $passport,
            'report' => $report,
            'diploma' => $diploma,     
            'certificate' => $certificate,
            'createtime' => time(),
        );
        if ($student_data) {
            $res = pdo_update('pte_userinfo', $inset_data, ['id' => $student_data['id']]);
        } else {
            $res = pdo_insert('pte_userinfo', $inset_data);
        }

        if ($res) {
            $this->my_message(200, '', '保存成功');
        } else {
            $this->my_message(201, '', '保存失败');
        }
    }
    include $this->template('wx/profile');
}
