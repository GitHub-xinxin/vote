<?php
!empty($_GPC['op'])?  $operation = $_GPC['op'] : $operation = 'list';
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
if($_W['isajax']){
	if($_GPC['oper'] == 'check'){
		/**
		 * 检查录入的学校名称是否重复
		 */
		$school_name = trim($_GPC['name']);
		$ishas = pdo_get('goddess_ly_school',array('rid'=>$rid,'uniacid'=>$uniacid,'name'=>$school_name));
		if(empty($_GPC['id'])){
			if($ishas)
				$resArr['code'] = 1;
			else
				$resArr['code'] = 0;
		}else{
			//是否是修改
			if($ishas['id'] == trim($_GPC['id']))
				$resArr['code'] = 0;
			else
				$resArr['code'] = 1;
		}

	}elseif($_GPC['oper'] == 'add'){
		$data = [
			'name'=>$_GPC['name'],
			'area'=>$_GPC['area'],
			'uniacid'=>$_W['uniacid'],
			'rid'=>$rid
		]; 
		if(empty($_GPC['id'])){
			$res = pdo_insert('goddess_ly_school',$data);
		}else{
			$res = pdo_update('goddess_ly_school',$data,array('id'=>$_GPC['id']));
		}
		if($res)
			$resArr['code'] = 0;
		else
			$resArr['code'] = 1;

	}elseif($_GPC['oper'] == 'school'){
		$resArr['data'] = pdo_getall('goddess_ly_school',array('rid'=>$rid,'uniacid'=>$_W['uniacid'],'area'=>$_GPC['sid']));
	}elseif($_GPC['oper'] == 'vote'){
		$is_vote = pdo_get('goddess_ly_school',array('id'=>$_GPC['id']))['isvote'];
		$is_vote == 1? $is_vote = 0 : $is_vote = 1;
		$res = pdo_update('goddess_ly_school',array('isvote'=>$is_vote),array('id'=>$_GPC['id']));
		$resArr['code'] = empty($res)? flase : true;
		$resArr['msg'] = $is_vote;
	}else{
		$is_dis = pdo_get('goddess_ly_school',array('id'=>$_GPC['id']))['is_display'];
		$is_dis == 1? $is_dis = 0 : $is_dis = 1;
		$res = pdo_update('goddess_ly_school',array('is_display'=>$is_dis),array('id'=>$_GPC['id']));
		$resArr['code'] = empty($res)? flase : true;
		$resArr['msg'] = $is_dis;
	}
	echo json_encode($resArr);exit;
}

if($operation == 'list'){ 
	if(checksubmit()){
		$school_list = pdo_fetchall('SELECT * FROM ims_goddess_ly_school WHERE uniacid='.$uniacid.' AND rid='.$rid.' AND name like "%'.$_GPC['keyword'].'%"');
	}else{
		$total = pdo_fetchcolumn('SELECT COUNT(1) FROM '.tablename('goddess_ly_school').' WHERE rid = :rid', array(':rid'=>$rid));
		$pager = pagination($total, $pindex, $psize);
		$school_list = pdo_fetchall('SELECT * FROM '.tablename('goddess_ly_school').' WHERE rid = :rid LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':rid' => $rid) );
	}
	foreach ($school_list as $key => $value) {
		$school_list[$key]['area'] = pdo_get('goddess_game_area',array('id'=>$value['area']))['name'];
	}
}elseif($operation == 'add'){
	$school = pdo_get('goddess_ly_school',array('id'=>$_GPC['id']));
	$area_list = pdo_getall('goddess_game_area',array('rid'=>$rid,'uniacid'=>$_W['uniacid']));
	if(empty($_GPC['id']))
		$schools = pdo_getall('goddess_ly_school',array('rid'=>$rid,'uniacid'=>$_W['uniacid'],'area'=>$area_list[0]['id']));
	else
		$schools = pdo_getall('goddess_ly_school',array('rid'=>$rid,'uniacid'=>$_W['uniacid'],'area'=>$school['area']));
	
}elseif($operation == 'del'){

	$res = pdo_delete('goddess_ly_school',array('id'=>$_GPC['id']));
	if($res)
		message('删除成功',$this->createWebUrl('ly_school',array('op'=>'list','rid'=>$rid)),'success');
	else
		message('删除失败','','error');
}

include $this->template('web/ly_school');
?>