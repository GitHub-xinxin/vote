<?php
/**
 * 女神来了模块定义
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');
//require IA_ROOT. '/addons/goddess/core/defines.php';
class Webcore extends WeModule {
	public $title 			 = '女神来了！';
	public $table_reply  	 = 'goddess_reply';//规则 相关设置
	public $table_reply_share  = 'goddess_reply_share';//规则 相关设置
	public $table_reply_huihua  = 'goddess_reply_huihua';//规则 相关设置
	public $table_reply_display  = 'goddess_reply_display';//规则 相关设置
	public $table_reply_vote  = 'goddess_reply_vote';//规则 相关设置
	public $table_reply_body  = 'goddess_reply_body';//规则 相关设置
	public $table_users  	 = 'goddess_provevote';	//报名参加活动的人
	public $table_tags  	 = 'goddess_tags';	//
	public $table_users_picarr  = 'goddess_provevote_picarr';	//
	public $table_users_voice  	= 'goddess_provevote_voice';	//
	public $table_users_name  	= 'goddess_provevote_name';	//
	public $table_log        = 'goddess_votelog';//投票记录
	public $table_bbsreply   = 'goddess_bbsreply';//投票记录
	public $table_banners    = 'goddess_banners';//幻灯片
	public $table_advs  	 = 'goddess_advs';//广告
	public $table_gift  	 = 'goddess_gift';
	public $table_data  	 = 'goddess_data';
	public $table_iplist 	 = 'goddess_iplist';//禁止ip段
	public $table_iplistlog  = 'goddess_iplistlog';//禁止ip段
	public $table_announce   = 'goddess_announce';//公告
	public $table_templates   = 'goddess_templates';//模板
	public $table_designer   = 'goddess_templates_designer';//模板页面
	public $table_order   = 'goddess_order';//付费投票

	public function __construct() {
		global $_W;
	}


}
