<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}
class Witchain_analysis_EweiShopV2Page extends WebPage 
{
	public function main() 
	{
		function witchain_analysis_count($sql,$flag=0) 
		{
			$c = pdo_fetchcolumn($sql);
			if($flag==1){
				return floatval($c);
			}else{
				return intval($c);
			}
			
		}
		global $_W;
		global $_GPC;
		$member_count = witchain_analysis_count('SELECT count(*) FROM ' . tablename('ewei_shop_member') . '   WHERE uniacid = \'' . $_W['uniacid'] . '\' ');

		$witchainCount = witchain_analysis_count('SELECT sum(bases) FROM ' . tablename('ewei_shop_member') . '  WHERE uniacid = \'' . $_W['uniacid'] . '\' ');

		$dividendCount=witchain_analysis_count('SELECT sum(increased) FROM ' . tablename('ewei_shop_member') . '  WHERE uniacid = \'' . $_W['uniacid'] . '\' ',1);
		$dividendMemberCount=witchain_analysis_count('SELECT count(*) FROM ' . tablename('ewei_shop_member') . '  WHERE uniacid = \'' . $_W['uniacid'] . '\' and increased>0',1);

		
		$viewcount = witchain_analysis_count('SELECT sum(viewcount) FROM ' . tablename('ewei_shop_goods') . ' WHERE uniacid = \'' . $_W['uniacid'] . '\' ');

		$members=pdo_fetchall("select m.bases,m.pv,m.agentid,w.* from ".tablename('ewei_shop_member')." m left join ".tablename('ewei_shop_member_withdrawdividend_log')." w on m.openid=w.openid where m.uniacid=".$_W['uniacid']);


		

		include $this->template('statistics/witchain_analysis');
	}
}
?>