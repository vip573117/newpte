<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    $uid = $_W['member']['uid'];
  //获取页数
  $pindex = max(1, intval($_GPC['page']));
  //获取页行数
  $psize = 5;
  //使用拼接sql语句
  $sql = "SELECT * FROM " . tablename('pte_subject')." WHERE status = 1 ";
  $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
  $subjectlist = pdo_fetchall($sql);
  $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('pte_subject') . ' WHERE status = 1');
//（前台用pager标签可以直接显示）
  $pager = pagination($total, $pindex, $psize);
    include $this->template('admin/object');
}if ($operation == 'status') {
    if($_W['isajax'] && $_W['ispost']){
        $id = $_GPC['id'];
        if(empty($id)){
            $this->my_message(202,'','id不存在');
        }
        $updata_data = array(
            'status' => 0
        );
        $result = pdo_update('pte_subject', $updata_data, array('id' => $id));
        if (!empty($result)) {
            $this->my_message(200,'','更新成功');
        }else {
            $this->my_message(202,'','更新失败');
        }
    }
   // include $this->template('admin/campus');
}if ($operation == 'add') {
    if($_W['isajax'] && $_W['ispost']){
        $name = $_GPC['name'];
        if(empty($name)){
            $this->my_message(202,'','标题不能为空');
        }
        $inset_data = array(
            'title' => $name,
            'createtime' => time(),
            'status' => 1
        );
        $result = pdo_insert('pte_subject', $inset_data);
        if($result){
            $this->my_message(200,'','添加成功');
        }else{
            $this->my_message(201,'','添加失败');
        }
    }
}