<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
        $uid = $_W['member']['uid'];
        $id = $_GPC['id'];

        $title =$_GPC['title'];
        $deadline =$_GPC['deadline'];
        // var_dump($_GPC['deadline']);
        // var_dump($_GPC['title']);die();
        //获取页数
        $pindex = max(1, intval($_GPC['page']));
        //获取页行数
        $psize = 5;
        //使用拼接sql语句
        $sql = "SELECT sd.name,st.*,t.deadline FROM " . tablename('pte_studenttask')." st LEFT JOIN " . tablename('pte_task')." 
        t ON st.taskid = t.id LEFT JOIN " . tablename('pte_student')."sd ON st.studentid = sd.id and sd.status=1  where t.id = " .$id. " ORDER BY t.createtime DESC";
        $countsql = $sql;
        $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
        $tasklist = pdo_fetchall($sql);
        // var_dump($tasklist);die();
        $totaldata = pdo_fetchall($countsql);
        $total = count($totaldata);
        //（前台用pager标签可以直接显示）
        $pager = pagination($total, $pindex, $psize);
        $schoollist = pdo_fetchall("SELECT id,title FROM " . tablename('pte_school'));
    include $this->template('teacher/taskdateil');
}if ($operation == 'status') {
    if($_W['isajax'] && $_W['ispost']){
        $id = $_GPC['id'];
        $type = $_GPC['type'];
        if(empty($id)){
            $this->my_message(202,'','id不存在');
        }
        $updata_data  = array();
        if($type==1){
            $updata_data = array(
                'status' => 1
            );
        }
        if($type==2){
            $updata_data = array(
                'status' => 2
            );
        }
        $result = pdo_update('pte_task', $updata_data, array('id' => $id));
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
}