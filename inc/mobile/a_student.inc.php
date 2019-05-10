<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    $id = $_GPC['id'];
    $class = pdo_fetch("SELECT title,id FROM " . tablename('pte_class')." WHERE id =". $id);
    $school =  pdo_fetch("SELECT ims_pte_school.title,ims_pte_school.id FROM " . tablename('pte_school') . " LEFT JOIN " . tablename('pte_class') . " ON ims_pte_school.id = ims_pte_class.schoolid 
    WHERE ims_pte_class.id = ". $id );
    $subjectlist = pdo_fetchall("SELECT id,title FROM " . tablename('pte_subject')." WHERE status =1");
         //获取页数
         $pindex = max(1, intval($_GPC['page']));
         //获取页行数
         $psize = 5;
         $sql = "SELECT * FROM " . tablename('pte_student')." WHERE status = 1 and classid =". $id;
         $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
         $studentlist = pdo_fetchall($sql);
         $total = pdo_fetchcolumn("SELECT COUNT(1) FROM " . tablename('pte_student') . " WHERE status = 1 and classid =". $id);
     //（前台用pager标签可以直接显示）
         $pager = pagination($total, $pindex, $psize);
         include $this->template('admin/student');
}if ($operation == 'status') {
    if($_W['isajax'] && $_W['ispost']){
        $id = $_GPC['id'];
        if(empty($id)){
            $this->my_message(202,'','id不存在');
        }
        $updata_data = array(
            'status' => 0
        );
        $result = pdo_update('pte_student', $updata_data, array('id' => $id));
        if (!empty($result)) {
            $result = pdo_query("DELETE FROM ".tablename('mc_members')." WHERE uid IN(SELECT userid FROM ".tablename('pte_student')." where ims_pte_student.id = ".$id.")");
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
        $classid = $_GPC['classid'];
        $userid = $_GPC['userid'];
        $username =   $tel ."@qq.com";
        $checkbox = $_GPC['checkbox'];
        $student_num = $_GPC['student_num'];
        if(empty($checkbox)){
            $this->my_message(202,'','科目不能为空');
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
            if( $emaillist){
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
                        'school_num' =>$uid.''.date('Ymd'),
                        'schoolid' => $schoolid,
                        'classid' => $classid,
                        'createtime' => time(),
                        'status' => 1,
                        'userid' => $uid,
                        'student_num' => $student_num,
                        'subjectlist' => json_encode($checkbox),
                );
                $result = pdo_insert('pte_student', $inset_data);
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
                'student_num' => $student_num,
                'subjectlist' => json_encode($checkbox),
            );
            $updata_userdata = array(
                'email' =>$username,
            );
            $result1 =  pdo_update('mc_members', $updata_userdata, array('uid' => $userid));
            $result = pdo_update('pte_student', $updata_data, array('id' => $id));
            if($result ||  $result1){
                $this->my_message(200,'','修改成功');
            }else{
                $this->my_message(201,'','修改失败');
            }
        }
    
    }
}if($operation == 'test'){
    $curl = curl_init(); // 启动一个CURL会话
    $token = "ww1cvALxgbUEEesxv_py3teO3k2n7Rxr9L0_swxRU3Y5IIaVfjFZtct9cdAtrCHQ_IhGERF8uFvc1B7VkCHUDxkxkRUbuuj7zqvecooZqM_CRAj1SD_dSLVZsez1fL2b4tcr069HN3GRQBMRjJfDJutvmYdX4bwKrpcaMeBx-DTWCRSDWB1WzX_j8AY_ma6-9Q31xcD5zINgb_RCevdufQ";
    $url = "https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?access_token=".$token."&department_id=1";
     
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        // curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        // curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        // curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $post); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $res = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        $list = json_decode($res);
        $userlist = $list->userlist;
        foreach($userlist as $item){
            var_dump($item->name);
            $sql = "SELECT * FROM " . tablename('pte_student')." WHERE status = 1 and name ='".$item->name."'";
            $student = pdo_fetch($sql);
            var_dump( $student);
            if($student){
                pdo_update('pte_student', ['student_num'=>$item->userid], array('id' => $student['id']));
            }
        }
        var_dump( $userlist);die();
        return $res; // 返回数据，json格式
}