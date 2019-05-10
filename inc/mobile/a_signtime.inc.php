<?php
global $_GPC, $_W;
//获取模块设置参数
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
    $sql = "SELECT * FROM " . tablename('pte_signtime');
    $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $signtime = pdo_fetchall($sql);
    $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('pte_signtime'));
    //（前台用pager标签可以直接显示）
    $pager = pagination($total, $pindex, $psize);
    include $this->template('admin/signtime');
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
            $res = pdo_fetch("SELECT * FROM " . tablename('pte_timemanage') . " where status=1");
            if($res){
                $this->my_message(202, '', '有打开的模板未关闭！');
            }
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
    $result = pdo_update('pte_timemanage', $updata_data, array('id' => $id));
    if (!empty($result)) {
        $this->my_message(200, '', '操作成功');
    } else {
        $this->my_message(202, '', '操作失败');
    }
}if ($operation == 'edit') {
    if ($_W['isajax'] && $_W['ispost']) {
        $id = $_GPC['id'];
        $time = $_GPC['time'];
        if (empty($time)) {
            $this->my_message(202, '', '填写时间');
        }
        if (!is_numeric($time)) {
            $this->my_message(202, '', '类型错误');
        }
       
            $updata_data = array(
                'time' => $time,
            );
            $result = pdo_update('pte_signtime', $updata_data, array('id' => $id));
            if ($result) {
                $this->my_message(200, '', '修改成功');
            } else {
                $this->my_message(201, '', '修改失败');
            }
    }
}
