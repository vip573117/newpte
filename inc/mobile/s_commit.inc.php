<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->func('tpl');
load()->func('file');
//var_dump($this->user);die();
if ($operation == 'display') {

    $studentid = $this->user['id'];
    $taskid = $_GPC['taskid'];
    $sttid = $_GPC['sttid'];
    $type = $_GPC['type'];
    $score = pdo_fetch("SELECT score FROM " . tablename('pte_studenttask') . " where studentid=" . $studentid . " and taskid= " .$taskid  )['score'];
    $sql = "SELECT su.*,td.problem,td.id as tdid  FROM  " . tablename('pte_taskdetail') . " td LEFT JOIN " . tablename('pte_submitstak') . "
    su on su.taskdetailid = td.id and su.studentid= " . $studentid . "
    where td.taskid =" . $taskid;
    $tasklist = pdo_fetchall($sql);
    //var_dump($tasklist);die();
    $trans   = array_flip(get_html_translation_table(HTML_ENTITIES));
        include $this->template('student/commit');
    }
    if ($operation == 'end') {
        if ($_W['isajax'] && $_W['ispost']) {
            $id = $_GPC['sttid'];
            //var_dump( $id);die();
            if (empty($id)) {
                $this->my_message(202, '', '出现错误');
            }
            $update_data = array(
                'status' => 2,
            );
            $result = pdo_update('pte_studenttask', $update_data, array('id' => $id));
            if ($result) {
                $this->my_message(200, '', '提交成功');
            } else {
                $this->my_message(201, '', '提交失败');
            }
        }
    }
    if ($operation == 'endstatus') {
        if ($_W['isajax'] && $_W['ispost']&& $_W['ispost']) {
            $taskid = $_GPC['taskid'];
            $studentid = $this->user['id'];
            $datainfo = pdo_fetch("SELECT * FROM " . tablename('pte_submitstak') . " where taskid=" . $taskid ." and studentid=".   $studentid);
            if ($datainfo) {
                $this->my_message(200, '', '有数据');
            } else {
                $this->my_message(201, '', '请先保存作业');
            }
        }
    }
    if ($operation == 'corrections') {
        // $httpname = $this->module['config']['param_a'];
        if ($_W['ispost']) {
            $itemid = $_GPC['itemid'];
            $studentid = $this->user['id'];
            $taskid = $_GPC['taskid'];
            $type = $_GPC['type'];
            $tdid = $_GPC['tdid'];
            $text = $_GPC['text'];
            //修改作业状态
            $taskstatus = pdo_fetch("SELECT ims_pte_studenttask.id,ims_pte_studenttask.status FROM " . tablename('pte_studenttask') . " where studentid=" . $studentid . " and taskid= " . $taskid);
            if ($tasktstatus['status'] == 0) {
                $update_data = array(
                    'status' => 1,
                );
                pdo_update('pte_studenttask', $update_data, array('id' => $taskstatus['id']));
            }
            if (empty($itemid)) {
                $teacherid = pdo_fetch("SELECT teacher FROM " . tablename('pte_task') . " where id=" . $taskid)['teacher'];
                $resdata = pdo_fetch("SELECT * FROM " . tablename('pte_submitstak') . " where taskdetailid=" . $tdid ." and studentid=".$studentid);
                if($resdata){
                    $update_data = array(
                        'textanswer' => $text
                    );
                    $result = pdo_update('pte_submitstak', $update_data, array('id' => $resdata['id']));
                    if ($result) {
                        $this->my_message(200, '', '保存成功');
                    } else {
                        $this->my_message(201, '', '保存失败');
                    } 
                } else{
                    $text = $_GPC['text'];
                    $inset_data = array(
                        'taskid' => $taskid,
                        'studentid' => $studentid,
                        'teacherid' => $teacherid,
                        'taskdetailid' => $tdid,
                        'textanswer' => $text
                    );
                    $result=  pdo_insert('pte_submitstak', $inset_data);
                    if ($result) {
                        $this->my_message(200, '', '保存成功');
                    } else {
                        $this->my_message(201, '', '保存失败');
                    } 
                }
            }else{
                    $update_data = array(
                        'textanswer' => $text
                    );
                    $result = pdo_update('pte_submitstak', $update_data, array('id' => $itemid));
                    if ($result) {
                        $this->my_message(200, '', '保存成功');
                    } else {
                        $this->my_message(201, '', '保存失败');
                    }
            }    
        }else{
            $this->my_message(201, '', '请求错误');
        }
    }
