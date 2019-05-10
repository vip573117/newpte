<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
	 //var_dump($this->user);
	if($this->user['type'] == 'admin'){
		$infolist = array();
		$infolist[0]['title']='校区总数';
		$infolist[1]['title']='班级总数';
		$infolist[2]['title']='老师总数';
		$infolist[3]['title']='科目总数';
		$infolist[0]['num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('pte_school'));
		$infolist[1]['num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('pte_class'));
		$infolist[2]['num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('pte_teacher'));
		$infolist[3]['num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('pte_subject'));

	}
	if($this->user['type'] == 'student'){
		$infolist = array();
		$infolist[0]['title']='完成作业';
		$infolist[1]['title']='逾期作业';
		$infolist[2]['title']='提交次数';
		$infolist[3]['title']='老师回复';
		$infolist[0]['num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('pte_studenttask') . " where studentid= ". $this->user['id'] . " and status=2");
		$infolist[1]['num'] =  pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('pte_studenttask') . " stt left join " . tablename('pte_task') . " t 
		  on t.id = stt.taskid where stt.studentid= ". $this->user['id'] . " and stt.status!=2 and t.deadline<".time());
		$infolist[2]['num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('pte_studenttask') . " where studentid= ". $this->user['id'] . " and status=2");
		$infolist[3]['num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('pte_studenttask') . " where studentid= ". $this->user['id'] . " and status=2");
	}
	if($this->user['type'] == 'teacher'){
		$infolist = array();
		$infolist[0]['title']='布置作业数';
		$infolist[1]['title']='批改作业数';
		$infolist[2]['title']='学生总数';
		$infolist[3]['title']='班级总数';
		$infolist[0]['num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('pte_task')." where teacher=". $this->user['id']);
		$infolist[1]['num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('pte_task')." t left join ". tablename('pte_studenttask')." st on st.taskid = t.id
		 where st.tstatus=1 and t.teacher=". $this->user['id']);
		$infolist[2]['num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('pte_student'));
		$infolist[3]['num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('pte_class')." where schoolid=".$this->user['schoolid']);
	}
	include $this->template($this->user['type'].'/manager');
}