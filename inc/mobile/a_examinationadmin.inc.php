<?php
global $_GPC, $_W;
//获取模块设置参数
$this->module['config'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$thistime = date('Y-m-d H:s', time());
$infolist = $this->authentication();
if ($operation == 'display') {
    $status = -1;
    $uid = $_W['member']['uid'];
    //获取页数
    $pindex = max(1, intval($_GPC['page']));
    //获取页行数
    $psize = 5;
    //使用拼接sql语句
    $sql = "SELECT * FROM " . tablename('pte_examination') . "where status!=" . $status;
    $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    $examinationlist = pdo_fetchall($sql);
    $total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('pte_examination')) . "where status!=" . $status;
    //（前台用pager标签可以直接显示）
    $pager = pagination($total, $pindex, $psize);
    include $this->template('admin/examination');
}
if ($operation == 'status') {
    $id = $_GPC['id'];
    $examinat_type = $_GPC['examinat_type'];
    $status =0;
    if($examinat_type =='delete'){
        $status = -1;
    }
    if($examinat_type =='open'){
        $status = 1;
    }
    if (empty($id)) {
        $this->my_message(202, '', 'id不存在');
    }
    $updata_data = array(
        'status' => $status,
    );
    $result = pdo_update('pte_examination', $updata_data, array('id' => $id));

    if (!empty($result)) {
        if($status_type =='delete'){
            pdo_update('pte_examination_detail',['status'=>-1], ['ex_id' => $id]);
        }
        if($status_type =='open'){
            pdo_update('pte_examination_detail',['status'=>1], ['ex_id' => $id]);
        }
        $this->my_message(200, '', '操作成功');
    } else {
        $this->my_message(202, '', '操作失败');
    }
}if ($operation == 'getdetail') {
    $id = $_GPC['id'];
    if (empty($id)) {
        $this->my_message(202, '', 'id不存在');
    }
    $sql = "SELECT * FROM " . tablename('pte_examination_detail') . "where ex_id=" . $id;
    $ground_array = pdo_fetchall($sql);
    $array = "";
    if (!empty($ground_array)) {
        foreach ($ground_array as $item) {
            $array .= '姓名：' . $item['student_name'] . '   学号：' . $item['student_num'] . ';'.  '  科目：' . $item['subject_name'] . ';'.  '   成绩：' . $item['subject_num'] . ';'."\n";
        }
        $this->my_message(200, $array, '删除成功');
    } else {
        $this->my_message(202, '', '查看失败');
    }
}if ($operation == 'add') {
    $file = $_FILES['excel'];
    if ($file['name'] && $file['error'] == 0) {
        $type = @end(explode('.', $file['name']));
        $type = strtolower($type);
        if (!in_array($type, array('xls', 'xlsx'))) {
            $this->my_message('202', '', '文件类型错误');
        }
        if (empty($_GPC['title'])) {
            $this->my_message(201, '', 'title错误');
        }
        if (empty($_GPC['ex_time'])) {
            $this->my_message(201, '', 'ex_time错误');
        }
        $insert_data = array(
            'title' => $_GPC['title'],
            'ex_time' => strtotime($_GPC['ex_time']),
            'createtime' => time(),
        );
        $res = pdo_insert('pte_examination', $insert_data);
        $g_id = pdo_insertid();
        if ($g_id) {
            //开始导入
            set_time_limit(0);
            include_once IA_ROOT . '/framework/library/phpexcel/PHPExcel.php';
            /** PHPExcel_IOFactory */
            include_once IA_ROOT . '/framework/library/phpexcel/PHPExcel/IOFactory.php';
            if ($type == 'xls') {
                $inputFileType = 'Excel5'; //这个是读 xls的
            } else {
                $inputFileType = 'Excel2007'; //这个是计xlsx的
            }
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($file['tmp_name']);
            $objWorksheet = $objPHPExcel->getActiveSheet(); //取得总数据
            $highestRow = $objWorksheet->getHighestRow(); //取得总行数

            for ($row = 1; $row <= $highestRow; $row++) {
                $student_name = $objWorksheet->getCellByColumnAndRow(0, $row)->getValue();
                $student_num = $objWorksheet->getCellByColumnAndRow(1, $row)->getValue();
                for ($i = 2; $i <= $highestRow; $i=$i+2) {
                    $subject_name = $objWorksheet->getCellByColumnAndRow($i, $row)->getValue();
                    $subject_num = $objWorksheet->getCellByColumnAndRow($i+1, $row)->getValue();
                    $insert_data_d = array(
                        'ex_id' => $g_id,
                        'student_num' => $student_num,
                        'student_name' => $student_name,
                        'subject_name' => $subject_name,
                        'subject_num' => $subject_num,
                    );
                    pdo_insert('pte_examination_detail', $insert_data_d);
                }            
            }
            $this->my_message(200, '', '添加成功');
        } else {
            $this->my_message(201, '', '添加失敗');
        }
    }
    $this->my_message(202, '', '上传文件失败');
}if ($operation == 'edit') {
    $id = $_GPC['id'];
    if (empty($_GPC['title'])) {
        $this->my_message(201, '', 'title错误');
    }
    if (empty($_GPC['ex_time'])) {
        $this->my_message(201, '', 'ex_time');
    }
    $file = $_FILES['excel'];
    $update_data = array(
        'title' => $_GPC['title'],
        'ex_time' => strtotime($_GPC['ex_time']),
        'createtime' => time(),
    );
    $res = pdo_update('pte_examination', $update_data, ['id' => $id]);
    if (empty($file)) {
        if ($res) {
            $this->my_message(200, '', '更新成功');
        } else {
            $this->my_message(201, '', '更新失败');
        }
    } else {
        pdo_delete('pte_examination_detail', ['ex_id' => $id]);
        if ($file['name'] && $file['error'] == 0) {
            $type = @end(explode('.', $file['name']));
            $type = strtolower($type);
            if (!in_array($type, array('xls', 'xlsx'))) {
                $this->my_message('202', '', '文件类型错误');
            }
            //开始导入
            set_time_limit(0);
            include_once IA_ROOT . '/framework/library/phpexcel/PHPExcel.php';
            /** PHPExcel_IOFactory */
            include_once IA_ROOT . '/framework/library/phpexcel/PHPExcel/IOFactory.php';
            if ($type == 'xls') {
                $inputFileType = 'Excel5'; //这个是读 xls的
            } else {
                $inputFileType = 'Excel2007'; //这个是计xlsx的
            }
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($file['tmp_name']);
            $objWorksheet = $objPHPExcel->getActiveSheet(); //取得总行数
            $highestRow = $objWorksheet->getHighestRow(); //取得总列数
            for ($row = 1; $row <= $highestRow; $row++) {
                $student_name = $objWorksheet->getCellByColumnAndRow(0, $row)->getValue();
                $student_num = $objWorksheet->getCellByColumnAndRow(1, $row)->getValue();
                for ($i = 2; $i <= $highestRow;$i=$i+2) {
                    $subject_name = $objWorksheet->getCellByColumnAndRow($i, $row)->getValue();
                    $subject_num = $objWorksheet->getCellByColumnAndRow($i+1, $row)->getValue();
                    $insert_data_d = array(
                        'ex_id' => $id,
                        'student_num' => $student_num,
                        'student_name' => $student_name,
                        'subject_name' => $subject_name,
                        'subject_num' => $subject_num,
                    );
                    pdo_insert('pte_examination_detail', $insert_data_d);
                }
            }
            $this->my_message(200, '', '操作成功');
        }
    }
}
