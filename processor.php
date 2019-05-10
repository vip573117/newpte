<?php
/**
 * 麦迅标准模块 模块处理程序
 *
 * @author cokeyang
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class Maixun_pteModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		
		$content = $this->message['content'];
		$rid = $this->rule;
		$openid = $_W['openid'];

		$reply = pdo_fetch("SELECT * FROM " . tablename('maixun_demo_reply') . " WHERE `rid`=:rid LIMIT 1", array(':rid' => $rid));
		if( empty($reply) ){
			return $this->respText( '活动已被删除！' );
		}

		$news = array();
		$news[] = array(
			'title' => $reply['title'],
			'description' => $reply['description'],
			'picurl' =>	tomedia($reply['thumb']),
			'url' => $this->createMobileUrl('index', array('id'=>$rid, '_openid'=>$openid)),
		);

		return $this->respNews( $news );
	}
}