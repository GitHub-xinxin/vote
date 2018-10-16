<?php  
require IA_ROOT.'/addons/goddess/static/php-sdk-7.2.6/autoload.php';
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
$accessKey = 'uRGSkwYdIb588Z7WV21Its1G8R8PJmB-xYqrPs6w';
$secretKey = '_zBz5aoTe6M4KR0bDs8fgzefUkBHW4wYNTkGoQJz';
$bucket = 'vote';

if($_W['isajax']){
	if($_GPC['op'] == 'store'){
		$insertdata = array(
			'rid'       => $rid,
			'uniacid'      => $uniacid,
			'from_user' => $from_user,
			'videoname' => $_GPC['fname'],
			'videonamefop' => '',
			'status' => 1,
			'createtime' => $now,
			'videopath' => 'http://huayu.leyaocn.com/'.$_GPC['fname'],
		);

		$insertdata['video'] = pdo_get('goddess_provevote_picarr',array('rid'=>$rid,'from_user'=>$from_user,'isfm'=>1))['imgpath'];
		//判断是否为更新视频
		$user_info = pdo_get('goddess_provevote',array('rid'=>$rid,'from_user'=>$from_user));
		if(!empty($user_info['realname'])){
			$insertdata['new_veido'] = 1;
		}
		$res = pdo_insert($this->table_users_videoarr, $insertdata);
		$lastmid = pdo_insertid();
		pdo_update($this->table_users_videoarr, array('mid' => $lastmid), array('rid' => $rid,'from_user' => $from_user, 'id'=>$lastmid));
		if(!empty($user_info['realname'])){
			//记录修改记录
			$res_modif = $this->save_modif($from_user,$rid,'video',$lastmid);
		}
		if($res_modif){
			$resArr['msg'] = '资料提交成功,等待审核';
		}
		if($res)
			$resArr['code'] =true;
		else
			$resArr['code'] = false;
		
		echo json_encode($resArr);exit;

	}else{
		$auth = new Auth($accessKey, $secretKey);
		// 生成上传Token
		$token = $auth->uploadToken($bucket);
		echo json_encode($token);exit;
	}
}
?>