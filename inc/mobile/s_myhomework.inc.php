<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
//var_dump($this->user);die();
if ($operation == 'display') {
        $id = $this->user['id'];
        $keyword = $_GPC['keyword'];
        //var_dump( $keyword );die();
        //获取页数
        $pindex = max(1, intval($_GPC['page']));
        //获取页行数
        $psize = 5;
        //使用拼接sql语句
        $sql = "SELECT task.*,te.name as teachername,su.title,stt.status as sttstatus,stt.tstatus as stttstatus,stt.id as sttid FROM " . tablename('pte_task') . " task LEFT JOIN " . tablename('pte_teacher') . " 
        te ON task.teacher = te.id  left join " . tablename('pte_subject') . " su ON task.subjectid = su.id LEFT JOIN " . tablename('pte_studenttask') . " 
        stt on stt.taskid = task.id WHERE task.status !=0 and stt.studentid = ".$id;
        if(!empty($keyword)){
        if($keyword == '完成中'){
        $keyword = 1;
        $sql .= " and stt.status=".$keyword;
        }
        if($keyword == '未开始填写'){
        $keyword = 0;
        $sql .= " and stt.status=".$keyword;
        }
        if($keyword == '已提交作业'){
        $keyword = 2;
        $sql .= " and stt.status=".$keyword;
        }else{
        $sql .= " and (su.title like '%".$keyword."%' or te.name like '%".$keyword."%' or task.deadline like '%".$keyword."%' or stt.status like '%".$keyword."%')";
        }
        }
        $countsql = $sql; 
        $sql .= " ORDER BY task.id DESC limit " . ($pindex - 1) * $psize . ',' . $psize;
        $tasklist = pdo_fetchall($sql);
        $totaldata = pdo_fetchall($countsql);
        $total = count($totaldata);
        $pager = pagination($total, $pindex, $psize);
        $schoollist = pdo_fetchall("SELECT id,title FROM " . tablename('pte_school'));
        $class = pdo_fetch("SELECT id,title FROM " . tablename('pte_class') . " WHERE status= 1 and id=".$this->user['classid']);
        include $this->template('student/myhomework');
}if ($operation == 'status') {
    if($_W['isajax'] && $_W['ispost']){ 
        $id = $_GPC['id'];
        if(empty($id)){
            $this->my_message(202,'','id不存在');
        }
        $updata_data = array(
            'status' => 0
        );
        $result = pdo_update('pte_teacher', $updata_data, array('id' => $id));
        if (!empty($result)) {
            $this->my_message(200,'','删除成功');
        }else {
            $this->my_message(202,'','删除失败');
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
}