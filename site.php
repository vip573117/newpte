<?php
/**
 * 麦迅标准模块 模块微站定义
 *
 * @author cokeyang
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class Maixun_pteModuleSite extends WeModuleSite {
	public $user;
	public $is_admin = false;
	public $is_teacher = false;
	public $is_student = false;
	public $image_path = "https://wx.yoois.com/attachment/";
	 
	public function __construct() {
		global $_W,$_GPC;
		$moble_array  = array('s_sign','s_upsign','s_task','s_userinfo','s_score','s_examination','s_leave','s_upsign','s_ground','qrlogin');
		if(in_array($_GPC['do'], $moble_array)){
			return;
		}
		 $funcarray  = array('login','logout','t_bindingopenid','s_bindingopenid','a_bindingopenid','qrlogin');
		if(!in_array($_GPC['do'], $funcarray)){
			checkauth();
			$this->user = [];
			$this->user['uid'] = $_W['member']['uid'];
			$student = pdo_fetch("SELECT * FROM " . tablename('pte_student') ." WHERE userid= ".$this->user['uid']);
			if($student){
				$this->user['type'] = "student"; 
				$this->user = array_merge($this->user,$student);
				$this->is_student = true;
				$banurl = "https://wx.yoois.com/app/index.php?i=4&c=entry&do=s_bindingopenid&m=maixun_pte&id=".$this->user['id'];
				$this->user['url'] = "http://demo.yoois.com/app/source/qr/?". $banurl;
		
			}
			$teacher = pdo_fetch("SELECT * FROM " . tablename('pte_teacher').' WHERE userid= '.$this->user['uid']);
			if(!$this->is_student && $teacher){
				$this->user['type'] = "teacher";
				$this->user = array_merge($this->user,$teacher);
				$this->is_teacher = true;
				$banurl = "https://wx.yoois.com/app/index.php?i=4&c=entry&do=t_bindingopenid&m=maixun_pte&id=".$this->user['id'];
				$this->user['url'] = "http://demo.yoois.com/app/source/qr/?". $banurl;
			}
			$admin = pdo_fetch("SELECT * FROM " . tablename('pte_admin').' WHERE userid= '.$this->user['uid']);
			if(!$this->is_student && !$this->is_teacher && $admin){
				$this->user['type'] = "admin";
				$this->user = array_merge($this->user,$admin);
				$this->is_admin = true;
				$banurl = "https://wx.yoois.com/app/index.php?i=4&c=entry&do=a_bindingopenid&m=maixun_pte&id=".$this->user['id'];
				$this->user['url'] = "http://demo.yoois.com/app/source/qr/?". $banurl;
			}
		}
	}

    // public function __mobile($f_name)
    // {
    //     global $_W, $_GPC;
    //     $weid = $_W['uniacid'];
    //     include_once 'inc/mobile/'.$this->user['type']."/". strtolower(substr($f_name, 8)) . '.inc.php';
    // }

	private function _checkSettings(){
		global $_W;
		if (empty($this->module['config'])) {
		  message('抱歉，系统参数没有填写，请先填写系统参数！', url('profile/module/setting', array(
		      'm' => 'maixun_demo'
		  )), 'error');
		}
	}

	//公共返回方法
	public function my_message($code,$data,$msg){
		$obj = new stdClass();
		$obj->code = $code;
		$obj->data = $data;
		$obj->msg = $msg;
		echo json_encode($obj);
		die();
	}

	public function doMobileLogout(){
		global $_GPC, $_W;
		unset($_SESSION);
		session_destroy();
		isetcookie('logout', 1, 60);
		$this->my_message(200,'','success');
	}
}