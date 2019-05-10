<?php
/**
 * 麦迅标准模块 模块PC定义
 *
 * @author cokeyang
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
class Maixun_pteModuleWebapp extends WeModuleWebapp {
    public function doPageIndex() {
        checklogin();
    	echo "hello.world";
    }
    public function doPageIndex2() {
    	echo "This is Index2";
    }
}