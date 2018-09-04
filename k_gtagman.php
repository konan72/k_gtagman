<?php
/*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author KoNaN <>
*  @copyright  2018 KoNaN
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of KoNaN
*/

if (!defined('_PS_VERSION_'))
	exit;

class k_gtagman extends Module
{
	public function __construct()
    {
        $this->name 		 = 'k_gtagman';
        $this->tab 			 = 'analytics_stats';
        $this->version 		 = '1.0.0';
        $this->author 		 = 'KoNaN';
        $this->bootstrap 	 = true;
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7.2.0', 'max' => _PS_VERSION_);
        parent::__construct();

        $this->displayName = $this->trans('Google Tag Manager', array(), 'Modules.GoogleTagManager.Admin' );
        $this->description = $this->trans('Google Tag Manager integration', array(), 'Modules.GoogleTagManager.Admin' );
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        if (!Configuration::get('GOOGLE_TAG_MANAGER_ID')) {
            $this->warning = $this->l('No name provided');
        }

    }


    public function install()
	{
        return (
            parent::install() 
            /* Custom hook <head> */
            && $this->registerHook('displayHeader') 
            /* add hook for <body> 
            && $this->registerHook('GoogleTagManagerAfterBody') */
            /* Custom hook after <body> */
            && $this->registerHook('displayAfterBodyOpeningTag') 
            /* Custom configuration */
            && Configuration::updateValue('GOOGLE_TAG_MANAGER_ID', '')
            );
	}

	public function uninstall()
	{
        return (
            parent::uninstall() 
            && $this->unregisterHook('displayHeader') 
            /* && $this->unregisterHook('GoogleTagManagerAfterBody') */
            && $this->unregisterHook('displayAfterBodyOpeningTag') 
            && Configuration::deleteByName('GOOGLE_TAG_MANAGER_ID')
        );
	}

	public function getContent() {
		$output = null;

		if(Tools::isSubmit('submit'.$this->name)) {
            $myModuleName = strval(Tools::getValue('k_gtagman'));

            if(
                !$myModuleName || 
                empty($myModuleName) || 
                !Validate::isGenericName($myModuleName)
            ) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('GOOGLE_TAG_MANAGER_ID', $myModuleName);
                $output .= $this->displayConfirmation($this->l('Settings update'));
            }
        }
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');
        $fieldsForm[0]['form'] = [
        'legend' => [
            'title' => $this->l('Settings'),
        ],
        'input' => [
            [
                'type' => 'text',
                'label' => $this->l('Google Tag Manager ID'),
                'desc' =>  $this->l('This information is available in your Google Tag Manager account.'),
                'name' => 'k_gtagman',
                'size' => 10,
                'validation' => 'isGenericName',
                'required' => true
            ]
        ],
        'submit' => [
            'title' => $this->l('Save'),
            'class' => 'btn btn-default pull-right'
        ]
        ];

        // Module, token and currentIndex
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = false;        

        $helper->submit_action = 'submit'.$this->name;

        $helper->toolbar_btn = [
        'save' => [
            'desc' => $this->l('Save'),
            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
            '&token='.Tools::getAdminTokenLite('AdminModules'),
        ],
        'back' => [
            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Back')
        ]
        ];

        $helper->fields_value['k_gtagman'] = Configuration::get('GOOGLE_TAG_MANAGER_ID');
 
        
		return $helper->generateForm($fieldsForm);
	}


    public function hookDisplayHeader($params)
    {
       $gtagman_id = Tools::safeOutput(Configuration::get('GOOGLE_TAG_MANAGER_ID'));
        if (!$gtagman_id)
            return;
        $this->context->smarty->assign(array(
            'google_tag_manager_id' => $gtagman_id
        ));
        return $this->display(__FILE__, 'views/templates/hook/script.tpl');
    }
/*
    public function hookGoogleTagManagerAfterBody($params)
    {
        $gtagman_id = Tools::safeOutput(Configuration::get('GOOGLE_TAG_MANAGER_ID'));
        if (!$gtagman_id)
            return;
        $this->context->smarty->assign(array(
            'google_tag_manager_id' => $gtagman_id
        ));

        return $this->display(__FILE__, 'views/templates/hook/noscript.tpl');
    }
*/
    public function hookDisplayAfterBodyOpeningTag($params)
    {
        $gtagman_id = Tools::safeOutput(Configuration::get('GOOGLE_TAG_MANAGER_ID'));
        if (!$gtagman_id)
            return;
        $this->context->smarty->assign(array(
            'google_tag_manager_id' => $gtagman_id
        ));

        return $this->display(__FILE__, 'views/templates/hook/noscript.tpl');
    }


}



?>