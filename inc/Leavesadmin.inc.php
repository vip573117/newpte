<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

if ($operation == 'display') {
    var_dump('leave');
} elseif ($operation == 'post') {

}