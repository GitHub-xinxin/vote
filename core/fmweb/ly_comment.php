<?php

if(checksubmit()){
	$insert_data = [
		'rid'=>$rid,
		'uniacid'=>$_W['uniacid'],
		'comment'=>$_GPC['info']
	];

	if(!empty($_GPC['id'])){
		$res = pdo_update('goddess_comment',$insert_data,array('id'=>$_GPC['id']));
	}else{
		$res = pdo_insert('goddess_comment',$insert_data);
	}
	if($res)
		message('操作成功',$this->createWebUrl('ly_comment',array('rid'=>$rid)),'success');
	else
		message('操作失败',$this->createWebUrl('ly_comment',array('rid'=>$rid)),'error');
}
$info = pdo_get('goddess_comment',array('rid'=>$rid));

include $this->template('web/ly_comment');
?>