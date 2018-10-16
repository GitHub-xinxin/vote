<?php
/**
* 女神来了模块定义
*
* @author 幻月科技
* @url http://bbs.fmoons.com/
*/
if ($_W['isajax']) {
	$data = pdo_fetchall('select id,name from ims_goddess_ly_school where area=:areaid and rid=:rid',[':areaid'=>$_GPC['area'],':rid'=>$rid]);
	echo json_encode($data);
	exit();
}
defined('IN_IA') or exit('Access Denied');
$now = time();
$base = pdo_fetch('SELECT * FROM '.tablename($this->table_reply).' WHERE rid =:rid ', array(':rid' => $rid) );
$vote = pdo_fetch("SELECT * FROM ".tablename($this->table_reply_vote)." WHERE rid = :rid", array(':rid' => $rid));
$reply = array_merge($base, $vote);
$rdisplay = pdo_fetch("SELECT * FROM ".tablename($this->table_reply_display)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
$regtitlearr = iunserializer($rdisplay['regtitlearr']);
//所有赛区
$arealist = pdo_getall('goddess_game_area',array('rid'=>$rid,'uniacid'=>$uniacid));
if (checksubmit('submitdr')) {
	foreach ($_GPC['content'] as $key => $path) {
		$ischongfu = pdo_fetch("SELECT * FROM ".tablename($this->table_users)." WHERE rid = :rid AND photo = :photo ORDER BY `id` DESC", array(':rid' => $rid, 'photo' => $path));
		if (!empty($ischongfu)) {
			continue;
		}else{
			$filename = pdo_fetch("SELECT filename FROM ".tablename('core_attachment')." WHERE attachment = :attachment ORDER BY `id` DESC", array(':attachment' => $path));
			$ext = pathinfo($filename['filename'], PATHINFO_EXTENSION);
			$nickname = basename($filename['filename'],".".$ext);

			$uid = pdo_fetch("SELECT uid FROM ".tablename($this->table_users)." WHERE rid = :rid ORDER BY uid DESC, id DESC LIMIT 1", array(':rid' => $rid));
			$from_user = 'FM'.random(16).time();
			$insertdata = array(
				'rid'       => $rid,
				'uid'       => $uid['uid'] + 1,
				'uniacid'      => $uniacid,
				'from_user' => $from_user,
				'avatar'    => $path,
				'nickname'  => $nickname,
				'realname'  => $nickname,
				'sex'  => '1',
				'photo'  => $path,
				'photosnum'  => '0',
				'xnphotosnum'  => '0',
				'hits'  => '1',
				'xnhits'  => '1',
				'yaoqingnum'  => '0',
				'createip' => getip(),
				'lastip' => getip(),
				'status'  => 1,
				'sharetime' => $now,
				'lasttime'  => $now,
				'createtime'  => $now,
			);

			$insertdata['iparr'] = getiparr($insertdata['lastip']);
			pdo_insert($this->table_users, $insertdata);
			$insertdatap = array(
				'rid'       => $rid,
				'uniacid'      => $uniacid,
				'from_user' => $from_user,
				'photoname' => $nickname,
				'status' => 1,
				'createtime' => $now,
				'imgpath' => $path,
				'isfm' => 1,
				'photos' => $path,
			);
			pdo_insert($this->table_users_picarr, $insertdatap);
			pdo_update($this->table_reply_display, array('csrs_total' => $rdisplay['csrs_total']+1), array('rid' => $rid));
			$this->addjifen($rid, $from_user, $tfrom_user,array($nickname,$path,$sex),array($uniacid),'reg');
		}
	}
}



if (checksubmit('delete')) {
	pdo_delete($this->table_users, " id IN ('".implode("','", $_GPC['select'])."')");
	message('删除成功！', create_url('site/module', array('do' => 'members', 'name' => 'goddess', 'rid' => $rid, 'page' => $_GPC['page'], 'foo' => 'display')));
}
//var_dump(checksubmit('chooseByArea'));

$where = '';
if (!empty($_GPC['keyword'])) {
	$keyword = $_GPC['keyword'];
	$where .= " AND (uid LIKE '%{$keyword}%' OR nickname LIKE '%{$keyword}%' OR realname LIKE '%{$keyword}%' OR mobile LIKE '%{$keyword}%' OR photoname LIKE '%{$keyword}%') ";

}
$now = time();
$starttime = empty($_GPC['time']['start']) ?  strtotime(date("Y-m-d H:i", $now - 604799)) : strtotime($_GPC['time']['start']);
$endtime = empty($_GPC['time']['end']) ?  strtotime(date("Y-m-d H:i", $now+86400)) : strtotime($_GPC['time']['end']);
if (!empty($_GPC['time']['start']) && !empty($_GPC['time']['end'])) {
	$where .= " AND createtime >= " . $starttime;
	$where .= " AND createtime < " . $endtime;
}
$where .= " AND status = '1'";
$total_num = '总参赛人数';
if ($_W['ispost'] && !empty($_GPC['chooseByArea'])) {
	if ($_GPC['area_id'] !== '1') {
		$where = $where." AND area = ".$_GPC['area_id'];
		$areaname = pdo_fetchcolumn('select name from ims_goddess_game_area where id=:id',[':id'=>$_GPC['area_id']]);
		$total_num = $areaname.'参赛人数';
	}
	if ($_GPC['school_id'] !== '1') {
		$where = $where." AND school = ".$_GPC['school_id'];
		$schoolname = pdo_fetchcolumn('select name from ims_goddess_ly_school where id=:id',[':id'=>$_GPC['school_id']]);
		$total_num = $schoolname.'参赛人数';
	}
	
}
$pindex = max(1, intval($_GPC['page']));
$psize = 15;

//取得用户列表
$members = pdo_fetchall('SELECT * FROM '.tablename($this->table_users).' WHERE rid = :rid '.$where.$uni.' order by `uid` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':rid' => $rid) );
$total_number = count($members);
$total = pdo_fetchcolumn('SELECT COUNT(1) FROM '.tablename($this->table_users).' WHERE rid = :rid  '.$where.$uni.' ', array(':rid'=>$rid));
$pager = pagination($total, $pindex, $psize);
$sharenum = array();
foreach ($members as $mid => $m) {
	$sharenum[$mid] = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_data)." WHERE tfrom_user = :tfrom_user and rid = :rid ".$uni." ", array(':tfrom_user' => $m['from_user'],':rid' => $rid)) + pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_data)." WHERE fromuser = :fromuser and rid = :rid ".$uni." ", array(':fromuser' =>$m['from_user'], ':rid' => $rid)) + $m['sharenum'];
}
//var_dump($members);
include $this->template('web/members');
