<?php
/**
 * 女神来了模块定义
 *
 * @author 幻月科技
 * @url http://bbs.fmoons.com/
 */
defined('IN_IA') or exit('Access Denied');
		/**
		 * 赛区
		 */
		$area_list = pdo_getall('goddess_game_area',array('rid'=>$rid,'uniacid'=>$_W['uniacid'],'is_display'=>0));
		/**
		 * 首先判断是否已阅读并同意参赛须知
		 */
		$is_read = pdo_fetch("SELECT * FROM ".tablename($this->table_voteer)." WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user,':rid' => $rid));
		if($is_read['is_read'] == 0){
			$mxurl = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('ly_information', array('rid' => $rid));
			header("location:".$mxurl);
			exit ;
		}
		//是否已经报名参赛
		$is_join = pdo_fetch("SELECT realname, nickname FROM " . tablename($this -> table_users) . " WHERE from_user = :from_user and rid = :rid and status = :status", array(':from_user' => $from_user, ':rid' => $rid,'status'=>1));
		/**
		 * 显示上传的头像和视频首图
		 */
		$head_img = pdo_get('goddess_provevote_headarr',array('from_user'=>$from_user,'rid'=>$rid,'head_img'=>1,'new_img'=>0))['photos'];
		$video_img = pdo_get('goddess_provevote_headarr',array('from_user'=>$from_user,'rid'=>$rid,'head_img'=>2,'new_img'=>0))['photos'];

		/**
		 * 如果没报名，仅投票人员，进入投票人员主页
		 * @var [type]
		 */
		$is_reg = pdo_fetch("SELECT * FROM ".tablename($this->table_users)." WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user,':rid' => $rid));
		if(empty($is_reg) && $_GPC['ly_bm'] != 1){
			$mxurl = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('ly_voter_center', array('rid' => $rid));
			header("location:".$mxurl);
			exit ;
		}
		/**
		 * 注册页上方图片
		 * @var [type]
		 */
		$ly_zd = pdo_get('goddess_provevote_picarr',array('rid'=>$rid,'from_user'=>$tfrom_user,'isfm'=>1))['photos'];
		
		$total_gift = $this -> getgiftnum($rid, $tfrom_user, $uni);
		$user = pdo_fetch("SELECT * FROM " . tablename($this -> table_users) . " WHERE from_user = :from_user and rid = :rid", array(':from_user' => $tfrom_user, ':rid' => $rid));

		$regtitlearr = iunserializer($rdisplay['regtitlearr']);
		$photosarrid = pdo_fetch("SELECT id FROM ".tablename($this->table_users_picarr)." WHERE rid = :rid ORDER BY id DESC LIMIT 1", array(':rid' => $rid));
		$mid = $photosarrid['id'] + 1;
		$addmid = $mid + 1;
		$photosarrnum = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_users_picarr)." WHERE from_user = :from_user and rid = :rid and new_img = :new_img", array(':from_user' => $from_user,':rid' => $rid,':new_img'=>0));

		$videoarrid = pdo_fetch("SELECT id FROM ".tablename($this->table_users_picarr)." WHERE rid = :rid ORDER BY id DESC LIMIT 1", array(':rid' => $rid));
		$vmid = $videoarrid['id'] + 1;
		$vaddmid = $vmid + 1;
		$videoarrnum = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->table_users_picarr)." WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user,':rid' => $rid));
		$mygift = pdo_fetch("SELECT realname,mobile,status FROM ".tablename($this->table_users)." WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user,':rid' => $rid));
		if ($_GPC['delete']) {
			if ($rshare['subscribe'] && !$follow) {
				$fmdata = array(
					"success" => -1,
					"flag" => 5,
					"msg" => '请先关注',
				);
				echo json_encode($fmdata);
				exit();
			}


			if($mygift['status'] == '1' && $rvote['isallow_modify'] != '1') {
				$msg = '您已经审核通过，报名资料无法修改！';
				$fmdata = array(
					"success" => -1,
					"flag" => -1,
					"msg" => $msg,
				);
				echo json_encode($fmdata);
				exit();
			}
			$photo = pdo_fetch("SELECT id,isfm, photoname FROM ".tablename($this->table_users_picarr)." WHERE from_user = :from_user AND rid = :rid AND id = :id", array(':from_user' => $from_user,':rid' => $rid,':id' => $_GPC['photoid']));
			if (empty($photo)) {
				$fmdata = array(
					"success" => -1,
					"flag" => -1,
					"msg" => '未找到你要删除的图片',
				);
				echo json_encode($fmdata);
				exit();
			}
			if ($photo['isfm']) {
				$fmdata = array(
					"success" => -1,
					"flag" => -1,
					"msg" => '该图片目前作为投票封面使用,请设置其他图片作为投票封面.然后再删除',
				);
				echo json_encode($fmdata);
				exit();
			}

			load()->func('file');
			$updir = '../attachment/images/'.$uniacid.'/'.date("Y").'/'.date("m").'/'.$photo['photoname'];
			file_delete($updir);
			pdo_delete($this->table_users_picarr, array('id' => intval($_GPC['photoid']), 'rid' => $rid, 'from_user' => $from_user));
			if($photosarrnum == 4)
				$photosarrnum = 3;
			$fmdata = array(
				"success" => 1,
				"flag" => 1,
				"lastmid" => $photosarrid,
				"addlastmid" => $mid,
				"photosarrnum" => $photosarrnum,
				"msg" => '删除成功'
			);
			echo json_encode($fmdata);
			exit();
		}
		if($_GPC['deletev']) {
			if ($rshare['subscribe'] && !$follow) {
				$fmdata = array(
					"success" => -1,
					"flag" => 5,
					"msg" => '请先关注',
				);
				echo json_encode($fmdata);
				exit();
			}
			if($mygift['status'] == '1' && $rvote['isallow_modify'] != '1') {
				$msg = '您已经审核通过，报名资料无法修改！';
				$fmdata = array(
					"success" => -1,
					"flag" => -1,
					"msg" => $msg,
				);
				echo json_encode($fmdata);
				exit();
			}
			$vedio = pdo_fetch("SELECT id, videopath FROM ".tablename($this->table_users_videoarr)." WHERE from_user = :from_user AND rid = :rid AND id = :id", array(':from_user' => $from_user,':rid' => $rid,':id' => $_GPC['vedio']));
			if (empty($vedio)) {
				$fmdata = array(
					"success" => -1,
					"flag" => -1,
					"msg" => '未找到你要删除的视频',
				);
				echo json_encode($fmdata);
				exit();
			}
			load()->func('file');
			//$updir = '../attachment/audios/'.$uniacid.'/'.date("Y").'/'.date("m").'/'.$vedio['vedio'];
			/**
			 * 判断是否报名
			 */
			file_delete($vedio['videopath']);
			pdo_delete($this->table_users_videoarr, array('id' => intval($vedio['id'])));
			$fmdata = array(
				"success" => 1,
				"flag" => 1,
				"mid" => $vedio['id'],
				"msg" => '删除成功'
			);
			echo json_encode($fmdata);
			exit();
		}

		if ($_GPC['deletem']) {
			if ($rshare['subscribe'] && !$follow) {
				$fmdata = array(
					"success" => -1,
					"flag" => 5,
					"msg" => '请先关注',
				);
				echo json_encode($fmdata);
				exit();
			}
			if($mygift['status'] == '1' && $rvote['isallow_modify'] != '1') {
				$msg = '您已经审核通过，报名资料无法修改！';
				$fmdata = array(
					"success" => -1,
					"flag" => -1,
					"msg" => $msg,
				);
				echo json_encode($fmdata);
				exit();
			}
			$music = pdo_fetch("SELECT id, music FROM ".tablename($this->table_users)." WHERE from_user = :from_user AND rid = :rid AND music = :music", array(':from_user' => $from_user,':rid' => $rid,':music' => $_GPC['music']));
			if (empty($music)) {
				$fmdata = array(
					"success" => -1,
					"flag" => -1,
					"msg" => '未找到你要删除的音频',
				);
				echo json_encode($fmdata);
				exit();
			}
			load()->func('file');
			$updir = '../attachment/audios/'.$uniacid.'/'.date("Y").'/'.date("m").'/'.$music['music'];
			file_delete($updir);
			pdo_update($this->table_users, array('music' => ''), array('id' => intval($music['id']), 'rid' => $rid, 'from_user' => $from_user));

			$fmdata = array(
				"success" => 1,
				"flag" => 1,
				"mid" => $music['id'],
				"msg" => '删除成功'
			);
			echo json_encode($fmdata);
			exit();
		}
		$photosarr = pdo_fetchall("SELECT * FROM ".tablename($this->table_users_picarr)." WHERE from_user = :from_user and rid = :rid and status = :status and new_img = 0 ORDER BY id ASC", array(':from_user' => $from_user,':rid' => $rid,':status' => 1));//显示所有图片

		$videoarr = pdo_fetchall("SELECT * FROM ".tablename($this->table_users_videoarr)." WHERE from_user = :from_user and rid = :rid and status = :status and new_veido = :new_veido ORDER BY id ASC", array(':from_user' => $from_user,':rid' => $rid,':status' => 1,':new_veido'=>0));//显示所有图片
		if ($_GPC['setfm']) {
			if ($rshare['subscribe'] && !$follow) {
				$fmdata = array(
					"success" => -1,
					"flag" => 5,
					"msg" => '请先关注',
				);
				echo json_encode($fmdata);
				exit();
			}
			$photo = pdo_fetch("SELECT id,isfm, photoname FROM ".tablename($this->table_users_picarr)." WHERE from_user = :from_user AND rid = :rid AND id = :id", array(':from_user' => $from_user,':rid' => $rid,':id' => $_GPC['photoid']));

			if (empty($photo)) {
				$fmdata = array(
					"success" => -1,
					"flag" => -1,
					"msg" => '未找到你要设置封面的图片',
				);
				echo json_encode($fmdata);
				exit();
			}
			if ($photo['isfm']) {
				$fmdata = array(
					"success" => -1,
					"flag" => -1,
					"msg" => '该图片已经是投票封面,请设置其他图片作为投票封面。',
				);
				echo json_encode($fmdata);
				exit();
			}
			foreach ($photosarr as $key => $value) { 
				if ($value['isfm'] == 1) {
					$delmid = $value['id'];
				}
				pdo_update($this->table_users_picarr,array('isfm' => 0), array('id' => intval($value['id'])));
			}
			pdo_update($this->table_users_picarr,array('isfm' => 1), array('id' => intval($_GPC['photoid']), 'rid' => $rid, 'from_user' => $from_user));

			$fmdata = array(
				"success" => 1,
				"flag" => 1,
				"lastmid" => $photosarrid,
				"addlastmid" => $mid,
				"delmid" => $delmid,
				"msg" => '设置成功！'
			);
			echo json_encode($fmdata);
			exit();
		}
		if ($rdisplay['ipannounce'] == 1) {
			$announce = pdo_fetchall("SELECT nickname,content,createtime,url FROM " . tablename($this->table_announce) . " WHERE rid= '{$rid}' ORDER BY id DESC");
		}
		//赞助商
		if ($rdisplay['isreg'] == 1) {
			$advs = pdo_fetchall("SELECT advname,link,thumb FROM " . tablename($this -> table_advs) . " WHERE enabled=1 AND ismiaoxian = 0 AND rid= '{$rid}'");
		}

		//查询是否参与活动
		$where = '';
		$mygift = pdo_fetch("SELECT * FROM ".tablename($this->table_users)." WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user,':rid' => $rid));
		/**
		 * 所选赛区学校列表
		 */
		$school_list = pdo_getall('goddess_ly_school',array('rid'=>$rid,'area'=>$mygift['area'],'is_display'=>0));
		if ($rvote['regpay']==1) {
			if ($_W['account']['level'] == 4) {
				$u_uniacid = $uniacid;
			}else{
				$u_uniacid = $cfg['u_uniacid'];
			}
			$pays = pdo_fetch("SELECT payment FROM " . tablename('uni_settings') . " WHERE uniacid='{$uniacid}' limit 1");
			$pay = iunserializer($pays['payment']);


			//付款
			$payordersn = pdo_fetch("SELECT id,payyz,ordersn FROM " . tablename($this->table_order) . " WHERE rid='{$rid}' AND from_user = :from_user AND paytype = 3  ORDER BY id DESC,paytime DESC limit 1", array(':from_user'=>$from_user));
			if ($payordersn['ordersn']) {
				$orderid = $payordersn['ordersn'];
			}else{
				$orderid = date('ymdhis') . random(4, 1);
			}

			$params['tid'] = $orderid;
			$params['rid'] = $rid;
			$params['user'] = $from_user;
			$params['fee'] = $rvote['regpayfee'];
			$params['title'] = $rvote['regpaytitle'];
			$params['content'] = $rvote['regpaydes'];
			$params['ordersn'] = $orderid;
			$params['module'] = $_GPC['m'];
			$params['paytype'] = 3;
			$params['payyz'] = random(8);
			//$params['virtual'] = $item['goodstype'] == 2 ? true : false;
			//$pparams = base64_encode(iserializer($params));
			if (!empty($_GPC['paymore'])) {
				$paymore = iunserializer(base64_decode(base64_decode($_GPC['paymore'])));
			}
			//print_r($params);
			$voteordersn = pdo_fetch("SELECT ordersn FROM " . tablename($this->table_users) . " WHERE rid='{$rid}' AND from_user = :from_user ORDER BY id DESC limit 1", array(':from_user'=>$from_user));

		}
		$fmimage = $this->getpicarr($uniacid,$rid, $mygift['from_user'],1);
		load()->func('tpl');
		$tags = pdo_fetchall("SELECT id,parentid,title FROM ".tablename($this->table_tags)." WHERE rid = '{$rid}' ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');

		if (!empty($tags)) {
			$tagid = $mygift['tagid'];
			$ptag = empty($mygift['tagpid']) ? $_GPC['tagpid'] : $mygift['tagpid'];
			$tagtid = $mygift['tagtid'];
			//echo $ptag;
			$tagname = $this->gettagname($tagid,$ptag,$tagtid,$rid);


			$parent = array();
			//$children = array();

			$children = '';
			foreach ($tags as $cid => $cate) {
				$cate['name'] = $cate['title'];
				if (!empty($cate['parentid'])) {
					$children[$cate['parentid']][] = $cate;
				} else {
					$parent[$cate['id']] = $cate;
				}
			}
		}
		$pnametag = pdo_fetchall("SELECT title FROM ".tablename($this->table_pnametag)." WHERE rid = :rid ORDER BY id DESC", array( ':rid' => $rid));
		$source = pdo_fetchall("SELECT id,title FROM ".tablename($this->table_source)." WHERE rid = '{$rid}' ORDER BY displayorder ASC, id ASC ");
		$school = pdo_fetchall("SELECT id,title FROM ".tablename($this->table_school)." WHERE rid = '{$rid}' ORDER BY displayorder ASC, id ASC ");
		//整理数据进行页面显示
		$title = $rshare['sharetitle'] . '报名';
		if (!empty($rshare['sharelink'])) {
			$_share['link'] = $rshare['sharelink'];
		}else{
			$_share['link'] = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('ly_area', array('rid' => $rid));
		//分享URL
		}
		$_share['title'] = $this -> get_share($uniacid, $rid, $tfrom_user, $rshare['sharetitle']);
		$_share['content'] = $this -> get_share($uniacid, $rid, $tfrom_user, $rshare['sharecontent']);
		$_share['imgUrl'] = $this -> getphotos($rshare['sharephoto'], $rshare['sharephoto'], $rshare['sharephoto']);
		if (!empty($rbody)) {
			$rbody_reg = iunserializer($rbody['rbody_reg']);
		}


		$templatename = $rbasic['templates'];
		if ($templatename != 'default' && $templatename != 'stylebase') {
			require FM_CORE. 'fmmobile/tp.php';
		}
		$toye = $this->templatec($templatename,$_GPC['do']);
		include $this->template($toye);

