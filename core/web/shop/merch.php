<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Merch_EweiShopV2Page extends WebPage 
{
	public function main() 
	{
		global $_W;
		global $_GPC;
		$kwd = trim($_GPC['keyword']);
		$params = array();
		$params[':uniacid'] = $_W['uniacid'];
		$condition = ' and uniacid=:uniacid and status=1';
		if (!(empty($kwd))) 
		{
			$condition .= ' AND `merchname` LIKE :keyword';
			$params[':keyword'] = '%' . $kwd . '%';
		}
		$ms = pdo_fetchall('SELECT id,merchname FROM ' . tablename('ewei_shop_merch_user') . ' WHERE 1 ' . $condition . ' order by id asc', $params);
		include $this->template('shop/merch');
		exit();
	}
}
?>