<?php
/**
 * 麦迅标准模块 模块定义
 *
 * @author cokeyang
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class Maixun_pteModule extends WeModule {
    public function fieldsFormDisplay($rid = 0) {
        global $_W, $_GPC;
        if (!empty($rid)) {
            $reply = pdo_fetch("SELECT * FROM ".tablename('maixun_demo_reply')." WHERE uniacid = :uniacid AND rid = :rid", array(':uniacid' => $_W['uniacid'],':rid' => $rid));
        }
        include $this->template('rule');
    }

	public function fieldsFormValidate($rid = 0) {
		global $_W, $_GPC;
        if ( empty($_GPC['title']) ) {
            return '请填写活动标题！';
        }
        if( empty($_GPC['thumb']) ){
            return '设置一张封面图片吧！';
        }
        return '';
	}

    public function fieldsFormSubmit($rid = 0) {
        global $_GPC, $_W;
        $insert_data = array(
            'title' =>  $_GPC['title'],
            'thumb' =>  $_GPC['thumb'],
            'description'   =>  $_GPC['description']
        );
        $reply = pdo_fetch('SELECT * FROM '.tablename('maixun_demo_reply').' WHERE uniacid = :uniacid AND rid = :rid' , array(':uniacid' => $_W['uniacid'],':rid'=>$rid));
        if( $reply ){
            pdo_update('maixun_demo_reply', $insert_data, array('rid'=>$rid));
        } else {
            $insert_data['rid'] =   $rid;
            $insert_data['uniacid'] =   $_W['uniacid'];
            pdo_insert('maixun_demo_reply', $insert_data);
            //$formid = pdo_insertid();
            message('保存成功！正转向活动报名字段管理！');
        }
        return true;
    }

	public function ruleDeleted($rid) {
        global $_W;
        pdo_delete('maixun_demo_reply', array(
            'rid' => $rid
        ));
        //TODO 其他一些规则也可以一起删除
        return true;
	}

	//配置页
    public function settingsDisplay($settings) {
        global $_GPC, $_W;
        if (checksubmit()) {
            $cfg = $_GPC['settings'];
            if ($this->saveSettings($cfg)) {
                message('保存成功', 'refresh');
            }
        }
		include $this->template('setting');
    }
}