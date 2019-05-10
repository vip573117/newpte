<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$weeklist_fan = array(1 => '一', 2 => '二', 3 => '三', 4 => '四', 5 => '五',  6 => '六',7 => '日', );
//var_dump($this->user);die();
if ($operation == 'display') {
    $start_xueqi = "2019-03-01";
    $uid = $_W['member']['uid'];
    $openid = $_W['openid'];
    $openid = "oNajf04IZ_IxBAODt-VB4O0YWOXc";
    $student = pdo_fetch("SELECT * FROM " . tablename('pte_student') . " WHERE openid='" . $openid . "'");
    $sksj = pdo_fetch("SELECT * FROM " . tablename('pte_timemanage') . " WHERE status=1");
    $sksjstart = json_decode($sksj['startlist_time']);
    $sksjend = json_decode( $sksj['endlist_time']);
    $kscjlist = array();
    foreach($sksjstart as $k =>$item){
        $kscjlist[$k]['start'] = $item;
        $kscjlist[$k]['end'] =  $sksjend[$k];
    }
    
    $course = pdo_fetchall("SELECT * FROM " . tablename('pte_course') . " WHERE id in " . str_replace('[', '(', (str_replace(']', ')', 
    $student['subjectlist']))));
    var_dump($course);die();
    //查询当前时间 开课前应该上多少课
    $end_time = time();
    $hs = date('H:i:s');
    $start_time = strtotime( $start_xueqi. ' ' . $hs);
    $click_tot_num = 0;
    $click_num = 0;
    $late_num = 0;
    $truancy_num = 0;
    $leave_num =  pdo_fetchcolumn("SELECT count(1) FROM " . tablename('pte_leave') . " WHERE student_openid='" . $openid . "' and end_time>".$start_time);
    $sign_num =  pdo_fetchcolumn("SELECT count(1) FROM " . tablename('pte_upsign') . " WHERE student_openid='" . $openid . "' and sign_time>".$start_time);
    for ($start = $start_time; $start <= $end_time + 3600; $start += 24 * 3600) {
        foreach ($course as $cou) {
            if ($cou['week_num'] == date('w', $start)) {
                $click_tot_num++;
                $rourse_click = pdo_fetch("SELECT * FROM " . tablename('pte_click') . "
                     WHERE subject_id=" . $cou['subject_id'] . " and week=" . $cou['week_num'] . " and student_id=" . $student['id'] . " and course_date='" . date('Y-m-d', $start)."'");
                if ($rourse_click) {
                    if ($rourse_click['status'] == '签到' || $rourse_click['status'] == '补卡'|| $rourse_click['status'] == '请假') {
                        $click_num++; 
                    }
                    if ($rourse_click['status'] == '迟到') {
                        $late_num++;
                    }
                    if ($rourse_click['status'] == '旷课') {
                        $truancy_num++;
                    }
                } else {
                    //$truancy_num++;
                }
            }
        } 
    }
    $obj =array();
    $obj['click_tot_num'] = $click_tot_num;
    $obj['click_num'] = $click_num;
    $obj['late_num'] = $late_num;
    $obj['truancy_num'] = $truancy_num;
    $obj['leave_num'] = $leave_num;
    $obj['sign_num'] = $sign_num;
    $obj['click_percent'] = round( $click_num/$click_tot_num * 100 , 2);
    include $this->template('wx/index');
}if ($operation == 'click_list') {
    $openid = $_W['openid'];
    $openid = "oNajf04IZ_IxBAODt-VB4O0YWOXc";
   // $student = pdo_fetch("SELECT * FROM " . tablename('pte_student') . " WHERE openid='" . $openid . "'");
    $click_list = pdo_fetchall("SELECT c.*,s.handurl FROM " . tablename('pte_click') . "c left join " . tablename('pte_student') . "s on c.student_id=s.id WHERE s.openid='" . $openid."' order by id DESC");
    //查询开课-当前时间 打卡数据
    //var_dump($click_list);
    include $this->template('wx/check');
}if ($operation == 'leave_list') {
    $openid = $_W['openid'];
    $openid = "oNajf04IZ_IxBAODt-VB4O0YWOXc";
    //$student = pdo_fetch("SELECT * FROM " . tablename('pte_student') . " WHERE openid='" . $openid . "'");
    $leave_list = pdo_fetchall("SELECT l.*,s.handurl FROM " . tablename('pte_leave') . "l left join " . tablename('pte_student') . "s on l.student_id=s.id  WHERE s.openid='" . $openid."'  order by id DESC");
     //查询开课-当前时间 请假数据
    //  var_dump($leave_list);
     include $this->template('wx/check');
}if ($operation == 'upsign_list') {
    $openid = $_W['openid'];
    $openid = "oNajf04IZ_IxBAODt-VB4O0YWOXc";
    //$upsign_list = pdo_fetchall("SELECT * FROM " . tablename('pte_upsign') . " WHERE student_id=" . $student['id']);
    $upsign_list = pdo_fetchall("SELECT u.*,s.handurl FROM " . tablename('pte_upsign') . "u left join " . tablename('pte_student') . "s on u.student_id=s.id  WHERE s.openid='" . $openid."'  order by id DESC");
    //查询开课-当前时间 补卡数据
    include $this->template('wx/check');
}
