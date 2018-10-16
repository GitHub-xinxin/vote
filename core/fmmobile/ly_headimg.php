<?php   

load()->func('file');
$mygift = pdo_fetch("SELECT * FROM ".tablename($this->table_users)." WHERE from_user = :from_user and rid = :rid", array(':from_user' => $from_user,':rid' => $rid));
if ($_GPC['upphotosone'] == 'start') {
	//是否允许审核
	if($mygift['status'] == '1' && $rvote['isallow_modify'] != '1') {
		$msg = '您已经审核通过，报名资料无法修改！';
		$fmdata = array(
			"success" => -1,
			"flag" => 2,
			"msg" => $msg,
		);
		echo json_encode($fmdata);
		exit();
	}

	$base64=file_get_contents("php://input"); //获取输入流
	$base64=json_decode($base64,1);
	$data = $base64['base64'];

	if($data){
		$harmtype = array('asp', 'php', 'jsp', 'js', 'css', 'php3', 'php4', 'php5', 'ashx', 'aspx', 'exe', 'cgi');

		preg_match("/data:image\/(.*?);base64/",$data,$res);
		$ext = $res[1];
		$setting = $_W['setting']['upload']['image'];
		if (!in_array(strtolower($ext), $setting['extentions']) || in_array(strtolower($ext), $harmtype)) {
			$fmdata = array(
				"success" => -1,
				"msg" => '系统不支持您上传的文件（扩展名为：'.$ext.'）,请上传正确的图片文件',
			);
			echo json_encode($fmdata);
			die;
		}

		$photoname = 'FMFetchi'.date('YmdHis').random(16);
		$nfilename = $photoname.'.'.$ext;
		$updir = '../attachment/images/'.$uniacid.'/'.date("Y").'/'.date("m").'/';
		mkdirs($updir);

				//$data = preg_replace("/^data:image\/(.*);base64,/","",$data);
		$darr = explode("base64,", $data,30);
		$data = end($darr);
		if (!$data) {
			$fmdata = array(
				"success" => -1,
				"msg" => $data.'当前图片宽度大于3264px,系统无法识别为其生成！',
			);
			echo json_encode($fmdata);
			exit;
		}

		if (file_put_contents($updir.$nfilename,base64_decode($data))===false) {
			$fmdata = array(
				"success" => -1,
				"msg" => '上传错误',
			);
			echo json_encode($fmdata);
			exit;
		}else{
			$mid = $_GPC['mid'];

			if (!$qiniu['isqiniu']) {
				$picurl = $updir.$nfilename;
				/**上传头像
				 * 新增
				 * @var array
				 */
				if($_GPC['op'] == 'ly'){
					if($_GPC['img_type'] == 'head_img'){
						
						$insertdata = array(
							'rid'       => $rid,
							'uniacid'   => $uniacid,
							'from_user' => $from_user,
							'photoname' => $photoname,
							'status' => 1,
							'createtime' => $now,
							'imgpath' => $picurl,
							'head_img'=>1,
							'photos'=>$picurl
						);
						$has_head = pdo_get('goddess_provevote_headarr',array('from_user'=>$from_user,'rid'=>$rid,'head_img'=>1));
						if($has_head){
							//判断是否已经报名
							$user_info = pdo_get('goddess_provevote',array('rid'=>$rid,'from_user'=>$from_user));
							if(!empty($user_info['realname'])){
								$insertdata['new_img'] = 1;
							}else{
								//未报名可更改
								//数据表中清除记录 
								pdo_delete('goddess_provevote_headarr',array('id'=>$has_head['id']));
								//将图片删除
								file_delete($has_head['photos']);
							}
						}
					}elseif ($_GPC['img_type'] == 'video_img') {
						$insertdata = array(
							'rid'       => $rid,
							'uniacid'      => $uniacid,
							'from_user' => $from_user,
							'photoname' => $photoname,
							'status' => 1,
							'createtime' => $now,
							'imgpath' => $picurl,
							'head_img'=>2,
							'photos'=>$picurl
						);
						$has_head = pdo_get('goddess_provevote_headarr',array('from_user'=>$from_user,'rid'=>$rid,'head_img'=>2));
						if($has_head){
							//判断是否已经报名
							$user_info = pdo_get('goddess_provevote',array('rid'=>$rid,'from_user'=>$from_user));

							if(!empty($user_info['realname'])){
								$insertdata['new_img'] = 1;
							}else{
								//未报名可更改
								//数据表中清除记录 
								pdo_delete('goddess_provevote_headarr',array('id'=>$has_head['id']));
								//将图片删除
								file_delete($has_head['photos']);
							}
						}
					}	
					$res = pdo_insert('goddess_provevote_headarr', $insertdata);
					$insertid = pdo_insertid();
					if($res){
						if($user_info['realname']){
							//记录修改值
							$this->save_modif($from_user,$rid,$_GPC['img_type'],$insertid);
							$fmdata = array(
								"success" => 1,
								"msg" => '上传成功,等待审核',
								"imgurl" =>tomedia($picurl),
							);
						}else{
							$fmdata = array(
								"success" => 1,
								"msg" => '上传成功！',
								"imgurl" =>tomedia($picurl),
							);
						}
						echo json_encode($fmdata);
						exit();
					}
				}
			}
		}
	}else{

		$fmdata = array(
			"success" => -1,
			"msg" =>'没有发现上传图片',
		);
		echo json_encode($fmdata);
		exit();
	}
}
?>