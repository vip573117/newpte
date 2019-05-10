<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->func('tpl');
load()->func('file');
//var_dump($this->user);die();
if ($operation == 'display') {
    $id = $_GPC['id'];
    $taskid = $_GPC['taskid'];
    $studentid =  $_GPC['studentid'];  
    $tasktstatus = pdo_fetch("SELECT tstatus FROM " . tablename('pte_studenttask') . " where studentid=" . $studentid . " and taskid= " .$taskid  )['tstatus'];
    $score = pdo_fetch("SELECT score FROM " . tablename('pte_studenttask') . " where studentid=" . $studentid . " and taskid= " .$taskid  )['score'];
    //var_dump( $tasktstatus);
    $sql = "SELECT su.*,td.problem,td.id as tdid  FROM  " . tablename('pte_taskdetail'). " td LEFT JOIN " . tablename('pte_submitstak') . " 
    su on su.taskdetailid = td.id and su.studentid= " . $studentid ."
    where td.taskid =" . $taskid;
    $tasklist = pdo_fetchall($sql);
    $schoollist = pdo_fetchall("SELECT id,title FROM " . tablename('pte_school'));
    $trans   = array_flip(get_html_translation_table(HTML_ENTITIES));
    include $this->template('teacher/suggest');
}
if ($operation == 'status') {
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
}
if ($operation == 'end') {
    if($_W['isajax'] && $_W['ispost']){
        $id = $_GPC['id'];
        $studentid = $_GPC['studentid'];
        $taskid = $_GPC['taskid'];
        $score = $_GPC['score'];
        $teacher = $this->user;
        if(empty($id)){
            $this->my_message(202,'','出现错误');
        }
                $update_data = array(
                    'tstatus' => 1,
                    'score'=> $score
                );
                $result = pdo_update('pte_studenttask', $update_data , array('id' => $id ));
                if($result){
                    $openid = pdo_fetch("SELECT openid FROM " . tablename('pte_student') . " where id=" .  $studentid )['openid'];
                    $task = pdo_fetch("SELECT t.*,su.title FROM " . tablename('pte_task') . " t left join  " . tablename('pte_subject') . " su on t.subjectid=su.id where t.id=" . $taskid );
                    if($openid){
                        $data = array(
                            'first' => array(
                                'value' =>  $teacher['name']."批改了". $task['title']."作业",
                                'color' => '#ff510'
                            ),
                            'keyword1' => array(
                                'value' => $task['title'],
                                'color' => '#ff510'
                            ),
                            'keyword2' => array(
                                'value' => date('Y-m-d', $task['createtime']).$task['title']."作业",
                                'color' => '#ff510'
                            ),
                            'keyword3' => array(
                                'value' =>date('Y-m-d H:i',time()),
                                'color' => '#ff510'
                            ),
                            'remark' => array(
                                'value' => "请登录PC端查看" ,
                                'color' => '#ff510'
                            ),
                        );
                        $account = WeAccount::create();
                        $status = $account->sendTplNotice($openid, 'RR6Obswcre9bNupcYMFNJtwAZfMByCATdpEBes-c-UY', $data, '');
                        $this->my_message(200,'','批改成功');
                        $insert_data = array(
                            'openid'=>$openid, 
                            'pushmsg'=> json_encode($data), 
                            'pushresut'=>json_encode($status), 
                            'createtime'=>time(),
                            'type'=>'批改'  
                        );
                        pdo_insert('pte_pushlog', $insert_data);
                    }
                }else{
                    $this->my_message(201,'','批改失败');
                }
        
    }
}
if ($operation == 'corrections') {
    $httpname =  $this->module['config']['param_a'];
    if($_W['ispost']){
        $teacher = $this->user['id'];
        $itemid = $_GPC['itemid'];
        $id = $_GPC['id'];
        $studentid = $_GPC['studentid'];
        $taskid = $_GPC['taskid'];
        $tdid = $_GPC['tdid'];
        $type = $_GPC['type'];
        // if(empty($itemid)){
        //     $inset_data = array(
        //         'taskid' => $taskid,
        //         'studentid' => $studentid,
        //         'teacherid' => $teacher,
        //         'taskdetailid' => $tdid,
        // );
        //  pdo_insert('pte_submitstak', $inset_data);
        //  $itemid = pdo_insertid();
        // }
        if (empty($itemid)) {
            $resdata = pdo_fetch("SELECT * FROM " . tablename('pte_submitstak') . " where taskdetailid=" . $tdid ." and teacherid=".$teacher);
            if($resdata){
                $text = $_GPC['text'];
                $update_data = array(
                    'textexamine' => $text
                );
                $result = pdo_update('pte_submitstak', $update_data, array('id' => $resdata['id']));
                if ($result) {
                    $resultdata = array(
                        'teacherid' => $teacher,
                        'taskid' => $taskid,
                    );
                    $this->my_message(200, $resultdata, '保存成功');

                } else {
                    $this->my_message(201, '', '保存失败');
                } 
            } else{
                $text = $_GPC['text'];
                $inset_data = array(
                    'taskid' => $taskid,
                    'studentid' => $studentid,
                    'teacherid' => $teacher,
                    'taskdetailid' => $tdid,
                    'textexamine' => $text
                );
                $result=  pdo_insert('pte_submitstak', $inset_data);
                $itemid = pdo_insertid();
                if ($result) {
                    $resultdata = array(
                        'teacherid' => $teacher,
                        'taskid' => $taskid,
                    );
                    $this->my_message(200, $resultdata, '保存成功');

                } else {
                    $this->my_message(201, '', '保存失败');
                } 
            }
        }
        if($type == 'voice'){
           $filesName = $_FILES['data']['name'];  //文件名数组
            $ccname = date("Ymd",time());
            $size = $_FILES['data']['size'];
            $type = $_FILES['data']['type'];
            $file = file_wechat_upload($_FILES['data']);
            if($file['success']){
                $inset_data = array(
                        'title' => $name,
                        'path' => '/attachment/' . $file['path'],
                        'type' => $type,
                        'size' => $size,
                        'name' => $filesName,
                );
                pdo_insert('pte_enclosure', $inset_data);
                $update_data = array(
                    'voiceexamine' =>  '/attachment/' . $file['path'],
                    );
                $result = pdo_update('pte_submitstak', $update_data , array('id' => $itemid ));
                if($result){
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
                $this->my_message(202,'','上传失败');
            }
        }
    if($_W['isajax']){
        if($type == 'text'){
            $text = $_GPC['text'];
            $update_data = array(
                    'textexamine' =>  $text
            );
            $result = pdo_update('pte_submitstak', $update_data , array('id' => $itemid ));
            if($result){
                $resultdata = array(
                    'studentid' => $studentid,
                    'taskid' => $taskid,
                ); 
                $this->my_message(200,$resultdata,'保存成功');
                
            }else{
                $this->my_message(201,'','保存失败');
            }
            }
            if($type == 'image'){
                $filesName = $_FILES['file']['name'];  //文件名数组
                $ccname = date("Ymd",time());
                $size = $_FILES['file']['size'];
                $type = $_FILES['file']['type'];
                $file = file_upload($_FILES['file']);
                if($file['success']){
                    $inset_data = array(
                            'path' => '/attachment/' . $file['path'],
                            'type' => $type,
                            'size' => $size,
                            'name' => $filesName,
                    );
                    pdo_insert('pte_enclosure', $inset_data);
                    $update_data = array(
                        'imgexamine' =>  '/attachment/' . $file['path'],
                        );
                    $result = pdo_update('pte_submitstak', $update_data , array('id' => $itemid ));
                    if($result){
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
                    $this->my_message(202,'','上传失败');
                }
            }
        if($type == 'file'){
            $filesName = $_FILES['file']['name'];  //文件名数组
            $ccname = date("Ymd",time());
            $size = $_FILES['file']['size'];
            $type = $_FILES['file']['type'];
            $file = file_wechat_upload($_FILES['file']);
            if($file['success']){
                $inset_data = array(
                    'path' => '/attachment/' . $file['path'],
                    'type' => $type,
                    'size' => $size,
                    'name' => $filesName,
                );
                pdo_insert('pte_enclosure', $inset_data);
                $update_data = array(
                    'filexamine_file' => '/attachment/' . $file['path'],
                    'filexamine_name' => $filesName,
                );
                $result = pdo_update('pte_submitstak', $update_data , array('id' => $itemid ));
                if($result){
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
                $this->my_message(202,'','上传失败');
            }
        }
        if($result){
            $resultdata = array(
                'id' => $id,
                'studentid' => $studentid,
                'taskid' => $taskid,
            ); 
            $this->my_message(200,$resultdata,'批改成功');
            
        }else{
            $this->my_message(201,'','批改失败');
        }
        
    }
}
}