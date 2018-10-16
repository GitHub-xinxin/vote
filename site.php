<?php

//decode by https://www.admincn.cc/
defined('IN_IA') or exit('Access Denied');
require IA_ROOT . '/addons/goddess/core/defines.php';
require FM_CORE . 'function/load.php';
class GoddessModuleSite extends FmCoreC2
{
	public function __web($f_name)
	{
		global $_GPC, $_W;
		checklogin();
		$uniacid = $_W['uniacid'];
		$rid = intval($_GPC['rid']);
		$r = pdo_fetch("SELECT uniacid,title FROM " . tablename($this->table_reply) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		$v = pdo_fetch("SELECT uni_all_users FROM " . tablename($this->table_reply_vote) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		if ($v['uni_all_users'] != 1) {
			if ($uniacid != $r['uniacid']) {
				$uni = " AND uniacid = " . $uniacid;
			}
		}
		include_once 'core/fmweb/' . strtolower(substr($f_name, 5)) . '.php';
	}
	public function __mobile($f_name)
	{
		global $_GPC, $_W;
		$uniacid = empty($_GPC['uniacid']) ? $_W['uniacid'] : $_GPC['uniacid'];
		$rid = $_GPC['rid'];
		$cfg = $this->module['config'];
		if (!empty($rid)) {
			$fromuser = !empty($_GPC['fromuser']) ? $_GPC["fromuser"] : $_COOKIE["user_fromuser_openid"];
			$usersinfo = $this->GetOauth($_GPC['do'], $uniacid, $fromuser, $rid, $_GPC['from_user'], $_GPC['duli']);
			$from_user = $usersinfo['from_user'];
			$follow = $usersinfo['follow'];
			$nickname = $usersinfo['nickname'];
			$avatar = $usersinfo['avatar'];
			$sex = $usersinfo['sex'];
			$unionid = $usersinfo['unionid'];
			if ($_GPC['tfrom_user'] == 'diytfrom') {
				$_GPC['tfrom_user'] = $from_user;
			}
			$tfrom_user = !empty($_GPC['tfrom_user']) ? $_GPC['tfrom_user'] : $_COOKIE["user_tfrom_user_openid"];
			$getreply = $this->GetReply($rid, $uniacid);
			$rbasic = $getreply['rbasic'];
			$rshare = $getreply['rshare'];
			$rhuihua = $getreply['rhuihua'];
			$rdisplay = $getreply['rdisplay'];
			$rvote = $getreply['rvote'];
			$rbody = $getreply['rbody'];
			$qiniu = $getreply['qiniu'];
			$now = time();
			if (!empty($cfg['skipurl'])) {
				$this->skipurl($rid, $cfg);
			}
			if ($_GPC['do'] == 'photosvote' || $_GPC['do'] == 'tuser' || $_GPC['do'] == 'tuserphotos' || $_GPC['do'] == 'des' || $_GPC['do'] == 'reg' || $_GPC['do'] == 'paihang') {
				if ($now - $rdisplay['xuninum_time'] > $rdisplay['xuninumtime']) {
					pdo_update($this->table_reply_display, array('xuninum_time' => $now, 'xuninum' => $rdisplay['xuninum'] + mt_rand($rdisplay['xuninuminitial'], $rdisplay['xuninumending'])), array('rid' => $rid));
				}
				$yuedu = $from_user . $rid . $uniacid;
				if (time() == mktime(0, 0, 0)) {
					setcookie("user_yuedua", -10000);
				}
				if ($_COOKIE["user_yuedua"] != $yuedu) {
					pdo_update($this->table_reply_display, array('cyrs_total' => $rdisplay['cyrs_total'] + 1, 'hits' => $rdisplay['hits'] + 1), array('rid' => $rid));
					if (!empty($tfrom_user)) {
						$user = pdo_fetch("SELECT id, from_user, hits FROM " . tablename($this->table_users) . " WHERE from_user = :from_user and rid = $rid", array(':from_user' => $tfrom_user));
						if ($user) {
							pdo_update($this->table_users, array('hits' => $user['hits'] + 1), array('rid' => $rid, 'from_user' => $tfrom_user));
						}
					}
					setcookie("user_yuedua", $yuedu, time() + 3600 * 24);
				}
			}
			if ($_GPC['do'] == 'photosvote' || $_GPC['do'] == 'tuser' || $_GPC['do'] == 'tuserphotos' || $_GPC['do'] == 'reg') {
				if ($rbasic['status'] == 0) {
					$stopurl = $_W['siteroot'] . 'app/' . $this->createMobileUrl('stop', array('status' => '0', 'rid' => $rid));
					header("location:$stopurl");
					exit;
				}
			}
			if ($rvote['isipv'] == 1) {
				if ($_GPC['do'] == 'photosvote' || $_GPC['do'] == 'tuser' || $_GPC['do'] == 'tuserphotos' || $_GPC['do'] == 'reg') {
					$this->stopip($rid, $uniacid, $from_user, getip(), $_GPC['do'], $rvote['ipturl'], $rvote['limitip']);
				}
			}
		}
		include_once 'core/fmmobile/' . strtolower(substr($f_name, 8)) . '.php';
	}
	private function templatec($templatename, $filename)
	{
		global $_GPC, $_W;
		$tf = 'templates/' . $templatename . '/' . $filename;
		$toye = $this->_stopllq($tf);
		$tmfile = FMFILE . $tf . '.html';
		if (!file_exists($tmfile) || $templatename == 'default') {
			$tf = $filename;
		}
		return $tf;
	}
	private function _stopllq($turl)
	{
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
			return $turl;
		} else {
			return $turl;
		}
	} 
	public function doMobilelisthome()
	{
		$this->doMobilelistentry();
	}
	public function gettiles($keyword = '')
	{
		global $_GPC, $_W;
		$uniacid = !empty($_W['uniacid']) ? $_W['uniacid'] : $_W['acid'];
		$urls = array();
		$list = pdo_fetchall("SELECT id FROM " . tablename('rule') . " WHERE uniacid = " . $uniacid . " and module = 'goddess'" . (!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		if (!empty($list)) {
			foreach ($list as $row) {
				$rbasic = pdo_fetch("SELECT title FROM " . tablename($this->table_reply) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $row['id']));
				$urls[] = array('title' => $rbasic['title'], 'url' => $_W['siteroot'] . 'app/' . $this->createMobileUrl('photosvote', array('rid' => $row['id'])));
			}
		}
		return $urls;
	}

	//名次函数
	public function ranking($tfrom_user,$rid){
		$single = pdo_get($this->table_users,array('rid'=>$rid,'from_user'=>$tfrom_user));
		$list = pdo_getall($this->table_users,array('rid'=>$rid),array(),'','photosnum desc');
		return array_search($single, $list)+1;
	}	
	//人气排名
	public function GetPaihangrq($rid,$from_user){
		$single = pdo_get($this->table_users,array('rid'=>$rid,'from_user'=>$from_user));
		$list = pdo_fetchall('SELECT * from '.tablename($this->table_users).' WHERE rid = :rid  order by `hits` + `xnhits` desc',array(':rid'=>$rid));
		// $list = pdo_getall($this->table_users,array('rid'=>$rid),array(),'','xnhits+hits desc');
		// var_dump($list);
		return array_search($single, $list)+1;
	}
	//保存修改值
	public function save_modif($from_user,$rid,$item,$value){
		$data = [
			'from_user'=>$from_user,
			'rid'=>$rid,
			'item'=>$item,
			'value'=>$value,
			'insert_time'=>time()
		];
		if($item != 'img'){
			//如果已经有改修改记录，则替换掉
			$is_has = pdo_get('goddess_ly_modif',array('rid'=>$rid,'from_user'=>$from_user,'item'=>$item));
			if($is_has)
				return pdo_update('goddess_ly_modif',$data,array('id'=>$is_has['id']));
			else
				return pdo_insert('goddess_ly_modif',$data);	
		}else
			return pdo_insert('goddess_ly_modif',$data);	
		
	}
	//计算星座
	public function birthext($birth){ 
		if(strstr($birth,'-')===false&&strlen($birth)!==8){ 
			$birth=date("Y-m-d",$birth); 
		} 
		if(strlen($birth)===8){ 
			if(eregi('([0-9]{4})([0-9]{2})([0-9]{2})$',$birth,$bir)) 
				$birth="{$bir[1]}-{$bir[2]}-{$bir[3]}"; 
		} 
		if(strlen($birth)<8){ 
			return false; 
		} 
		$tmpstr= explode('-',$birth); 
		if(count($tmpstr)!==3){ 
			return false; 
		} 
		$y=(int)$tmpstr[0]; 
		$m=(int)$tmpstr[1]; 
		$d=(int)$tmpstr[2]; 
		$xzdict=array('摩羯','水瓶','双鱼','白羊','金牛','双子','巨蟹','狮子','处女','天秤','天蝎','射手'); 
		$zone=array(1222,122,222,321,421,522,622,722,822,922,1022,1122,1222); 
		if((100*$m+$d)>=$zone[0]||(100*$m+$d)<$zone[1]){ 
			$i=0; 
		}else{ 
			for($i=1;$i<12;$i++){ 
				if((100*$m+$d)>=$zone[$i]&&(100*$m+$d)<$zone[$i+1]){ break; } 
			} 
		} 
		$result=$xzdict[$i].'座'; 
		return $result; 
	}
	//获取视频首图接口
	public function getvideoimg($tfrom_user,$rid){
		$res = pdo_get('goddess_provevote_headarr',array('from_user'=>$tfrom_user,'rid'=>$rid,'head_img'=>2,'new_img'=>0));
		if($res)
			return tomedia($res['photos']);
		else
			return tomedia('../attachment/images/global/videoimgg.jpg');
	}
	//获取头像接口
	public function getheadimg($tfrom_user,$rid){
		$res = pdo_get('goddess_provevote_headarr',array('from_user'=>$tfrom_user,'rid'=>$rid,'head_img'=>1));
		if($res)
			return tomedia($res['photos']);
		else
			return tomedia('../attachment/images/global/head_img.png');
	}
	//获取真实姓名
	public function getrealname($tfrom_user,$rid){
		$res = pdo_get('goddess_provevote',array('from_user'=>$tfrom_user,'rid'=>$rid))['realname'];
		if($res)
			return $res;
		else
			return '未填';
	}
	//获取修改项目
	public function getitem($item){
		$item_name ='';
		switch ($item) {
			case 'realname': 	$item_name="姓名";break;
			case 'mobile': 		$item_name="手机";break;
			case 'sex': 		$item_name="性别";break;
			case 'birth': 		$item_name="出生日期";break;
			case 'id_card': 	$item_name="身份证号码";break;
			case 'nation': 		$item_name="民族";break;
			case 'area': 		$item_name="报名赛区";break;
			case 'school': 		$item_name="报名学校";break;
			case 'stature': 	$item_name="身高";break;
			case 'weight': 		$item_name="体重";break;
			case 'signature': 	$item_name="个性签名";break;
			case 'interest': 	$item_name="兴趣爱好";break;
			case 'head_img': 	$item_name="头像";break;
			case 'video_img': 	$item_name="视频封面";break;
			case 'video': 		$item_name="视频";break;
			case 'img': 		$item_name="展示图";break;
		}
		return $item_name;
	}
	//获取修改项目
	public function getitem_value($item,$value){
		$item_value ='';
		switch ($item) {
			case 'realname': 	$item_value=$value;break;
			case 'mobile': 		$item_value=$value;break;
			case 'sex': 		if($value == 1) $item_value = '男'; elseif($value == 2) $item_value = '女'; else $item_value = '未知'; break;
			case 'birth': 		$item_value=date('Y-m-d',$value);break;
			case 'area': 		$item_value=pdo_get('goddess_game_area',array('id'=>$value))['name'];break;
			case 'id_card': 	$item_value=$value;break;
			case 'nation': 		$item_value=$value;break;
			case 'stature': 	$item_value=$value;break;
			case 'weight': 		$item_value=$value;break;
			case 'signature': 	$item_value=$value;break;
			case 'interest': 	$item_value=$value;break;
			case 'school': 		$item_value=pdo_get('goddess_ly_school',array('id'=>$value))['name'];break;
			case 'head_img': 
			case 'video_img': 	$img = tomedia(pdo_get('goddess_provevote_headarr',array('id'=>$value))['photos']); $item_value="<a href=".$img."><img src=".$img." style='width:3vw'></a>";break;
			case 'img': 		$img = tomedia(pdo_get('goddess_provevote_picarr',array('id'=>$value))['photos']); $item_value="<a href=".$img."><img src=".$img." style='width:3vw'></a>";break;
			case 'video': 		$item_value="<a href=".pdo_get('goddess_provevote_videoarr',array('id'=>$value))['videopath'].">视频链接</a>";break;
		}
		return $item_value;
	}
	//赛区选择
	public function doMobileLy_area(){
		$this->__mobile(__FUNCTION__);
	}
	//删除视频首页图接口
	public function doMobileDel_videoimg(){
		$this->__mobile(__FUNCTION__);
	}
	//赛区学校切换接口
	public function doMobileArea2school(){
		$this->__mobile(__FUNCTION__);
	}
	//上传头像与视频封面接口
	public function doMobileLy_headimg(){
		$this->__mobile(__FUNCTION__);
	}
	//报名参赛
	public function doMobileLy_join(){
		$this->__mobile(__FUNCTION__);
	}
	//视频上传七牛
	public function doMobileUp_qiniu(){
		$this->__mobile(__FUNCTION__);
	}
	//转发赠票
	public function doMobileIs_give(){
		$this->__mobile(__FUNCTION__);
	}
	//用户视频
	public function doMobileUser_vedio(){
		$this->__mobile(__FUNCTION__);
	}
	//未报名个人中心
	public function doMobileLy_voter_center(){
		$this->__mobile(__FUNCTION__);
	}
	//参赛须知
	public function doMobileLy_information()
	{
		$this->__mobile(__FUNCTION__);
	}
	//主页面
	public function doMobileLy_index(){
		$this->__mobile(__FUNCTION__);
	}
	public function doMobilelistentry()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileStop()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileStopip()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileStopllq()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobilePhotosvote()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobilePhdatabase()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobilePhdata()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobilePhdatab()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileTuser()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileSubscribe()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileSubscribeshare()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileTvote()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileTvotestart()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileTbbs()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileTbbsreply()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileTuserphotos()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobilereg()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileSaverecord()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileTreg()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobilereguser()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobilePaihang()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileDes()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileshareuserview()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileshareuserdata()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileMiaoxian()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileComment()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileCommentdata()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileCmzan()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobilePreview()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileGetuserinfo()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileTags()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileLocker()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileJiyan()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileJiyans()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileLogin_lock()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileMember()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileTvotelist()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileJifen()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileJifenlist()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileXiaofeilist()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileGiftlist()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileAutosave()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileCharge()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileGiftvote()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileGiftsong()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileChargeend()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileCash()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileAnswer()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileIndex()
	{
		$this->__mobile(__FUNCTION__);
	}
	public function fm_qrcode($value = 'http://fmoons.com', $filename = '', $pathname = '', $logo, $scqrcode = array('errorCorrectionLevel' => 'H', 'matrixPointSize' => '4', 'margin' => '5'))
	{
		global $_W;
		$uniacid = !empty($_W['uniacid']) ? $_W['uniacid'] : $_W['acid'];
		require_once '../framework/library/qrcode/phpqrcode.php';
		load()->func('file');
		$filename = empty($filename) ? date("YmdHis") . '' . random(10) : date("YmdHis") . '' . random(istrlen($filename));
		if (!empty($pathname)) {
			$dfileurl = 'attachment/images/' . $uniacid . '/qrcode/cache/' . date("Ymd") . '/' . $pathname;
			$fileurl = '../' . $dfileurl;
		} else {
			$dfileurl = 'attachment/images/' . $uniacid . '/qrcode/cache/' . date("Ymd");
			$fileurl = '../' . $dfileurl;
		}
		mkdirs($fileurl);
		$fileurl = empty($pathname) ? $fileurl . '/' . $filename . '.png' : $fileurl . '/' . $filename . '.png';
		QRcode::png($value, $fileurl, $scqrcode['errorCorrectionLevel'], $scqrcode['matrixPointSize'], $scqrcode['margin']);
		$dlogo = $_W['attachurl'] . 'headimg_' . $uniacid . '.jpg?uniacid=' . $uniacid;
		if (!$logo) {
			$logo = toimage($dlogo);
		}
		$QR = $_W['siteroot'] . $dfileurl . '/' . $filename . '.png';
		if ($logo !== FALSE) {
			$QR = imagecreatefromstring(file_get_contents($QR));
			$logo = imagecreatefromstring(file_get_contents($logo));
			$QR_width = imagesx($QR);
			$QR_height = imagesy($QR);
			$logo_width = imagesx($logo);
			$logo_height = imagesy($logo);
			$logo_qr_width = $QR_width / 5;
			$scale = $logo_width / $logo_qr_width;
			$logo_qr_height = $logo_height / $scale;
			$from_width = ($QR_width - $logo_qr_width) / 2;
			imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
		}
		if (!empty($pathname)) {
			$dfileurllogo = 'attachment/images/' . $uniacid . '/qrcode/fm_qrcode/' . date("Ymd") . '/' . $pathname;
			$fileurllogo = '../' . $dfileurllogo;
		} else {
			$dfileurllogo = 'attachment/images/' . $uniacid . '/qrcode/fm_qrcode';
			$fileurllogo = '../' . $dfileurllogo;
		}
		mkdirs($fileurllogo);
		$fileurllogo = empty($pathname) ? $fileurllogo . '/' . $filename . '_logo.png' : $fileurllogo . '/' . $filename . '_logo.png';
		imagepng($QR, $fileurllogo);
		return $fileurllogo;
	}
	function downloadImage($mediaid, $filename)
	{
		global $_W;
		$uniacid = !empty($_W['uniacid']) ? $_W['uniacid'] : $_W['acid'];
		load()->func('file');
		$access_token = WeAccount::token();
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$mediaid";
		$fileInfo = $this->downloadWeixinFile($url);
		$updir = '../attachment/images/' . $uniacid . '/' . date("Y") . '/' . date("m") . '/';
		if (!is_dir($updir)) {
			mkdirs($updir);
		}
		$filename = $updir . $filename . ".jpg";
		$this->saveWeixinFile($filename, $fileInfo["body"]);
		return $filename;
	}
	function downloadVoice($mediaid, $filename, $savetype = 0)
	{
		global $_W;
		load()->func('file');
		$uniacid = !empty($_W['uniacid']) ? $_W['uniacid'] : $_W['acid'];
		$access_token = WeAccount::token();
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$mediaid";
		$fileInfo = $this->downloadWeixinFile($url);
		$updir = '../attachment/audios/' . $uniacid . '/' . date("Y") . '/' . date("m") . '/';
		if (!is_dir($updir)) {
			mkdirs($updir);
		}
		$filename = $updir . $filename . ".amr";
		$this->saveWeixinFile($filename, $fileInfo["body"]);
		if ($savetype == 1) {
			return $qimedia;
		} else {
			return $filename;
		}
	}
	function downloadThumb($mediaid, $filename)
	{
		global $_W;
		load()->func('file');
		$uniacid = !empty($_W['uniacid']) ? $_W['uniacid'] : $_W['acid'];
		$access_token = WeAccount::token();
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$access_token&media_id=$mediaid";
		$fileInfo = $this->downloadWeixinFile($url);
		$updir = '../attachment/images/' . $uniacid . '/' . date("Y") . '/' . date("m") . '/';
		if (!is_dir($updir)) {
			mkdirs($updir);
		}
		$filename = $updir . $filename . ".jpg";
		$this->saveWeixinFile($filename, $fileInfo["body"]);
		return $filename;
	}
	function downloadWeixinFile($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$package = curl_exec($ch);
		$httpinfo = curl_getinfo($ch);
		curl_close($ch);
		$imageAll = array_merge(array('header' => $httpinfo), array('body' => $package));
		return $imageAll;
	}
	function saveWeixinFile($filename, $filecontent)
	{
		$local_file = fopen($filename, 'w');
		if (false !== $local_file) {
			if (false !== fwrite($local_file, $filecontent)) {
				fclose($local_file);
			}
		}
	}
	public function doWebsendMobileQfMsg()
	{
		global $_GPC, $_W;
		$groupid = $_GPC['gid'];
		$id = $_GPC['id'];
		$rid = $_GPC['rid'];
		$url = urldecode($_GPC['url']);
		$uniacid = $_W['uniacid'];
		if (!empty($groupid) || $groupid != 0) {
			$w = " AND id = '{$groupid}'";
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$a = $item = pdo_fetch("SELECT * FROM " . tablename('site_article') . " WHERE id = :id", array(':id' => $id));
		if ($groupid == -1) {
			$userinfo = pdo_fetchall("SELECT openid FROM " . tablename('mc_mapping_fans') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY updatetime DESC, fanid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			$total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('mc_mapping_fans') . " WHERE uniacid = '{$_W['uniacid']}'");
		} elseif ($groupid == -2) {
			$userinfo = pdo_fetchall("SELECT from_user FROM " . tablename($this->table_users) . " WHERE uniacid = '{$_W['uniacid']}' AND rid = '{$rid}' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			$total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this->table_users) . " WHERE uniacid = '{$_W['uniacid']}' AND rid = '{$rid}' ");
		} elseif ($groupid == -3) {
			$userinfo = pdo_fetchall("SELECT distinct(from_user) FROM " . tablename('goddess_votelog') . " WHERE uniacid = '{$_W['uniacid']}' AND rid = '{$rid}'  ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			$total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('goddess_votelog') . " WHERE uniacid = '{$_W['uniacid']}' AND rid = '{$rid}' ");
		} else {
			$userinfo = pdo_fetchall("SELECT openid FROM " . tablename('mc_mapping_fans') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY updatetime DESC, fanid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
			$total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('mc_mapping_fans') . " WHERE uniacid = '{$_W['uniacid']}'");
		}
		$pager = pagination($total, $pindex, $psize);
		$fmqftemplate = pdo_fetch("SELECT fmqftemplate FROM " . tablename($this->table_reply_huihua) . " WHERE rid = :rid LIMIT 1", array(':rid' => $rid));
		foreach ($userinfo as $mid => $u) {
			if (empty($u['from_user'])) {
				$from_user = $u['openid'];
			} else {
				$from_user = $u['from_user'];
			}
			include 'core/mtemplate/fmqf.php';
			if (!empty($template_id)) {
				$this->sendtempmsg($template_id, $url, $data, '#FF0000', $from_user);
			}
			if ($psize - 1 == $mid) {
				$mq = round(($pindex - 1) * $psize / $total * 100);
				$msg = '正在发送，目前：<strong style="color:#5cb85c">' . $mq . ' %</strong>';
				$page = $pindex + 1;
				$to = $this->createWebUrl('sendMobileQfMsg', array('gid' => $groupid, 'rid' => $rid, 'id' => $id, 'url' => $url, 'page' => $page));
				message($msg, $to);
			}
		}
		message('发送成功！', $this->createWebUrl('fmqf', array('rid' => $rid)));
	}
	private function sendMobileRegMsg($from_user, $rid, $uniacid)
	{
		global $_GPC, $_W;
		$reply = pdo_fetch("SELECT regmessagetemplate FROM " . tablename($this->table_reply_huihua) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		$userinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_users) . " WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user, ':rid' => $rid));
		include 'core/mtemplate/regvote.php';
		$url = $_W['siteroot'] . 'app/' . $this->createMobileUrl('tuser', array('rid' => $rid, 'from_user' => $from_user, 'tfrom_user' => $from_user));
		if (!empty($template_id)) {
			$this->sendtempmsg($template_id, $url, $data, '#FF0000', $from_user);
		}
	}
	private function sendMobileVoteMsg($tuservote, $tousers, $template_id = '')
	{
		global $_GPC, $_W;
		$uniacid = $_W['uniacid'];
		$rid = $tuservote['rid'];
		$reply = pdo_fetch("SELECT title, start_time,templates FROM " . tablename($this->table_reply) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		$u = pdo_fetch("SELECT uid,realname, nickname, from_user, photosnum, xnphotosnum FROM " . tablename($this->table_users) . " WHERE from_user = :from_user AND rid = :rid", array(':from_user' => $tuservote['tfrom_user'], ':rid' => $rid));
		include 'core/mtemplate/vote.php';
		if ($reply['templates'] == 'stylebase') {
			$tdo = 'photosvote';
		} else {
			$tdo = 'tuser';
		}
		$url = $_W['siteroot'] . 'app/' . $this->createMobileUrl($tdo, array('rid' => $rid, 'from_user' => $tousers, 'tfrom_user' => $tuservote['tfrom_user']));
		if (!empty($template_id)) {
			$this->sendtempmsg($template_id, $url, $data, '#FF0000', $tousers);
		}
		include 'core/mtemplate/tvote.php';
		$turl = $_W['siteroot'] . 'app/' . $this->createMobileUrl('paihang', array('rid' => $rid, 'votelog' => '1', 'tfrom_user' => $tuservote['tfrom_user']));
		if (!empty($template_id)) {
			$this->sendtempmsg($template_id, $turl, $tdata, '#FF0000', $tuservote['tfrom_user']);
		}
	}
	private function sendMobileHsMsg($from_user, $rid, $uniacid)
	{
		global $_GPC, $_W;
		$reply = pdo_fetch("SELECT title FROM " . tablename($this->table_reply) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		$replyhh = pdo_fetch("SELECT shmessagetemplate FROM " . tablename($this->table_reply_huihua) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		$userinfo = pdo_fetch("SELECT * FROM " . tablename($this->table_users) . " WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user, ':rid' => $rid));
		include 'core/mtemplate/shenhe.php';
		$url = $_W['siteroot'] . 'app/' . $this->createMobileUrl('tuser', array('rid' => $rid, 'from_user' => $from_user, 'tfrom_user' => $from_user));
		if (!empty($template_id)) {
			$this->sendtempmsg($template_id, $url, $data, '#FF0000', $from_user);
		}
	}
	private function sendMobileMsgtx($from_user, $rid, $uniacid)
	{
		global $_GPC, $_W;
		$msgtemplate = pdo_fetch("SELECT msgtemplate FROM " . tablename($this->table_reply_huihua) . " WHERE rid = :rid LIMIT 1", array(':rid' => $rid));
		$msgs = pdo_fetch('SELECT from_user,tfrom_user, content,createtime FROM ' . tablename($this->table_bbsreply) . ' WHERE rid = :rid AND from_user = :from_user  LIMIT 1', array(':rid' => $rid, ':from_user' => $from_user));
		include 'core/mtemplate/msg.php';
		$url = $_W['siteroot'] . 'app/' . $this->createMobileUrl('comment', array('rid' => $rid, 'tfrom_user' => $msgs['tfrom_user']));
		if (!empty($template_id)) {
			$this->sendtempmsg($template_id, $url, $data, '#FF0000', $msgs['tfrom_user']);
		}
	}
	public function sendtempmsg($template_id, $url, $data, $topcolor, $tousers = '')
	{
		$access_token = WeAccount::token();
		if (empty($access_token)) {
			return;
		}
		$postarr = '{"touser":"' . $tousers . '","template_id":"' . $template_id . '","url":"' . $url . '","topcolor":"' . $topcolor . '","data":' . $data . '}';
		$res = ihttp_post('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token, $postarr);
		return true;
	}
	//资料审核管理
	public function doWebLy_audit(){
		$this->__web(__FUNCTION__);
	}
	//赛区管理
	public function doWebLy_school(){
		$this->__web(__FUNCTION__);
	}
	//赛区管理
	public function doWebLy_game_area(){
		$this->__web(__FUNCTION__);
	}
	//评论敏感词过滤
	public function doWebLy_comment(){
		$this->__web(__FUNCTION__);
	}
	public function doWebSystem()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebIndex()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebDeleteAll()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebDelete()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebMembers()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebVotemembers()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebDeletefans()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebDeletemsg()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebDeletevote()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebProvevote()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebupaudios()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebupimages()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebAddProvevote()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebVotelog()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebMessage()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebFmqf()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebAnnounce()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebAddMessage()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebIplist()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebdeletealllog()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebdeleteallmessage()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebRankinglist()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebstatus()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebBanner()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebAdv()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebGetunionid()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebTemplates()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebTags()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebUpgrade()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebFmcount()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebAnswer()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebSource()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebSchool()
	{
		$this->__web(__FUNCTION__);
	}
	public function doWebCopyactivity()
	{
		$this->__web(__FUNCTION__);
	}
	public function webmessage($error, $url = '', $errno = -1)
	{
		$data = array();
		$data['errno'] = $errno;
		if (!empty($url)) {
			$data['url'] = $url;
		}
		$data['error'] = $error;
		echo json_encode($data);
		exit;
	}
}