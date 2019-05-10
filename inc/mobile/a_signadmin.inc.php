<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$sign_type = !empty($_GPC['sign_type']) ? $_GPC['sign_type'] : 'display';
$infolist = $this->authentication();
if ($operation == 'display') {
    $status = 0;
    if($sign_type=='adopt'){
        $status = 1;
    }
    if($sign_type=='reject'){
        $status = -1;
    }
      $uid = $_W['member']['uid'];
      //获取页数
      $pindex = max(1, intval($_GPC['page']));
      //获取页行数
      $psize = 5;
      //使用拼接sql语句 
      $sql = "SELECT * FROM " . tablename('pte_upsign') . "where status=". $status;
      $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
      $signlist = pdo_fetchall($sql);
      $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('pte_upsign')). "where status=". $status;
      $signtype = pdo_fetch("SELECT * FROM " . tablename('pte_signleave_type') . " WHERE type=1");
  //（前台用pager标签可以直接显示）
      $pager = pagination($total, $pindex, $psize);
	include $this->template('admin/sign');
} 
if ($operation == 'status') {
        $id = $_GPC['id'];
        $type = $_GPC['type'];
        $status = 0;
        if(empty($id)){
            $this->my_message(202,'','id不存在');
        }
        if( $type =='adopt'){
            $status = 1;
            $sign_data = pdo_fetch("SELECT * FROM " . tablename('pte_upsign') . "where id=".  $id);
            if( $sign_data ){
                $click = pdo_fetch("SELECT * FROM " . tablename('pte_click') . "where id=".  $sign_data['click_id']);
                $teacher = pdo_fetch("SELECT * FROM " . tablename('pte_teacher') . "where id=".  $sign_data['sp_people']);
                $updata_data = array(
                    'click_time'=> strtotime($click['course_date']),
                    'time'=> date("Y-m-d H:i",time()),
                    'status'=> '补卡',
                );
                $result = pdo_update('pte_click', $updata_data, array('id' =>  $click['id']));
                if( $teacher['openid']){
                    $data = array(
                        'first' => array(
                            'value' =>  $teacher['name']."审核了补卡申请",
                            'color' => '#ff510'
                        ),
                        'keyword1' => array(
                            'value' => $sign_data['subject_name'],
                            'color' => '#ff510'
                        ),
                        'keyword2' => array(
                            'value' => "",
                            'color' => '#ff510'
                        ),
                        'keyword3' => array(
                            'value' => "",
                            'color' => '#ff510'
                        ),
                        'remark' => array(
                            'value' => "" ,
                            'color' => '#ff510'
                        ),
                    );
                    $account = WeAccount::create();
                    $status = $account->sendTplNotice($sign_data['student_openid'], 'kVNyGYw3dMgdVoPbgqYDMVwjb48tez0bT5CqKPRtFqw', $data, '');
                }
            }else{
                $this->my_message(202,'','操作失败');  
            }

        }
        if( $type =='reject'){
            $status = -1;
            $sign_data = pdo_fetch("SELECT * FROM " . tablename('pte_upsign') . "where id=".  $id);
            if( $sign_data ){
                $teacher = pdo_fetch("SELECT * FROM " . tablename('pte_teacher') . "where id=".  $sign_data['sp_people']);
                if( $teacher['openid']){
                    $data = array(
                        'first' => array(
                            'value' =>  $teacher['name']."审核了补卡申请",
                            'color' => '#ff510'
                        ),
                        'keyword1' => array(
                            'value' => $sign_data['subject_name'],
                            'color' => '#ff510'
                        ),
                        'keyword2' => array(
                            'value' => "",
                            'color' => '#ff510'
                        ),
                        'keyword3' => array(
                            'value' => "",
                            'color' => '#ff510'
                        ),
                        'remark' => array(
                            'value' => "" ,
                            'color' => '#ff510'
                        ),
                    );
                    $account = WeAccount::create();
                    $status = $account->sendTplNotice($teacher['openid'], 'kVNyGYw3dMgdVoPbgqYDMVwjb48tez0bT5CqKPRtFqw', $data, '');
                }
            }else{
                $this->my_message(202,'','操作失败');  
            }
        }
        $updata_data = array(
            'status' => $status
        );
        $result = pdo_update('pte_upsign', $updata_data, array('id' => $id));
        if (!empty($result)) {
            $this->my_message(200,'','更新成功');
        }else {
            $this->my_message(202,'','更新失败');
        }
}if ($operation == 'add') {
    if ($_W['isajax'] && $_W['ispost']) {
        $id = $_GPC['id'];
        $signtype = $_GPC['signtype'];
        if (empty($signtype)) {
            $this->my_message(202, '', '填写补卡类型');
        }
        if (count(explode(',', $signtype)) > count(explode('，', $signtype))) {
            $typelist = json_encode(explode(',', $signtype));
        } else {
            $typelist = json_encode(explode('，', $signtype));
        }
        if (empty($id)) {
            $inset_timedata = array(
                'title' => '补卡类型',
                'content' => $typelist,
                'content_ys' => $signtype,
                'type' => 1,
                'createtime' => time(),
            );
            pdo_insert('pte_signleave_type', $inset_timedata);
            $uid = pdo_insertid();
            if ($uid) {
                $this->my_message(200, '', '添加成功');

            } else {
                $this->my_message(201, '', '添加失败');
            }

        } else {
            $updata_data = array(
                'content' => $typelist,
                'content_ys' => $signtype,
            );
            $result = pdo_update('pte_signleave_type', $updata_data, array('id' => $id));
            if ($result) {
                $this->my_message(200, '', '修改成功');
            } else {
                $this->my_message(201, '', '修改失败');
            }
        }

    }
}
