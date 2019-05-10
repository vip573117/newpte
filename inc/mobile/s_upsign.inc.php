<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$sign_type = !empty($_GPC['sign_type']) ? $_GPC['sign_type'] : 'display';
$infolist = $this->authentication();
if ($operation == 'display') {
    $openid = 'oNajf04IZ_IxBAODt-VB4O0YWOXc';
    $student_data = pdo_fetch("SELECT id,subjectlist FROM " . tablename('pte_student') . " where openid='" . $openid . "'");
    $student_id = $student_data['id'];

    $sign_array = pdo_fetch("SELECT content FROM " . tablename('pte_signleave_type') . "where type=1")['content'];
    $sp_peopledata = pdo_fetchall("SELECT * FROM " . tablename('pte_examine') . "where  status = 1 and  type=1");
    $cs_peoplelist = pdo_fetchall("SELECT * FROM " . tablename('pte_examine') . "where status = 1 and type=2");
    foreach($cs_peoplelist as $k => $item1){
        $cs_people[$k]['label']=$item1['name'];
        $cs_people[$k]['value']=$item1['teacher_id'];
    }
    foreach($sp_peopledata as $k => $item2){
        $sp_people[$k]['label']=$item2['name'];
        $sp_people[$k]['value']=$item2['teacher_id'];
    }
    $cs_people = json_encode($cs_people);
    $sp_people = json_encode($sp_people);
    $subjectlist =pdo_fetchall("SELECT * FROM " . tablename('pte_click') . "where status='旷课' and student_id=".$student_id . " and course_date>'".date('Y-m-d',time()-604800)."'");
    foreach($subjectlist as $k => $item){
        $subject[$k]['label']=$item['subject_name']."-".$item['course_date'];
        $subject[$k]['value']=$item['id'];
    }
    $subject_list = json_encode($subject);
	include $this->template('wx/reissue');
} 
if ($operation == 'upload') {
    $openid = $_W['openid'];
    $openid = "oNajf04IZ_IxBAODt-VB4O0YWOXc";
    $sign_type = $_GPC['sign_type'];
    if(empty($sign_type)){
        $this->my_message(201,'','选择补卡类型');
    }
    $subject_name = $_GPC['subject_name'];
    if(empty($subject_name)){
        $this->my_message(201,'','选择课程');
    }
    $sign_time = strtotime($_GPC['sign_time']) ;
    if(empty($sign_time)){
        $this->my_message(201,'','选择补卡时间');
    }
    $sign_content = $_GPC['textarea'];
    if(empty($sign_content)){
        $this->my_message(201,'','填写补卡原因');
    }
    $sign_file = $_GPC['upload'];
    $sp_people = $_GPC['sp_people'];
    $cs_people = $_GPC['cs_people'];
    $click_id = $_GPC['click_id'];
    $student_id =pdo_fetch("SELECT id FROM " . tablename('pte_student') . "where openid='". $openid."'")['id'];
    $click_data =pdo_fetch("SELECT * FROM " . tablename('pte_click') . "where id=". $click_id);
    $inset_data = array(
        'sign_type' => $sign_type,
        'sign_time' => $sign_time,
        'sign_file' => $sign_file,
        'sign_content' => $sign_content,
        'student_id' => $student_id,
        'student_openid' => $openid,
        'sp_people' => $sp_people,
        'cs_people' => $cs_people, 
        'createtime' => time(),
        'subject_id' => $click_data['subject_id'],
        'subject_name' =>$click_data['subject_name'],
        'click_id' =>$click_id,
);
    $res =  pdo_insert('pte_upsign', $inset_data);
    if($res){
        $teacher_openid = pdo_fetch("SELECT openid FROM " . tablename('pte_teacher') . "where id='" . $sp_people . "'")['openid'];
        if( $teacher_openid){
            $data = array(
                'first' => array(
                    'value' =>  $teacher['name']."审核了补卡申请",
                    'color' => '#ff510'
                ),
                'keyword1' => array(
                    'value' => $sign_data['subject_name'],
                    'color' => '#ff510'
                ),
                'keyword2' => array(
                    'value' => "",
                    'color' => '#ff510'
                ),
                'keyword3' => array(
                    'value' => "",
                    'color' => '#ff510'
                ),
                'remark' => array(
                    'value' => "" ,
                    'color' => '#ff510'
                ),
            );
            $account = WeAccount::create(); 
            $status = $account->sendTplNotice($teacher['openid'], 'kVNyGYw3dMgdVoPbgqYDMVwjb48tez0bT5CqKPRtFqw', $data, '');
        $this->my_message(200,'','上传成功');
    }else{
        $this->my_message(201,'','上传失败');
    }
   // var_dump( $file);die();

}