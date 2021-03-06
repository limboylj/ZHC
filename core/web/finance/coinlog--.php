<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Coinlog_EweiShopV2Page extends WebPage 
{
	protected function main($type = 0) 
	{

		global $_W;
		global $_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = ' and log.uniacid=:uniacid and m.uniacid=:uniacid and log.type=:type and log.money<>0';
		$params = array(':uniacid' => $_W['uniacid'], ':type' => $type);
		if (!(empty($_GPC['keyword']))) 
		{
			$_GPC['keyword'] = trim($_GPC['keyword']);
			if ($_GPC['searchfield'] == 'logno') 
			{
				$condition .= ' and log.logno like :keyword';
			}
			else if ($_GPC['searchfield'] == 'member') 
			{
				$condition .= ' and (m.realname like :keyword or m.nickname like :keyword or m.mobile like :keyword)';
			}
			$params[':keyword'] = '%' . $_GPC['keyword'] . '%';
		}
		if (empty($starttime) || empty($endtime)) 
		{
			$starttime = strtotime('-1 month');
			$endtime = time();
		}
		if (!(empty($_GPC['time']['start'])) && !(empty($_GPC['time']['end']))) 
		{
			$starttime = strtotime($_GPC['time']['start']);
			$endtime = strtotime($_GPC['time']['end']);
			$condition .= ' AND log.createtime >= :starttime AND log.createtime <= :endtime ';
			$params[':starttime'] = $starttime;
			$params[':endtime'] = $endtime;
		}
		if (!(empty($_GPC['level']))) 
		{
			$condition .= ' and m.level=' . intval($_GPC['level']);
		}
		if (!(empty($_GPC['groupid']))) 
		{
			$condition .= ' and m.groupid=' . intval($_GPC['groupid']);
		}
		if (!(empty($_GPC['rechargetype']))) 
		{
			$_GPC['rechargetype'] = trim($_GPC['rechargetype']);
			if ($_GPC['rechargetype'] == 'system1') 
			{
				$condition .= ' AND log.rechargetype=\'system\' and log.money<0';
			}
			else 
			{
				$condition .= ' AND log.rechargetype=:rechargetype';
				$params[':rechargetype'] = $_GPC['rechargetype'];
			}
		}
		if ($_GPC['status'] != '') 
		{
			$condition .= ' and log.status=' . intval($_GPC['status']);
		}
		$sql = 'select log.id,m.id as mid, m.realname,m.avatar,m.weixin,log.logno,log.type,log.status,log.rechargetype,log.sendmoney,m.nickname,m.mobile,g.groupname,log.money,log.createtime,l.levelname,log.realmoney,log.deductionmoney,log.charge,log.remark,log.alipay,log.bankname,log.bankcard,log.realname as applyrealname,log.applytype,log.creditducaion from ' . tablename('ewei_shop_member_coinlog') . ' log ' . ' left join ' . tablename('ewei_shop_member') . ' m on m.openid=log.openid' . ' left join ' . tablename('ewei_shop_member_group') . ' g on m.groupid=g.id' . ' left join ' . tablename('ewei_shop_member_level') . ' l on m.level =l.id' . ' where 1 ' . $condition . ' ORDER BY log.createtime DESC ';
		if (empty($_GPC['export'])) 
		{
			$sql .= 'LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;
		}
		$list = pdo_fetchall($sql, $params);
		$apply_type = array(0 => '微信钱包', 2 => '支付宝', 3 => '银行卡');
		if (!(empty($list))) 
		{
			foreach ($list as $key => $value ) 
			{
				$list[$key]['typestr'] = $apply_type[$value['applytype']];
				/*
				if ($value['deductionmoney'] == 0) 
				{
					$list[$key]['realmoney'] = $value['money'];
				}
				*/
			}
		}
		if ($_GPC['export'] == 1) 
		{
			if ($_GPC['type'] == 1) 
			{
				plog('finance.coinlog.withdraw.export', '导出提现记录');
			}
			else 
			{
				plog('finance.coinlog.recharge.export', '导出充值记录');
			}
			foreach ($list as &$row ) 
			{
				$row['createtime'] = date('Y-m-d H:i', $row['createtime']);
				$row['groupname'] = ((empty($row['groupname']) ? '无分组' : $row['groupname']));
				$row['levelname'] = ((empty($row['levelname']) ? '普通会员' : $row['levelname']));
				$row['typestr'] = $apply_type[$row['applytype']];
				if ($row['status'] == 0) 
				{
					if ($row['type'] == 0) 
					{
						$row['status'] = '未充值';
					}
					else 
					{
						$row['status'] = '申请中';
					}
				}
				else if ($row['status'] == 1) 
				{
					if ($row['type'] == 0) 
					{
						$row['status'] = '充值成功';
					}
					else 
					{
						$row['status'] = '完成';
					}
				}
				else if ($row['status'] == -1) 
				{
					if ($row['type'] == 0) 
					{
						$row['status'] = '';
					}
					else 
					{
						$row['status'] = '失败';
					}
				}
				if ($row['rechargetype'] == 'system') 
				{
					$row['rechargetype'] = '后台';
				}
				else if ($row['rechargetype'] == 'wechat') 
				{
					$row['rechargetype'] = '微信';
				}
				else if ($row['rechargetype'] == 'alipay') 
				{
					$row['rechargetype'] = '支付宝';
				}
			}
			unset($row);
			$columns = array();
			$columns[] = array('title' => '昵称', 'field' => 'nickname', 'width' => 12);
			$columns[] = array('title' => '姓名', 'field' => 'realname', 'width' => 12);
			$columns[] = array('title' => '手机号', 'field' => 'mobile', 'width' => 12);
			$columns[] = array('title' => '会员等级', 'field' => 'levelname', 'width' => 12);
			$columns[] = array('title' => '会员分组', 'field' => 'groupname', 'width' => 12);
			$columns[] = array('title' => (empty($type) ? '充值金额' : '提现金额'), 'field' => 'money', 'width' => 12);
			if (!(empty($type))) 
			{
				$columns[] = array('title' => '到账金额', 'field' => 'realmoney', 'width' => 12);
				$columns[] = array('title' => '手续费金额', 'field' => 'deductionmoney', 'width' => 12);
				$columns[] = array('title' => '积分转换', 'field' => 'creditducaion', 'width' => 12);

				$columns[] = array('title' => '提现方式', 'field' => 'typestr', 'width' => 12);
				$columns[] = array('title' => '提现姓名', 'field' => 'applyrealname', 'width' => 24);
				$columns[] = array('title' => '支付宝', 'field' => 'alipay', 'width' => 24);
				$columns[] = array('title' => '银行', 'field' => 'bankname', 'width' => 24);
				$columns[] = array('title' => '银行卡号', 'field' => 'bankcard', 'width' => 24);
				$columns[] = array('title' => '申请时间', 'field' => 'applytime', 'width' => 24);
			}
			$columns[] = array('title' => (empty($type) ? '充值时间' : '提现申请时间'), 'field' => 'createtime', 'width' => 12);
			if (empty($type)) 
			{
				$columns[] = array('title' => '充值方式', 'field' => 'rechargetype', 'width' => 12);
			}
			$columns[] = array('title' => '备注', 'field' => 'remark', 'width' => 24);
			m('excel')->export($list, array('title' => ((empty($type) ? '会员充值数据-' : '会员提现记录')) . date('Y-m-d-H-i', time()), 'columns' => $columns));
		}
		$total = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_coinlog') . ' log ' . ' left join ' . tablename('ewei_shop_member') . ' m on m.openid=log.openid and m.uniacid= log.uniacid' . ' left join ' . tablename('ewei_shop_member_group') . ' g on m.groupid=g.id' . ' left join ' . tablename('ewei_shop_member_level') . ' l on m.level =l.id' . ' where 1 ' . $condition . ' ', $params);
		$pager = pagination($total, $pindex, $psize);
		$groups = m('member')->getGroups();
		$levels = m('member')->getLevels();
		include $this->template();
	}
	
	public function wechat() 
	{

		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);

		$tradeset = m('common')->getSysset('fullbackmoney');

		$log = pdo_fetch('select * from ' . tablename('ewei_shop_member_coinlog') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
		if (empty($log)) 
		{
			show_json(0, '未找到记录!');
		}
		if ($log['deductionmoney'] == 0 && $log['creditducaion'] == 0) 
		{
			$realmoney = $log['money'];
		}
		else 
		{
			$realmoney = $log['realmoney'];
		}
		$set = $_W['shopset']['shop'];
		$data = m('common')->getSysset('pay');
		
		
		if (!(empty($data['paytype']['withdraw']))) 
		{
			$result = m('finance')->payRedPack($log['openid'], $realmoney * 100, $log['logno'], $log, $set['name'] . $tradeset['fullbacktext'].'提现', $data['paytype']);
			pdo_update('ewei_shop_member_coinlog', array('sendmoney' => $result['sendmoney'], 'senddata' => json_encode($result['senddata'])), array('id' => $log['id']));
			if ($result['sendmoney'] == $realmoney) 
			{
				$result = true;
			}
			else 
			{
				$result = $result['error'];
			}
		}
		else 
		{

			$result = m('finance')->pay($log['openid'], 1, $realmoney * 100, $log['logno'], $set['name'] . $tradeset['fullbacktext'].'提现');
		}
		if (is_error($result)) 
		{
			show_json(0, array('message' => $result['message']));
		}
		$flag=pdo_update('ewei_shop_member_coinlog', array('status' => 1), array('id' => $id, 'uniacid' => $_W['uniacid']));
		if($flag!==false){
			if( $log['creditducaion'] > 0){
				m('member')->setCredit($log['openid'], 'credit1', $log['creditducaion'], array(0, $set['name'] . $tradeset['fullbacktext'].'转换积分'));
			}
			
			// 提现时候部分算入pv
			if($tradeset['fullbacktowit'] > 0){
				$pv = 0;
				if($tradeset['fullbackwithdrawtowit']){
					$pv = $realmoney;
				} else {
					$pv = $log['money'];
				}
				$pv = round($pv*$tradeset['fullbacktowit'] / 100,2);
				m('member')->setWit($log['openid'], 'pv', $pv);
				$logno = m('common')->createNO('member_addpv_log', 'logno', 'CTP');
				$apply = [
					'uniacid' => $_W['uniacid'],
					'openid' => $log['openid'],
					'logno' => $logno,
					'fromtype' => 0, // 0补贴提现计入， 1 补贴直接转换 ，2 佣金 , -1系统送的
					'title' => $tradeset['fullbacktext'].'提现计入到pv',
					'createtime' => time(),
					'money' => $pv,
					'withdrawtopv' => $tradeset['fullbacktowit']
				];

				pdo_insert('ewei_shop_member_addpv_log', $apply);
			}
		}


		m('notice')->sendMemberCoinLogMessage($log['id']);
		$member = m('member')->getMember($log['openid']);
		plog('finance.coinlog.wechat', $tradeset['fullbacktext'].'提现 ID: ' . $log['id'] . ' 方式: 微信 '.$tradeset['fullbacktext'].'提现: ' . $log['money'] . ' ,到账金额: ' . $realmoney . ' ,手续费金额 : ' . $log['deductionmoney'] . ',积分转换 : ' . $log['creditducaion'] . '<br/>会员信息:  ID: ' . $member['id'] . ' / ' . $member['openid'] . '/' . $member['nickname'] . '/' . $member['realname'] . '/' . $member['mobile']);
		show_json(1);
	}
	public function alipay() 
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);

		$tradeset = m('common')->getSysset('fullbackmoney');

		$log = pdo_fetch('select * from ' . tablename('ewei_shop_member_coinlog') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
		if (empty($log)) 
		{
			show_json(0, '未找到记录!');
		}
		if ($log['deductionmoney'] == 0 && $log['creditducaion'] == 0) 
		{
			$realmoney = $log['money'];
		}
		else 
		{
			$realmoney = $log['realmoney'];
		}
		$set = $_W['shopset']['shop'];
		$sec = m('common')->getSec();
		$sec = iunserializer($sec['sec']);
		if (!(empty($sec['alipay_pay']['open']))) 
		{
			
			$batch_no_money = $realmoney * 100;
			$batch_no = 'D' . date('Ymd') . 'RW' . $log['id'] . 'MONEY' . $batch_no_money;
			$res = m('finance')->AliPay(array('account' => $log['alipay'], 'name' => $log['realname'], 'money' => $realmoney), $batch_no, $sec['alipay_pay'], $log['title']);
			if (is_error($res)) 
			{
				show_json(0, $res['message']);
			}
			//
			if( $log['creditducaion'] > 0){
				m('member')->setCredit($log['openid'], 'credit1', $log['creditducaion'], array(0, $set['name'] . $tradeset['fullbacktext'].'转换积分'));
			}
		     ///
			 
			 // 提现时候部分算入pv
			if($tradeset['fullbacktowit'] > 0){
				$pv = 0;
				if($tradeset['fullbackwithdrawtowit']){
					$pv = $realmoney;
				} else {
					$pv = $log['money'];
				}
				$pv = round($pv*$tradeset['fullbacktowit'] / 100,2);
				m('member')->setWit($log['openid'], 'pv', $pv);
				$logno = m('common')->createNO('member_addpv_log', 'logno', 'CTP');
				$apply = [
					'uniacid' => $_W['uniacid'],
					'openid' => $log['openid'],
					'logno' => $logno,
					'fromtype' => 0, // 0补贴提现计入， 1 补贴直接转换 ，2 佣金 , -1系统送的
					'title' => $tradeset['fullbacktext'].'提现计入到pv',
					'createtime' => time(),
					'money' => $pv,
					'withdrawtopv' => $tradeset['fullbacktowit']
				];

				pdo_insert('ewei_shop_member_addpv_log', $apply);
			}
			
			show_json(1, array('url' => $res));
		}
		show_json(0, '未开启,支付宝打款!');
	}
	public function manual() 
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);

		$tradeset = m('common')->getSysset('fullbackmoney');

		$log = pdo_fetch('select * from ' . tablename('ewei_shop_member_coinlog') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
		if (empty($log)) 
		{
			show_json(0, '未找到记录!');
		}
		if ($log['deductionmoney'] == 0 && $log['creditducaion'] == 0) 
		{
			$realmoney = $log['money'];
		}
		else 
		{
			$realmoney = $log['realmoney'];
		}
		$member = m('member')->getMember($log['openid']);
		$flag=pdo_update('ewei_shop_member_coinlog', array('status' => 1), array('id' => $id, 'uniacid' => $_W['uniacid']));
		
		if($flag!==false){
			if( $log['creditducaion'] > 0){
				m('member')->setCredit($log['openid'], 'credit1', $log['creditducaion'], array(0, $set['name'] . $tradeset['fullbacktext'].'转换积分'));				
			}
			
			// 提现时候部分算入pv
			if($tradeset['fullbacktowit'] > 0){
				$pv = 0;
				if($tradeset['fullbackwithdrawtowit']){
					$pv = $realmoney;
				} else {
					$pv = $log['money'];
				}
				$pv = round($pv*$tradeset['fullbacktowit'] / 100,2);
				m('member')->setWit($log['openid'], 'pv', $pv);
				$logno = m('common')->createNO('member_addpv_log', 'logno', 'CTP');
				$apply = [
					'uniacid' => $_W['uniacid'],
					'openid' => $log['openid'],
					'logno' => $logno,
					'fromtype' => 0, // 0补贴提现计入， 1 补贴直接转换 ，2 佣金 , -1系统送的
					'title' => $tradeset['fullbacktext'].'提现计入到pv',
					'createtime' => time(),
					'money' => $pv,
					'withdrawtopv' => $tradeset['fullbacktowit']
				];

				pdo_insert('ewei_shop_member_addpv_log', $apply);
			}
		}
		
		m('notice')->sendMemberCoinLogMessage($log['id']);
		plog('finance.coinlog.manual', $tradeset['fullbacktext'].'提现 方式: 手动 ID: ' . $log['id'] . ' <br/>会员信息: ID: ' . $member['id'] . ' / ' . $member['openid'] . '/' . $member['nickname'] . '/' . $member['realname'] . '/' . $member['mobile']);
		show_json(1);
	}
	public function refuse() 
	{
		global $_W;
		global $_GPC;
		$id = intval($_GPC['id']);

		$tradeset = m('common')->getSysset('fullbackmoney');
		
		$log = pdo_fetch('select * from ' . tablename('ewei_shop_member_coinlog') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
		if (empty($log)) 
		{
			show_json(0, '未找到记录!');
		}
		pdo_update('ewei_shop_member_coinlog', array('status' => -1), array('id' => $id, 'uniacid' => $_W['uniacid']));
		if (0 < $log['money']) 
		{
			m('member')->setCredit8($log['openid'], 'credit8', $log['money'], array(0, $set['name'] . $tradeset['fullbacktext'].'提现退回'));
			//拒绝之后滚动
			/*
			$today=strtotime(date("Y-m-d"));
		    $yesterday=strtotime(date("Y-m-d",strtotime("-1 days")));
		    $tomorrow=strtotime(date("Y-m-d",strtotime("+1 days")));
		    
			$taskLog=pdo_fetch("select * from ".tablename("ewei_shop_fullbacktask_log")." where uniacid=".$_W['uniacid']." and openid='".$_W['openid']."' and updatetime>=".$today." and updatetime<".$tomorrow);
			$newmemberinfo=m('member')->getMember($_W['openid']);
			
			$newLogInfo=array(
				'from'=>$newmemberinfo['credit8'],
				'maxto'=>$newmemberinfo['credit8']+($taskLog['money']-$taskLog['fullbacked'])
				);
			pdo_update("ewei_shop_fullbacktask_log",$newLogInfo,array('id'=>$taskLog['id']));
			*/
		}
		m('notice')->sendMemberCoinLogMessage($log['id']);
		plog('finance.coinlog.refuse', '拒绝'.$tradeset['fullbacktext'].'提现 ID: ' . $log['id'] . ' 金额: ' . $log['money'] . ' <br/>会员信息:  ID: ' . $member['id'] . ' / ' . $member['openid'] . '/' . $member['nickname'] . '/' . $member['realname'] . '/' . $member['mobile']);
		show_json(1);
	}
	public function recharge() 
	{
		$this->main(0);
	}
	public function withdraw() 
	{
		$this->main(1);
	}

}
?>