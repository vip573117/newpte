<?php
global $_GPC, $_W;
//获取模块设置参数  审批人管理
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$sign_type = !empty($_GPC['sign_type']) ? $_GPC['sign_type'] : 'display';
$infolist = $this->authentication();
if ($operation == 'display') {
    //获取页数
    $pindex = max(1, intval($_GPC['page']));
    //获取页行数
    $psize = 5;
    //使用拼接sql语句
    $sql = "SELECT * FROM " . tablename('pte_examine') . " where status!=-1  and type=2";
    $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $copydata = pdo_fetchall($sql);
    $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('pte_examine') . " where status!=-1 and type=2");
    //（前台用pager标签可以直接显示）
    $pager = pagination($total, $pindex, $psize);
    $teacher = pdo_fetchall("select * from " . tablename('pte_teacher') . " where status=1");
    //var_dump( $teacher);
    include $this->template('admin/copyadmin');
}
if ($operation == 'status') {
    $id = $_GPC['id'];
    $type = $_GPC['type'];
    $type_x = $_GPC['type_x'];
    if (empty($id)) {
        $this->my_message(202, '', 'id不存在');
    }
    if ($type_x == 'edit') {
        if ($type == '0') {
            // $res = pdo_fetch("SELECT * FROM " . tablename('pte_examine') . " where status=1");
            // if($res){
            //     $this->my_message(202, '', '有打开的审批人未关闭！');
            // }
            $status = 1;
        }
        if ($type == '1') {
            $status = 0;
        }
    }
    if ($type_x == 'delete') {
        $status = -1;
    }
    $updata_data = array(
        'status' => $status,
    );
    $result = pdo_update('pte_examine', $updata_data, array('id' => $id));
    if (!empty($result)) {
        $this->my_message(200, '', '操作成功');
    } else {
        $this->my_message(202, '', '操作失败');
    }
}if ($operation == 'add') {
    if ($_W['isajax'] && $_W['ispost']) {
        $id = $_GPC['id'];
        $name = $_GPC['name'];
        $teacher_id = $_GPC['teacher_id'];
        $openid = $_GPC['openid'];
        if (empty($teacher_id)) {
            $this->my_message(202, '', '操作错误！');
        }
        $teacher = pdo_fetch("select * from " . tablename('pte_examine') . " where type=2 and teacher_id=".  $teacher_id);
        if ( $teacher) {
            $this->my_message(202, '', '重复添加');
        }
            $inset_data = array(
                'name' => $name,
                'teacher_id' => $teacher_id,
                'openid' => $openid,
                'type' => 2,
                'createtime' => time(),
            );
            pdo_insert('pte_examine', $inset_data);
            $uid = pdo_insertid();
            if ($uid) {
                $this->my_message(200, '', '添加成功');

            } else {
                $this->my_message(201, '', '添加失败');
            }
    }
}
