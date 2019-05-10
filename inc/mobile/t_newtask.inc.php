<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
//var_dump(date('Y-m-d',1542683593));die();
load()->func('tpl');
load()->func('file');
if ($operation == 'display') {
    $id = $_GPC['id'];
    $teacher_subjson = pdo_fetch("SELECT subjectlist FROM " . tablename('pte_teacher')." where id=".$this->user['id'])['subjectlist'];
    $sublist = json_decode($teacher_subjson);
    $calsslist = pdo_fetchall("SELECT id,title FROM " . tablename('pte_class')." where status=1 and  schoolid= ".$this->user['schoolid']);
    $subjectlist = pdo_fetchall("SELECT id,title FROM " . tablename('pte_subject')." where status=1 and id IN (".implode(',', $sublist).")");
    
    if(!empty($id)){
    //   $taskold =  pdo_fetchall("SELECT * FROM " . tablename('pte_task')." ta LEFT JOIN " . tablename('pte_taskdetail')." tad ON tad.taskid = ta.id where ta.id= ".$id);
    $taskold =  pdo_fetch("SELECT * FROM " . tablename('pte_task') . " where id= ".$id);
    $problem =  pdo_fetchall("SELECT * FROM " . tablename('pte_taskdetail') . " where taskid= ".$id);
    //var_dump($problem);die();
    $enclosure =  pdo_fetchall("SELECT * FROM " . tablename('pte_enclosure') . " where taskid= ".$id);
    $trans   = array_flip(get_html_translation_table(HTML_ENTITIES));
  
    //print_r($problem);   var_dump($taskold['schoolid']);  var_dump($problem);die();
    }
    include $this->template('teacher/addhomework');
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
    if($_W['ispost']){
        $id = $_GPC['id'];
        //$classid = $_GPC['classid'];
        $students = $_GPC['students'];
        $subjectid = $_GPC['subject'];
        // $srcid= $_GPC['srcid'];
        $problem = $_GPC['editor1'];
        $count = $_GPC['count'];
        $titletime = time();
        $deadline = strtotime($_GPC['deadline']);
        if(empty($subjectid)){
            $this->my_message(202,'','请选择科目');
        }
        if(empty($problem)){
            $this->my_message(202,'','问题不能为空');
        }
        if(empty($id)){
            if(empty($students)){
                $this->my_message(202,'','学生不能为空');
            }
            $inset_data = array(
                'schoolid' => $this->user['schoolid'],
                'teacher' => $this->user['id'],
                'subjectid' => $subjectid,
                'titletime' => $titletime,
                'deadline' => $deadline,
                'createtime' => time(),
                'status' => 0,
                'students' => json_encode($students),
            );
            pdo_insert('pte_task', $inset_data);
            $uid = pdo_insertid();
            if($uid){
                for($i = 1; $i <=(int)$count; $i++){
                    if($_GPC['editor'.$i]){
                        $inset_problemdata = array(
                            'taskid' =>$uid,
                            'problem' => $_GPC['editor'.$i]
                        );
                        $result = pdo_insert('pte_taskdetail', $inset_problemdata);
                    }
                }
                if($result){
                    if($_GPC['form_type']==2){
                        $updata_data = array(
                            'status' => 1,
                        );
                        $result = pdo_update('pte_task', $updata_data, array('id' => $uid));
                        if($result){
                            $students = json_decode(pdo_fetch("SELECT students FROM " . tablename('pte_task') . " where id=". $uid)['students']);
                            foreach($students as $item){
                                $inset_data = array(
                                    'studentid' => $item,
                                    'taskid' => $uid,
                                    'status' => 0,
                                    'tstatus' => 0,
                                );
                                pdo_insert('pte_studenttask', $inset_data);
                                $openid = pdo_fetch("SELECT openid FROM " . tablename('pte_student') . " where status=1 and id=" . $item )['openid'];
                                $task = pdo_fetch("SELECT t.*,su.title FROM " . tablename('pte_task') . " t left join  " . tablename('pte_subject') . " su on t.subjectid=su.id where t.id=" . $uid);
                                $teacher_name = pdo_fetch("SELECT name FROM " . tablename('pte_teacher') . " where id=" . $task['teacher'] )['name'];
                                if($openid){
                                    $data = array(
                                        'first' => array(
                                            'value' =>  $teacher_name."布置了". $task['title']."作业",
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
                    $success = 'success';
                    include $this->template('teacher/success');
                }else{
                    $success = 'eroor22';
                    include $this->template('teacher/success');
                }
            }else{
                $success = 'eroor11';
                include $this->template('teacher/success');
            }
        }else{
            if(empty($students)){
                $update_data = array(
                    'subjectid' => $subjectid,
                    'titletime' => $titletime,
                    'deadline' => $deadline,
                    'status' => 0,
                );
            }else{
                $update_data = array(
                    'subjectid' => $subjectid,
                    'titletime' => $titletime,
                    'deadline' => $deadline,
                    'status' => 0,
                    'students' => json_encode($students),
                );
            }
                 pdo_update('pte_task',$update_data,array('id'=>$id));
          $res = pdo_delete('pte_taskdetail', array('taskid' => $id));
            if($res){
                for($i = 1; $i <=(int)$count; $i++){
                    if($_GPC['editor'.$i]){
                        $inset_problemdata1 = array(
                            'taskid' =>$id,
                            'problem' => $_GPC['editor'.$i]
                        );
                        $result = pdo_insert('pte_taskdetail', $inset_problemdata1);
                    }
                }
                if($result){
                    if($_GPC['form_type']==2){
                        $updata_data = array(
                            'status' => 1,
                        );
                        $result = pdo_update('pte_task', $updata_data, array('id' => $id));
                        if($result){
                            $students = json_decode(pdo_fetch("SELECT students FROM " . tablename('pte_task') . " where id=". $id)['students']);
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
                                            'value' =>  $teacher_name."布置了". $task['title']."作业",
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
                    $success = 'success';
                    include $this->template('teacher/success');
                }else{
                    $success = 'eroor2';
                    include $this->template('teacher/success');
                }
            }else{
                $success = 'eroor1';
                include $this->template('teacher/success');
            }
        }
    }
}if ($operation == 'add2') {
    //var_dump($_GPC);die();
    if($_W['ispost']&&$_W['isajax']){
        $id = $_GPC['id'];
        $students = $_GPC['students'];
        $subjectid = $_GPC['subject'];
        $problem = $_GPC['problem'];
        $titletime = time();
        $deadline = strtotime($_GPC['deadline']);

        if(empty($subjectid)){
            $this->my_message(202,'','请选择科目');
        }
        if(empty($problem)){
            $this->my_message(202,'','问题不能为空');
        }
        if(empty($id)){
            if(empty($students)){
                $this->my_message(202,'','学生不能为空');
            }
            $inset_data = array(
                'schoolid' => $this->user['schoolid'],
                'teacher' => $this->user['id'],
                'subjectid' => $subjectid,
                'titletime' => $titletime,
                'deadline' => $deadline,
                'createtime' => time(),
                'status' => 0,
                'students' => json_encode($students),
            );
            pdo_insert('pte_task', $inset_data);
            $uid = pdo_insertid();
            if($uid){
                for($i = 0; $i <count($problem); $i++){
                    $inset_problemdata = array(
                        'taskid' =>$uid,
                        'problem' => $problem[$i]
                    );
                   $result = pdo_insert('pte_taskdetail', $inset_problemdata);
                   $rid = pdo_insertid();
                    // if($srcid[$i]){
                    //     $plist = explode(",",$srcid[$i]);
                    //     foreach( $plist as $ite){
                    //         $ims_enclosuredata = array(
                    //             'taskid' =>$uid,
                    //             'taskdatileid' => $rid
                    //         );
                    //         pdo_update('pte_enclosure', $ims_enclosuredata,array('id'=>$ite));
                    //     }
                    // }
                }
                if($result){
                    $success = 'success';
                    $this->my_message(200, '','success');
                }else{
                    $this->my_message(201, '','error');
                }
            }else{
                $this->my_message(202, '','error');
            }
        }else{
      
            if(empty($students)){
                $update_data = array(
                    'subjectid' => $subjectid,
                    'titletime' => $titletime,
                    'deadline' => $deadline,
                    'status' => 0,
                );
            }else{
                $update_data = array(
                    'subjectid' => $subjectid,
                    'titletime' => $titletime,
                    'deadline' => $deadline,
                    'status' => 0,
                    'students' => json_encode($students),
                );
            }
         
          $res =  pdo_update('pte_task',$update_data,array('id'=>$id));
            if($res){
                pdo_delete('pte_taskdetail', array('taskid' => $id));
                foreach($problem as $item){ 
                    $inset_problemdata = array(
                        'taskid' =>$id ,
                        'problem' => $item,
                    );
                   $result= pdo_insert('pte_taskdetail', $inset_problemdata);
                }
        
                if($result){
                    $this->my_message(200, '','success');
                }else{
                    $this->my_message(201, '','error');
                }
            }else{
                $this->my_message(202, '','error');
            }
        }
    }
}
else if($operation == 'upload'){
    $httpname =  $this->module['config']['param_a'];
    $name = $_POST['name']; //获取索引
	$filesName = $_FILES['file']['name'];  //文件名数组
    $ccname = date("Ymd",time());
    $size = $_FILES['file']['size'];
    $type = $_FILES['file']['type'];
    $file = file_wechat_upload($_FILES['file']);
   // var_dump( $file);die();
    if($file['success']){
        $inset_data = array(
                'title' => $name,
                'path' =>  $httpname.'/attachment/' . $file['path'],
                'type' => $type,
                'size' => $size,
                'name' => $filesName,

        );
        pdo_insert('pte_enclosure', $inset_data);
        $uid = pdo_insertid();
        if($uid){
            $returndata = array(
                'id' => $uid,
                'title' =>  $name,
                'name' => $filesName,
            );
        $this->my_message(200, $returndata ,'上传成功');
        }else{
            $this->my_message(201,'','上传失败');     
        }
    }else{
        $this->my_message(201,'','上传失败');
    }
}else if($operation == 'selectstudent'){  
    // var_dump($_GPC['cid']);die();
    if($_W['isajax'] && $_W['ispost']){
        $cid = $_GPC['cid'];
        if(empty($cid)){
            $this->my_message(202,'','id不存在');
        }
        $students =  pdo_fetchall("SELECT * FROM " . tablename('pte_student') . " where status=1 and classid= ".$cid);
        if (!empty($students)) {

            $this->my_message(200, $students,'success');
        }else {
            $this->my_message(202,'','error');
        }
    }
}