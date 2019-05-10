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
    $sql = "SELECT * FROM " . tablename('pte_timemanage') . " where status!=-1";
    $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $timelist = pdo_fetchall($sql);
    $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('pte_timemanage') . " where status!=-1");
    //（前台用pager标签可以直接显示）
    $pager = pagination($total, $pindex, $psize);
    include $this->template('admin/timeadmin');
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
}if ($operation == 'add') {
    if ($_W['isajax'] && $_W['ispost']) {
        $id = $_GPC['id'];
        $startlist_time = $_GPC['startlist_time_x'];
        $endlist_time = $_GPC['endlist_time_x'];
        $title = $_GPC['title'];
        if (empty($startlist_time)) {
            $this->my_message(202, '', '填写课程开始时间');
        }
        if (empty($endlist_time)) {
            $this->my_message(202, '', '填写课程结束时间');
        }
        if (empty($title)) {
            $this->my_message(202, '', '填写标题');
        }
        if (count(explode(',', $startlist_time)) > count(explode('，', $startlist_time))) {
            $startlist = json_encode(explode(',', $startlist_time));
        } else {
            $startlist = json_encode(explode('，', $startlist_time));
        }
        if (count(explode(',', $endlist_time)) > count(explode('，', $endlist_time))) {
            $endlist = json_encode(explode(',', $endlist_time));
        } else {
            $endlist = json_encode(explode('，', $endlist_time));
        }
        if (empty($id)) {
            $inset_timedata = array(
                'startlist_time' => $startlist,
                'endlist_time' => $endlist,
                'startlist_time_ys' => $startlist_time,
                'endlist_time_ys' => $endlist_time,
                'createtime' => time(),
                'title' => $title,
            );
            pdo_insert('pte_timemanage', $inset_timedata);
            $uid = pdo_insertid();
            if ($uid) {
                $this->my_message(200, '', '添加成功');

            } else {
                $this->my_message(201, '', '添加失败');
            }

        } else {
            $updata_data = array(
                'startlist_time' => $startlist,
                'endlist_time' => $endlist,
                'startlist_time_ys' => $startlist_time,
                'endlist_time_ys' => $endlist_time,
                'createtime' => time(),
                'title' => $title,
            );
            $result = pdo_update('pte_timemanage', $updata_data, array('id' => $id));
            if ($result) {
                $this->my_message(200, '', '修改成功');
            } else {
                $this->my_message(201, '', '修改失败');
            }
        }

    }
}
