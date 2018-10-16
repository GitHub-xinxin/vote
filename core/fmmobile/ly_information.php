<?php

if($_W['isajax']){
	
	if(pdo_update('goddess_voteer',array('is_read'=>1),array('rid'=>$_GPC['rid'],'from_user'=>$_GPC['from_user'])))
		$res = true;
	else
		$res = false;
	echo json_encode($res);exit;
}

$content = pdo_get($this->table_reply,array('rid'=>$_GPC['rid']))['content'];

if (!empty($rshare['sharelink'])) {
	$_share['link'] = $rshare['sharelink'];
}else{ 
	$_share['link'] = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('ly_area', array('rid' => $rid));
//分享URL
}
$_share['title'] = $this -> get_share($uniacid, $rid, $tfrom_user, $rshare['sharetitle']);
$_share['content'] = $this -> get_share($uniacid, $rid, $tfrom_user, $rshare['sharecontent']);
$_share['imgUrl'] = $this -> getphotos($rshare['sharephoto'], $rshare['sharephoto'], $rshare['sharephoto']);

include $this->template('ly_information');