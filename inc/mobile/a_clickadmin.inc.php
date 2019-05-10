<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
// var_dump(date('Y-m-d H:i:s','1556171891'));
$infolist = $this->authentication();
$weeklist = array('一' => 1, '二' => 2, '三' => 3, '四' => 4, '五' => 5, '六' => 6, '日' => 7);
$weeklist_fan = array(1 => '一', 2 => '二', 3 => '三', 4 => '四', 5 => '五', 6 => '六', 7 => '日');
if ($operation == 'display') {

    $uid = $_W['member']['uid'];
    //获取页数
    $pindex = max(1, intval($_GPC['page']));
    $page = $_GPC['page'];
    //获取页行数
    $psize = 5;
    //使用拼接sql语句
    $sql = "SELECT * FROM " . tablename('pte_subject_click') . " order by id desc";
    $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $clicklist = pdo_fetchall($sql);
    $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('pte_subject_click'));
    //（前台用pager标签可以直接显示）
    $pager = pagination($total, $pindex, $psize);
    // $classlist = pdo_fetchall("SELECT ims_pte_class.*,ims_pte_school.title as schoolname FROM " . tablename('pte_class') . " LEFT JOIN " . tablename('pte_school') . " ON ims_pte_class.schoolid = ims_pte_school.id" );
    $schoollist = pdo_fetchall("SELECT id,title FROM " . tablename('pte_school'));

    include $this->template('admin/click');
}
if ($operation == 'status') {
    if ($_W['isajax'] && $_W['ispost']) {
        $id = $_GPC['id'];
        $type = $_GPC['type'];
        $typeid = $_GPC['typeid'];
        if (empty($id)) {
            $this->my_message(202, '', 'id不存在');
        }
        $updata_data = array(
            'status' => $type == 1 ? 0 : 1,
        );
        $result = pdo_update('pte_class', $updata_data, array('id' => $id));
        if (!empty($result)) {
            $this->my_message(200, '', '更新成功');
        } else {
            $this->my_message(202, '', '更新失败');
        }
    }
    // include $this->template('admin/campus');
}
if ($operation == 'add') {
    if ($_W['isajax'] && $_W['ispost']) {
        $id = $_GPC['id'];
        $schoolid = $_GPC['schoolid'];
        $name = $_GPC['name'];
        if (empty($schoolid)) {
            $this->my_message(202, '', '请选择学校');
        }
        if (empty($name)) {
            $this->my_message(202, '', '标题不能为空');
        }
        if (empty($id)) {
            $inset_data = array(
                'title' => $name,
                'schoolid' => $schoolid,
                'createtime' => time(),
                'status' => 1,
            );
            $result = pdo_insert('pte_class', $inset_data);
            if ($result) {
                $this->my_message(200, '', '添加班级成功');
            } else {
                $this->my_message(201, '', '添加班级失败');
            }
        } else {
            $updata_data = array(
                'title' => $name,
                'schoolid' => $schoolid,
            );
            $result1 = pdo_update('pte_class', $updata_data, array('id' => $id));
            if ($result1) {
                $this->my_message(200, '', '修改班级成功');
            } else {
                $this->my_message(201, '', '修改班级失败');
            }

        }

    }
}
if ($operation == 'update') {
    $sign_config = pdo_fetchall("SELECT * FROM " . tablename('pte_signtime'));
    $leave_click = pdo_fetchall("SELECT * FROM " . tablename('pte_leaveclick') . " where status=0");

    $sign_sign = $sign_config[0]['time']; //配置的打卡时间
    $sign_late = $sign_config[1]['time']; //迟到时间
    $sign_truancy = $sign_config[2]['time']; //旷课时间
    $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=wwe0f4385c4e6bb308&corpsecret=NzFKdkllC9w7vY-ScxBY1Mj_tJ_tbnmhm-DtOmq1bFE";
    $content = file_get_contents($url);
    $access = json_decode($content);
    $access_token = $access->access_token;
    //$access_token = "ww1cvALxgbUEEesxv_py3teO3k2n7Rxr9L0_swxRU3Y5IIaVfjFZtct9cdAtrCHQ_IhGERF8uFvc1B7VkCHUDxkxkRUbuuj7zqvecooZqM_CRAj1SD_dSLVZsez1fL2b4tcr069HN3GRQBMRjJfDJutvmYdX4bwKrpcaMeBx-DTWCRSDWB1WzX_j8AY_ma6-9Q31xcD5zINgb_RCevdufQ";
    $studentlist = pdo_fetchall("SELECT student_num FROM " . tablename('pte_student') . " WHERE student_num is not null and student_num !='' and status !=0");
    $studentarray = array();
    if ($access_token) {
        foreach ($studentlist as $item) {
            $studentarray[] = $item['student_num'];

        }

        $curl = curl_init(); // 启动一个CURL会话
        $url = "https://qyapi.weixin.qq.com/cgi-bin/checkin/getcheckindata?access_token=" . $access_token;
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        // curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        // curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $starttime = pdo_fetch("SELECT createtime FROM " . tablename('pte_click_time') . " Order By createtime Desc")['createtime'];
        $endtime = time() - 3600;
        if (!$starttime) {
            $starttime = time() - 2595600;
        }
        if ($starttime < $endtime - 2595600) {
            $starttime = time() - 2595600;
        }
        $obj = new stdClass();
        $obj->opencheckindatatype = 3;
        $obj->starttime = $starttime;
        //$obj->starttime = $endtime-2591000;//测试
        $obj->endtime = $endtime;
        $obj->useridlist = $studentarray;
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($obj)); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $res = curl_exec($curl); // 执行操作
        curl_close($curl); // 关闭CURL会话
        if ($res) {
            pdo_insert('pte_click_time', ['createtime' => $endtime]);
            $clicklist = json_decode($res);
            if ($clicklist) {
                pdo_delete('pte_click_tmp');
                foreach ($clicklist->checkindata as $tt) {
                    if ($tt->exception_type != '未打卡') {
                        $student_id = pdo_fetch("SELECT id FROM " . tablename('pte_student') . " where student_num = '" . $tt->userid . "'")['id'];
                        $insert_data = array(
                            'student_num' => $tt->userid,
                            'student_id' => $student_id,
                            'click_time' => $tt->checkin_time,
                            'week' => date('w', $tt->checkin_time),
                            'createtime' => time(),
                        );
                        $res = pdo_insert('pte_click_tmp', $insert_data);
                    }
                }
                for ($start = $starttime; $start <= $endtime + 3600; $start += 24 * 3600) {
                    // echo $start, "  \t  ", date("Y-m-d", $start), "";
                    $week = date('w', $start);
                    $courselist = pdo_fetchall("SELECT * FROM " . tablename('pte_course') . " where week_num = " . $week);
                    foreach ($courselist as $vo) {
                        $studentlist = pdo_fetchall("SELECT * FROM " . tablename('pte_student') . " where status=1 and subjectlist like " . "'%" . $vo['subject_id'] . "%'");
                        $c_id = pdo_fetchall("SELECT id FROM " . tablename('pte_subject_click') . " where week=" . $vo['week'] . "and subject_id =" . $vo['subject_id'] . "and date =" . date('Y-m-d', $start))['id'];
                        if ($c_id) {
                            $coure_updatedata = array(
                                'createtime' => time(),
                            );
                            pdo_update('pte_subject_click', $coure_updatedata, ['id' => $c_id]);
                        } else {
                            $coure_insertdata = array(
                                'subject_id' => $vo['subject_id'],
                                'subject_name' => $vo['subject_name'],
                                'week' => $vo['week'],
                                'week_num' => $weeklist[$vo['week']],
                                'datetime' => $start,
                                'date' => date('Y-m-d', $start),
                                'time' => $vo['start_time'],
                                'tot_num' => count($studentlist),
                                'createtime' => time(),
                            );
                            pdo_insert('pte_subject_click', $coure_insertdata);
                            $c_id = pdo_insertid();
                        }
                        foreach ($studentlist as $student) {
                            $student_num = $student['student_num'];
                            $subject_start_time = strtotime(date('Y-m-d', $start) . ' ' . $vo['start_time']);
                            $subject_end_time = strtotime(date('Y-m-d', $start) . ' ' . $vo['end_time']);
                            $click = pdo_fetch("SELECT * FROM " . tablename('pte_click_tmp') . " where week=" . $week . "  and student_num ='" . $student['student_num'] . "' and click_time between " . ($subject_start_time - 3600) . " and " . ($subject_end_time));
                            if ($click) {
                                $type = false;
                                $c_timearray = explode(":", date('G:s', $click['click_time']));
                                $coure_timearray = explode(":", $vo['start_time']);
                                $coure_endarray = explode(":", $vo['end_time']);
                                $c_time = ($c_timearray[0] * 3600) + ($c_timearray[1] * 60);
                                $coure_time = ($coure_timearray[0] * 3600) + ($coure_timearray[1] * 60);
                                $end_time = ($coure_endarray[0] * 3600) + ($coure_endarray[1] * 60);
                                //正常打卡
                                if ($c_time > $coure_time - ($sign_sign * 60) && $c_time < $coure_time) {
                                    $type = true;
                                    $in_data = array(
                                        'student_num' => $click['student_num'],
                                        'student_id' => $student['id'],
                                        'student_name' => $student['name'],
                                        'click_time' => $click['click_time'],
                                        'course_date' => date('Y-m-d', $start),
                                        'subject_id' => $vo['subject_id'],
                                        'subject_name' => $vo['subject_name'],
                                        'subject_click_id' => $c_id,
                                        'week' => $week,
                                        'status' => '签到',
                                        'time' => date('Y-m-d H:s', $click['click_time']),
                                        'createtime' => time(),
                                    );
                                    pdo_insert('pte_click', $in_data);
                                    $click_num = pdo_fetch("SELECT click_num FROM " . tablename('pte_subject_click') . " where id=" . $c_id)['click_num'];
                                    pdo_update('pte_subject_click', ['click_num' => $click_num + 1], ['id' => $c_id]);
                                }
                                //迟到打卡
                                if ($c_time > $coure_time && $c_time < $coure_time + ($sign_late * 60)) {
                                    $type = true;
                                    $in_data = array(
                                        'student_num' => $click['student_num'],
                                        'student_id' => $student['id'],
                                        'student_name' => $student['name'],
                                        'click_time' => $click['click_time'],
                                        'course_date' => date('Y-m-d', $start),
                                        'subject_id' => $vo['subject_id'],
                                        'subject_name' => $vo['subject_name'],
                                        'subject_click_id' => $c_id,
                                        'week' => $week,
                                        'status' => '迟到',
                                        'time' => date('Y-m-d H:s', $click['click_time']),
                                        'createtime' => time(),
                                    );
                                    pdo_insert('pte_click', $in_data);
                                    $late_num = pdo_fetch("SELECT late_num FROM " . tablename('pte_subject_click') . " where id=" . $c_id)['late_num'];
                                    pdo_update('pte_subject_click', ['late_num' => $late_num + 1], ['id' => $c_id]);
                                }
                                //旷课打卡
                                if ($c_time > $coure_time + ($sign_truancy * 60) && $c_time < $end_time) {
                                    $type = true;
                                    $in_data = array(
                                        'student_num' => $click['student_num'],
                                        'student_id' => $student['id'],
                                        'student_name' => $student['name'],
                                        'click_time' => $click['click_time'],
                                        'course_date' => date('Y-m-d', $start),
                                        'subject_id' => $vo['subject_id'],
                                        'subject_name' => $vo['subject_name'],
                                        'subject_click_id' => $c_id,
                                        'week' => $week,
                                        'status' => '旷课',
                                        'time' => date('Y-m-d H:s', $click['click_time']),
                                        'createtime' => time(),
                                    );
                                    pdo_insert('pte_click', $in_data);
                                    $truancy_num = pdo_fetch("SELECT truancy_num FROM " . tablename('pte_subject_click') . " where id=" . $c_id)['truancy_num'];
                                    pdo_update('pte_subject_click', ['truancy_num' => $truancy_num + 1], ['id' => $c_id]);
                                }
                                if (!$type) {
                                    $status_c = 0;
                                    if (in_array($student['student_num'], $leave_click)) {
                                        foreach ($leave_click as $lea) {
                                            if ($status_c == 0) {
                                                if ($lea['student_num'] == $student['student_num']) {
                                                    if ($start > $lea['start_time'] && $start < $lea['end_time']) {
                                                        $status_c = 1;
                                                        pdo_update('pte_leaveclick', ['status' => 1], ['id' => $lea['id']]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    //未打卡
                                    $in_data = array(
                                        'student_num' => $student['student_num'],
                                        'student_id' => $student['id'],
                                        'student_name' => $student['name'],
                                        'click_time' => $click['click_time'],
                                        'course_date' => date('Y-m-d', $start),
                                        'subject_id' => $vo['subject_id'],
                                        'subject_name' => $vo['subject_name'],
                                        'subject_click_id' => $c_id,
                                        'week' => $week,
                                        'status' => $status_c == 1 ? '请假' : '旷课',
                                        'time' => date('Y-m-d H:s', $click['click_time']),
                                        'createtime' => time(),
                                    );

                                    pdo_insert('pte_click', $in_data);
                                    $truancy_num = pdo_fetch("SELECT truancy_num FROM " . tablename('pte_subject_click') . " where id=" . $c_id)['truancy_num'];
                                    pdo_update('pte_subject_click', ['truancy_num' => $truancy_num + 1], ['id' => $c_id]);
                                }

                            } else {
                                $status_c = 0;
                                if (in_array($student['student_num'], $leave_click)) {
                                    foreach ($leave_click as $lea) {
                                        if ($status_c == 0) {
                                            if ($lea['student_num'] == $student['student_num']) {
                                                if ($start > $lea['start_time'] && $start < $lea['end_time']) {
                                                    $status_c = 1;
                                                    pdo_update('pte_leaveclick', ['status' => 1], ['id' => $lea['id']]);
                                                }
                                            }
                                        }
                                    }
                                }
                                //未打卡
                                $in_data = array(
                                    'student_num' => $student['student_num'],
                                    'student_id' => $student['id'],
                                    'student_name' => $student['name'],
                                    'click_time' => '',
                                    'course_date' => date('Y-m-d', $start),
                                    'subject_id' => $vo['subject_id'],
                                    'subject_name' => $vo['subject_name'],
                                    'subject_click_id' => $c_id,
                                    'week' => $week,
                                    'status' => $status_c == 1 ? '请假' : '旷课',
                                    'time' => "",
                                    'createtime' => time(),
                                );
                                pdo_insert('pte_click', $in_data);
                                $truancy_num = pdo_fetch("SELECT truancy_num FROM " . tablename('pte_subject_click') . " where id=" . $c_id)['truancy_num'];
                                pdo_update('pte_subject_click', ['truancy_num' => $truancy_num + 1], ['id' => $c_id]);
                            }

                        }
                    }
                }
                if ($res) {
                    $this->my_message(200, '', '更新成功');
                }
            }
        } else {
            $this->my_message(201, '', '更新失败');
        }
    } else {
        $this->my_message(201, '', '更新失败');
    }

}
if ($operation == 'look_detail') {
    $subject_click_id = $_GPC['id'];
    $old_page = $_GPC['old_page'];
    $pindex = max(1, intval($_GPC['page']));
    //获取页行数
    $psize = 5;
    //使用拼接sql语句
    $sql = "SELECT * FROM " . tablename('pte_click') . " where subject_click_id=" . $subject_click_id;
    $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $click_list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('pte_click') . " where subject_click_id=" . $subject_click_id);
    //（前台用pager标签可以直接显示）
    $pager = pagination($total, $pindex, $psize);
    include $this->template('admin/click_detail');
}
