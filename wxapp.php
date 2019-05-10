<?php
/**
 * 麦迅标准模块 模块小程序接口定义
 *
 * @author cokeyang
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class Maixun_pteModuleWxapp extends WeModuleWxapp {
    public $token = 'maixun_demo_token'; //接口通信token
    
    public function doPageIndex() {
        $this->result(0, '', array('test' => 1235));
    }
    
    public function doPageTest() {
    
    }
}