<?php 

if($_W['isajax']){
	//判断是否已经报名
	$user_info = pdo_get('goddess_provevote',array('rid'=>$rid,'from_user'=>$from_user));
	if(!empty($user_info['realname'])){
		$res = true;
	}else{
		$res = pdo_delete('goddess_provevote_headarr',array('rid'=>$rid,'from_user'=>$from_user,'head_img'=>2));
	}
	if($res)
		$data =true;
	else
		$data =false;
	echo json_encode($data);exit;
}
?>