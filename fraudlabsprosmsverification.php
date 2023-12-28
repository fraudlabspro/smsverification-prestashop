<?php
if (!defined('_PS_VERSION_')) {
	exit;
}

class fraudlabsprosmsverification extends Module
{
	protected $_html = '';
	protected $_postErrors = [];

	public function __construct()
	{
		$this->name = 'fraudlabsprosmsverification';
		$this->tab = 'payment_security';
		$this->version = '1.2.0';
		$this->author = 'FraudLabs Pro';
		$this->controllers = ['payment', 'validation'];
		$this->module_key = 'cdb22a61c7ec8d1f900f6c162ad96caa';

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('FraudLabs Pro SMS Verification');
		$this->description = $this->l('FraudLabs Pro SMS Verification extension that help merchants to authenticate the client\'s identity by sending them a SMS for verification.');
	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('orderConfirmation')) {
			return false;
		}

		Configuration::updateValue('FLP_SMS_ENABLED', '1');
		Configuration::updateValue('FLP_SMS_LICENSE_KEY', '');
		Configuration::updateValue('FLP_SMS_MSG_CONTENT', 'Hi, your OTP is {otp}.');
		Configuration::updateValue('FLP_SMS_OTP_TIMEOUT', '3600');
		Configuration::updateValue('FLP_SMS_DEFAULT_CC', '21');

		Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'fraudlabsprosmsverification` (
			`id_order` VARCHAR(11) NOT NULL,
			`fraudlabspro_sms_code` VARCHAR(30) NULL,
			`fraudlabspro_sms_phone` VARCHAR(30) NULL,
			`fraudlabspro_sms_status` VARCHAR(20) NULL,
			`fraudlabspro_id` VARCHAR(15) NULL,
			`api_key` CHAR(32) NOT NULL,
			PRIMARY KEY (`id_order`)
		) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;');

		return true;
	}

	public function getContent()
	{
		if (Tools::isSubmit('btnSubmit')) {
			$this->_postValidation();
			if (!count($this->_postErrors)) {
				$this->_postProcess();
			} else {
				foreach ($this->_postErrors as $err) {
					$this->_html .= $this->displayError($err);
				}
			}
		} else {
			$this->_html .= '<br />';
		}

		$this->_html .= $this->renderForm();

		return $this->_html;
	}

	public function hookOrderConfirmation($params)
	{
		if (!Configuration::get('PS_SHOP_ENABLE') || !Configuration::get('FLP_SMS_LICENSE_KEY') || !Configuration::get('FLP_SMS_ENABLED')) {
			return;
		}

		$rowSms = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'fraudlabsprosmsverification` WHERE `id_order` = ' . (int) $params['order']->id . ' AND `fraudlabspro_sms_status` = "VERIFIED"');
		if ($rowSms) {
			return;
		}

		$row = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'orders_fraudlabspro` WHERE `id_order` = ' . (int) $params['order']->id);
		if (!$row) {
			return;
		}

		if ((($row['flp_status'] == 'APPROVE') && (Configuration::get('FLP_SMS_APPROVE'))) || (($row['flp_status'] == 'REVIEW') && (Configuration::get('FLP_SMS_REVIEW'))) || (($row['flp_status'] == 'REJECT') && (Configuration::get('FLP_SMS_REJECT')))) {
		} else {
			return;
		}

		if (Tools::getValue('do_otp')) {
			$rtnSend = $this->smsSend(Tools::getValue('tel'), Tools::getValue('tel_cc'));
			if (strpos($rtnSend, 'ERROR') == false) {
				die($rtnSend);
			} else if (strpos($rtnSend, 'OK') == false) {
				die($rtnSend);
			}
		}

		if (Tools::getValue('check_otp')) {
			$rtnVerify = $this->smsVerify(Tools::getValue('otp'), Tools::getValue('tran_id'), Tools::getValue('tel'), $params['order']->id);
			if (strpos($rtnVerify, 'ERROR') == false) {
				die($rtnVerify);
			} else if (strpos($rtnVerify, 'OK') == false) {
				die($rtnVerify);
			}
		}

		$countries = ['231' => 'AF', '244' => 'AX', '230' => 'AL', '38' => 'DZ', '39' => 'AS', '40' => 'AD', '41' => 'AO', '42' => 'AI', '232' => 'AQ', '43' => 'AG', '44' => 'AR', '45' => 'AM', '46' => 'AW', '24' => 'AU', '2' => 'AT', '47' => 'AZ', '48' => 'BS', '49' => 'BH', '50' => 'BD', '51' => 'BB', '52' => 'BY', '3' => 'BE', '53' => 'BZ', '54' => 'BJ', '55' => 'BM', '56' => 'BT', '34' => 'BO', '233' => 'BA', '57' => 'BW', '234' => 'BV', '58' => 'BR', '235' => 'IO', '59' => 'BN', '236' => 'BG', '60' => 'BF', '62' => 'BI', '65' => 'CV', '63' => 'KH', '64' => 'CM', '4' => 'CA', '237' => 'KY', '66' => 'CF', '67' => 'TD', '68' => 'CL', '5' => 'CN', '238' => 'CX', '239' => 'CC', '69' => 'CO', '70' => 'KM', '72' => 'CG', '71' => 'CD', '240' => 'CK', '73' => 'CR', '32' => 'CI', '74' => 'HR', '75' => 'CU', '76' => 'CY', '16' => 'CZ', '20' => 'DK', '77' => 'DJ', '78' => 'DM', '79' => 'DO', '81' => 'EC', '82' => 'EG', '83' => 'SV', '84' => 'GQ', '85' => 'ER', '86' => 'EE', '87' => 'ET', '88' => 'FK', '89' => 'FO', '90' => 'FJ', '9' => 'FI', '8' => 'FR', '241' => 'GF', '242' => 'PF', '243' => 'TF', '91' => 'GA', '92' => 'GM', '93' => 'GE', '1' => 'DE', '94' => 'GH', '97' => 'GI', '9' => 'GR', '96' => 'GL', '95' => 'GD', '98' => 'GP', '99' => 'GU', '100' => 'GT', '101' => 'GG', '102' => 'GN', '103' => 'GW', '104' => 'GY', '105' => 'HT', '106' => 'HM', '107' => 'VA', '108' => 'HN', '22' => 'HK', '143' => 'HU', '109' => 'IS', '110' => 'IN', '111' => 'ID', '112' => 'IR', '113' => 'IQ', '26' => 'IE', '114' => 'IM', '29' => 'IL', '10' => 'IT', '115' => 'JM', '11' => 'JP', '116' => 'JE', '117' => 'JO', '118' => 'KZ', '119' => 'KE', '120' => 'KI', '121' => 'KP', '28' => 'KR', '122' => 'KW', '123' => 'KG', '124' => 'LA', '125' => 'LV', '126' => 'LB', '127' => 'LS', '128' => 'LR', '129' => 'LY', '130' => 'LI', '131' => 'LT', '12' => 'LU', '132' => 'MO', '133' => 'MK', '134' => 'MG', '135' => 'MW', '136' => 'MY', '137' => 'MV', '138' => 'ML', '139' => 'MT', '140' => 'MH', '141' => 'MQ', '142' => 'MR', '35' => 'MU', '144' => 'YT', '145' => 'MX', '146' => 'FM', '147' => 'MD', '148' => 'MC', '149' => 'MN', '150' => 'ME', '151' => 'MS', '152' => 'MA', '153' => 'MZ', '61' => 'MM', '154' => 'NA', '155' => 'NR', '156' => 'NP', '13' => 'NL', '158' => 'NC', '27' => 'NZ', '159' => 'NI', '160' => 'NE', '31' => 'NG', '161' => 'NU', '162' => 'NF', '163' => 'MP', '23' => 'NO', '164' => 'OM', '165' => 'PK', '166' => 'PW', '167' => 'PS', '168' => 'PA', '169' => 'PG', '170' => 'PY', '171' => 'PE', '172' => 'PH', '173' => 'PN', '14' => 'PL', '15' => 'PT', '174' => 'PR', '175' => 'QA', '176' => 'RE', '36' => 'RO', '177' => 'RU', '178' => 'RW', '179' => 'BL', '180' => 'KN', '181' => 'LC', '182' => 'MF', '183' => 'PM', '184' => 'VC', '185' => 'WS', '186' => 'SM', '187' => 'ST', '188' => 'SA', '189' => 'SN', '190' => 'RS', '191' => 'SC', '192' => 'SL', '25' => 'SG', '157' => 'SX', '37' => 'SK', '193' => 'SI', '194' => 'SB', '195' => 'SO', '30' => 'ZA', '196' => 'GS', '6' => 'ES', '197' => 'LK', '198' => 'SD', '199' => 'SR', '200' => 'SJ', '201' => 'SZ', '18' => 'SE', '19' => 'CH', '202' => 'SY', '203' => 'TW', '204' => 'TJ', '205' => 'TZ', '206' => 'TH', '80' => 'TL', '33' => 'TG', '207' => 'TK', '208' => 'TO', '209' => 'TT', '210' => 'TN', '211' => 'TR', '212' => 'TM', '213' => 'TC', '214' => 'TV', '215' => 'UG', '216' => 'UA', '217' => 'AE', '17' => 'GB', '21' => 'US', '218' => 'UY', '219' => 'UZ', '220' => 'VU', '221' => 'VE', '222' => 'VN', '223' => 'VG', '224' => 'VI', '225' => 'WF', '226' => 'EH', '227' => 'YE', '228' => 'ZM', '229' => 'ZW'];

		$this->smarty->assign([
			'sms_instruction' => (Configuration::get('FLP_SMS_VERIFICATION_INST')) ? Configuration::get('FLP_SMS_VERIFICATION_INST') : 'Please verify your phone number by completing the below SMS verification. Please enter your phone number (with country code) to receive the OTP message.',
			'sms_cc' => $countries[Configuration::get('FLP_SMS_DEFAULT_CC')],
			'sms_order_id' => $params['order']->id,
			'sms_msg_sms_required' => (Configuration::get('FLP_SMS_MSG_SMS_REQUIRED')) ? Configuration::get('FLP_SMS_MSG_SMS_REQUIRED') : 'You are required to verify you phone number via SMS verification.',
			'sms_msg_otp_success' => (Configuration::get('FLP_SMS_MSG_OTP_SUCCESS')) ? Configuration::get('FLP_SMS_MSG_OTP_SUCCESS') : 'A SMS containing the OTP (One Time Passcode) has been sent to [phone]. Please enter the 6 digits OTP value to complete the verification.',
			'sms_msg_otp_fail' => (Configuration::get('FLP_SMS_MSG_OTP_FAIL')) ? Configuration::get('FLP_SMS_MSG_OTP_FAIL') : 'Error: Unable to send the SMS verification message to [phone].',
			'sms_msg_invalid_phone' => (Configuration::get('FLP_SMS_MSG_INVALID_PHONE')) ? Configuration::get('FLP_SMS_MSG_INVALID_PHONE') : 'Please enter a valid phone number.',
			'sms_msg_invalid_otp' => (Configuration::get('FLP_SMS_MSG_INVALID_OTP')) ? Configuration::get('FLP_SMS_MSG_INVALID_OTP') : 'Error: Invalid OTP. Please enter the correct OTP.',
		]);

		return $this->display(__FILE__, 'order_confirm.tpl');
	}

	public function renderForm()
	{
		$fields_form = [
			'form' => [
				'legend' => [
					'title' => $this->l('Settings'),
					'icon'  => 'icon-cog',
				],
				'input' => [
					[
						'type'   => 'checkbox',
						'name'   => 'FLP_SMS_ENABLED',
						'values' => [
							'query' => [
								[
									'id'   => 'on',
									'name' => $this->l('Enable'),
									'val'  => '1',
								],
							],
							'id'   => 'id',
							'name' => 'name',
						],
						'desc'	 => $this->l('Please note that you have to install FraudLabs Pro Fraud Prevention module (https://github.com/fraudlabspro/prestashop/releases/latest) before enabling this module.'),
					],
					[
						'type'	 => 'text',
						'label'	=> $this->l('API Key'),
						'name'	 => 'FLP_SMS_LICENSE_KEY',
						'desc'	 => $this->l('Please sign up for a free Micro plan at http://www.fraudlabspro.com/sign-up, if you do not have the API key.'),
						'required' => true,
					],
					[
						'type'   => 'checkbox',
						'name'   => 'FLP_SMS_APPROVE',
						'values' => [
							'query' => [
								[
									'id'   => 'on',
									'name' => $this->l('Trigger the SMS Verification when Order is Approved by FraudLabs Pro'),
									'val'  => '1',
								],
							],
							'id'   => 'id',
							'name' => 'name',
						],
					],
					[
						'type'   => 'checkbox',
						'name'   => 'FLP_SMS_REVIEW',
						'values' => [
							'query' => [
								[
									'id'   => 'on',
									'name' => $this->l('Trigger the SMS Verification when Order is in Review by FraudLabs Pro'),
									'val'  => '1',
								],
							],
							'id'   => 'id',
							'name' => 'name',
						],
					],
					[
						'type'   => 'checkbox',
						'name'   => 'FLP_SMS_REJECT',
						'values' => [
							'query' => [
								[
									'id'   => 'on',
									'name' => $this->l('Trigger the SMS Verification when Order is Rejected by FraudLabs Pro'),
									'val'  => '1',
								],
							],
							'id'   => 'id',
							'name' => 'name',
						],
						'desc'	 => $this->l('Setting to trigger the SMS Verification based on the status of FraudLabs Pro returned.'),
					],
					[
						'type'	 => 'textarea',
						'label'	=> $this->l('SMS Verification Instruction'),
						'name'	 => 'FLP_SMS_VERIFICATION_INST',
						'desc'	 => $this->l('Messages to brief the user about this SMS verification and what needs to be done to complete the verification. Leave it blank for default SMS verification instruction.'),
						'required' => false,
					],
					[
						'type'	 => 'text',
						'label'	=> $this->l('SMS Message Content'),
						'name'	 => 'FLP_SMS_MSG_CONTENT',
						'desc'	 => $this->l('The SMS text message to be sent to the user\'s mobile phone. You must include the {otp} tag which will contain the auto-generated OTP code.'),
						'required' => true,
					],
					[
						'type'	 => 'text',
						'label'	=> $this->l('SMS OTP Timeout'),
						'name'	 => 'FLP_SMS_OTP_TIMEOUT',
						'desc'	 => $this->l('OTP validtity timeout. The default value is 3600 seconds (1 hour) and the maximum value is 86400 seconds (24 hours).'),
						'required' => false,
					],
					[
						'type'	 => 'select',
						'label'	=> $this->l('Default Country Code For SMS Sending'),
						'name'	 => 'FLP_SMS_DEFAULT_CC',
						'required' => false,
						'options'  => [
							'query' => Country::getCountries((int)$this->context->language->id, false),
							'id'	=> 'id_country',
							'name'  => 'name',
						],
						'desc'	 => $this->l('Please visit https://www.fraudlabspro.com/developer/reference/country-codes-sms to learn more about the supported countries.'),
					],
					[
						'type'	 => 'textarea',
						'label'	=> $this->l('SMS Verification Required Message'),
						'name'	 => 'FLP_SMS_MSG_SMS_REQUIRED',
						'desc'	 => $this->l('Messages to show the user that the phone number verification via SMS verification is required.'),
						'required' => false,
					],
					[
						'type'	 => 'textarea',
						'label'	=> $this->l('OTP Sent Succesfully Message'),
						'name'	 => 'FLP_SMS_MSG_OTP_SUCCESS',
						'desc'	 => $this->l('Messages to show the user when the OTP is sent successfully to the phone number. You must include the [phone] tag which will be replaced by the user\'s phone number.'),
						'required' => false,
					],
					[
						'type'	 => 'textarea',
						'label'	=> $this->l('OTP Sent Failed Message'),
						'name'	 => 'FLP_SMS_MSG_OTP_FAIL',
						'desc'	 => $this->l('Messages to show the user when the OTP is sent failed to the phone number. You must include the [phone] tag which will be replaced by the user\'s phone number.'),
						'required' => false,
					],
					[
						'type'	 => 'textarea',
						'label'	=> $this->l('Invalid Phone Number Message'),
						'name'	 => 'FLP_SMS_MSG_INVALID_PHONE',
						'desc'	 => $this->l('Messages to show the user when invalid phone number is entered.'),
						'required' => false,
					],
					[
						'type'	 => 'textarea',
						'label'	=> $this->l('Invalid OTP Message'),
						'name'	 => 'FLP_SMS_MSG_INVALID_OTP',
						'desc'	 => $this->l('Messages to show the user when invalid OTP is entered.'),
						'required' => false,
					],
				],
				'submit' => [
					'title' => $this->l('Save'),
				],
			],
		];

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = [];
		$helper->id = (int) Tools::getValue('id_carrier');
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'btnSubmit';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = [
			'fields_value' => $this->getConfigFieldsValues(),
			'languages'	=> $this->context->controller->getLanguages(),
			'id_language'  => $this->context->language->id,
		];

		return $helper->generateForm([$fields_form]);
	}

	public function getConfigFieldsValues()
	{
		return [
			'FLP_SMS_ENABLED_on'		  => Tools::getValue('FLP_SMS_ENABLED_on', Configuration::get('FLP_SMS_ENABLED')),
			'FLP_SMS_LICENSE_KEY'		 => Tools::getValue('FLP_SMS_LICENSE_KEY', Configuration::get('FLP_SMS_LICENSE_KEY')),
			'FLP_SMS_APPROVE_on'		  => Tools::getValue('FLP_SMS_APPROVE_on', Configuration::get('FLP_SMS_APPROVE')),
			'FLP_SMS_REVIEW_on'		   => Tools::getValue('FLP_SMS_REVIEW_on', Configuration::get('FLP_SMS_REVIEW')),
			'FLP_SMS_REJECT_on'		   => Tools::getValue('FLP_SMS_REJECT_on', Configuration::get('FLP_SMS_REJECT')),
			'FLP_SMS_VERIFICATION_INST'   => Tools::getValue('FLP_SMS_VERIFICATION_INST', Configuration::get('FLP_SMS_VERIFICATION_INST')),
			'FLP_SMS_MSG_CONTENT'		 => Tools::getValue('FLP_SMS_MSG_CONTENT', Configuration::get('FLP_SMS_MSG_CONTENT')),
			'FLP_SMS_OTP_TIMEOUT'		 => Tools::getValue('FLP_SMS_OTP_TIMEOUT', Configuration::get('FLP_SMS_OTP_TIMEOUT')),
			'FLP_SMS_DEFAULT_CC'		  => Tools::getValue('FLP_SMS_DEFAULT_CC', Configuration::get('FLP_SMS_DEFAULT_CC')),
			'FLP_SMS_MSG_SMS_REQUIRED'	=> Tools::getValue('FLP_SMS_MSG_SMS_REQUIRED', Configuration::get('FLP_SMS_MSG_SMS_REQUIRED')),
			'FLP_SMS_MSG_OTP_SUCCESS'	 => Tools::getValue('FLP_SMS_MSG_OTP_SUCCESS', Configuration::get('FLP_SMS_MSG_OTP_SUCCESS')),
			'FLP_SMS_MSG_OTP_FAIL'		=> Tools::getValue('FLP_SMS_MSG_OTP_FAIL', Configuration::get('FLP_SMS_MSG_OTP_FAIL')),
			'FLP_SMS_MSG_INVALID_PHONE'   => Tools::getValue('FLP_SMS_MSG_INVALID_PHONE', Configuration::get('FLP_SMS_MSG_INVALID_PHONE')),
			'FLP_SMS_MSG_INVALID_OTP'	 => Tools::getValue('FLP_SMS_MSG_INVALID_OTP', Configuration::get('FLP_SMS_MSG_INVALID_OTP')),
		];
	}

	protected function _postProcess()
	{
		if (Tools::isSubmit('btnSubmit')) {
			Configuration::updateValue('FLP_SMS_ENABLED', Tools::getValue('FLP_SMS_ENABLED_on'));
			Configuration::updateValue('FLP_SMS_LICENSE_KEY', Tools::getValue('FLP_SMS_LICENSE_KEY'));
			Configuration::updateValue('FLP_SMS_APPROVE', Tools::getValue('FLP_SMS_APPROVE_on'));
			Configuration::updateValue('FLP_SMS_REVIEW', Tools::getValue('FLP_SMS_REVIEW_on'));
			Configuration::updateValue('FLP_SMS_REJECT', Tools::getValue('FLP_SMS_REJECT_on'));
			Configuration::updateValue('FLP_SMS_VERIFICATION_INST', Tools::getValue('FLP_SMS_VERIFICATION_INST'));
			Configuration::updateValue('FLP_SMS_MSG_CONTENT', Tools::getValue('FLP_SMS_MSG_CONTENT'));
			Configuration::updateValue('FLP_SMS_OTP_TIMEOUT', Tools::getValue('FLP_SMS_OTP_TIMEOUT'));
			Configuration::updateValue('FLP_SMS_DEFAULT_CC', Tools::getValue('FLP_SMS_DEFAULT_CC'));
			Configuration::updateValue('FLP_SMS_MSG_SMS_REQUIRED', Tools::getValue('FLP_SMS_MSG_SMS_REQUIRED'));
			Configuration::updateValue('FLP_SMS_MSG_OTP_SUCCESS', Tools::getValue('FLP_SMS_MSG_OTP_SUCCESS'));
			Configuration::updateValue('FLP_SMS_MSG_OTP_FAIL', Tools::getValue('FLP_SMS_MSG_OTP_FAIL'));
			Configuration::updateValue('FLP_SMS_MSG_INVALID_PHONE', Tools::getValue('FLP_SMS_MSG_INVALID_PHONE'));
			Configuration::updateValue('FLP_SMS_MSG_INVALID_OTP', Tools::getValue('FLP_SMS_MSG_INVALID_OTP'));

			if (!Tools::getValue('FLP_SMS_OTP_TIMEOUT')) {
				Configuration::updateValue('FLP_SMS_OTP_TIMEOUT', '3600');
			}
		}

		$this->_html .= $this->displayConfirmation($this->l('Settings updated'));
	}

	protected function _postValidation()
	{
		if (Tools::isSubmit('btnSubmit')) {
			if (!Tools::getValue('FLP_SMS_LICENSE_KEY')) {
				$this->_postErrors[] = $this->l('FraudLabs Pro API key is required.');
			} else if (strpos(Tools::getValue('FLP_SMS_MSG_CONTENT'), '{otp}') == false) {
				$this->_postErrors[] = $this->l('The {otp} tag must be included in the SMS Message Content.');
			} else if (Tools::getValue('FLP_SMS_MSG_OTP_SUCCESS') != '') {
				if (strpos(Tools::getValue('FLP_SMS_MSG_OTP_SUCCESS'), '[phone]') == false) {
					$this->_postErrors[] = $this->l('The [phone] tag must be included in the OTP Sent Succesfully Message.');
				}
			} else if (Tools::getValue('FLP_SMS_MSG_OTP_FAIL') != '') {
				if (strpos(Tools::getValue('FLP_SMS_MSG_OTP_FAIL'), '[phone]') == false) {
					$this->_postErrors[] = $this->l('The [phone] tag must be included in the OTP Sent Failed Message.');
				}
			}
		}
	}

	private function smsSend($tel, $telCc)
	{
		$apiKey = Configuration::get('FLP_SMS_LICENSE_KEY');
		$params['format'] = 'json';
		$params['key'] = $apiKey;
		$params['tel'] = trim($tel);
		if(strpos($params['tel'], '+') !== 0)
			$params['tel'] = '+' . $params['tel'];
		$params['mesg'] = Configuration::get('FLP_SMS_MSG_CONTENT');
		$params['mesg'] = str_replace(['{', '}'], ['<', '>'], $params['mesg']);
		$params['tel_cc'] = $telCc;
		$params['otp_timeout'] = (Configuration::get('FLP_SMS_OTP_TIMEOUT')) ? Configuration::get('FLP_SMS_OTP_TIMEOUT') : 3600;
		$params['source'] = 'prestashop';

		$request = $this->post('https://api.fraudlabspro.com/v2/verification/send', $params);

		if ($request) {
			$data = json_decode($request);

			if (isset($data->error->error_message)) {
				$rtn = 'ERROR 600-' . $data->error->error_message;
			} else {
				$rtn = 'OK' . $data->tran_id . $data->otp_char;
			}
		} else {
			// Network error
			$rtn = 'ERROR 500';
		}
		return $rtn;
	}

	private function smsVerify($otp, $tranId, $tel, $orderId)
	{
		$apiKey = Configuration::get('FLP_SMS_LICENSE_KEY');
		$params['format'] = 'json';
		$params['key'] = $apiKey;
		$params['otp'] = $otp;
		$params['tran_id'] = $tranId;

		$request = $this->post('https://api.fraudlabspro.com/v2/verification/result', $params);

		if ($request) {
			$data = json_decode($request);

			if (isset($data->error->error_message)) {
				if ($data->error->error_message == 'INVALID OTP') {
					$rtn = 'ERROR 601-' . $data->error->error_message;
				} else {
					$rtn = 'ERROR 600-' . $data->error->error_message;
				}
			} else {
				$rtn = 'OK';

				Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'fraudlabsprosmsverification` (`id_order`, `fraudlabspro_sms_phone`, `fraudlabspro_sms_status`, `api_key`) VALUES ("' . (int)$orderId . '", "' . $tel . '", "VERIFIED", "' . Configuration::get('FLP_SMS_LICENSE_KEY') . '")');
				Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'orders_fraudlabspro` SET is_phone_verified="' . Tools::getValue('tel') . ' verified" WHERE id_order=' . (int)$orderId . ' LIMIT 1');
			}
		} else {
			// Network error
			$rtn = 'ERROR 500';
		}
		return $rtn;
	}

	private function post($url, $fields = '')
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		curl_setopt($ch, CURLOPT_HTTP_VERSION, '1.1');
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);

		if (!empty($fields)) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, (is_array($fields)) ? http_build_query($fields) : $fields);
		}

		$response = curl_exec($ch);

		if (!curl_errno($ch)) {
			return $response;
		}

		return false;
	}
}
