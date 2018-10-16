<?php
/**
 * 女神来了模块定义
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');
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
if($_W['isajax']){
	if($_GPC['op'] == 'more'){
		$begin = 20 * intval($_GPC['num']);
		$res = array_slice(cache_load('comm_list'),$begin,20);
		foreach ($res as $key => $value) {
			$res[$key]['createtime'] = date('Y-m-d H:i:s',$value['createtime']);
			$res[$key]['avatar'] = tomedia($value['avatar']);
		}
	}else{
		$comment_data = [
			'rid'=>$_GPC['rid'],
			'tfrom_user'=>$_GPC['tfrom_user'],
			'from_user'=>$from_user,
			"avatar"=>$avatar,
			'nickname'=>$nickname,
			'content'=>$_GPC['comment'],
			'createtime'=>time(),
			'uniacid'=>$_W['uniacid']
		];
		if(pdo_insert($this->table_bbsreply,$comment_data))
			$res =true;
		else
			$res = false;
	}
	echo json_encode($res); exit();
}

if (!empty($rshare['sharelink'])) {
	$_share['link'] = $rshare['sharelink'];
}else{
	$_share['link'] = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('tuserphotos', array('rid' => $rid, 'tfrom_user' => $tfrom_user));
//分享URL
}
$_share['title'] = $this -> get_share($uniacid, $rid, $tfrom_user, $rshare['sharetitle']);
$_share['content'] = $this -> get_share($uniacid, $rid, $tfrom_user, $rshare['sharecontent']);
$_share['imgUrl'] = $this -> getphotos($rshare['sharephoto'], $rshare['sharephoto'], $rshare['sharephoto']);

//视频首页图片
$video_img = pdo_get('goddess_provevote_headarr',array('from_user'=>$tfrom_user,'rid'=>$rid,'head_img'=>2,'new_img'=>0))['photos'];
//敏感信息
$comment = pdo_get('goddess_comment',array('rid'=>$rid))['comment'];
$comment = str_replace("\r\n",".",$comment);
//用户信息
$user = pdo_fetch("SELECT * FROM ".tablename($this->table_users)." WHERE from_user = :from_user and rid = :rid", array(':from_user' => $tfrom_user,':rid' => $rid));
//视频首页图片
$fmimage = $this -> getpicarr($uniacid, $rid, $tfrom_user, 1);

$comment_list = pdo_fetchall("SELECT u.avatar,u.nickname,b.content,b.createtime FROM ".tablename($this->table_bbsreply).' AS b LEFT JOIN '.tablename($this->table_voteer).' AS u ON b.from_user = u.from_user WHERE b.tfrom_user = "'.$tfrom_user.'"'.' and b.rid = '.$rid.' and b.uniacid = '.$uniacid.' and u.uniacid = '.$uniacid.' ORDER BY b.createtime desc');
cache_delete('comm_list');
cache_write('comm_list', $comment_list);

$videoarr = pdo_fetchall("SELECT * FROM ".tablename($this->table_users_videoarr)." WHERE from_user = :from_user and new_veido =:new_veido and rid = :rid and status = :status ORDER BY id ASC", array(':from_user' => $tfrom_user,':rid' => $rid,':status' => 1,':new_veido'=>0));//显示所有视频

include $this->template('user_vedio');
