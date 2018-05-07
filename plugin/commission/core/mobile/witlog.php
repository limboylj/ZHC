<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'commission/core/page_login_mobile.php';
class Witlog_EweiShopV2Page extends CommissionMobileLoginPage
{
	public function main()
	{
		global $_W;
		global $_GPC;
		
		$backset = m('common')->getSysset('fullbackmoney');
		$witset = m('common')->getSysset('witchain');
		$commission = p('commission')->getSet()['texts']['commission'];
		
		include $this->template();
	}
	
	
	public function trans_list() 
	{
		global $_W;
		global $_GPC;
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$condition = ' and openid=:openid and uniacid=:uniacid ';
		$params = array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']);
		$list = pdo_fetchall('select * from ' . tablename('ewei_shop_member_trans2wit_log') . ' where 1 ' . $condition . ' order by createtime desc LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
		$total = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_trans2wit_log') . ' where 1 ' . $condition, $params);
		foreach ($list as &$row ) 
		{
			$row['createtime'] = date('Y-m-d H:i', $row['createtime']);
		}
		unset($row);
		show_json(1, array('list' => $list, 'total' => $total, 'pagesize' => $psize));
	}
}

?>
