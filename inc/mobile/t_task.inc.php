<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
        $uid = $_W['member']['uid'];
        $keyword = $_GPC['keyword'];
        //获取页数
        $pindex = max(1, intval($_GPC['page']));
        //获取页行数
        $psize = 5;
        //使用拼接sql语句
        $sql = "SELECT t.*,sb.title FROM " . tablename('pte_task')." t LEFT JOIN " . tablename('pte_subject')." sb ON sb.id = t.subjectid WHERE  teacher = ".$this->user['id'] ;
        if(!empty($keyword)){
        $sql .= " and sb.title like '%".$keyword."%'";
        }
        $countsql =  $sql;
        $sql .= " ORDER BY t.id DESC limit " . ($pindex - 1) * $psize . ',' . $psize;
        $tasklist = pdo_fetchall($sql);
        $totaldata = pdo_fetchall($countsql);
        $total = count($totaldata);
        //（前台用pager标签可以直接显示）
        $pager = pagination($total, $pindex, $psize);
        $schoollist = pdo_fetchall("SELECT id,title FROM " . tablename('pte_school'));
    include $this->template('teacher/homework');
}if ($operation == 'status') {
    $teacher = $this->user;
    if($_W['isajax'] && $_W['ispost']){
        $id = $_GPC['id'];
        $type = $_GPC['type'];
        if(empty($id)){
            $this->my_message(202,'','id不存在');
        }
        $updata_data  = array();
        if($type==1){
            $updata_data = array(
                'status' => 1,
            );
            $result = pdo_update('pte_task', $updata_data, array('id' => $id));
            if($result){
                $students = json_decode(pdo_fetch("SELECT students FROM " . tablename('pte_task') . " where id=". $id)['students']);
                //$task = pdo_fetchall("SELECT id,problem FROM " . tablename('pte_taskdetail'). " where taskid=". $id);
                foreach($students as $item){
                    $inset_data = array(
                        'studentid' => $item,
                        'taskid' => $id,
                        'status' => 0,
                        'tstatus' => 0,
                    );
                    pdo_insert('pte_studenttask', $inset_data);
                    $openid = pdo_fetch("SELECT openid FROM " . tablename('pte_student') . " where status=1 and id=" . $item )['openid'];
                    $task = pdo_fetch("SELECT t.*,su.title FROM " . tablename('pte_task') . " t left join  " . tablename('pte_subject') . " su on t.subjectid=su.id where t.id=" . $id);
                    $teacher_name = pdo_fetch("SELECT name FROM " . tablename('pte_teacher') . " where id=" . $task['teacher'] )['name'];
                    if($openid){
                        $data = array(
                            'first' => array(
                                'value' => $teacher_name."布置了". $task['title']."作业",
                                'color' => '#ff510'
                            ),
                            'keyword1' => array(
                                'value' => date('Y-m-d H:i', $task['deadline']),
                                'color' => '#ff510'
                            ),
                            'keyword2' => array(
                                'value' => date('Y-m-d', $task['createtime']).$task['title']."作业",
                                'color' => '#ff510'
                            ),
                            'keyword3' => array(
                                'value' => "请按时完成，准时提交",
                                'color' => '#ff510'
                            ),
                            'remark' => array(
                                'value' => "请登录PC端查看" ,
                                'color' => '#ff510'
                            ),
                        );
                        $account = WeAccount::create();
                        $status = $account->sendTplNotice($openid, 'kVNyGYw3dMgdVoPbgqYDMVwjb48tez0bT5CqKPRtFqw', $data, '');
                        $insert_data = array(
                            'openid'=>$openid, 
                            'pushmsg'=> json_encode($data), 
                            'pushresut'=>json_encode($status), 
                            'createtime'=>time(),
                            'type'=>'发布'   
                        );
                        pdo_insert('pte_pushlog', $insert_data);
                    }
                }
            }
        }

        if($type==2){
            $updata_data = array(
                'status' => 2
            );
            $result = pdo_update('pte_task', $updata_data, array('id' => $id));
        }
        if (!empty($result)) {
            if($type==1){

                $this->my_message(200,'','发布成功');
            }
            if($type==2){
                $this->my_message(200,'','截止成功');
            }
        }else {
            $this->my_message(202,'','操作失败，出现错误');
        }
    }
    // include $this->template('admin/campus');
}if ($operation == 'add') {
    if($_W['isajax'] && $_W['ispost']){
        $id = $_GPC['id'];
        $schoolid = $_GPC['schoolid'];
        $name = $_GPC['name'];
        $tel = $_GPC['tel'];
        $email = $_GPC['email'];
        $userid = $_GPC['userid'];
        if(empty($schoolid)){
            $this->my_message(202,'','请选择学校');
        }
        if(empty($name)){
            $this->my_message(202,'','标题不能为空');
        }
        if(empty($tel)){
            $this->my_message(202,'','电话不能为空');
        }
        if(empty($email)){
            $this->my_message(202,'','Email不能为空');
        }
        $indexnum = 0;
        $emaillist = pdo_fetchall("SELECT email FROM " . tablename('mc_members'));
        foreach($emaillist as $value){
            if($value['email'] == $email){
                $indexnum++;
            }
        }
        if($indexnum > 1){
            $this->my_message(202,'','该邮箱已注册');
        }
        if(empty($id)){
            $salt = random(8);
            $password = md5('12345678' . $salt . $_W['config']['setting']['authkey']);
            $inset_userdata = array(
                'email' => $email,
                'password' => $password,
                'createtime' => time(),
                'salt' => $salt,
            );
            pdo_insert('mc_members', $inset_userdata);
            $uid = pdo_insertid();
            if($uid){
                $inset_data = array(
                    'phone' => $tel,
                    'name' => $name,
                    'Email' =>$email,
                    'work_num' =>$uid.''.date('Ymd'),
                    'schoolid' => $schoolid,
                    'createtime' => time(),
                    'status' => 1,
                    'userid' => $uid,
                );
                $result = pdo_insert('pte_teacher', $inset_data);
                if($result){
                    $this->my_message(200,'','添加成功');
                }else{
                    $this->my_message(201,'','添加失败');
                }
            }else{
                $this->my_message(201,'','添加失败');
            }

        }else{
            $updata_data = array(
                'phone' => $tel,
                'name' => $name,
                'Email' =>$email,
                'schoolid' => $schoolid,
            );
            $updata_userdata = array(
                'email' =>$email,
            );
            $result1 =  pdo_update('mc_members', $updata_userdata, array('uid' => $userid));
            $result = pdo_update('pte_teacher', $updata_data, array('id' => $id));
            if($result ||  $result1){
                $this->my_message(200,'','修改成功');
            }else{
                $this->my_message(201,'','修改失败');
            }
        }

    }
}if ($operation == 'selecttask') {
    $taskid = $_GPC['id'];
    $trans   = array_flip(get_html_translation_table(HTML_ENTITIES));
    $task = pdo_fetchall("SELECT * FROM " . tablename('pte_taskdetail') . " where taskid=".$taskid);
    
    include $this->template('teacher/looktask');
}