<?php

//是否已经报名参赛

$area = pdo_getall('goddess_game_area',array('rid'=>$rid));
if($_W['isajax']){
	$resArr['data'] = pdo_getall('goddess_ly_school',array('uniacid'=>$uniacid,'rid'=>$rid,'area'=>$_GPC['area']));
	echo json_encode($resArr);exit;
}
if(checksubmit()){
	//更新用户表中赛区字段
	if(empty($_GPC['school']))
		$res = pdo_update('goddess_voteer',array('area'=>$_GPC['area'],'school'=>0),array('rid'=>$rid,'from_user'=>$from_user));
	else
		$res = pdo_update('goddess_voteer',array('area'=>$_GPC['area'],'school'=>$_GPC['school']),array('rid'=>$rid,'from_user'=>$from_user));
	$is_has = pdo_get('goddess_voteer',array('rid'=>$rid,'from_user'=>$from_user))['area'];
	if(empty($is_has))
		message('赛区选择失败',$this->createMobileUrl('ly_area',array('rid'=>$rid)),'error');
	else{
		header('Location:'.$_W['siteroot'].'app/index.php?i='.$_W['uniacid'].'&c=entry&rid='.$rid.'&do=ly_index&m=goddess');
		exit();
	}
}
$select_info = pdo_get('goddess_voteer',array('rid'=>$rid,'from_user'=>$from_user));
if(!empty($select_info['area'])){
	$schools = pdo_getall('goddess_ly_school',array('rid'=>$rid,'uniacid'=>$uniacid,'area'=>$select_info['area']));
}
if (!empty($rshare['sharelink'])) {
	$_share['link'] = $rshare['sharelink'];
}else{
	$_share['link'] = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('ly_area', array('rid' => $rid));
//分享URL
}
$_share['title'] = $this -> get_share($uniacid, $rid, $tfrom_user, $rshare['sharetitle']);
$_share['content'] = $this -> get_share($uniacid, $rid, $tfrom_user, $rshare['sharecontent']);
$_share['imgUrl'] = $this -> getphotos($rshare['sharephoto'], $rshare['sharephoto'], $rshare['sharephoto']);
include $this->template('ly_area');