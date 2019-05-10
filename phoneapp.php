<?php
/**
 * maixun_demo模块APP接口定义
 *
 * @author adibaby
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class Maixun_pteModulePhoneapp extends WeModulePhoneapp {
	public function doPageTest(){
		global $_GPC, $_W;
		$errno = 0;
		$message = '返回消息';
		$data = array();
		return $this->result($errno, $message, $data);
	}
}