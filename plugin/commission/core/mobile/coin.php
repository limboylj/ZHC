<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
require EWEI_SHOPV2_PLUGIN . 'commission/core/page_login_mobile.php';
class Coin_EweiShopV2Page extends CommissionMobileLoginPage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$tradeset = m('common')->getSysset('trade');
		$set = m('common')->getSysset('fullbackmoney');
		$witset = m('common')->getSysset('witchain');
		$status = 1;
		$openid = $_W['openid'];
		
		$method = strtolower($_GPC['to']);
		if(empty($method)){
			
		} else if($method == 'cash'){			
			if (empty($set['fullbackwithdraw'])) 
			{
				$this->message('系统未开启提现!', '', 'error');
			}
		} else if($method == 'wit'){
			if (empty($set['isfullbacktowit'])) 
			{
				$this->message('系统未开启转换!', '', 'error');
			}
		}
		$withdrawcharge = $set['fullbackwithdrawcharge'];
		$withdrawbegin = floatval($set['fullbackwithdrawbegin']);
		$withdrawend = floatval($set['fullbackwithdrawend']);
		// 扣除比例到积分
		$cointocredit=$set['fullbacktocredit'];
		
		$credit=pdo_fetchcolumn("select credit8 from ".tablename("ewei_shop_member")." where uniacid=".$_W['uniacid']." and openid='".$_W['openid']."'");
		$credit=floatval(intval($credit*100)/100);

		//上次提现数据
		$last_data = $this->getLastApply($openid);

		$type_array = array();
		if ($set['fullbackwithdrawcashweixin'] == 1) 
		{
			$type_array[0]['title'] = '提现到微信钱包';
		}
		if ($set['fullbackwithdrawcashalipay'] == 1) 
		{
			$type_array[2]['title'] = '提现到支付宝';
			if (!(empty($last_data))) 
			{
				if ($last_data['applytype'] != 2) 
				{
					$type_last = $this->getLastApply($openid, 2);
					if (!(empty($type_last))) 
					{
						$last_data['alipay'] = $type_last['alipay'];
					}
				}
			}
		}
		if ($set['fullbackwithdrawcashcard'] == 1) 
		{
			$type_array[3]['title'] = '提现到银行卡';
			if (!(empty($last_data))) 
			{
				if ($last_data['applytype'] != 3) 
				{
					$type_last = $this->getLastApply($openid, 3);
					if (!(empty($type_last))) 
					{
						$last_data['bankname'] = $type_last['bankname'];
						$last_data['bankcard'] = $type_last['bankcard'];
					}
				}
			}
			$condition = ' and uniacid=:uniacid';
			$params = array(':uniacid' => $_W['uniacid']);
			$banklist = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_commission_bank') . ' WHERE 1 ' . $condition . '  ORDER BY displayorder DESC', $params);
		}
		if (!(empty($last_data))) 
		{
			if (array_key_exists($last_data['applytype'], $type_array)) 
			{
				$type_array[$last_data['applytype']]['checked'] = 1;
			}
		}
		include $this->template();
	}
	public function submit() 
	{
		global $_W;
		global $_GPC;
		$tradeset = m('common')->getSysset('trade');
		$set = m('common')->getSysset('fullbackmoney');
		$witset = m('common')->getSysset('witchain');
		$set['fullbacktext'] = empty($set['fullbacktext']) ? '补贴' : $set['fullbacktext'];
		if (empty($set['fullbackwithdraw'])) 
		{
			show_json(0, '系统未开启提现!');
		}
		$set_array = array();
		$set_array['charge'] = $set['fullbackwithdrawcharge'];
		$set_array['begin'] = floatval($set['fullbackwithdrawbegin']);
		$set_array['end'] = floatval($set['fullbackwithdrawend']);
		$money = floatval($_GPC['money']);

		$credit=pdo_fetchcolumn("select credit8 from ".tablename("ewei_shop_member")." where uniacid=".$_W['uniacid']." and openid='".$_W['openid']."'");

		if ($money <= 0) 
		{
			show_json(0, '提现金额错误!');
		}
		if ($credit < $money) 
		{
			show_json(0, '提现金额过大!');
		}
		$apply = array();
		$type_array = array();
		if ($set['fullbackwithdrawcashweixin'] == 1) 
		{
			$type_array[0]['title'] = '提现到微信钱包';
		}
		if ($set['fullbackwithdrawcashalipay'] == 1) 
		{
			$type_array[2]['title'] = '提现到支付宝';
		}
		if ($set['fullbackwithdrawcashcard'] == 1) 
		{
			$type_array[3]['title'] = '提现到银行卡';
			$condition = ' and uniacid=:uniacid';
			$params = array(':uniacid' => $_W['uniacid']);
			$banklist = pdo_fetchall('SELECT * FROM ' . tablename('ewei_shop_commission_bank') . ' WHERE 1 ' . $condition . '  ORDER BY displayorder DESC', $params);
		}
		$applytype = intval($_GPC['applytype']);
		if (!(array_key_exists($applytype, $type_array))) 
		{
			show_json(0, '未选择提现方式，请您选择提现方式后重试!');
		}
		if ($applytype == 2) 
		{
			$realname = trim($_GPC['realname']);
			$alipay = trim($_GPC['alipay']);
			$alipay1 = trim($_GPC['alipay1']);
			if (empty($realname)) 
			{
				show_json(0, '请填写姓名!');
			}
			if (empty($alipay)) 
			{
				show_json(0, '请填写支付宝帐号!');
			}
			if (empty($alipay1)) 
			{
				show_json(0, '请填写确认帐号!');
			}
			if ($alipay != $alipay1) 
			{
				show_json(0, '支付宝帐号与确认帐号不一致!');
			}
			$apply['realname'] = $realname;
			$apply['alipay'] = $alipay;
		}
		else if ($applytype == 3) 
		{
			$realname = trim($_GPC['realname']);
			$bankname = trim($_GPC['bankname']);
			$bankcard = trim($_GPC['bankcard']);
			$bankcard1 = trim($_GPC['bankcard1']);
			if (empty($realname)) 
			{
				show_json(0, '请填写姓名!');
			}
			if (empty($bankname)) 
			{
				show_json(0, '请选择银行!');
			}
			if (empty($bankcard)) 
			{
				show_json(0, '请填写银行卡号!');
			}
			if (empty($bankcard1)) 
			{
				show_json(0, '请填写确认卡号!');
			}
			if ($bankcard != $bankcard1) 
			{
				show_json(0, '银行卡号与确认卡号不一致!');
			}
			$apply['realname'] = $realname;
			$apply['bankname'] = $bankname;
			$apply['bankcard'] = $bankcard;
		}
		$realmoney = $money;
		if (!(empty($set_array['charge']))) 
		{
			$money_array = m('member')->getCalculateMoney($money, $set_array);
			if ($money_array['flag']) 
			{
				$realmoney = $money_array['realmoney'];
				$deductionmoney = $money_array['deductionmoney'];
			}
		}
		// 积分转换
		if (!(empty($set['fullbacktocredit']))) 
		{
			$cointocredit=$money*$set['fullbacktocredit']/100;
			$realmoney=$realmoney-$cointocredit;
	    }
		m('member')->setCredit8($_W['openid'], 'credit8', -$money, array(0, $set['fullbacktext'] . '提现预扣除: ' . $money . ',实际到账金额:' . $realmoney . ',手续费金额:' . $deductionmoney.',转换'.$tradeset['credittext'].'金额'.$cointocredit,1));
		
		//
		$logno = m('common')->createNO('member_coinlog', 'logno', 'CW');
		$apply = [
			'uniacid' => $_W['uniacid'],
			'logno' => $logno,
			'openid' => $_W['openid'],
			'title' => $set['fullbacktext'].'提现',
			'type' => 1,
			'createtime' => time(),
			'status' => 0,
			'money' => $money,
			'realmoney' => $realmoney,
			'deductionmoney' => $deductionmoney,
			'charge' => $set_array['charge'],
			'applytype' => $applytype,
			'creditcharge' => $set['fullbacktocredit'],
			'creditducaion' => $cointocredit,
		];


		pdo_insert('ewei_shop_member_coinlog', $apply);
		$logid = pdo_insertid();
		m('notice')->sendMemberCoinLogMessage($logid);
		show_json(1,print_r($apply,true));
	}
	
	public function getLastApply($openid, $applytype = -1) 
	{
		global $_W;
		$params = array(':uniacid' => $_W['uniacid'], ':openid' => $openid);
		$sql = 'select applytype,alipay,bankname,bankcard,realname from ' . tablename('ewei_shop_member_log') . ' where openid=:openid and uniacid=:uniacid';
		if (-1 < $applytype) 
		{
			$sql .= ' and applytype=:applytype';
			$params[':applytype'] = $applytype;
		}
		$sql .= ' order by id desc Limit 1';
		$data = pdo_fetch($sql, $params);
		return $data;
	}
	
	// 转换成智慧链
	public function coin2wit(){		
		global $_W;
		global $_GPC;
		$tradeset = m('common')->getSysset('trade');
		$set = m('common')->getSysset('fullbackmoney');
		$witset = m('common')->getSysset('witchain');
		if (empty($set['isfullbacktowit'])) 
		{
			show_json(0, '系统未开启转换!');
		}
		
		$money = floatval($_GPC['money']);

		$result=pdo_fetch("select credit8,bases from ".tablename("ewei_shop_member")." where uniacid=".$_W['uniacid']." and openid='".$_W['openid']."'");
		$currentcoin=$result['credit8'];
        if($witset['maxbases']>0){
        	if($result['bases']>=$witset['maxbases']){
				show_json(0,'最大'.$witset['wittext'].'不能超过'.$witset['maxbases']);
			}else{
				if($witset['maxbases']<($result['bases']+$money)){
				    $money=$witset['maxbases']-$result['bases'];
			    }
			}
        }
		
		if ($money <= 0) 
		{
			show_json(0, '转换金额错误!');
		}
		if ($currentcoin < $money) 
		{
			show_json(0, '转换金额过大!');
		}
		m('member')->setCredit8($_W['openid'], 'credit8', -$money, array(0, $set['fullbacktext'] . '转换到' . $witset['wittext'] . '扣除' . $set['fullbacktext'] . ': ' . $money . ',' . $witset['wittext'] . '增加' . $money ));
		m('member')->setWit($_W['openid'], 'bases', $money);
		m('member')->setWit($_W['openid'], 'pv', $money);
		$logno = m('common')->createNO('member_addpv_log', 'logno', 'CTP');
		$apply = [
			'uniacid' => $_W['uniacid'],
			'openid' => $_W['openid'],
			'logno' => $logno,
			'fromtype' => 1, // 0补贴提现计入， 1 补贴直接转换 ，2 佣金
			'title' => $set['fullbacktext'].'转换计入到pv',
			'createtime' => time(),
			'money' => $money,
			'withdrawtopv' => 0
		];

		pdo_insert('ewei_shop_member_addpv_log', $apply);
		
		//转换之后的滚动修改
		/*$today=strtotime(date("Y-m-d"));
	    $yesterday=strtotime(date("Y-m-d",strtotime("-1 days")));
	    $tomorrow=strtotime(date("Y-m-d",strtotime("+1 days")));
	    
		$taskLog=pdo_fetch("select * from ".tablename("ewei_shop_fullbacktask_log")." where uniacid=".$_W['uniacid']." and openid='".$_W['openid']."' and updatetime>=".$today." and updatetime<".$tomorrow);
		$newmemberinfo=m('member')->getMember($_W['openid']);
		
		$newLogInfo=array(
			'from'=>$newmemberinfo['credit8'],
			'maxto'=>$newmemberinfo['credit8']+($taskLog['money']-$taskLog['fullbacked'])
			);
		pdo_update("ewei_shop_fullbacktask_log",$newLogInfo,array('id'=>$taskLog['id']));*/
		//
		$logno = m('common')->createNO('member_trans2wit_log', 'logno', 'CTW');
		$apply = [
			'uniacid' => $_W['uniacid'],
			'openid' => $_W['openid'],
			'logno' => $logno,
			'fromtype' => 1, // 1 补贴 ，2 佣金
			'title' => $set['fullbacktext'].'转换到'.$witset['wittext'],
			'createtime' => time(),
			'money' => $money,
			'realmoney' => $money
		];

		pdo_insert('ewei_shop_member_trans2wit_log', $apply);
		$logid = pdo_insertid();
		m('notice')->sendMemberCTWLogMessage($logid);
		show_json(1,'yoho');
	}
}
?>