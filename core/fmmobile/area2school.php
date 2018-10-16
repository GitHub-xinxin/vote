<?php 

	if($_W['isajax']){
		$data = pdo_getall('goddess_ly_school',array('rid'=>$rid,'uniacid'=>$_W['uniacid'],'area'=>$_GPC['aid'],'is_display'=>0));

		echo json_encode($data);exit;
	}


 ?>