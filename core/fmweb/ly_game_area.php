<?php
!empty($_GPC['op'])?  $operation = $_GPC['op'] : $operation = 'list';

if($_W['isajax']){
	if($_GPC['oper'] == 'vote'){
		$is_vote = pdo_get('goddess_game_area',array('id'=>$_GPC['id']))['isvote'];
		$is_vote == 1? $is_vote = 0 : $is_vote = 1;
		$res = pdo_update('goddess_game_area',array('isvote'=>$is_vote),array('id'=>$_GPC['id']));
		$resArr['code'] = empty($res)? flase : true;
		$resArr['msg'] = $is_vote;
	}else{
		$is_dis = pdo_get('goddess_game_area',array('id'=>$_GPC['id']))['is_display'];
		if($is_dis == 0)
			$is_dis = 1;
		else
			$is_dis = 0;
		$res = pdo_update('goddess_game_area',array('is_display'=>$is_dis),array('id'=>$_GPC['id']));
		$resArr['code'] = empty($res)? flase : true;
		$resArr['msg'] = $is_dis;
	}
	
	echo json_encode($resArr);exit;
}
if($operation == 'list'){
	if(checksubmit())
		$area_list = pdo_fetchall('SELECT * FROM ims_goddess_game_area WHERE uniacid='.$uniacid.' AND rid='.$rid.' AND name like "%'.$_GPC['keyword'].'%"');
	else
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

include $this->template('web/ly_game_area');
?>