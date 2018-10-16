<?php

/**
 * 是否单学校投票模式
 */
$user = pdo_get('goddess_voteer',array('uniacid'=>$uniacid,'rid'=>$rid,'from_user'=>$from_user));
if($rbasic['isschool_mode']){
	$title_name = pdo_get('goddess_ly_school',array('rid'=>$rid,'uniacid'=>$uniacid,'id'=>$user['school']))['name'];
	if(empty($title_name)){
		$title_name = "请选择学校-";
	}else{
		$title_name .= "-";
	}
}else{
	//显示赛区
	$title_name = pdo_get('goddess_game_area',array('id'=>$user['area']))['name'].'-';
}

//是否已经报名参赛
$is_join = pdo_fetch("SELECT realname, nickname FROM " . tablename($this -> table_users) . " WHERE from_user = :from_user and rid = :rid and status = :status", array(':from_user' => $from_user, ':rid' => $rid,'status'=>1));

if (!empty($rshare['sharelink'])) {
	$_share['link'] = $rshare['sharelink'];
}else{
	$_share['link'] = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('ly_area', array('rid' => $rid));
//分享URL
}
$_share['title'] = $this -> get_share($uniacid, $rid, $tfrom_user, $rshare['sharetitle']);
$_share['content'] = $this -> get_share($uniacid, $rid, $tfrom_user, $rshare['sharecontent']);
$_share['imgUrl'] = $this -> getphotos($rshare['sharephoto'], $rshare['sharephoto'], $rshare['sharephoto']);
include $this->template('ly_index');