<?php
/**
 * maixun_demo模块system_welcome接口定义
 *
 * @author adibaby
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
	
class Maixun_pteModuleSystemWelcome extends WeModuleSystemWelcome {
    public function systemWelcomeDisplay() {
        echo '微信首页显示内容';
        //或是也可以引用一个模板
    }
	public function doPageSetting(){
		global $_GPC, $_W;
		// 此处开发者自行处理
		echo '设置页显示内容';	
	}
}