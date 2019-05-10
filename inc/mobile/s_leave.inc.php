<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$sign_type = !empty($_GPC['sign_type']) ? $_GPC['sign_type'] : 'display';
$infolist = $this->authentication();
if ($operation == 'display') {
    $openid = 'oNajf04IZ_IxBAODt-VB4O0YWOXc';
    $subjectlist =pdo_fetchall("SELECT * FROM " . tablename('pte_click') . "where status='旷课' and student_id=".$student_id . " and course_date>'".date('Y-m-d',time()-604800)."'");
    foreach($subjectlist as $k => $item){
        $subject[$k]['label']=$item['subject_name']."-".$item['course_date'];
        $subject[$k]['value']=$item['id'];
    }
    $subject_list = json_encode($subject);
    $leave_array = pdo_fetch("SELECT content FROM " . tablename('pte_signleave_type') . "where type=2")['content'];
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

    // $subject = pdo_fetchall("SELECT * FROM " . tablename('pte_subject') . "where status=1");
    // $subject_list = array();
    // foreach ($subject as $item) {
    //     $subject_list[] = $item['title'];
    // }
    // $subject_list = json_encode($subject_list);
    include $this->template('wx/leave');
}
if ($operation == 'upload') {
    $openid = $_W['openid'];
    $openid = "oNajf04IZ_IxBAODt-VB4O0YWOXc";
    $leave_type = $_GPC['leave_type'];
    if (empty($leave_type)) {
        $this->my_message(201, '', '选择请假类型');
    }
    // $subject_name = $_GPC['subject_name'];
    // if (empty($subject_name)) {
    //     $this->my_message(201, '', '选择请假课程');
    // }
    $start_time = strtotime($_GPC['start_time']);
    if (empty($start_time)) {
        $this->my_message(201, '', '选择开始时间');
    }
    $end_time = strtotime($_GPC['end_time']);
    if (empty($end_time)) {
        $this->my_message(201, '', '选择结束时间');
    }
    $leave_content = $_GPC['leave_content'];
    if (empty($leave_content)) {
        $this->my_message(201, '', '请填写描述');
    }
    // $leave_day = $_GPC['leave_day'];
    // if (empty($leave_day)) {
    //     $this->my_message(201, '', '请填写天数');
    // }
    $sp_people = $_GPC['sp_people'];
    if (empty($sp_people)) {
        $this->my_message(201, '', '选择审批人');
    }
    $cs_people = $_GPC['cs_people'];
    if (empty($cs_people)) {
        $this->my_message(201, '', '选择抄送人');
    }
    $leave_file = $_GPC['upload'];
    $student_id = pdo_fetch("SELECT id FROM " . tablename('pte_student') . "where openid='" . $openid . "'")['id'];
    // $subject_id = pdo_fetch("SELECT id FROM " . tablename('pte_subject') . "where status=1 and title='" . $subject_name . "'")['id'];
     $inset_data = array(
        'leave_type' => $leave_type,
        'start_time' => $start_time,
        'end_time' => $end_time,
        'leave_file' => $leave_file,
        'leave_content' => $leave_content,
        'student_id' => $student_id,
        'student_openid' => $openid,
        'sp_people' => $sp_people,
        'cs_people' => $cs_people,
        'createtime' => time(),
        // 'subject_id' => $subject_id,
        // 'subject_name' => $subject_name,
    );
    $res = pdo_insert('pte_leave', $inset_data);
    if ($res) {
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
    }
        $this->my_message(200, '', '上传成功');
    } else {
        $this->my_message(201, '', '上传失败');
    }
    // var_dump( $file);die();

}
