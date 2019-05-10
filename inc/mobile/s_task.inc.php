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
    $student_data = pdo_fetch("SELECT id,subjectlist FROM " . tablename('pte_student') . " where openid='" . $openid . "'");
    $student_id = $student_data['id'];
    $student_subject_array = $student_data['subjectlist'];
    $student_subjects = pdo_fetchall("SELECT id,title FROM " . tablename('pte_subject') . " where status=1 and  id in (" .implode(',',json_decode($student_subject_array)) . ")");
    if ($_GPC['subject_id']) {
        $subject_id = $_GPC['subject_id'];
    } else {
        $subject_id = 0;
    }
    //$subject_id =22;
    $sql = "SELECT t.*,sub.title,teacher.name,st.status as student_status,st.score,st.tstatus from " . tablename('pte_task') . " t LEFT JOIN " . tablename('pte_subject') . " sub
    ON t.subjectid=sub.id  LEFT JOIN " . tablename('pte_teacher') . " teacher on teacher.id=t.teacher LEFT JOIN " . tablename('pte_studenttask') . "st on st.taskid=t.id and st.studentid=".$student_id ." where t.students like '%" . $student_id . "%' and t.status!=0";
    if( $subject_id!=0){
        $sql.=" and t.subjectid=" . $subject_id; 
    }
    $sql.=" ORDER BY createtime DESC";
    $task_list = pdo_fetchall($sql);

    include $this->template('wx/kczy');
}
if ($operation == 'upload') {
    $sign_type = $_GPC['sign_type'];
    $subject = $_GPC['subject'];
    $sign_time = $_GPC['sign_time'];
    $sign_content = $_GPC['sign_content'];
    $student_openid = $_GPC['student_openid'];
    $student_id = pdo_fetch("SELECT id FROM " . tablename('pte_student') . "where openid=" . $student_openid)['id'];

    $sign_type = '缺勤';
    $subject = '1';
    $sign_time = time();
    $sign_content = '早上走得太急,我忘记打卡了';
    $student_openid = 'oNajf04IZ_IxBAODt-VB4O0YWOXc';
    $student_id = pdo_fetch("SELECT id FROM " . tablename('pte_student') . "where openid=" . $student_openid)['id'];
    $subject_name = pdo_fetch("SELECT title FROM " . tablename('pte_subject') . "where id=" . $subject)['title'];
    //$httpname =  $this->module['config']['param_a'];
    // $name = $_POST['name']; //获取索引
    // $filesName = $_FILES['file']['name'];  //文件名数组
    // $size = $_FILES['file']['size'];
    // $type = $_FILES['file']['type'];
    $inset_data = array(
        'sign_type' => $sign_type,
        'sign_time' => $sign_time,
        'sign_content' => $sign_content,
        'student_id' => $student_id,
        'student_openid' => $student_openid,
        'createtime' => time(),
        'subject_id' => $subject,
        'subject_name' => $subject_name,
    );
    if ($_FILES['file']) {
        $file = file_wechat_upload($_FILES['file']);
        if ($file['success']) {
            $inset_data['sign_file'] = $httpname . '/attachment/' . $file['path'];
        } else {
            $this->my_message(201, '', '上传失败');
        }
    }

    $res = pdo_insert('pte_upsign', $inset_data);
    if ($res) {
        $this->my_message(200, '', '上传成功');
    } else {
        $this->my_message(201, '', '上传失败');
    }
    // var_dump( $file);die();

}
