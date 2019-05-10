<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
//var_dump($this->user);die();
if ($operation == 'display') {
    $uid = $_W['member']['uid'];
    $task =  pdo_fetchall("SELECT task.*,te.name,su.title FROM " . tablename('pte_task') . " task LEFT JOIN " . tablename('pte_teacher') . " 
     te ON task.teacher = te.id  left join " . tablename('pte_subject') . " su ON task.subjectid = su.id 
    WHERE task.deadline >".time() );
    $class = pdo_fetch("SELECT id,title FROM " . tablename('pte_class') . " WHERE status= 1 and id=".$this->user['classid']);
    //var_dump($task);die()
    include $this->template('student/suggest');
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