<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}
!defined('FMROOT') && define('FMROOT', IA_ROOT . '/addons/goddess/');
!defined('FM_CORE') && define('FM_CORE', IA_ROOT . '/addons/goddess/core/');
!defined('FMURL') && define('FMURL', IA_ROOT . '/addons/goddess/template/');
!defined('FM_PHOTOSVOTE_AUTH_URL') && define('FM_PHOTOSVOTE_AUTH_URL', 'http://api.fmoons.com/api/');
!defined('FM_PHOTOSVOTE_RESOURCE_URL') && define('FM_PHOTOSVOTE_RESOURCE_URL',   '../addons/goddess/');
!defined('FM_PHOTOSVOTE_PAYMENT') && define('FM_PHOTOSVOTE_PAYMENT', FM_PHOTOSVOTE_RESOURCE_URL . 'payment/');
!defined('FM_STATIC_MOBILE') && define('FM_STATIC_MOBILE', FM_PHOTOSVOTE_RESOURCE_URL.'static/mobile/');
!defined('FM_STATIC_WEB') && define('FM_STATIC_WEB', FM_PHOTOSVOTE_RESOURCE_URL.'static/web/');
!defined('FM_STATIC_PLUGIN') && define('FM_STATIC_PLUGIN', FM_PHOTOSVOTE_RESOURCE_URL.'static/plugin/');
!defined('FMFILE') && define('FMFILE', IA_ROOT.'/addons/goddess/template/mobile/');