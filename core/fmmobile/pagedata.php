<?php
/**
 * 女神来了模块定义
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');
$op = $_GPC['op'];
$rid = $_GPC['rid'];
$item_per_page = $_GPC['pagesnum'];
$page_number = $_GPC['page'];
if(!is_numeric($page_number)){
	header('HTTP/1.1 500 Invalid page number!');
	exit();
}

$position = ($page_number * $item_per_page);
$where = '';
if (!empty($_GPC['keyword'])) {
	$keyword = trim($_GPC['keyword']);
	if (is_numeric($keyword) && empty($op))
		$where .= " AND uid = '".$keyword."'";
	else{
		$where .= " AND (nickname LIKE '%{$keyword}%' OR realname LIKE '%{$keyword}%' )";
	}

}

if(!empty($_GPC['keyword_school'])){
	//找到模糊搜索学校列表
	$school_val = pdo_fetchall('SELECT * FROM '.tablename('goddess_ly_school').' WHERE is_display = 0 and rid='.$rid.' and isvote=0 and name LIKE "%'.$_GPC['keyword_school'].'%"');
	if(!empty($school_val)){
		$where .= " AND school in (";
		foreach ($school_val as $key => $value) {
			$where .= $value['id'];
			if($key + 1 < count($school_val))
				$where .= ',';
		}
		$where .= ')';
	}else{
		$where .= " AND school = -1";
	}
}
$where .= " AND status = '1'";

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
			if(!$is_school_vote)//后台学校是否开启
				$where .=" AND school = ".$select_info['school'];
			else
				$where .=" AND school = -1";
		}
	}else  //赛区未开启
		$where .= " AND area = -1";
}

$tagid = $_GPC['tagid'];
$tagpid = $_GPC['tagpid'];
$tagtid = $_GPC['tagtid'];

if (!empty($tagid)) {
	$where .= " AND tagid = '".$tagid."'";
}
if (!empty($tagpid)) {
	$where .= " AND tagpid = '".$tagpid."'";
}
if (!empty($tagtid)) {
	$where .= " AND tagtid = '".$tagtid."'";
}
if ($_GPC['indexorder'] == '1') {
	$where .= " ORDER BY `istuijian` DESC, `createtime` DESC";
}elseif ($_GPC['indexorder'] == '11') {
	$where .= " ORDER BY `istuijian` DESC, `createtime` ASC";
}elseif ($_GPC['indexorder'] == '2') {
	$where .= " ORDER BY `istuijian` DESC, `uid` DESC, `id` DESC";
}elseif ($_GPC['indexorder'] == '22') {
	$where .= " ORDER BY `istuijian` DESC, `uid` ASC, `id` ASC";
}elseif ($_GPC['indexorder'] == '3') {
	$where .= " ORDER BY `istuijian` DESC, `photosnum` + `xnphotosnum` DESC";
}elseif ($_GPC['indexorder'] == '33') {
	$where .= " ORDER BY `istuijian` DESC, `photosnum` + `xnphotosnum` ASC";
}elseif ($_GPC['indexorder'] == '4') {
	$where .= " ORDER BY `istuijian` DESC, `hits` + `xnhits` DESC";
}elseif ($_GPC['indexorder'] == '44') {
	$where .= " ORDER BY `istuijian` DESC, `hits` + `xnhits` ASC";
}elseif ($_GPC['indexorder'] == '5') {
	$where .= " ORDER BY `istuijian` DESC, `vedio` DESC, `music` DESC, `id` DESC";
}else {
	$where .= " ORDER BY `istuijian` DESC, `id` DESC";
}
$userlist = pdo_fetchall('SELECT * FROM '.tablename($this->table_users).' WHERE rid = :rid AND istuijian <> 1 '.$where.'  LIMIT ' . $position . ',' . $item_per_page, array(':rid' => $rid) );

$tjlist = pdo_fetchall('SELECT * FROM '.tablename($this->table_users).' WHERE rid = :rid AND istuijian = 1 '.$where.'  LIMIT ' . $position . ',' . $item_per_page, array(':rid' => $rid) );

if (!empty($userlist)){
	if ($_GPC['tmoshi'] == 1 || $_GPC['tmoshi'] == 2 || $_GPC['tmoshi'] == 3) {
		foreach ($tjlist as $mid => $row) {
			$fmimage = $this->getpicarr($uniacid,$rid, $row['from_user'],1);
			if ($row['realname']){
				$usernames = cutstr($row['realname'], '6');
			}else{
				$usernames = cutstr($row['nickname'], '6');
			}
			$result = $result.'<input type="hidden" name="ucreatetime" id="ucreatetime" value="'.$row['createtime'].'" />';

			// $istuijianli = ' width:100%;height:auto;margin-bottom:20px;';
			// $istuijianxspic = ' width:100%;height:auto;';
			$istuijianimg = ' max-height:100%;';

			$result = $result.'<li style="cursor: pointer; '.$istuijianli.'"><div class="li_box">';


			if ($_GPC['moshi'] == 2) {
				$result = $result.'<a href="'.$this->createMobileUrl('tuser', array('rid' => $rid, 'tfrom_user'=> $row['from_user'])).'">';
			}else {
				$result = $result.'<a href="'.$this->createMobileUrl('tuserphotos', array('rid' => $rid, 'tfrom_user'=> $row['from_user'])).'">';
			}

			$result = $result.'<div class="xs_pic"  style=" margin: 0px 0px 0px 0px;" >';

			$result = $result.'<img src="'.$this->getphotos($fmimage['photos'], $row['avatar'],  $row['avatar']).'">';

			$result = $result.'<span style="  left: 0px;  top: 0px;  position: absolute;  color: #fff;  background: #F46767;  padding: 1px 6px;  border-radius: 0px;">ID: '.$row['uid'].'</span>';
					// $result = $result.'<div class="biaozhu_s" style="font-size:12px;">';
					// $result = $result.$usernames;
			$result = $result.'<span style="  right: 2px;  top: 2px;  position: absolute;  color: #fff;  background: rgba(0, 0, 0, 0.51);  padding: 1px 6px;  border-radius: 5px;">推荐</span>';
			$result = $result.'</div></div></a>';
			$result = $result.'<div class="toupiao" id="'.$row['uid'].'" style=" padding: 0px 0px 0px;  "><div class="ly_name">'.$usernames.'<span style="float:right;padding-right: 0.5vw">名次：'.$this->GetPaihangchaa($rid,$row['from_user'],1)['rank'].'</span></div>';
			$piaoshu = ($row['photosnum'] + $row['xnphotosnum']);
			$result = $result.'<dd style="text-decoration: none;position:relative;top:-2vw">';
			if ($_GPC['tmoshi'] == 3) {
				$result = $result.'<span id="photosnum'.$row['from_user'].'" class="ly_piao" style="padding-left:4vw">'.$piaoshu.'票</span>';
			}else{
				$result = $result.'<span id="photosnum'.$row['from_user'].'" class="ly_piao" style="margin-left:10px;">'.$piaoshu.' </span><span class="piaoshu">票</span>';
			}
			if ($_GPC['tpname']) {
				$result = $result.'<a href="'.$this->createMobileUrl("tuserphotos",array("rid"=>$rid,"tfrom_user"=>$row['from_user'])).'" id="'.$row['uid'].'" class="btn-danger"  data-toggle="tooltip" data-placement="top"';
				$result = $result.'style="font-size: 11px;position:absolute;right:0;bottom:1vw ">投一票</a>';

			}

			$result = $result.'</dd></div></div></li>';

		}

		foreach ($userlist as $mid => $row) {
			$fmimage = $this->getpicarr($uniacid,$rid, $row['from_user'],1);
			if ($row['realname']){
				$usernames = cutstr($row['realname'], '6');
			}else{
				$usernames = cutstr($row['nickname'], '6');
			}
			$result = $result.'<input type="hidden" name="ucreatetime" id="ucreatetime" value="'.$row['createtime'].'" />';

			if ($_GPC['tmoshi'] == 1) {
				$result = $result.'<li style="cursor: pointer; "><div class="li_box">';
			}elseif ($_GPC['tmoshi'] == 2) {
				if (($mid+1)%2 == 1) {
					$result = $result.'<li style="cursor: pointer;  "><div class="li_box">';
				}else{
					$result = $result.'<li style="cursor: pointer;  margin: 0px 0% 8px 2%;"><div class="li_box">';
				}
			}else{
				$result = $result.'<li style="cursor: pointer; "><div class="li_box">';
			}



			if ($_GPC['moshi'] == 2) {
				$result = $result.'<a href="'.$this->createMobileUrl('tuser', array('rid' => $rid, 'tfrom_user'=> $row['from_user'])).'">';
			}else {
				$result = $result.'<a href="'.$this->createMobileUrl('tuserphotos', array('rid' => $rid, 'tfrom_user'=> $row['from_user'])).'">';
			}

			$result = $result.'<div class="xs_pic"  style=" margin: 0px 0px 0px 0px;" >';

			$result = $result.'<img src="'.$this->getphotos($fmimage['photos'], $row['avatar'],  $row['avatar']).'">';

			$result = $result.'<span style="  left: 0px;  top: 0px;  position: absolute;  color: #fff;  background: #F46767;  padding: 1px 6px;  border-radius: 0px;">ID: '.$row['uid'].'</span>';
					// $result = $result.'<div class="biaozhu_s" style="font-size:12px;">';
					// $result = $result.$usernames;
			$result = $result.'</div></div></a>';
			$result = $result.'<div class="toupiao" id="'.$row['uid'].'" style=" padding: 0px 0px 0px;  "><div class="ly_name">'.$usernames.'<span style="float:right;padding-right: 0.5vw">名次：'.$this->GetPaihangchaa($rid,$row['from_user'],1)['rank'].'</span></div>';
			$piaoshu = ($row['photosnum'] + $row['xnphotosnum']);
			$result = $result.'<dd style="text-decoration: none;position:relative;top:-2vw">';
			if ($_GPC['tmoshi'] == 3) {
				$result = $result.'<span id="photosnum'.$row['from_user'].'" class="ly_piao" style="padding-left:4vw">'.$piaoshu.'票</span>';
			}else{
				$result = $result.'<span id="photosnum'.$row['from_user'].'" class="ly_piao" style="margin-left:10px;">'.$piaoshu.' </span><span class="piaoshu">票</span>';
			}
			if ($_GPC['tpname']) {
				$result = $result.'<a href="'.$this->createMobileUrl("tuserphotos",array("rid"=>$rid,"tfrom_user"=>$row['from_user'])).'" id="'.$row['uid'].'" class="btn-danger"  data-toggle="tooltip" data-placement="top"';
				$result = $result.'style="font-size: 11px;position:absolute;right:0;bottom:1vw ">投一票</a>';

			}

			$result = $result.'</dd></div></div></li>';

		}

		print_r($result);
	}else {
		foreach ($userlist as $mid => $row) {
			$fmimage = $this->getpicarr($uniacid,$rid, $row['from_user'],1);
			if (($mid+1)%2 == 1) {
				continue;
			}

			if ($row['realname']){
				$usernames = cutstr($row['realname'], '4');
			}else{
				$usernames = cutstr($row['nickname'], '4');
			}
			$result = $result.'<input type="hidden" name="ucreatetime" id="ucreatetime'.$row['from_user'].'" value="'.$row['createtime'].'" />';
			if ($page_number%2 == 0) {
				$result = $result.'<li style="cursor: pointer;"><div class="li_box">';
			}else {
				$result = $result.'<li style="cursor: pointer;margin: 0px 0% 8px 2%;"><div class="li_box">';
			}




			if ($_GPC['moshi'] == 2) {
				$result = $result.'<a href="'.$this->createMobileUrl('tuser', array('rid' => $rid, 'tfrom_user'=> $row['from_user'])).'">';
			}else {
				$result = $result.'<a href="'.$this->createMobileUrl('tuserphotos', array('rid' => $rid, 'tfrom_user'=> $row['from_user'])).'">';
			}
			$result = $result.'<div class="xs_pic">';

			$result = $result.'<img src="'.$this->getphotos($fmimage['photos'], $row['avatar'],  $row['avatar']).'">';

			$result = $result.'<span style="  left: 6px;  top: 6px;  position: absolute;  color: #fff;  background: rgba(0, 0, 0, 0.51);  padding: 1px 6px;  border-radius: 5px;">ID: '.$row['uid'].'</span>';
			$result = $result.'<div class="biaozhu_s" style="font-size:12px;">';
			$result = $result.$usernames;
			$result = $result.'</div></div></a>';
			$result = $result.'<div class="toupiao" id="'.$row['uid'].'" style=" padding: 0px 10px 0px;  height: 70px;">';
			$piaoshu = ($row['photosnum'] + $row['xnphotosnum']);
			$result = $result.'<span class="piaoshu">'.$_GPC['tpsname'].' '.$piaoshu.'</span>';
			$result = $result.'<dd style="text-align:center;  text-decoration: none;">';

			if ($_GPC['tpname']) {
				$result = $result.'<a href="javascript:void(0)" id="'.$row['uid'].'" class="btn  btn-danger"  data-toggle="tooltip" data-placement="top" ';
				$result = $result.'onclick="tvotep(\''.$row['from_user'].'\', \''.$usernames.'\')"';
				$result = $result.'style="font-size: 12px;">'.$_GPC['tpname'].'</a>';

			}
			$result = $result.'</dd></div></div></li>';
		}
		print_r($result);
		if ($_GPC['pagedatas'] == 'fr') {
			foreach ($userlist as $mid => $row) {
				$fmimage = $this->getpicarr($uniacid,$rid, $row['from_user'],1);
				if (($mid+1)%2 == 0) {
					continue;
				}

				if ($row['realname']){
					$usernames = cutstr($row['realname'], '4');
				}else{
					$usernames = cutstr($row['nickname'], '4');
				}
				$resultr = $resultr.'<input type="hidden" name="ucreatetime" id="ucreatetime'.$row['from_user'].'" value="'.$row['createtime'].'" />';

				if (($page_number + 1)%2 == 0) {
					$resultr = $resultr.'<li style="cursor: pointer;"><div class="li_box">';
				}else {
					$resultr = $resultr.'<li style="cursor: pointer;margin: 0px 0% 8px 2%;"><div class="li_box">';
				}

				if ($_GPC['moshi'] == 2) {
					$resultr = $resultr.'<a href="'.$this->createMobileUrl('tuser', array('rid' => $rid, 'tfrom_user'=> $row['from_user'])).'">';
				}else {
					$resultr = $resultr.'<a href="'.$this->createMobileUrl('tuserphotos', array('rid' => $rid, 'tfrom_user'=> $row['from_user'])).'">';
				}
				$resultr = $resultr.'<div class="xs_pic">';

				$resultr = $resultr.'<img src="'.$this->getphotos($fmimage['photos'], $row['avatar'],  $row['avatar']).'">';

				$resultr = $resultr.'<span style="  left: 6px;  top: 6px;  position: absolute;  color: #fff;  background: rgba(0, 0, 0, 0.51);  padding: 1px 6px;  border-radius: 5px;">ID: '.$row['uid'].'</span>';
				$resultr = $resultr.'<div class="biaozhu_s" style="font-size:12px;"><img src="'.toimage($row['avatar']).'" width="35" style="border-radius: 35px;margin-right:10px;width:15px;">';
				$resultr = $resultr.$usernames;
				$resultr = $resultr.'</div></div></a>';
				$resultr = $resultr.'<div class="toupiao" id="'.$row['uid'].'" style=" padding: 0px 10px 0px;  height: 70px;">';
				$piaoshu = ($row['photosnum'] + $row['xnphotosnum']);
				$resultr = $resultr.'<span class="piaoshu">'.$_GPC['tpsname'].' '.$piaoshu.'</span>';
				$resultr = $resultr.'<dd style="text-align:center;  text-decoration: none;">';

				if ($_GPC['tpname']) {
					$resultr = $resultr.'<a href="javascript:void(0)" id="'.$row['uid'].'" class="btn  btn-danger"  data-toggle="tooltip" data-placement="top" ';
					$resultr = $resultr.'onclick="tvotep(\''.$row['from_user'].'\', \''.$usernames.'\')"';
					$resultr = $resultr.'style="font-size: 12px;">'.$_GPC['tpname'].'</a>';

				}
				$resultr = $resultr.'</dd></div></div></li>';
			}
			print_r($resultr);
		}

	}
}else{
	if(!empty($_GPC['keyword']) && empty($_GPC['keyword_school'])){
		print_r('<div style="text-align:center">搜索人员没有参赛<div>');
	}elseif(!empty($_GPC['keyword_school']) && empty($_GPC['keyword'])){
		print_r('<div style="text-align:center">搜索学校暂无开通,请期待<div>');
	}elseif(!empty($_GPC['keyword_school']) && !empty($_GPC['keyword'])){
		print_r('<div style="text-align:center">没有找到搜索结果<div>');
	}else{
		if($is_area_vote)
			print_r('<div style="text-align:center;margin-top:1.5em">本赛区投票尚未开始<div>');
		elseif($is_school_vote)
			print_r('<div style="text-align:center;margin-top:1.5em">本校区投票尚未开始<div>');
		else
			print_r('<div style="text-align:center;margin-top:1.5em">暂无参赛选手<div>');
	}
}
