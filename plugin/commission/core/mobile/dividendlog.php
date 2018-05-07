<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class dividendlog_EweiShopV2Page extends MobileLoginPage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$_GPC['type'] = intval($_GPC['type']);
		include $this->template();
	}
	public function get_list() 
	{
		global $_W;
		global $_GPC;
		$type = intval($_GPC['type']);
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		if($type){
			$apply_type = array(0 => '微信钱包', 2 => '支付宝', 3 => '银行卡');
			$condition = ' and openid=:openid and uniacid=:uniacid';
			$params = array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']);
			$list = pdo_fetchall('select * from ' . tablename('ewei_shop_member_withdrawdividend_log') . ' where 1 ' . $condition . ' order by createtime desc LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
			$total = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_withdrawdividend_log') . ' where 1 ' . $condition, $params);
			foreach ($list as &$row ) 
			{
				$row['createtime'] = date('Y-m-d H:i', $row['createtime']);
				$row['typestr'] = $apply_type[$row['applytype']];
			}
			unset($row);
		} else {
			$condition = ' and openid=:openid and uniacid=:uniacid';
			$params = array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']);
			$list = pdo_fetchall('select * from ' . tablename('ewei_shop_member_dividend_log') . ' where 1 ' . $condition . ' order by createtime desc LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
			$total = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_dividend_log') . ' where 1 ' . $condition, $params);
			foreach ($list as &$row ) 
			{
				$row['createtime'] = date('Y-m-d', $row['createtime']);
				$row['diviend'] = $this->sumDividend(iunserializer($row['dividend']));
			}
			unset($row);
		}
		
		show_json(1, array('list' => $list, 'total' => $total, 'pagesize' => $psize));
	}
	
	public function gain(){
		global $_W;
		global $_GPC;
		
		$id = intval($_GPC['id']);
		$did = intval($_GPC['did']);
		//
		$open_redis = function_exists('redis') && !(is_error(redis()));
		if ($open_redis) 
		{
			$redis_key = $_W['uniacid'] . '_dividend_submit_' . $id."_".$did;
			$redis = redis();
			if (!(is_error($redis))) 
			{
				if ($redis->setnx($redis_key, time())) 
				{
					$redis->expireAt($redis_key, time() + 2);
				}
				else if (($redis->get($redis_key) + 2) < time()) 
				{
					$redis->del($redis_key);
				}
				else 
				{
					show_json(0);
				}
			}
		}
		
		$member = m('member')->getMember($_W['openid']);
		//智汇链利息
		$nowTime=time();
		$today=strtotime(date("Y-m-d"));
		
    	$tomorrow=strtotime(date("Y-m-d",strtotime("+1 days")));

		$profitInfo =pdo_fetch("select id,dividend from ".tablename('ewei_shop_member_dividend_log')." where uniacid=".$_W['uniacid']." and openid='".$_W['openid']."' and id=".$id);
		if(!empty($profitInfo)){
			$datas=iunserializer($profitInfo['dividend']);
			
			if($datas[$did]['showtime']>=$today && $datas[$did]['showtime']<$tomorrow){
				
				if($datas[$did]['status']==0 && empty($datas[$did]['endtime'])){//如果是未拾取的状态 才进行红利增加
					$datas[$did]['status']=1;
					$datas[$did]['endtime']=time();
					pdo_update('ewei_shop_member_dividend_log',['dividend'=>serialize($datas)],['id'=>$profitInfo['id']]);
					pdo_update('ewei_shop_member',['increased'=>($member['increased']+floatval($datas[$did]['dividend']))],['id'=>$member['id']]);
					show_json(1);
				}
			}else{
				show_json(0,'无利息');
			}
		} else {			
			show_json(0,'无利息');			
		}
		
	}
	
	private function sumDividend($arr){
		$sum = 0;
		if(!empty($arr)){
			foreach($arr as $d){
				if($d['status']){
					$sum += $d['dividend'];
				}
			}
		}	
		return $sum;
	}
}
?>