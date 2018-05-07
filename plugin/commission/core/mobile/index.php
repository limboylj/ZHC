<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'commission/core/page_login_mobile.php';
class Index_EweiShopV2Page extends CommissionMobileLoginPage
{
	public function main()
	{
		global $_W;
		global $_GPC;
		
		$tradeset = m('common')->getSysset('trade');
		$set = m('common')->getSysset('fullbackmoney');
		$witset = m('common')->getSysset('witchain');

		$this->diypage('commission');
				
		$params = array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']);
		$merch_plugin = p('merch');
		$merch_data = m('common')->getPluginset('merch');
		if ($merch_plugin && $merch_data['is_openmerch']) 
		{
			$statics = array('order_0' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and status=0 and (isparent=1 or (isparent=0 and parentid=0)) and paytype<>3 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_1' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and (status=1 or (status=0 and paytype=3)) and isparent=0 and refundid=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_2' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and (status=2 or (status=1 and sendtype>0)) and isparent=0 and refundid=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_4' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and refundstate=1 and isparent=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'cart' => pdo_fetchcolumn('select ifnull(sum(total),0) from ' . tablename('ewei_shop_member_cart') . ' where uniacid=:uniacid and openid=:openid and deleted=0', $params), 'favorite' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_favorite') . ' where uniacid=:uniacid and openid=:openid and deleted=0', $params));
		}
		else 
		{
			$statics = array('order_0' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and status=0 and isparent=0 and paytype<>3 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_1' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and (status=1 or (status=0 and paytype=3)) and isparent=0 and refundid=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_2' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and (status=2 or (status=1 and sendtype>0)) and isparent=0 and refundid=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_4' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and refundstate=1 and isparent=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'cart' => pdo_fetchcolumn('select ifnull(sum(total),0) from ' . tablename('ewei_shop_member_cart') . ' where uniacid=:uniacid and openid=:openid and deleted=0 and selected = 1', $params), 'favorite' => ($merch_plugin && $merch_data['is_openmerch'] ? pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_favorite') . ' where uniacid=:uniacid and openid=:openid and deleted=0 and `type`=0', $params) : pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_favorite') . ' where uniacid=:uniacid and openid=:openid and deleted=0', $params)));
		}
		$newstore_plugin = p('newstore');
		if ($newstore_plugin) 
		{
			$statics['norder_0'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and status=0 and isparent=0 and istrade=1 and uniacid=:uniacid', $params);
			$statics['norder_1'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and status=1 and isparent=0 and istrade=1 and refundid=0 and uniacid=:uniacid', $params);
			$statics['norder_3'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and status=3 and isparent=0 and istrade=1 and uniacid=:uniacid', $params);
			$statics['norder_4'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and refundstate=1 and isparent=0 and istrade=1 and uniacid=:uniacid', $params);
		}
		
		$member = $this->model->getInfo($_W['openid'], array('total', 'ordercount0', 'ok', 'ordercount', 'wait', 'pay'));
		//生成红利
		m('member')->createDividend($member['openid']); 

		$cansettle = (1 <= $member['commission_ok']) && (floatval($this->set['withdraw']) <= $member['commission_ok']);
		$level1 = $level2 = $level3 = 0;
		$level1 = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member') . ' where agentid=:agentid and uniacid=:uniacid limit 1', array(':agentid' => $member['id'], ':uniacid' => $_W['uniacid']));
		if ((2 <= $this->set['level']) && (0 < count($member['level1_agentids']))) {
			$level2 = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member') . ' where agentid in( ' . implode(',', array_keys($member['level1_agentids'])) . ') and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
		}

		if ((3 <= $this->set['level']) && (0 < count($member['level2_agentids']))) {
			$level3 = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member') . ' where agentid in( ' . implode(',', array_keys($member['level2_agentids'])) . ') and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
		}

		$member['downcount'] = $level1 + $level2 + $level3;
		$member['applycount'] = pdo_fetchcolumn('select count(id) from ' . tablename('ewei_shop_commission_apply') . ' where mid=:mid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':mid' => $member['id']));
		$openselect = false;

		if ($this->set['select_goods'] == '1') {
			if (empty($member['agentselectgoods']) || ($member['agentselectgoods'] == 2)) {
				$openselect = true;
			}
		}
		else {
			if ($member['agentselectgoods'] == 2) {
				$openselect = true;
			}
		}

		$this->set['openselect'] = $openselect;
		$level = $this->model->getLevel($_W['openid']);
		$up = false;

		if (!empty($member['agentid'])) {
			$up = m('member')->getMember($member['agentid']);
		}

		$hasglobonus = false;
		$plugin_globonus = p('globonus');

		if ($plugin_globonus) {
			$plugin_globonus_set = $plugin_globonus->getSet();
			$hasglobonus = !empty($plugin_globonus_set['open']) && empty($plugin_globonus_set['closecommissioncenter']);
		}

		$hasabonus = false;
		$plugin_abonus = p('abonus');

		if ($plugin_abonus) {
			$plugin_abonus_set = $plugin_abonus->getSet();
			$hasabonus = !empty($plugin_abonus_set['open']) && empty($plugin_abonus_set['closecommissioncenter']);
		}

		$hasauthor = false;
		$plugin_author = p('author');

		if ($plugin_author) {
			$plugin_author_set = $plugin_author->getSet();
			$hasauthor = !empty($plugin_author_set['open']) && empty($plugin_author_set['closecommissioncenter']);

			if ($hasauthor) {
				$team_money = $plugin_author->getTeamPay($member['id']);
			}
		}
		//补贴
		$shopset = m('common')->getSysset('trade');

		
		$newFullbackLog=pdo_fetchall("select * from ".tablename("ewei_shop_fullback_log")." where uniacid=".$_W['uniacid']." and openid='".$_W['openid']."' and isfullback=0 and fullbackedmoney<price and fullbackday<day and fullbacktime<finishtime");

		$fullbackData = [];
		if(!empty($newFullbackLog)){
			$fullbackData = $this->recalculation($newFullbackLog);			
		}
		$member['credit8']=floatval($member['credit8']);
		$minicoin=(floatval($shopset['withdrawcoin'])<=$member['credit8']);
		

		//下级的本金提成
		$lowerBase=m('member')->getLowerBase($member['id']);
		//智汇链利息
		$today=strtotime(date("Y-m-d"));
    	$yesterday=strtotime(date("Y-m-d",strtotime("-1 days")));
    	$tomorrow=strtotime(date("Y-m-d",strtotime("+1 days")));

		$dividendLog=pdo_fetch("select * from ".tablename('ewei_shop_member_dividend_log')." where uniacid=".$_W['uniacid']." and openid='".$_W['openid']."' and createtime>=".$today." and createtime<".$tomorrow);
		
		if(!empty($dividendLog)){
			$datas=iunserializer($dividendLog['dividend']);
		}else{
			m('member')->createDividend($member['openid']); 
			$dividendLog=pdo_fetch("select * from ".tablename('ewei_shop_member_dividend_log')." where uniacid=".$_W['uniacid']." and openid='".$_W['openid']."' and createtime>=".$today." and createtime<".$tomorrow);
			$datas=iunserializer($dividendLog['dividend']);	
		}
		
		$dividend_level = $this->getDividendLevel($datas);
		$datas = $this->filterData($datas);
		
		$showfloat = 2;
		
		if(empty($datas)){
			if($member['increased'] < 1){
				$showfloat = 4;
			}else if($member['increased'] < 10){
				$showfloat = 3;
			}
		} else {
			$firstdata = current($datas)['dividend'];
			if($firstdata < 0.01){
				$showfloat = 5;
			}else if($firstdata < 0.1){
				$showfloat = 4;
			}else if($firstdata < 1){
				$showfloat = 3;
			}
		}


		$sharetitle = $witset['wittext'].'中心';
		$sharedesc = '全球首个“Dian商+区块链”应用商城';
		
		$_W['shopshare'] = array('title' => $sharetitle, 'imgUrl' => './addons/ewei_shopv2/plugin/commission/template/mobile/default/static/images/crystal1.png', 'desc' => $sharedesc, 'link' => mobileUrl($_W['routes'], array('mid' => $member['id']), true));	

		$withdraw_amount = m('member')->WithdrawAmount($member['openid']);//提现额度
		
		include $this->template();
	}
	
	// 重新计算时间排序
	private function recalculation($data){
		$redata = [];
		$result = [];
		foreach($data as $val){
			$lesstime = round($val['finishtime']-$val['fullbacktime'],6);
			// 返回时长对应速度
			if(empty($redata[$lesstime])){
				$redata[''.$lesstime.''] = round($val['priceevery']/($val['units']=='H'?3600:86400),6);
			} else {
				$redata[''.$lesstime.''] += round($val['priceevery']/($val['units']=='H'?3600:86400),6);
			}
			// 按时长由短到长排列
			if(count($redata) > 1){
				ksort($redata);			
			}
		}
		
		if(count($redata) > 1){
			$len = count($redata);
			$shiftdata;
			for($i = 0; $i < $len; $i++){
				$one = 0;
				foreach($redata as $k=>$val){
					if($one > 0){
						break;
					}
					// 把相同时间区域内的速度叠加上去 , 下一个就减去上一个所用时长
					if($i > 0){
						$result[] = [$k-$shiftdata,array_sum($redata)];	
					} else {
						$result[] = [$k,array_sum($redata)];
					}
					unset($redata[$k]);
					$one++;
					$shiftdata = $k;
				}
			}
		} else {
			foreach($redata as $k=>$val){
				$result[] = [$k,$val];				
			}
		}
		return $result;
	}
	
	// 过滤不显示
	private function filterData($data){
		$time = time();
		
		if(!empty($data)){
			foreach($data as $k=>$val){
				if($val['showtime'] > $time || $val['status'] > 0){
					unset($data[$k]);
				}
			}
		} 
		return $data;
	}
	
	private function getDividendLevel($data){
		$level = 0;		
		$sum = 0;
		
		if(!empty($data)){
			foreach($data as $val){
				$sum += $val['dividend'];
			}
		} else {
			return $level;
		}		
		$sum *= 1000;
	
		$witset=m('common')->getSysset('witchain');
		if(!empty($witset)){
			$barr=iunserializer($witset['dividenddata']['data']);
		}
		
		if(!empty($barr)){
			foreach($barr as $value){
				if($sum>=$value['dividendfrom']*$value['dividendrate'])	{
				  $level++;	
				} else {
					break;
				}				
			}
		}
		return $level;
	}
}

?>
