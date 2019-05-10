<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
      //获取页数
      $pindex = max(1, intval($_GPC['page']));
      $page = $_GPC['page'];
      //获取页行数
      $psize = 5;
      //使用拼接sql语句
      $sql = "SELECT ims_pte_teacher.*,ims_pte_school.title as schoolname FROM " . tablename('pte_teacher') . " LEFT JOIN " . tablename('pte_school') . " ON ims_pte_teacher.schoolid = ims_pte_school.id 
      WHERE ims_pte_teacher.status = 1 ";
      $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
      $teacherlist = pdo_fetchall($sql);
      $subjectlist = pdo_fetchall("SELECT id,title FROM " . tablename('pte_subject')." WHERE status =1");
      $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('pte_teacher') . ' WHERE status = 1');
  //（前台用pager标签可以直接显示）
      $pager = pagination($total, $pindex, $psize);
    $schoollist = pdo_fetchall("SELECT id,title FROM " . tablename('pte_school'));
    include $this->template('admin/teacher');
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
        //删除用户记录
        $result = pdo_query("DELETE FROM ".tablename('mc_members')." WHERE uid IN(SELECT userid FROM ".tablename('pte_teacher')." where ims_pte_teacher.id = ".$id.")");
        if (!empty($result)) {
                $this->my_message(200,'','删除成功');
            }else{
                $this->my_message(200,'','账户删除失败'); 
            }
    
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
        $username =  $tel."@qq.com"; 
        $checkbox = $_GPC['checkbox'];
        if(empty($schoolid)){
            $this->my_message(202,'','请选择学校');
        }
        if(empty($name)){
            $this->my_message(202,'','姓名不能为空');
        }
        if(empty($tel)){
            $this->my_message(202,'','电话不能为空');
        }
   
        $indexnum = 0;
        $emaillist = pdo_fetchall("SELECT email FROM " . tablename('mc_members')." where email= :email",array(':email'=>$username));
        if(empty($id)){
            if($emaillist){
                $this->my_message(202,'','该电话已注册');
            }
            $salt = random(8);
            $password = md5('12345678' . $salt . $_W['config']['setting']['authkey']);
            $inset_userdata = array(
                'email' => $username,
                'password' => $password,
                'createtime' => time(),
                'salt' => $salt,
                'uniacid'=>$_W['uniacid']
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
                        'subjectlist' => json_encode($checkbox),
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
                'subjectlist' => json_encode($checkbox),
            );
            $updata_userdata = array(
                'email' =>$username,
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