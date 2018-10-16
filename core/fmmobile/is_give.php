<?php

if($_W['isajax']){

	// 是否已经赠送转发票
	$is_give = pdo_get('goddess_voteer',array('rid'=>$rid,'from_user'=>$from_user))['is_share'];
	$res = false;
	if(!$is_give){
		//找到今天可用的投票数 + 5
		
		//今天剩余票数
		$where = "";
		$starttime = mktime(0,0,0);//当天：00：00：00
		$endtime = $starttime + 86399;//当天：23：59：59
		$where .= ' AND createtime >=' .$starttime;
		$where .= ' AND createtime <=' .$endtime;
		//有无今天投票记录
		$counter = pdo_fetch("SELECT * FROM ".tablename($this->table_counter)." WHERE rid = :rid AND from_user = :from_user AND type = :type $where", array(':rid' => $rid,':from_user' => $from_user,':type' => 2));

		if($counter){
			//如果有当前票数  将今天票数-5 （因为票数是累计的） 
			if(pdo_update('goddess_counter',array('tp_times -='=>5),array('id'=>$counter['id'])))
				$res = true;
		}else{
			//没有记录 添加今天记录
			$insert_data = [
				'uniacid'=>$_W['uniacid'],
				'rid'=>$rid,
				'from_user'=>$from_user,
				'type'=>2,
				'tp_times'=>-5,
				'createtime'=>time()
			];
			if(pdo_insert($this->table_counter,$insert_data))
				$res = true;
		}
		if($res)
			pdo_update('goddess_voteer',array('is_share'=>1),array('rid'=>$rid,'from_user'=>$from_user));
	}
	echo json_encode($res);exit;
}
?>