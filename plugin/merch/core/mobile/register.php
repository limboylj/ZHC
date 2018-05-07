<?php
class Register_EweiShopV2Page extends PluginMobileLoginPage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$set = $_W['shopset']['merch'];
		
		if (empty($set['apply_openmobile'])) 
		{
			$this->message('未开启商户入驻申请', '', 'error');
		}
		
		if(empty($member)){
			$member = m('member')->getMember($_W['openid']);
		}
		if($set['lesszhc'] > 0 && $member['bases'] < $set['lesszhc']){
			$this->message('您的'.$_W['shopset']['witchain']['wittext'].'不足'.$set['lesszhc'].'不能申请入驻', '', 'error');
		}
		$reg = pdo_fetch('select * from ' . tablename('ewei_shop_merch_reg') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
		$user = false;
		if (!(empty($reg['status']))) 
		{
			$user = pdo_fetch('select * from ' . tablename('ewei_shop_merch_user') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
		}
		if (!(empty($user))) 
		{
			$this->message('您已经申请，无需重复申请!', '', 'error');
		}
		$apply_set = array();
		$apply_set['open_protocol'] = $set['open_protocol'];
		if (empty($set['applytitle'])) 
		{
			$apply_set['applytitle'] = '入驻申请协议';
		}
		else 
		{
			$apply_set['applytitle'] = $set['applytitle'];
		}
		$apply_set['thumbsize'] = $set['thumbsize'];
		$template_flag = 0;
		$diyform_plugin = p('diyform');
		$fields = array();
		if ($diyform_plugin) 
		{
			$area_set = m('util')->get_area_config_set();
			$new_area = intval($area_set['new_area']);
			if (!(empty($set['apply_diyform'])) && !(empty($set['apply_diyformid']))) 
			{
				$template_flag = 1;
				$diyform_id = $set['apply_diyformid'];
				if (!(empty($diyform_id))) 
				{
					$formInfo = $diyform_plugin->getDiyformInfo($diyform_id);
					$fields = $formInfo['fields'];
					$diyform_data = iunserializer($reg['diyformdata']);
					$member = m('member')->getMember($_W['openid']);
					$f_data = $diyform_plugin->getDiyformData($diyform_data, $fields, $member);
				}
			}
		}
		if ($_W['ispost']) 
		{
			if (empty($set['apply_openmobile'])) 
			{
				show_json(0, '未开启商户入驻申请!');
			}
			if($set['lesszhc'] > 0 && $member['bases'] < $set['lesszhc']){
				show_json(0,'您的'.$_W['shopset']['witchain']['wittext'].'不足'.$set['lesszhc'].'不能申请入驻');
			}
			
			if (!(empty($user)) && (1 <= $user['status'])) 
			{
				show_json(0, '您已经申请，无需重复申请!');
			}
			$uname = trim($_GPC['uname']);
			$upass = $_GPC['upass'];
			if (empty($uname)) 
			{
				show_json(0, '请填写帐号!');
			}
			if (empty($upass)) 
			{
				show_json(0, '请填写密码!');
			}
			
			$shopimg = $_GPC['shopimg'];
			if(empty($shopimg)){
				show_json(0, '请上传商铺照片!');
			}
			$certificate = $_GPC['certificate'];
			if(empty($certificate)){
				show_json(0, '请上传营业执照!');
			}
			$certificate = $_GPC['certificate'];
			$where1 = ' uname=:uname';
			$params1 = array(':uname' => $uname);
			if (!(empty($reg))) 
			{
				$where1 .= ' and id<>:id';
				$params1[':id'] = $reg['id'];
			}
			$usercount1 = pdo_fetchcolumn('select count(1) from ' . tablename('ewei_shop_merch_reg') . ' where ' . $where1 . ' limit 1', $params1);
			$where2 = ' username=:username';
			$params2 = array(':username' => $uname);
			$usercount2 = pdo_fetchcolumn('select count(1) from ' . tablename('ewei_shop_merch_account') . ' where ' . $where2 . ' limit 1', $params2);
			if ((0 < $usercount1) || (0 < $usercount2)) 
			{
				show_json(0, '帐号 ' . $uname . ' 已经存在,请更改!');
			}
			$upass = m('util')->pwd_encrypt($upass, 'E');
			$data = array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid'], 'status' => 0, 'realname' => trim($_GPC['realname']), 'mobile' => trim($_GPC['mobile']), 'uname' => $uname, 'upass' => $upass, 'merchname' => trim($_GPC['merchname']), 'salecate' => trim($_GPC['salecate']), 'desc' => trim($_GPC['desc']), 'address' => trim($_GPC['address']), 'tel' => trim($_GPC['tel']), 'shopimg' => implode(';',$shopimg), 'certificate' => $certificate, 'othercertificate' => empty($_GPC['othercertificate'])?'':implode(';',$_GPC['othercertificate']));
			if ($template_flag == 1) 
			{
				$mdata = $_GPC['mdata'];
				$insert_data = $diyform_plugin->getInsertData($fields, $mdata);
				$datas = $insert_data['data'];
				$m_data = $insert_data['m_data'];
				$mc_data = $insert_data['mc_data'];
				$data['diyformfields'] = iserializer($fields);
				$data['diyformdata'] = $datas;
			}
			if (empty($reg)) 
			{
				$data['applytime'] = time();
				pdo_insert('ewei_shop_merch_reg', $data);
			}
			else 
			{
				pdo_update('ewei_shop_merch_reg', $data, array('id' => $reg['id']));
			}
			$this->model->sendMessage(array('merchname' => $data['merchname'], 'salecate' => $data['salecate'], 'realname' => $data['realname'], 'mobile' => $data['mobile'], 'applytime' => time()), 'merch_apply');
			show_json(1);
		}
		
		$sharetitle = '入驻'.$_W['shopset']['witchain']['wittext'].'商圈';
		$sharedesc = '免费入驻'.$_W['shopset']['witchain']['wittext'].'商圈,正在火热申请中...';
		
		$_W['shopshare'] = array('title' => $sharetitle, 'imgUrl' => tomedia($member['avatar']), 'desc' => $sharedesc, 'link' => mobileUrl($_W['routes'], array('mid' => $member['id']), true));				
		
		$shopimg = null;
		$attachment_dir = str_replace('//','/',ATTACHMENT_ROOT.'/');
		if(!empty($reg)){
			$shopimg = explode(';',$reg['shopimg']);
			foreach($shopimg as $k=>$one){
				if(!is_file($attachment_dir.$one)){
					unset($shopimg[$k]);
				}
			}
		}
		$certificate = is_file($attachment_dir.$reg['certificate'])? $reg['certificate']:null;
		$othercertificate = null;
		if(!empty($reg['othercertificate'])){
			$othercertificate = explode(';',$reg['othercertificate']);
			$attachment_dir = str_replace('//','/',ATTACHMENT_ROOT.'/');
			foreach($othercertificate as $k=>$one){
				if(!is_file($attachment_dir.$one)){
					unset($othercertificate[$k]);
				}
			}
		}
		
		include $this->template();
	}
	public function notice() 
	{
		global $_W;
		$set = $_W['shopset']['merch'];
		include $this->template('merch/register_notice');
	}
	public function message($msg, $redirect = '', $type = '') 
	{
		global $_W;
		$title = '';
		$buttontext = '';
		$message = $msg;
		if (is_array($msg)) 
		{
			$message = ((isset($msg['message']) ? $msg['message'] : ''));
			$title = ((isset($msg['title']) ? $msg['title'] : ''));
			$buttontext = ((isset($msg['buttontext']) ? $msg['buttontext'] : ''));
		}
		if (empty($redirect)) 
		{
			$redirect = 'javascript:history.back(-1);';
		}
		else if ($redirect == 'close') 
		{
			$redirect = 'javascript:WeixinJSBridge.call("closeWindow")';
		}
		$buttondisplay = true;
		include $this->template('_message');
		exit();
	}
}
?>