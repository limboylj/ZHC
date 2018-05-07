<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
require EWEI_SHOPV2_PLUGIN . 'commission/core/page_login_mobile.php';
class Coinlog_EweiShopV2Page extends CommissionMobileLoginPage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		
		$tradeset = m('common')->getSysset('trade');
		$backset = m('common')->getSysset('fullbackmoney');
		$witset = m('common')->getSysset('witchain');
		
		$to = strtolower($_GPC['to']);
		
		include $this->template();
	}
	public function get_list() 
	{
		global $_W;
		global $_GPC;
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$apply_type = array(0 => '微信钱包', 2 => '支付宝', 3 => '银行卡');
		$condition = ' and openid=:openid and uniacid=:uniacid and type=1';
		$params = array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']);
		$list = pdo_fetchall('select * from ' . tablename('ewei_shop_member_coinlog') . ' where 1 ' . $condition . ' order by createtime desc LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
		$total = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_coinlog') . ' where 1 ' . $condition, $params);
		foreach ($list as &$row ) 
		{
			$row['createtime'] = date('Y-m-d H:i', $row['createtime']);
			$row['typestr'] = $apply_type[$row['applytype']];
			$row['creditducaion'] = round($row['creditducaion'],2);
		}
		unset($row);
		show_json(1, array('list' => $list, 'total' => $total, 'pagesize' => $psize));
	}
	
}
?>