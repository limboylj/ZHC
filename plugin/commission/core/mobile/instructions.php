<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require EWEI_SHOPV2_PLUGIN . 'commission/core/page_login_mobile.php';
class Instructions_EweiShopV2Page extends CommissionMobileLoginPage
{
	public function main()
	{
		global $_W;
		global $_GPC;
		
		$backset = m('common')->getSysset('fullbackmoney');
		$witset = m('common')->getSysset('witchain');
		
		$brief = intval($_GPC['st']);
		
		
		$sharetitle = $brief ? '挖矿攻略简介' : $witset['wittext'].'说明';
		$sharedesc = $brief ? '全球首个“Dian商+区块链”应用商城' : '全球首个“Dian商+区块链”应用商城';
		
		$_W['shopshare'] = array('title' => $sharetitle, 'imgUrl' => '../addons/ewei_shopv2/plugin/commission/template/mobile/default/static/images/crystal1.png', 'desc' => $sharedesc, 'link' => mobileUrl('commission/instructions', array('st' => $brief, 'mid' => $member['id']), true));
		
		include $this->template();
	}
}

?>
