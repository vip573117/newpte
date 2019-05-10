<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
      //获取页数
      $pindex = max(1, intval($_GPC['page']));
      //获取页行数
      $psize = 5;
      //使用拼接sql语句
      $sql = 'SELECT * FROM ' . tablename('pte_school');
      $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
      $schoollist = pdo_fetchall($sql);
      foreach ($schoollist as $key => $value) {
          $schoollist[$key]['statusname'] = $value['status'] == 1?'启用':'禁用';
      }
      $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('pte_school') . ' WHERE status = 1');
  //（前台用pager标签可以直接显示）
      $pager = pagination($total, $pindex, $psize);
    //$uid = $_W['member']['uid'];
    include $this->template('admin/campus');

}if ($operation == 'status') {
    if($_W['isajax'] && $_W['ispost']){
        $id = $_GPC['id'];
        $type = $_GPC['type'];
        $typeid = $_GPC['typeid'];
        if(empty($id)){
            $this->my_message(202,'','id不存在');
        }
        $updata_data = array(
            'status' => $type==1?0:1
        );
        $result = pdo_update('pte_school', $updata_data, array('id' => $id));
        if (!empty($result)) {
            $this->my_message(200,'','更新成功');
        }else {
            $this->my_message(202,'','更新失败');
        }
    }
   // include $this->template('admin/campus');
}if ($operation == 'add') {
    if($_W['isajax'] && $_W['ispost']){
        $schooltitle = $_GPC['name'];
        if(empty($schooltitle)){
            $this->my_message(202,'','标题不能为空');
        }
        $inset_data = array(
            'title' => $schooltitle,
            'createtime' => time(),
            'status' => 1
        );
        $result = pdo_insert('pte_school', $inset_data);
        if($result){
            $this->my_message(200,'','添加学校成功');
        }else{
            $this->my_message(201,'','添加学校失败');
        }
    }
}