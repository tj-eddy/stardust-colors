<?php
class ThemeLicense
{
	const API_CALL_EXCEOPTION = -1;

	const ERROR_IN_ALL_STORE = -2;

	private static $instance;

	private $api_url = 'https://www.sunnytoo.com/themelic.php';

	private $dl_api_url = 'https://download.sunnytoo.com';

	public $themeeditor;

	public $token;

	public $config_path = _PS_MODULE_DIR_.'stthemeeditor/config/';

	public $backup_log_file = 'file-backup-log.html';

	public $update_log_file = 'file-auto-update-log.html';

	public function __construct($themeeditor)
	{
		$this->themeeditor = $themeeditor;
		$this->checkGoumaima();
	}

	public static function getInstance($themeeditor)
	{
		if (!self::$instance) {
			self::$instance = new ThemeLicense($themeeditor);
		}
		return self::$instance;
	}

	public function validateLicense($goumaima = '')
	{
		if ($goumaima) {
			$param = $this->getLicenseParams('vallic', $goumaima);
			if ($data = $this->makeCall($param)) {
				$this->writeLog('vallic '.print_r($data, true));
				if (isset($data['err']) && !$data['err']) {
					return true;
				}
			}
		}
		return false;
	}

	public function registerLicense($goumaima = '')
	{
		if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
			return self::ERROR_IN_ALL_STORE;
		}
		if ($goumaima) {
			$param = $this->getLicenseParams('reglic', $goumaima, true);
			if ($data = $this->makeCall($param)) {
				$this->writeLog('reglic '.$goumaima.': '.print_r($data, true));
				if (isset($data['err']) && !$data['err']) {
					$this->token = $data['token'];
					return true;
				}
				if (isset($data['err']) && $data['err']) {
					return $data['msg'];
				}
			} else {
				return self::API_CALL_EXCEOPTION;
			}
		}
		return false;
	}

	public function unRegisterLicense() 
	{
		if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
			return self::ERROR_IN_ALL_STORE;
		}
		if ($token = $this->getGoumaimaToken()) {
			$param = $this->getLicenseParams('deregister', '');
			$param['token'] = $token;
			if ($data = $this->makeCall($param)) {
				$this->writeLog('deregister '.print_r($data, true));
				if (isset($data['err']) && !$data['err']) {
					return true;
				}
				if (isset($data['err']) && $data['err']) {
					return $data['msg'];
				}
			} else {
				return self::API_CALL_EXCEOPTION;
			}
		}
		return true;
	}

	public function validateGoumaima()
	{
		if ($token = $this->getGoumaimaToken()) {
			$param = $this->getLicenseParams('getbytoken', '');
			$param['token'] = $token;
			if ($data = $this->makeCall($param)) {
				$this->writeLog('getbytoken '.print_r($data, true));
				if (isset($data['err']) && !$data['err']) {
					foreach(Shop::getShops(false) as $shop) {
						$domain = str_replace('www.', '', $shop['domain']);
						if ($data['msg']['pc_domain'] == $domain) {
							return true;
						}	
					}
					return false;
				}
			} else {
				// Net or other erro, return true.
				return true;
			}
		}
		return false;
	}

	public function doValidateGoumai()
	{
		if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
			return false;
		}
		if (!$this->validateGoumaima()) {
			Configuration::updateValue('STSN_GOUMAIMA_VALID', 0);
		} else {
			Configuration::updateValue('STSN_GOUMAIMA_VALID', 1);
		}
		Configuration::updateValue('STSN_GOUMAIMA_LAST_VALIDATE', time());
	}

	public function checkGoumaima()
	{
		if ($this->isRegistered() && (Configuration::get('STSN_GOUMAIMA_LAST_VALIDATE')===false || (time() - Configuration::get('STSN_GOUMAIMA_LAST_VALIDATE')) > 86400 * 15)) {
			$this->doValidateGoumai();
		}
	}

	public function GoumaimaIsValid()
	{
		if(file_exists(_PS_MODULE_DIR_.$this->themeeditor->name.'/classes/easy.php'))
			return true;
		if (!$this->isRegistered()) {
			$shop = (object)Shop::getShop((int)Context::getContext()->shop->id);
			if (!(bool)Configuration::get('PS_SHOP_ENABLE') || !(bool)Configuration::get('PS_SHOP_ENABLE') || strpos($shop->domain, 'localhost') !== false || strpos($shop->domain, '127.0.0.1') !== false || strpos($shop->domain, '192.168') !== false) {
				return true;
			}
			return;
		}
		return (int)Configuration::get('STSN_GOUMAIMA_VALID') > 0;
	}

	public static function themeIsValid()
	{
		if(file_exists(_PS_MODULE_DIR_.'stthemeeditor/classes/easy.php'))
			return true;
		if (!Configuration::get('STSN_GOUMAIMA')) {
			$shop = (object)Shop::getShop((int)Context::getContext()->shop->id);
			if (!(bool)Configuration::get('PS_SHOP_ENABLE') || !(bool)Configuration::get('PS_SHOP_ENABLE') || strpos($shop->domain, 'localhost') !== false || strpos($shop->domain, '127.0.0.1') !== false || strpos($shop->domain, '192.168') !== false) {
				return true;
			}
			return;
		}
		return (int)Configuration::get('STSN_GOUMAIMA_VALID') > 0;
	}

	public function getLiceseInfo($goumaima = '')
	{
		if ($goumaima) {
			$param = $this->getLicenseParams('qurylic', $goumaima);
			if ($data = $this->makeCall($param)) {
				$this->writeLog('qurylic '.print_r($data, true));
				if (isset($data['err']) && !$data['err']) {
					return $data['msg'];
				}
			}
		}
		return false;
	}

	public function getLicenseParams($act, $goumaima, $need_domain = false)
	{
		$params = array(
			'pc' => $goumaima,
			'act' => $act,
			'ck_key' => defined('_COOKIE_KEY_') ? _COOKIE_KEY_ : '',
			'ck_iv' => defined('_COOKIE_IV_') ? _COOKIE_IV_ : '',
		);
		if ($need_domain) {
			$shop = (object)Shop::getShop((int)Context::getContext()->shop->id);
			$params['dm'] = $shop->domain;
		}
		return $params;
	}

	public function updateGoumaima($goumaima = null)
	{
		if ($goumaima) {
			Configuration::updateValue('STSN_GOUMAIMA', $goumaima);
			Configuration::updateValue('STSN_GOUMAIMA_TOKEN', $this->token);
			Configuration::updateValue('STSN_GOUMAIMA_VALID', 1);
			Configuration::updateValue('STSN_GOUMAIMA_LAST_VALIDATE', time());
		} else {
			Configuration::updateValue('STSN_GOUMAIMA', '');
			Configuration::updateValue('STSN_GOUMAIMA_TOKEN', '');
			Configuration::updateValue('STSN_GOUMAIMA_VALID', 0);
			Configuration::updateValue('STSN_GOUMAIMA_LAST_VALIDATE', 0);
		}
	}

	public function getGoumaima($with_mask = false)
	{
		$goumaima = Configuration::get('STSN_GOUMAIMA');
		if($goumaima=='')
			return '';
		if ($with_mask) {
			$mask = str_repeat('*', strlen($goumaima)-6);
			$goumaima = preg_replace('/^(\d{3})(.+)(\d{3})$/Us','${1}'.$mask.'${3}', $goumaima);
		}
		return $goumaima;
	}

	public function getGoumaimaToken()
	{
		return Configuration::get('STSN_GOUMAIMA_TOKEN');
	}

	public function isRegistered()
	{
		return $this->getGoumaima() ? true : false;
	}

	public function writeLog($content)
	{
		if ($content) {
			$date = date('Y-m-d H:i:s');
			@file_put_contents(_PS_MODULE_DIR_.$this->themeeditor->name.'/config/theme-ctl.log', $date.' '.$content."\n", FILE_APPEND);
		}
	}

	public function getVerInfo()
	{
		if (!isset($_SESSION['st_version_info']) || !$_SESSION['st_version_info']) {
			$api_url = $this->dl_api_url . '/version.php';
			$theme = $this->getTheme();
			$param = array(
				'theme' => $theme,
				'ver_only' => false,
			);
			if ($data = $this->makeCall($param, $api_url)) {
				$_SESSION['st_version_info'] = $data;
			} else {
				$_SESSION['st_version_info'] = '';
			}
		}
		return $_SESSION['st_version_info'];
	}

	public function getByKey($key)
	{
		$data = $this->getVerInfo();
		if(!$data || !is_array($data))
			return false;
		return key_exists($key, $data) ? $data[$key] : false;
	}

	public function getTheme($version = true)
	{
		$theme = strtolower(_THEME_NAME_);
        $arr = explode('.', $this->themeeditor->version);
        $primary = array_shift($arr);
        if (!in_array($theme, array('transformer', 'panda'))) {
            if ($primary == 1 || $primary == 2) {
                $theme = 'panda';
            } else {
                $theme = 'transformer';
            }
        }
        return $version ? $theme . $primary : $theme;
	}

	public function checkUpdate($force = false)
    {
    	if($force || Configuration::get('STSN_LAST_CHECK_UPDATE')===false || (time() - Configuration::get('STSN_LAST_CHECK_UPDATE')) > 86400){
    		if (isset($_SESSION['st_version_info'])) {
    			unset($_SESSION['st_version_info']);
    		}
			Configuration::updateValue('STSN_LAST_CHECK_UPDATE', time());
    	}

        $remote_version = $this->getByKey('ver');
        if(!$remote_version || strpos($remote_version, '.') === false)
        	 return;
        $arr = explode('.', $remote_version);
        $arr2 = explode('.', $this->themeeditor->version);
   		$primary = array_shift($arr2);
        // Must ensure the primary version is same.
        if ($arr[0] == $primary) {
            if (Tools::version_compare($this->themeeditor->version, $remote_version)) {
                // If current version is lower than remote version, need update.
                return $remote_version;
            }
        }
        return false;
    }

    public function checkFiles()
    {
    	$result = ['deleted' => [], 'changed' => []];
    	$file_name = 'checksum-'.strtolower(_THEME_NAME_).'-'.$this->themeeditor->version.'.xml';
        $xml_remotefile = $this->dl_api_url.'/downloads/'.strtolower(_THEME_NAME_).'/xml/'.$file_name;
        $local_file = $this->config_path . $file_name;
        if (!file_exists($local_file) || !($xml = @simplexml_load_string(file_get_contents($local_file)))) {
        	@unlink($local_file);
	        $xml_string = Tools::file_get_contents($xml_remotefile, false, stream_context_create(array('http' => array('timeout' => 30))));
	        if ($xml = @simplexml_load_string($xml_string)) {
	        	file_put_contents($local_file, $xml->asXML());
	        } else {
	        	return false;
	        }
        }
        return $this->compareFiles($xml);
    }

    public function compareFiles($xml)
    {
    	if (!is_object($xml)) {
	        return false;
	    }
	    $result = ['deleted' => [], 'changed' => []];
	    foreach($xml as $key => $node) {
	        if (is_object($node) && $node->getName() == 'md5file') {
	            $name = (string)$node['name'];
	            $md5 = (string)$node;
	            $file = trim(_PS_ROOT_DIR_, '/').'/'.$name;
	            if (!file_exists($file)) {
	                $result['deleted'][$md5] = $name;
	            } elseif (md5_file($file) != $md5) {
	                $result['changed'][$md5] = $name;
	            }
	        }
	    }
    	return $result;
    }

    public function filesBackup($files = [], $html_file = '')
    {
    	$check_result = $files ? $files : $this->checkFiles();
    	if (!$check_result) {
    		return false;
    	}
    	$html = '<html lang="en">
  <head>
  <meta charset="utf-8">
  <title>File backup log</title>
  <style>
  	h1{text-align:center;background: antiquewhite;}.wrap{border:1px solid #d5d1d5;padding:10px;}.row{border-bottom: 1px dotted saddlebrown;margin-bottom: 10px;}.row span{display:inline-block;font-size: 14px;background: #f9f9f9;box-sizing: border-box;word-break: break-all;}.row span.name{width:35%;}.row span.message{width:30%;}
  	.warning .message{background: #e5c9cd;}.error .message{background: #f00;}.success .message{background: #61e761;}
  	.heading span{background: antiquewhite;height: 32px;line-height: 32px;padding-left: 2%;font-weight:bold;
}

  </style>
   </head>
   <body>';
   		$i = 1;
    	if (isset($check_result['success']) || isset($check_result['error'])) {
    		$html .= '<h1 class="heading">Theme update log - '.date('Y-m-d H:i:s').'</h1><div class="wrap"><div class="row heading"><span class="name">Step</span><span class="message">Result</span><span class="name">Detail</span></div>';
    		foreach($check_result['error'] as $value) {
    			$html .= '<div class="row error"><span class="name">'.$i++.': '.$value['step'].'</span><span class="message">'.$value['msg'].'</span><span class="name">--</span></div>';
    		}
    		foreach($check_result['success'] as $value) {
    			$html .= '<div class="row success"><span class="name">'.$i++.': '.$value['step'].'</span><span class="message">'.$value['msg'].'</span><span class="name">'.(isset($value['detail'])?$value['detail']:'').'</span></div>';
    		}
    	}
    	$html .= '</div><h1 class="heading">File backup list - '.date('Y-m-d H:i:s').'</h1><div class="wrap"><div class="row heading"><span class="name">Original file</span><span class="name">Backup file</span><span class="message">Result</span></div>';
    	$i = 1;
    	foreach($check_result['changed'] as $name) {
    		if (!$name) {
    			continue;
    		}
    		$file = trim(_PS_ROOT_DIR_, '/').'/'.$name;
    		if (!file_exists($file)) {
    			$html .= '<div class="row warning"><span class="name">'.$i++.': /'.$name.'</span><span class="name">--</span><span class="message">File not exists or was deleted!</span></div>';
    		} elseif (!copy($file, $file.'-bak')) {
    			$html .= '<div class="row error"><span class="name">'.$i++.': /'.$name.'</span><span class="name">--</span><span class="message">Copy file failed!</span></div>';
    		} else {
    			$html .= '<div class="row success"><span class="name">'.$i++.': /'.$name.'</span><span class="name">/'.$name.'-bak</span><span class="message">Backup success</span></div>';
    		}
    	}
    	if (!$check_result['changed']) {
    		$html .= '<h2>No files were backup!</h2>';
    	}
    	$html .= '</div>';
    	
    	$html .= '</body></html>';
    	$html_file =  $html_file ? $html_file : $this->config_path . $this->backup_log_file;
    	@unlink($html_file);
    	return @file_put_contents($html_file, $html);

    	return false;
    }

    public function getNotice()
    {
    	$html = '';
    	$remote_version = $this->checkUpdate();
    	if($remote_version===null){
    		$html .= $this->themeeditor->displayError(
                $this->themeeditor->getTranslator()->trans('Unable to get information from ST-themes.', array(), 'Modules.Stthemeeditor.Admin')
            );
    	}
    	if($remote_version){
    		$html .= $this->themeeditor->displayConfirmation(
                $this->themeeditor->getTranslator()->trans('A new version %ver% is available.', array('%ver%'=>$remote_version), 'Modules.Stthemeeditor.Admin')
            );
    	}
    	$notices = $this->getByKey('notice');
    	if ($notices) {
	    	foreach($notices AS $val) {
	    		if (!isset($val['text']) || !$val['text']) {
	    			continue;
	    		}
	    		if ($val['type'] == 'error') {
	    			$html .= $this->themeeditor->displayError($val['text']);
	    		} elseif ($val['type'] == 'info') {
	    			$html .= $this->themeeditor->displayConfirmation($val['text']);
	    		} else{
	    			$html .= $val['text'];
	    		}
	    	}
    	}
    	if (($rs = $this->checkEnv()) !== true) {
    		$html = $this->themeeditor->displayError($rs).$html;
    	}
    	return $html;
    }

    public function checkEnv()
    {
    	$env = $this->getByKey('env');
    	if (is_array($env) && count($env)) {
			if (key_exists('core_ver', $env)) {
				list($core_min, $core_max) = $env['core_ver'];
				if ($core_min && Tools::version_compare($core_min, _PS_VERSION_, '>')) {
					return $this->themeeditor->getTranslator()->trans('The theme requires minimal Prestashop version is %1%, but your current version is %2%, please upgrade Prestashop.', array('%1%'=>$core_min, '%2%'=>_PS_VERSION_), 'Modules.Stthemeeditor.Admin');
				}
				if ($core_max && $core_max != 'MAX_VERSION' && Tools::version_compare($core_max, _PS_VERSION_)) {
					return $this->themeeditor->getTranslator()->trans('The theme requires maximum Prestashop version is %1%, but your current version is %2%, please downgrade Prestashop or update the theme.', array('%1%'=>$core_max, '%2%'=>_PS_VERSION_), 'Modules.Stthemeeditor.Admin');
				}
			}
    	}
    	return true;
    }

    public function getAd()
    {
    	$html = '';
    	$ads = $this->getByKey('ad');
    	if($ads){
    		foreach($ads AS $val) {
	    		if (isset($val['html']) && $val['html']) {
	    			$html .= $val['html'];	
	    		}
	    	}
    	}
    	return $html;
    }

	public function makeCall($params = array(), $api_url = '', $method = 'GET') {
	    if (!$api_url) {
	    	$api_url = $this->api_url;
	    }
	    if (!extension_loaded('curl')) {
	    	return ['err' => true, 'msg' => $this->themeeditor->getTranslator()->trans('cURL extension was not installed properly.', array(), 'Modules.Stthemeeditor.Admin')];
	    }
	    $params = (array)$params;
	    if (is_array($params) && count($params)) {
	        $param_string = '&' . http_build_query($params);
	    } else {
	        $param_string = null;
	    }
	    $api_url = $api_url . '?' . ('GET' === $method ? ltrim($param_string, '&') : null);
	    try {
	        $curl_connection = curl_init($api_url);
	        curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 60);
	        curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);

	        if ('POST' == $method) {
	            curl_setopt($curl_connection, CURLOPT_POST, count($params));
	            curl_setopt($curl_connection, CURLOPT_POSTFIELDS, ltrim($param_string, '&'));
	        }
	        
	        $data = json_decode(curl_exec($curl_connection), true);
	        curl_close($curl_connection);
	        if ($data) {
	            return $data;
	        }
	        $this->writeLog('Make call error: '.$api_url.'; data: '.$data);
	        return false;
	    } catch (Exception $e) {
	    	$this->writeLog('Make call Exception: '.$e->getMessage());
	        return false;
	    }
	}

	public function popMessage(&$array, $msg = '', $rs = false)
	{
		$msg = ['m' => $msg, 'r' => $rs];
		$array['msg'][] = $msg;
		if (!$rs) {
			$array['r'] = false;
		}
	}

	/**
	* Update the theme from server.
	*/
	public function upgrade($step = 0)
	{
		$step = (int)$step;
		$result = ['r' => true, 'msg' => [], 'next' => 0, 'next_title'=> ''];
		$sandbox = _PS_CACHE_DIR_.'sandbox/';
		$theme = $this->getTheme(false);
		$remote_version = $this->checkUpdate();
		switch($step) {
			// Check environment
			case 0:
				$msg = $this->themeeditor->getTranslator()->trans('The theme was registered properly.', array(), 'Modules.Stthemeeditor.Admin');
				$this->popMessage($result, $msg, $this->GoumaimaIsValid());
				
				// Need update ?
				$msg = $this->themeeditor->getTranslator()->trans('Get the correct theme version to check.', array(), 'Modules.Stthemeeditor.Admin');
				$this->popMessage($result, $msg, $remote_version !== null);
				
				$msg = $this->themeeditor->getTranslator()->trans('The theme isn\'t the latest version, need to update.', array(), 'Modules.Stthemeeditor.Admin');
				$this->popMessage($result, $msg, $remote_version !== false);
				
		        $msg = $this->themeeditor->getTranslator()->trans('PHP\'s "allow_url_fopen" option is turned on, or cURL is installed', array(), 'Modules.Stthemeeditor.Admin');
				$this->popMessage($result, $msg, ConfigurationTest::test_fopen() || extension_loaded('curl'));

		        $msg = $this->themeeditor->getTranslator()->trans('PHP\'s "zip" extension is enabled', array(), 'Modules.Stthemeeditor.Admin');
				$this->popMessage($result, $msg, ConfigurationTest::test_zip());
		        $msg = $this->themeeditor->getTranslator()->trans('The store\'s root directory is writable.', array(), 'Modules.Stthemeeditor.Admin');
				$this->popMessage($result, $msg, ConfigurationTest::test_dir('/'));
		        
		        // Test sandbox is writeable ? 
				$msg = $this->themeeditor->getTranslator()->trans('The %s folder is writable.', array('%s' => str_replace(_PS_ROOT_DIR_, '', $sandbox)), 'Modules.Stthemeeditor.Admin');
				@file_put_contents($sandbox . 'step-'.$step, '');
				$this->popMessage($result, $msg, file_exists($sandbox . 'step-0'));
				if (!$result['r']) {
					return $result;
				}
				$result['next'] = $step+1;
				$result['next_title'] = $this->themeeditor->getTranslator()->trans('Download the update pack, just a moment.', array(), 'Modules.Stthemeeditor.Admin');
				return $result;
			break;
			// Download pack.
			case 1:
				if (!file_exists($sandbox . 'step-'.($step-1))) {
					$msg = $this->themeeditor->getTranslator()->trans('You don\'t have permisson to do the step.', array(), 'Modules.Stthemeeditor.Admin');
					$this->popMessage($result, $msg, false);
					return $result;
				} else {
					unlink($sandbox . 'step-'.($step-1));
				}
				if(is_dir($sandbox . $theme)) {
					Tools::deleteDirectory($sandbox . $theme);
				}
				@set_time_limit(600);
				// Get access
				$goumaima = $this->getGoumaima();
				$api_url = $this->dl_api_url . '/download-update.php';
				$param = $this->getLicenseParams('get_download_auth', $goumaima, true);
				$param['theme'] = $theme;
				$param['ver'] = $remote_version;
				$param['cur_ver'] = $this->themeeditor->version;
				if ($data = $this->makeCall($param, $api_url)) {
					if (isset($data['err']) && !$data['err']) {
						$token = $data['token'];
						$md5 = $data['md5'];
						// Download .zip file.
						$param = array(
							'act' => 'download_file',
							'ac_token' => $token,
							'theme' => $theme,
							'ver' => $remote_version,
							'cur_ver' => $this->themeeditor->version,
						);
						// Download file.
						$download_link = $api_url.'?'.http_build_query($param);
						$tmpfile = tempnam($sandbox, 'TMP0');
						$fp = fopen($tmpfile, 'w');
						$ch = curl_init($download_link);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 360);
						curl_setopt($ch, CURLOPT_FILE, $fp);
						curl_exec($ch);
						curl_close($ch);
						fclose($fp);
						// test file & check md5
						if (!Tools::ZipTest($tmpfile) || $md5 != md5_file($tmpfile)) {
							@unlink($tmpfile);
							$msg = $this->themeeditor->getTranslator()->trans('The upate pack is broken, update theme failed.', array(), 'Modules.Stthemeeditor.Admin');
							$this->popMessage($result, $msg, false);
						} elseif (!Tools::ZipExtract($tmpfile, $sandbox . $theme)) {
							@unlink($tmpfile);
							$msg = $this->themeeditor->getTranslator()->trans('Unable to unzip the update pack, update the theme failed.', array(), 'Modules.Stthemeeditor.Admin');
							$this->popMessage($result, $msg, false);
						} else {
							// Delete temp file.
							@unlink($tmpfile);
							$msg = $this->themeeditor->getTranslator()->trans('Downloaded the theme update pack completely.', array(), 'Modules.Stthemeeditor.Admin');
							$this->popMessage($result, $msg, true);
						}
					} elseif (isset($data['err']) && $data['err']) {
			            $this->popMessage($result, $data['msg'], false);
			        } else {
			        	$msg = $this->themeeditor->getTranslator()->trans('Download the theme update faild, parameters error.', array(), 'Modules.Stthemeeditor.Admin');
			        	$this->popMessage($result, $msg, false);
			        }
				} else {
					$msg = $this->themeeditor->getTranslator()->trans('Download the theme update faild, connectiong error.', array(), 'Modules.Stthemeeditor.Admin');
					$this->popMessage($result, $msg, false);
				}
				@unlink($tmpfile);
				if (!$result['r']) {
					return $result;
				}
				@file_put_contents($sandbox . 'step-'.$step, '');
				$result['next'] = $step+1;
				$result['next_title'] = $this->themeeditor->getTranslator()->trans('Make changed files backup and update the theme', array(), 'Modules.Stthemeeditor.Admin');
				return $result;
			break;
			case 2:
				if (!file_exists($sandbox . 'step-'.($step-1))) {
					$msg = $this->themeeditor->getTranslator()->trans('You don\'t have permisson to do the step.', array(), 'Modules.Stthemeeditor.Admin');
					$this->popMessage($result, $msg, false);
					return $result;
				} else {
					unlink($sandbox . 'step-'.($step-1));
				}
				$log = [];
				$files = scandir($sandbox . $theme, false);
				$xml_files = $zip_files = [];
				foreach($files as $file) {
					if (preg_match('/(\d+\.\d+(\.\d+)*)/', $file, $ma) && isset($ma[1])) {
						$info = pathinfo($file);
						if ($info['extension'] == 'xml') {
							$xml_files[$ma[1]] = $file;
						}
						if ($info['extension'] == 'zip') {
							$zip_files[$ma[1]] = $file;
						}
					}
				}

				ksort($xml_files);
				// Merged all xml file to one.
				$md5_array = [];
				foreach($xml_files as $ver => $xml_file) {
					$xml = @simplexml_load_string(file_get_contents($sandbox . $theme . '/' . $xml_file));
					if (!$xml) {
						$log['error'][] = ['step' => $this->themeeditor->getTranslator()->trans('Update version %s', array('%s' => $ver), 'Modules.Stthemeeditor.Admin'), 'msg' => $this->themeeditor->getTranslator()->trans('Load XML comparatioin file failed.', array(), 'Modules.Stthemeeditor.Admin'), 'detail' => ''];
					} else {
						foreach($xml as $key => $node) {
							if (is_object($node) && $node->getName() == 'md5file') {
					            $md5_array[(string)$node['name']] = (string)$node;
					        }
				    	}
					}
				}
				// Coompare files.
				if (($res = $this->checkFiles()) && $md5_array) {
					if (!isset($log['changed'])) {
						$log['changed'] = [];
					}

					if ($res['changed']) {
						$md5_array = array_keys($md5_array);
						$log['changed'] = array_intersect($res['changed'], $md5_array);
					}
					
					if (!isset($log['deleted'])) {
						$log['deleted'] = [];
					}
					if ($res['deleted']) {
						$log['deleted'] = array_merge($log['deleted'], $res['deleted']);
					}
					$log['success'][] = ['step' => $this->themeeditor->getTranslator()->trans('Check modification files', array(), 'Modules.Stthemeeditor.Admin'), 'msg' =>$this->themeeditor->getTranslator()->trans('Compare modified files successfully.', array(), 'Modules.Stthemeeditor.Admin'), 'detail' => $this->themeeditor->getTranslator()->trans('Changed files were backp, see the list below.', array(), 'Modules.Stthemeeditor.Admin')];
				} else {
					$log['error'][] = ['step' => $this->themeeditor->getTranslator()->trans('Check modification files', array(), 'Modules.Stthemeeditor.Admin'), 'msg' =>$this->themeeditor->getTranslator()->trans('compare modified files faild, XML files are incorrect.', array(), 'Modules.Stthemeeditor.Admin'), 'detail' => ''];
					$msg = $this->themeeditor->getTranslator()->trans('Check modification files failed [Compare modified files faild, XML files are incorrect], update was stopped.', array(), 'Modules.Stthemeeditor.Admin');
					$this->popMessage($result, $msg, false);
					Tools::deleteDirectory($sandbox . $theme);
					return $result;
				}

				// File backup.
				$this->filesBackup($log, $this->config_path . $this->update_log_file);

				ksort($zip_files);
				// Extract all zip files.
				foreach($zip_files as $ver => $zip_file) {
					$file = $sandbox . $theme . '/' . $zip_file;
					if (!Tools::ZipTest($file) || !Tools::ZipExtract($file, _PS_ROOT_DIR_)) {
						$log['error'][] = ['step' => $this->themeeditor->getTranslator()->trans('Update version %s', array('%s' => $ver), 'Modules.Stthemeeditor.Admin'), 'msg' =>$this->themeeditor->getTranslator()->trans('Extract update pack failed.', array(), 'Modules.Stthemeeditor.Admin'), 'detail' => $this->themeeditor->getTranslator()->trans('The latest version: '.$remote_version, array(), 'Modules.Stthemeeditor.Admin')];
					} else {
						$log['success'][] = ['step' => $this->themeeditor->getTranslator()->trans('Update version %s', array('%s' => $ver), 'Modules.Stthemeeditor.Admin'), 'msg' =>$this->themeeditor->getTranslator()->trans('upgraded the theme successfully.', array(), 'Modules.Stthemeeditor.Admin'), 'detail' => $this->themeeditor->getTranslator()->trans('The latest version: %s', array('%s' => $remote_version), 'Modules.Stthemeeditor.Admin')];
					}
				}

				if (!$log['error']) {
					$log['success'][] = ['step' => $this->themeeditor->getTranslator()->trans('Complete version %s', array('%s' => $remote_version), 'Modules.Stthemeeditor.Admin'), 'msg' =>$this->themeeditor->getTranslator()->trans('All versions were upgraded successfully.', array(), 'Modules.Stthemeeditor.Admin'), 'detail'=>$this->themeeditor->getTranslator()->trans('From V%s1% to V%s2%', array('%s1%' =>$this->themeeditor->version, '%s2%' =>$remote_version), 'Modules.Stthemeeditor.Admin')];
				}

				foreach($log['error'] as $val) {
					$msg = $val['step'] . ': ' . $val['msg'] . '['.$val['detail'].']';
					$this->popMessage($result, $msg, false);
				}

				foreach($log['success'] as $val) {
					$msg = $val['step'] . ': ' . $val['msg'] . '['.$val['detail'].']';
					$this->popMessage($result, $msg, true);
				}

				// Delete directory
				Tools::deleteDirectory($sandbox . $theme);
				@file_put_contents($sandbox . 'step-'.$step, '');
				$result['next'] = $step+1;
				$result['next_title'] = $this->themeeditor->getTranslator()->trans('Complete the theme update', array(), 'Modules.Stthemeeditor.Admin');
				return $result;
			break;
			case 3:
				if (!file_exists($sandbox . 'step-'.($step-1))) {
					$msg = $this->themeeditor->getTranslator()->trans('You don\'t have permisson to do the step.', array(), 'Modules.Stthemeeditor.Admin');
					$this->popMessage($result, $msg, false);
					return $result;
				} else {
					unlink($sandbox . 'step-'.($step-1));
				}
				if (is_dir($sandbox . $theme)) {
					Tools::deleteDirectory($sandbox . $theme);	
				}
				Tools::clearSmartyCache();
				// reset session
		        unset($_SESSION['st_version_info']);
		        $msg = $this->themeeditor->getTranslator()->trans('Update the theme successfully, please go to the module list page to click the "Upgrade" button to update all theme modules. %s1%see the update log here%s2%.', array('%s1%'=>'<a href="'.trim(Context::getContext()->link->getBaseLink(), '/') .'/modules/'.$this->themeeditor->name.'/config/'.$this->update_log_file.'" target="_blank">', '%s2%' => '</a>'), 'Modules.Stthemeeditor.Admin');
		        $this->popMessage($result, $msg, true);
		        return $result;
			break;
			default:
				$msg = $this->themeeditor->getTranslator()->trans('Unexpected action.', array(), 'Modules.Stthemeeditor.Admin');
				$this->popMessage($result, $msg, false);
		}
		return $result;
	}
}