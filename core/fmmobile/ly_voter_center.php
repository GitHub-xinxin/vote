<?php
/**
 * 女神来了模块定义
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 * (c) Copyright 2016 FantasyMoons. All Rights Reserved.
 */
defined('IN_IA') or exit('Access Denied');
//是否已经报名参赛
$is_join = pdo_fetch("SELECT realname, nickname FROM " . tablename($this -> table_users) . " WHERE from_user = :from_user and rid = :rid and status = :status", array(':from_user' => $from_user, ':rid' => $rid,'status'=>1));

/**
 * 查找所有投票记录
 */
//总投票数
$count = pdo_fetch('select sum(vote_times) as count from ims_goddess_votelog where from_user ="'.$from_user.'" and rid = '.$rid)['count'];

//今天剩余票数
$where = "";
$starttime = mktime(0,0,0);//当天：00：00：00
$endtime = $starttime + 86399;//当天：23：59：59
$where .= ' AND createtime >=' .$starttime;
$where .= ' AND createtime <=' .$endtime;
//有无今天投票记录
$counter = pdo_fetch("SELECT * FROM ".tablename($this->table_counter)." WHERE rid = :rid AND from_user = :from_user AND type = :type $where", array(':rid' => $rid,':from_user' => $from_user,':type' => 2))['tp_times'];
//设置一天最多投票数
$set_count = pdo_get('goddess_reply_vote',array('rid'=>$rid))['daytpxz'];

if(empty($counter) && $counter !=0)
	$has = $set_count;
else{
	if($counter <= 0){
		$has = abs(intval($counter)) + $set_count;
	}
	else
		$has = intval($set_count - $counter);
}
//投票人
$vote_all = pdo_fetchall('SELECT tfrom_user  FROM ims_goddess_votelog WHERE from_user = "'.$from_user.'" and rid = '.$rid.' GROUP BY tfrom_user order by createtime');
$vote_info = [];
foreach ($vote_all as $key => $value) {
	$temp = pdo_get($this->table_users,array('from_user'=>$value,'rid'=>$rid));
	$temp['pic'] = pdo_get($this->table_users_picarr,array('from_user'=>$value,'rid'=>$rid,'isfm'=>1))['photos'];
	$vote_info[] = $temp;
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


include $this->template('ly_voter_center');