<?php
/**
 * 女神来了模块定义 
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/  `hits` + `xnhits`
 */
defined('IN_IA') or exit('Access Denied');

$item_per_page = $_GPC['pagesnum'];
$page_number = intval($_GPC['page']);
if(!is_numeric($page_number)){
	header('HTTP/1.1 500 Invalid page number!');
	exit();
}
$position = ($page_number * $item_per_page);

$m = $position+1;
if ($_GPC['phdata'] == 'phb') {
	$tagid = $_GPC['tagid'];
	$tagpid = $_GPC['tagpid'];
	$tagtid = $_GPC['tagtid'];
	if ($_GPC['op'] == 'tags') {
		$where = ' AND status =1';
		update_tags_piaoshu($rid);

		if (!empty($tagid)) {
			$where .= " AND parentid = " . $tagid;
			$where .= " AND icon = 1";
		}elseif (!empty($tagpid)) {
			$where .= " AND parentid = " . $tagpid;
			$where .= " AND icon = 2";
		}elseif (!empty($tagtid)) {
			$where .= " AND parentid = " . $tagtid;
			$where .= " AND icon = 3";
		}
		/**
		 * 是否是开启赛区投票
		 */
		$select_info = pdo_get('goddess_voteer',array('rid'=>$rid,'from_user'=>$_GPC['from_user']));
		if(!empty($select_info['area'])){
			//判断赛区是否开始投票
			$is_area_vote = pdo_get('goddess_game_area',array('id'=>$select_info['area']))['isvote'];
			if(!$is_area_vote){
				//赛区开始投票
				$where .= " AND area = ".$select_info['area'];
				//是否开启学校投票模式
				$isschool_mode = pdo_get('goddess_reply',array('rid'=>$rid))['isschool_mode'];
				if($isschool_mode){
					//开启学校投票模式
					$is_school_vote = pdo_get('goddess_ly_school',array('id'=>$select_info['school']))['isvote'];
					if(!$is_school_vote)
						$where .=" AND school = ".$select_info['school'];//后台学校已开启
					else
						$where .=" AND school = -1";
				}
			}else  
			$where .= " AND area = -1";
		}

		$userlist = pdo_fetchall('SELECT * FROM '.tablename($this->table_users).' WHERE rid = :rid'.$where.' order by `hits` + `xnhits` desc  LIMIT ' . $position . ',' . $item_per_page, array(':rid' => $rid) );
		//output results from database
		if (!empty($userlist)){
			/**
			 * 获取头像
			 * @var [type]
			 */
			foreach ($userlist as $key => $value) {
				$user_head_img = pdo_get('goddess_provevote_headarr',array('from_user'=>$value['from_user'],'rid'=>$value['rid'],'head_img'=>1))['photos'];
				if(empty($user_head_img)){
					$user_head_img = "../attachment/images/global/head_img.png";
				}
				$userlist[$key]['head_img'] = $user_head_img;
			}
			foreach ($userlist as $mid => $row) {
				if ($row['realname']){
					$usernames = cutstr($row['realname'], '10');
				}else{
					$usernames = '未填';
				}

				$result = $result.'<li>';
				if ($rvote['moshi'] == 2) {
					$result = $result.'<a href="'.$this->createMobileUrl('tuser', array('rid' => $rid, 'tfrom_user'=> $row['from_user'])).'"><div style="width:15%;  text-align: center;" >';
				}else {
					$result = $result.'<a href="'.$this->createMobileUrl('tuserphotos', array('rid' => $rid, 'tfrom_user'=> $row['from_user'])).'"><div style="width:15%;  text-align: center;" >';
				}


				$mmid = $m+$mid;
				if ($page_number == 0) {


					if ($mid >= 0 && $mid < 3) {
						$pid = 'one'.($mid + 1);
					}else{
						$pid = 'two';
					}

					$result = $result.'<i class="'.$pid.'">';
					if ($mid >= 3) {
						$mida = $mid+1;
						$result = $result.$mida;
					}
					$result = $result.'</i>';

				}else{

					$result = $result.'<i class="two">'.$mmid.'</i>';
				}


				$result = $result.'</div><div style="width:35%; text-align: left;padding-left:4vw;" class="btext"><span style="  text-align: left;">';
				$result = $result.'<img src="'.toimage($row['head_img']).'"  style="width:8vw; height:8vw; border-radius: 50%;"/> '.$usernames.'</span></div>';

				if (!$rdisplay['open_vote_count']) {
					if (!empty($rdisplay['open_vote_size'])) {
						if ($rdisplay['open_vote_size'] > $mmid) {
							$pxnum = $row['photosnum'] + $row['xnphotosnum'];
						}else{
							$pxnum = '****';
						}
					}else{
						$pxnum = '****';
					}
				}else{
					$pxnum = $row['photosnum'] + $row['xnphotosnum'];
				}
				if ($cfg['isopenjsps']) {
					$result = $result.'<div style="width:16%;  text-align: center;" class="bnum">'.$pxnum.'</div>';
					$hxnum = $row['hits'] + $row['xnhits'];
					$result = $result.'<div style="width:16%;  text-align: center;" class="bnum">'.$hxnum.'</div>';
					$result = $result.'<div style="width:16%;  text-align: center;" class="bnum">'.$row['unphotosnum'].'</div>';
				}else{
					$result = $result.'<div style="width:24%;  text-align: center;" class="bnum">'.$pxnum.'</div>';
					$hxnum = $row['hits'] + $row['xnhits'];
					$result = $result.'<div style="width:24%;  text-align: center;" class="bnum">'.$hxnum.'</div>';
				}


				$result = $result.'</a></li>';


			}
		}else{
			$result = '<li style="text-align:center;">本赛区或学校投票尚未开始</li>';
		}

		
	}else{

		$where = '';
		$where .= " AND status = '1'";
		if (!empty($tagid)) {
			$where .= " AND tagid = '".$tagid."'";
		}
		if (!empty($tagpid)) {
			$where .= " AND tagpid = '".$tagpid."'";
		}
		if (!empty($tagtid)) {
			$where .= " AND tagtid = '".$tagtid."'";
		}
		/**
		 * 是否赛区开启投票
		 */
		$select_info = pdo_get('goddess_voteer',array('rid'=>$rid,'from_user'=>$_GPC['from_user']));
		if(!empty($select_info['area'])){
			//判断赛区是否开始投票
			$is_area_vote = pdo_get('goddess_game_area',array('id'=>$select_info['area']))['isvote'];
			if(!$is_area_vote){
				//赛区开始投票
				$where .= " AND area = ".$select_info['area'];
				//是否开启学校投票模式
				$isschool_mode = pdo_get('goddess_reply',array('rid'=>$rid))['isschool_mode'];
				if($isschool_mode){
					//开启学校投票模式
					$is_school_vote = pdo_get('goddess_ly_school',array('id'=>$select_info['school']))['isvote'];
					if(!$is_school_vote)
						$where .=" AND school = ".$select_info['school'];//后台学校已开启
					else
						$where .=" AND school = -1";
				}
			}else  
				$where .= " AND area = -1";
		}
		$where .= " ORDER BY `photosnum` + `xnphotosnum` DESC, `hits` + `xnhits` DESC";
		
		$userlist = pdo_fetchall('SELECT * FROM '.tablename($this->table_users).' WHERE rid = :rid '.$where.'  LIMIT ' . $position . ',' . $item_per_page, array(':rid' => $rid) );


						//output results from database
		if (!empty($userlist)){
			/**
			 * 获取头像
			 * @var [type]
			 */
			foreach ($userlist as $key => $value) {
				$user_head_img = pdo_get('goddess_provevote_headarr',array('from_user'=>$value['from_user'],'rid'=>$value['rid'],'head_img'=>1))['photos'];
				if(empty($user_head_img)){
					$user_head_img = "../attachment/images/global/head_img.png";
				}
				$userlist[$key]['head_img'] = $user_head_img;
			}
			foreach ($userlist as $mid => $row) {
				if ($row['realname']){
					$usernames = cutstr($row['realname'], '10');
				}else{
					$usernames = '未填';
				}

				$result = $result.'<li>';
				if ($rvote['moshi'] == 2) {
					$result = $result.'<a href="'.$this->createMobileUrl('tuser', array('rid' => $rid, 'tfrom_user'=> $row['from_user'])).'"><div style="width:15%;  text-align: center;" >';
				}else {
					$result = $result.'<a href="'.$this->createMobileUrl('tuserphotos', array('rid' => $rid, 'tfrom_user'=> $row['from_user'])).'"><div style="width:15%;  text-align: center;" >';
				}


				$mmid = $m+$mid;
				if ($page_number == 0) {


					if ($mid >= 0 && $mid < 3) {
						$pid = 'one'.($mid + 1);
					}else{
						$pid = 'two';
					}

					$result = $result.'<i class="'.$pid.'">';
					if ($mid >= 3) {
						$mida = $mid+1;
						$result = $result.$mida;
					}
					$result = $result.'</i>';

				}else{

					$result = $result.'<i class="two">'.$mmid.'</i>';
				}


				$result = $result.'</div><div style="width:35%;  text-align: left;padding-left:4vw;" class="btext"><span style="  text-align: left;">';
				$result = $result.'<img src="'.toimage($row['head_img']).'"  style="width:8vw; height:8vw; border-radius: 50%;"/> '.$usernames.'</span></div>';

				if (!$rdisplay['open_vote_count']) {
					if (!empty($rdisplay['open_vote_size'])) {
						if ($rdisplay['open_vote_size'] > $mmid) {
							$pxnum = $row['photosnum'] + $row['xnphotosnum'];
						}else{
							$pxnum = '****';
						}
					}else{
						$pxnum = '****';
					}
				}else{
					$pxnum = $row['photosnum'] + $row['xnphotosnum'];
				}
				if ($cfg['isopenjsps']) {
					$result = $result.'<div style="width:16%;  text-align: center;" class="bnum">'.$pxnum.'</div>';
					$hxnum = $row['hits'] + $row['xnhits'];
					$result = $result.'<div style="width:16%;  text-align: center;" class="bnum">'.$hxnum.'</div>';
					$result = $result.'<div style="width:16%;  text-align: center;" class="bnum">'.$row['unphotosnum'].'</div>';
				}else{
					$result = $result.'<div style="width:24%;  text-align: center;" class="bnum">'.$pxnum.'</div>';
					$hxnum = $row['hits'] + $row['xnhits'];
					$result = $result.'<div style="width:24%;  text-align: center;" class="bnum">'.$hxnum.'</div>';
				}


				$result = $result.'</a></li>';


			}
		}else{
			$result = '<li style="text-align:center">本赛区或学校投票尚未开始</li>';
		}

	}
}else{

	$where = '';
	if (!empty($tfrom_user)) {
		$where .= " AND tfrom_user = '".$tfrom_user."'";
	}
	$userlist = pdo_fetchall('SELECT * FROM '.tablename($this->table_log).' WHERE rid = :rid '.$where.' ORDER BY `id` DESC LIMIT ' . $position . ',' . $item_per_page, array(':rid' => $rid) );


					//output results from database
	if (!empty($userlist)){

		foreach ($userlist as $mid => $row) {
			if ($row['realname']){
				$usernames = cutstr($row['realname'], '5');
			}elseif ($row['nickname']){
				$usernames = cutstr($row['nickname'], '5');
			}else{
				$usernames = cutstr($row['from_user'], '5');
			}
 

			$result = $result.'<li><a href="javascript::;">';

			$result = $result.'<div style="width:35%; " class="btext">';
			$result = $result.'<img src="'.toimage($row['avatar']).'" width="30" style=" max-height:30px;  border-radius: 135px;"/> '.$usernames.'</div>';
			$result = $result.'<div style="width:17%;  line-height: 20px;text-align:center;" class="bnum">'.$row['vote_times'].'</div>';
			if ($row['is_del'] == 1) {
				$status = '<span style="color:red;" >无效</span>';
			}else{
				$status = '正常';
			}
			$result = $result.'<div style="width:17%;  line-height: 20px;text-align:center;" class="bnum">'.$status.'</div>';

			$ctime = date('Y-m-d H:i:s', $row['createtime']);
			$result = $result.'<div style="width:30%;  line-height: 20px;text-align:center;" class="bnum">'.$ctime.'</div>';
			$result = $result.'</a></li>';

		}
	}

}
print_r($result);