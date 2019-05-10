 <?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$sign_type = !empty($_GPC['sign_type']) ? $_GPC['sign_type'] : 'display';
// $infolist = $this->authentication();
if ($operation == 'display') {
    $status = 1;
    $uid = $_W['member']['uid'];
    //获取页数
    $pindex = max(1, intval($_GPC['page']));
    //获取页行数
    $psize = 5;
    //使用拼接sql语句
    $sql = "SELECT * FROM " . tablename('pte_course') . "where status=" . $status;
    $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $courselist = pdo_fetchall($sql);
    $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('pte_course')) . "where status=" . $status;
    //（前台用pager标签可以直接显示）
    $pager = pagination($total, $pindex, $psize);
    $subjectlist = pdo_fetchall("SELECT id,title FROM " . tablename('pte_subject') . " WHERE status =1");
    $week = array('一', '二', '三', '四', '五', '六', '日');
    $start_time = array('8:30', '9:20', '10:20', '11:10', '14:00', '14:50', '15:50', '16:40', '19:00', '20:30');
    $end_time = array('9:10', '10:00', '11:00', '11:50', '14:40', '15:30', '16:30', ' :20', '20:20', '21:45');
    include $this->template('admin/course');
}
if ($operation == 'status') {

    $id = $_GPC['id'];
    if (empty($id)) {
        $this->my_message(202, '', 'id不存在');
    }
    $updata_data = array(
        'status' => 0,
    );
    $result = pdo_update('pte_course', $updata_data, array('id' => $id));
    if (!empty($result)) {
        $this->my_message(200, '', '删除成功');
    } else {
        $this->my_message(202, '', '删除失败');
    }
}if ($operation == 'add') {

    if ($_W['isajax'] && $_W['ispost']) {
        $weeklist = array('一'=>1, '二'=>2, '三'=>3, '四'=>4, '五'=>5, '六'=>6, '日'=>7);
        $id = $_GPC['id'];
        $subject_id = $_GPC['subject_id'];
        $subject_name = $_GPC['subject_name'];
        $week = $_GPC['week'];
        $start_time = $_GPC['start_time'];
        $end_time = $_GPC['end_time'];
        if (empty($subject_id)) {
            $this->my_message(202, '', '科目不能为空');
        }
        if (empty($week)) {
            $this->my_message(202, '', 'week不能为空');
        }
        if (empty($start_time)) {
            $this->my_message(202, '', '请选择开始时间');
        }
        if (empty($end_time)) {
            $this->my_message(202, '', '请选择结束时间');
        }
        $start_xqtime = explode(":",$start_time);
        $start_xq =  ($start_xqtime[0]*60)+$start_xqtime[1];
        $end_xqtime = explode(":",$end_time);
        $end_xq =  ($end_xqtime[0]*60)+$end_xqtime[1];
        $course_num = intval(($end_xq-$start_xq)/40);
        if (empty($id)) {
            $inset_data = array(
                'subject_id' => $subject_id,
                'subject_name' => $subject_name,
                'week_num' => $weeklist[$week],
                'week' => $week,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'course_num' => $course_num,
                'createtime' => time(),
            );
            pdo_insert('pte_course', $inset_data);
            $uid = pdo_insertid();
   
            if ($uid) {
                $this->my_message(200, '', '添加成功');
            } else {
                $this->my_message(201, '', '添加失败');
            }

        } else {
            $update_data = array(
                'subject_id' => $subject_id,
                'subject_name' => $subject_name,
                'week' => $week,
                'week_num' => $weeklist[$week],
                'start_time' => $start_time,
                'end_time' => $end_time,
                'course_num' => $course_num,
                'createtime' => time(),
            );
            $result =  pdo_update('pte_course', $update_data,['id'=>$id]);
            if ($result) {
                $this->my_message(200, '', '修改成功');
            } else {
                $this->my_message(201, '', '修改失败');
            }
        }

    }
}
