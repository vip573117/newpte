<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$infolist = $this->authentication();

if ($operation == 'display') {
    $uid = $_W['member']['uid'];
      //获取页数
      $pindex = max(1, intval($_GPC['page']));
      //获取页行数
      $psize = 5;
      //使用拼接sql语句 
      $sql = "SELECT ims_pte_class.*,ims_pte_school.title as schoolname FROM " . tablename('pte_class') . " LEFT JOIN " . tablename('pte_school') . " ON ims_pte_class.schoolid = ims_pte_school.id where ims_pte_class.status=1";
      $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
      $classlist = pdo_fetchall($sql);
    
      $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('pte_class'));
  //（前台用pager标签可以直接显示）
      $pager = pagination($total, $pindex, $psize);
      
    // $classlist = pdo_fetchall("SELECT ims_pte_class.*,ims_pte_school.title as schoolname FROM " . tablename('pte_class') . " LEFT JOIN " . tablename('pte_school') . " ON ims_pte_class.schoolid = ims_pte_school.id" );
    $schoollist = pdo_fetchall("SELECT id,title FROM " . tablename('pte_school'));

	include $this->template('admin/class');
} 
if ($operation == 'status') {
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
        $result = pdo_update('pte_class', $updata_data, array('id' => $id));
        if (!empty($result)) {
            $this->my_message(200,'','更新成功');
        }else {
            $this->my_message(202,'','更新失败');
        }
    }
   // include $this->template('admin/campus');
}if ($operation == 'add') {
    if($_W['isajax'] && $_W['ispost']){
        $id = $_GPC['id'];
        $schoolid = $_GPC['schoolid'];
        $name = $_GPC['name'];
        if(empty($schoolid)){
            $this->my_message(202,'','请选择学校');
        }
        if(empty($name)){
            $this->my_message(202,'','标题不能为空');
        }
        if(empty($id)){
        $inset_data = array(
			'title' => $name,
			'schoolid' =>$schoolid,
            'createtime' => time(),
            'status' => 1
        );
        $result = pdo_insert('pte_class', $inset_data);
        if($result){
            $this->my_message(200,'','添加班级成功');
        }else{
            $this->my_message(201,'','添加班级失败');
        }
        }else{
            $updata_data = array(
                'title' => $name,
                'schoolid' =>$schoolid,
            );
            $result1 =  pdo_update('pte_class', $updata_data, array('id' => $id));
            if($result1){
                $this->my_message(200,'','修改班级成功');
            }else{
                $this->my_message(201,'','修改班级失败');
            }

        }

    }
}