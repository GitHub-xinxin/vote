<?php
load()->func('file');
if($_GPC['op'] == 'del'){
	$res = pdo_delete('goddess_ly_modif',array('id'=>$_GPC['id']));
	if($res)
		message('删除成功',$this->createWebUrl('ly_audit',array('rid'=>$rid)),'success');
	else
		message('删除失败',$this->createWebUrl('ly_audit',array('rid'=>$rid)),'error');
}elseif($_GPC['op'] == 'agree'){
	$modif_data = pdo_get('goddess_ly_modif',array('id'=>$_GPC['id']));
	switch ($modif_data['item']) {
		case 'realname': 	
		case 'mobile': 		
		case 'sex': 		
		case 'birth': 		
		case 'area': 		
		case 'id_card': 	
		case 'nation': 		
		case 'stature': 	
		case 'weight': 		
		case 'signature': 	
		case 'interest': 	
		case 'school': 
			$res = pdo_update('goddess_provevote',array($modif_data['item']=>$modif_data['value']),array('rid'=>$modif_data['rid'],'from_user'=>$modif_data['from_user']));	
			break;	
		case 'head_img': 	
			$is_has = pdo_get('goddess_provevote_headarr',array('head_img'=>1,'new_img'=>0,'from_user'=>$modif_data['from_user'],'rid'=>$modif_data['rid']));
			if($is_has){
				$res1 = pdo_delete('goddess_provevote_headarr',array('id'=>$is_has['id']));
				file_delete($is_has['photos']);
				if($res1)
					$res = pdo_update('goddess_provevote_headarr',array('new_img'=>0,),array('id'=>$modif_data['value']));
			}else{
				$res = pdo_update('goddess_provevote_headarr',array('new_img'=>0,),array('id'=>$modif_data['value']));
			}
			break;
		case 'video_img': 	
			$is_has = pdo_get('goddess_provevote_headarr',array('head_img'=>2,'new_img'=>0,'from_user'=>$modif_data['from_user'],'rid'=>$modif_data['rid']));
			if($is_has){
				$res1 = pdo_delete('goddess_provevote_headarr',array('id'=>$is_has['id']));
				file_delete($is_has['photos']);
				if($res1)
					$res = pdo_update('goddess_provevote_headarr',array('new_img'=>0,),array('id'=>$modif_data['value']));
			}else{
				$res = pdo_update('goddess_provevote_headarr',array('new_img'=>0,),array('id'=>$modif_data['value']));
			}
			break;
		case 'img': 	
			$res = pdo_update('goddess_provevote_picarr',array('new_img'=>0,),array('id'=>$modif_data['value']));
			break;	
		case 'video': 
			$is_has = pdo_get('goddess_provevote_videoarr',array('new_veido'=>0,'from_user'=>$modif_data['from_user'],'rid'=>$modif_data['rid']));
			if($is_has){
				$res1 = pdo_delete('goddess_provevote_videoarr',array('id'=>$is_has['id']));
				if($res1)
					$res = pdo_update('goddess_provevote_videoarr',array('new_veido'=>0,),array('id'=>$modif_data['value']));
			}else{
				$res = pdo_update('goddess_provevote_videoarr',array('new_veido'=>0,),array('id'=>$modif_data['value']));
			}
			break;
	}
	pdo_delete('goddess_ly_modif',array('id'=>$_GPC['id']));
}

$pindex = max(1, intval($_GPC['page']));
$psize = 15;
$total = pdo_fetchcolumn('SELECT COUNT(1) FROM '.tablename('goddess_ly_modif').' WHERE rid = :rid', array(':rid'=>$rid));
$pager = pagination($total, $pindex, $psize);
$modif_list = pdo_fetchall('SELECT * FROM '.tablename('goddess_ly_modif').' WHERE rid = :rid LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':rid' => $rid) );

include $this->template('web/ly_audit');
?>