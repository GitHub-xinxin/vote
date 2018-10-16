<?php
!empty($_GPC['op'])?  $operation = $_GPC['op'] : $operation = 'list';

if($operation == 'list'){
	$area_list = pdo_getall('goddess_game_area',array('rid'=>$rid,'uniacid'=>$_W['uniacid']));
}elseif($operation == 'add'){
	if(checksubmit()){
		$data = [
			'name'=>$_GPC['name'],
			'uniacid'=>$_W['uniacid'],
			'rid'=>$rid
		];
		if(empty($_GPC['id'])){
			$res = pdo_insert('goddess_game_area',$data);
		}else{
			$res = pdo_update('goddess_game_area',$data,array('id'=>$_GPC['id']));
		}
		if($res)
			message('操作成功',$this->createWebUrl('ly_game_area',array('op'=>'list','rid'=>$rid)),'success');
		else
			message('操作失败','','error');
	}else{
		$area = pdo_get('goddess_game_area',array('id'=>$_GPC['id']));
	}
	
}elseif($operation == 'del'){

	$res = pdo_delete('goddess_game_area',array('id'=>$_GPC['id']));
	if($res)
		message('删除成功',$this->createWebUrl('ly_game_area',array('op'=>'list','rid'=>$rid)),'success');
	else
		message('删除失败','','error');
}

include $this->template('web/ly_school');
?>