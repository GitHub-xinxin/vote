<?php
/**
 * 女神来了模块定义
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');

if (!$_COOKIE["user_limittime"]) {
	if (empty($from_user) || $follow != 1) {
		setcookie("user_limittime", '1', time()+$rshare['open_limittime']*60);
	}
} 

/**
 * 获取概述信息
 */
//选择赛区是否开始投票
$user = pdo_get('goddess_voteer',array('uniacid'=>$uniacid,'rid'=>$rid,'from_user'=>$from_user));

if(!empty($user['area'])){
	$voter_total = 0;
	$vote_count = 0;
	$browse_count = 0;
	if(!pdo_get('goddess_game_area',array('rid'=>$rid,'uniacid'=>$uniacid,'id'=>$user['area']))['isvote']){
		/**
		 * 赛区开始投票
		 * 先判断是否单学校投票模式
		 */
		if($rbasic['isschool_mode']){
			//开始投票
			$title_name = pdo_get('goddess_ly_school',array('rid'=>$rid,'uniacid'=>$uniacid,'id'=>$user['school']))['name'];
			if(empty($title_name)){
				$title_name = "请选择学校";
			}
			$title_name .= "-";
			//判断学校是否开始投票
			if(!pdo_get('goddess_ly_school',array('rid'=>$rid,'uniacid'=>$uniacid,'id'=>$user['school']))['isvote']){
				/**
				 * 开通学校投票模式
				 * 统计学校参赛数据
				 */
				$school_voters = pdo_getall('goddess_provevote',array('rid'=>$rid,'uniacid'=>$uniacid,'school'=>$user['school']));
				if(!empty($school_voters)){
					$voter_total = count($school_voters);
					foreach ($school_voters as $key => $value) {
						$vote_count += ($value['photosnum'] + $value['xnphotosnum']);
						$browse_count += ($value['hits'] + $value['xnhits']);
					}
				}
			}else{
				/**
				 * 学校未开始投票
				 */
				$active_time ="未开始";
			}
		}else{
			/**
			 * 未开启学校投票模式
			 * 统计赛区参赛信息
			 */
			$area_voters = pdo_getall('goddess_provevote',array('rid'=>$rid,'uniacid'=>$uniacid,'area'=>$user['area']));
			if(!empty($area_voters)){
				$voter_total = count($area_voters);
				foreach ($area_voters as $key => $value) {
					$vote_count += ($value['photosnum'] + $value['xnphotosnum']);
					$browse_count += ($value['hits'] + $value['xnhits']);
				}
			}
			$title_name = pdo_get('goddess_game_area',array('id'=>$user['area']))['name'].'-';
		}
	}else{
		/**
		 * 赛区未开始投票
		 */
		$title_name = pdo_get('goddess_game_area',array('id'=>$user['area']))['name'].'-';
		$active_time ="未开始";
	}
}

/**
 * 是否报名参加活动
 */
$is_join = pdo_fetch("SELECT realname, nickname FROM " . tablename($this -> table_users) . " WHERE from_user = :from_user and rid = :rid and status = :status", array(':from_user' => $from_user, ':rid' => $rid,'status'=>1));

$videoarr = pdo_fetchcolumn("SELECT video FROM ".tablename($this->table_users_videoarr)." WHERE from_user = :from_user and rid = :rid and status = :status ORDER BY id ASC", array(':from_user' => $from_user,':rid' => $rid,':status' => 1));//显示所有视频-----修改

if (is_array($videoarr)){
	$ceshi = $videoarr[0];
}else{
	$ceshi = $videoarr;
}





$voteer = pdo_fetch("SELECT chance FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid LIMIT 1", array(':from_user' => $from_user,':rid' => $rid));
if ($rvote['votepay'] == 1) {
	if ($_W['account']['level'] == 4) {
		$u_uniacid = $uniacid;
	}else{
		$u_uniacid = $cfg['u_uniacid'];
	}
	$pays = pdo_fetch("SELECT payment FROM " . tablename('uni_settings') . " WHERE uniacid='{$u_uniacid}' limit 1");
	$pay = iunserializer($pays['payment']);

	if (!empty($_GPC['paymore'])) {
		$paymore = iunserializer(base64_decode(base64_decode($_GPC['paymore'])));
	}
	$payordersn = pdo_fetch("SELECT id,payyz,ordersn FROM " . tablename($this -> table_order) . " WHERE rid='{$rid}' AND from_user = :from_user AND paytype = 2 ORDER BY id DESC,paytime DESC limit 1", array(':from_user' => $from_user));
	//print_r($payordersn);
	$voteordersn = pdo_fetch("SELECT id FROM " . tablename($this -> table_log) . " WHERE rid='{$rid}' AND from_user = :from_user AND ordersn = :ordersn ORDER BY id DESC limit 1", array(':from_user' => $from_user, ':ordersn' => $paymore['ordersn']));
}
if (empty($_GPC['paymore'])) {
	if ($cfg['ismiaoxian'] && $cfg['mxnexttime'] != 0) {
		if (!isset($_COOKIE["fm_miaoxian"])) {
			setcookie("fm_miaoxian", 'startmiaoxian', time() + $cfg['mxnexttime']);
			$mxurl = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('miaoxian', array('rid' => $rid));
			header("location:$mxurl");
			exit ;
		}
	}
}

//幻灯片
$banners = pdo_fetchall("SELECT bannername,link,thumb FROM " . tablename($this -> table_banners) . " WHERE enabled=1 AND rid= '{$rid}' ORDER BY displayorder ASC");
if ($rdisplay['ipannounce'] == 1) {
	$announce = pdo_fetchall("SELECT nickname,content,createtime,url FROM " . tablename($this -> table_announce) . " WHERE rid= '{$rid}' ORDER BY id DESC");
}

//赞助商
if ($rdisplay['isindex'] == 1) {
	$advs = pdo_fetchall("SELECT advname,link,thumb FROM " . tablename($this -> table_advs) . " WHERE enabled=1 AND ismiaoxian = 0 AND rid= '{$rid}'");
	$advarr = $this->get_advs($rid);
	//print_r($advs);
}
if ($rvote['isanswer'] == 1) {
	$answer = $this->get_answer($rid);
	$answers = iunserializer($answer['answer']);
}

$keyword = $_GPC['keyword'];
$keyword_area = $_GPC['keyword_area'];
$keyword_school = $_GPC['keyword_school'];
$op = $_GPC['op'];
$tagid = !empty($_GPC['category']['childid']) ? $_GPC['category']['childid'] : $_GPC['tagid'];
$tagpid = !empty($_GPC['category']['parentid']) ? $_GPC['category']['parentid'] : $_GPC['tagpid'];
$tagtid = !empty($_GPC['category']['threecs']) ? $_GPC['category']['threecs'] : $_GPC['tagtid'];

$tags = pdo_fetchall("SELECT * FROM " . tablename($this -> table_tags) . " WHERE uniacid = :uniacid AND rid = :rid AND parentid = 0 ORDER BY id DESC", array(':uniacid' => $uniacid, ':rid' => $rid));
	//print_r($tags);echo ''
$tagsall = pdo_fetchall("SELECT * FROM " . tablename($this -> table_tags) . " WHERE uniacid = :uniacid AND rid = :rid ORDER BY id DESC", array(':uniacid' => $uniacid, ':rid' => $rid));


$tagname = $this -> gettagname($tagid, $tagpid,$tagtid, $rid);

$pindex = max(1, intval($_GPC['page']));
$psize = empty($rdisplay['indextpxz']) ? 10 : $rdisplay['indextpxz'];
//取得用户列表
$where = '';
if (!empty($keyword)) {
	if (is_numeric($keyword))
		$where .= " AND uid = '" . $keyword . "'";
	else{
		$where .= " AND (nickname LIKE '%{$keyword}%' OR realname LIKE '%{$keyword}%' )";
	}
}
$where .= " AND status = '1'";

if (!empty($tagid)) {
	$where .= " AND tagid = '" . $tagid . "'";
} elseif (!empty($tagpid)) {
	$where .= " AND tagpid = '" . $tagpid . "'";
}

if ($_GPC['indexorder'] == 4) {
	$where .= " ORDER BY `hits` + `xnhits` DESC";
} else {
	if ($rdisplay['indexorder'] == '1') {
		$where .= " ORDER BY `createtime` DESC";
	} elseif ($rdisplay['indexorder'] == '11') {
		$where .= " ORDER BY `createtime` ASC";
	} elseif ($rdisplay['indexorder'] == '2') {
		$where .= " ORDER BY `uid` DESC";
	} elseif ($rdisplay['indexorder'] == '22') {
		$where .= " ORDER BY `uid` ASC";
	} elseif ($rdisplay['indexorder'] == '3') {
		$where .= " ORDER BY `photosnum` + `xnphotosnum` DESC";
	} elseif ($rdisplay['indexorder'] == '33') {
		$where .= " ORDER BY `photosnum` + `xnphotosnum` ASC";
	} elseif ($rdisplay['indexorder'] == '4') {
		$where .= " ORDER BY `hits` + `xnhits` DESC";
	} elseif ($rdisplay['indexorder'] == '44') {
		$where .= " ORDER BY `hits` + `xnhits` ASC";
	} elseif ($rdisplay['indexorder'] == '5') {
		$where .= " ORDER BY `vedio` DESC, `music` DESC, `uid` DESC";
	} else {
		$where .= " ORDER BY `uid` DESC";
	}
}
$userlist = pdo_fetchall('SELECT * FROM ' . tablename($this -> table_users) . ' WHERE rid = :rid  ' . $where . ' LIMIT  ' . ($pindex - 1) * $psize . ',' . $psize, array(':rid' => $rid));
$musicall = pdo_fetchall('SELECT music,voice,uid FROM ' . tablename($this -> table_users) . ' WHERE rid = :rid AND (music != "" or voice != "")  ' . $where . '   ', array(':rid' => $rid));

$total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this -> table_users) . ' WHERE rid = :rid ' . $where . '', array(':rid' => $rid));
$total_pages = ceil($total / $psize);
$pager = pagination($total, $pindex, $psize, '', array('before' => 0, 'after' => 0, 'ajaxcallback' => ''));

if (!empty($fromuser)) {
	$titem = pdo_fetch("SELECT * FROM " . tablename($this -> table_users) . " WHERE rid = :rid AND from_user = :from_user LIMIT 1", array(':rid' => $rid, ':from_user' => $fromuser));
	$tcommentnum = $this -> getcommentnum($rid, $uniacid, $fromuser);
}
$gtfrom_user = $_GPC['tfrom_user'];


$ceshi4 = $gtfrom_user;
$ceshi2 = $ykurl;



if (!empty($gtfrom_user)) {
	$item_tfrom = pdo_fetch("SELECT * FROM " . tablename($this -> table_users) . " WHERE rid = :rid AND from_user = :from_user LIMIT 1", array(':rid' => $rid, ':from_user' => $gtfrom_user));
	
	$item_tfrom['vedio'] = $ceshi;

	$ceshi3 = "2222222";
	
	
	if (!empty($item_tfrom)) {
		$commentnum_tfrom = $this -> getcommentnum($rid, $uniacid, $gtfrom_user);
		$mphotosnum_tfrom = $this -> getphotosnum($rid, $uniacid, $gtfrom_user);
		$fmimage_tfrom = $this -> getpicarr($uniacid, $rid, $gtfrom_user, 1);
		$photos_tfrom = $this -> getphotos($fmimage_tfrom['photos'], $item_tfrom['avatar'], FM_STATIC_MOBILE . 'public/images/nofoundpic.gif');
		$username_tfrom = $this -> getname($rid, $gtfrom_user);
		if ($item_tfrom['vedio'] || $item_tfrom['youkuurl']) {
			if ($item_tfrom['vedio']) {
				$rightmedia_v = '<video id="videocon" controls width="100%" height="200" poster="' . $photos_tfrom . '" webkit-playsinline><source src="' . tomedia($item_tfrom['vedio']) . '" type="video/mp4" /><p class="vjs-no-js">你的浏览器不支持该视频</a></p></video>';
			} elseif ($item_tfrom['youkuurl']) {
				if (substr($item_tfrom['youkuurl'], 0, 7) == 'http://') {
					$ykurl = $item_tfrom['youkuurl'];
				} else {
					$ykurl = 'http://player.youku.com/embed/' . $item_tfrom['youkuurl'];
				}
				$rightmedia_v = '<iframe  src="' . $ykurl . '" frameborder="0" allowfullscreen="" style="width:100%;min-height: 200px;"></iframe>';
			}
		} elseif ($item_tfrom['voice'] || $item_tfrom['music']) {
			if ($item_tfrom['voice']) {
				$rightmedia_v = '<img style="width:100%" src="' . $photos_tfrom . '">';
			} elseif ($item_tfrom['music']) {
				$rightmedia_v = '<img style="width:100%" src="' . $photos_tfrom . '">';
			}
		} else {
			$rightmedia_v = '<img id="bigimg' . $item_tfrom['uid'] . '" style="width:100%" src="' . $photos_tfrom . '">';
		}
	}
}

//查询自己是否参与活动
if (!empty($from_user)) {
	$mygift = pdo_fetch("SELECT * FROM " . tablename($this -> table_users) . " WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user, ':rid' => $rid));
	$voteer = pdo_fetch("SELECT * FROM " . tablename($this -> table_voteer) . " WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user, ':rid' => $rid));
	$mcommentnum = $this -> getcommentnum($rid, $uniacid, $from_user);
}

if (!empty($rbody)) {
	$rbody_photosvote = iunserializer($rbody['rbody_photosvote']);
	$rbody_qxbfooter = iunserializer($rbody['qxbfooter']);
}

$rjifen = pdo_fetch("SELECT is_open_jifen,is_open_jifen_sync,jifen_vote,jifen_vote_reg,jifen_reg FROM " . tablename($this -> table_jifen) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
$title = $rbasic['title'] . ' ';

$fmimage = $this -> getpicarr($uniacid, $rid, $from_user, 1);
if (!empty($rshare['sharelink'])) {
	$_share['link'] = $rshare['sharelink'];
}else{ 
	$_share['link'] = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('ly_area', array('rid' => $rid));
//分享URL
}
$_share['title'] = $this -> get_share($uniacid, $rid, $tfrom_user, $rshare['sharetitle']);
$_share['content'] = $this -> get_share($uniacid, $rid, $tfrom_user, $rshare['sharecontent']);
$_share['imgUrl'] = $this -> getphotos($rshare['sharephoto'], $rshare['sharephoto'], $rshare['sharephoto']);
if (!empty($from_user)) {
	$this->recoveryorder($rid, $from_user);
}
$templatename = $rbasic['templates'];
if ($templatename != 'default' && $templatename != 'stylebase') {
	require FM_CORE . 'fmmobile/tp.php';
}
$toye = $this -> templatec($templatename, $_GPC['do']);
include $this -> template($toye);