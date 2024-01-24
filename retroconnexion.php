<?php
/**
 * 2007-2023 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2023 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class RetroConnexion extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'retroconnexion';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Mathias KLIEM';
        $this->need_instance = 0;
        $this->key = "";

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Retro Rococo Connexion', array(), 'Modules.Retroconnexion.Admin');
        $this->description = $this->trans('This module allows you to connect to your Prestashop backoffice from a nodejs app.', array(), 'Modules.Retroconnexion.Admin');
        $this->image = __PS_BASE_URI__ . 'modules/retroconnexion/views/img/logo.png';

        // Define hooks
        //$this->registerHook('generateSecureKey');
        /*$this->registerHook('actionCustomerAccountAdd');
        $this->registerHook('actionAuthentication');
        $this->registerHook('displayCustomerAccountForm');
        $this->registerHook('actionAfterEmployeeLoginFormSubmit');
        $this->registerHook('displayBackOfficeHeader');*/

        $this->confirmUninstall = $this->l('You\'re sure that you wan\'t to delete this awesome');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('RETROCONNEXION_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('actionAuthentication') &&
            $this->registerHook('actionAdminLoginControllerLoginAfter') &&
            $this->registerHook('actionCustomerAccountAdd');
    }

    public function uninstall()
    {
        Configuration::deleteByName('RETROCONNEXION_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $this->handleFormSubmission();
        /**
         * If values have been submitted in the form, process.
         */

        $secretKey = Configuration::get('RETRO_CONNEXION_SECRET_KEY');

        if (!empty($secretKey)) {
            $this->context->smarty->assign('secretKey', $secretKey);
        } else {
            $this->context->smarty->assign('secretKey', "");
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $action = $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name]);

        $this->context->smarty->assign('action', $action);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output;

        /*if (((bool)Tools::isSubmit('submitRetroConnexionModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output;*/
    }

    public function handleFormSubmission()
    {
        if (Tools::isSubmit('submit')) {
            $this->saveSecretKeyToDatabase();
        }
    }

    public function saveSecretKeyToDatabase()
    {
        // Get the secret key from the form
        $this->secretKey = Tools::getValue('key');
        if (empty($this->secretKey)) {
            return;
        }
        Configuration::updateValue('RETRO_CONNEXION_SECRET_KEY', $this->secretKey);
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

//    public function hookretrieveUserInfos()
//
//    {
//        /*$context = Context::getContext();
//        if ($context->customer->isLogged()) {
//            // Access customer data here
//            $customer = $context->customer;
//        } else {
//            // Customer is not logged in
//            $customer = "not logged in";
//        }
//        return (var_export($context, true));*/
//    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function hookActionAuthentication()
    {
        // Context
        $this->context = Context::getContext();
        if ($this->context->customer !== null) {
            // Encode the customer ID in a hash with a secret key to be stored in a cookie and decrypted later with a nodejs app
            $secret = Configuration::get('RETRO_CONNEXION_SECRET_KEY');
            $userId = $this->context->customer->id;
            $userFirstName = $this->context->customer->firstname;
            $hmac = hash_hmac('sha512', $userId, $secret);
            setcookie('id_customer', $userId . '|' . $userFirstName . '|' . $hmac, time() + 60 * 60 * 24 * 30 * 12);
        }
    }

    public function hookActionCustomerAccountAdd()
    {
        // Context
        $this->context = Context::getContext();
        if ($this->context->customer !== null) {
            // Encode the customer ID in a hash with a secret key to be stored in a cookie and decrypted later with a nodejs app
            $secret = Configuration::get('RETRO_CONNEXION_SECRET_KEY');
            $userId = $this->context->customer->id;
            $userFirstName = $this->context->customer->firstname;
            $hmac = hash_hmac('sha512', $userId, $secret);
            setcookie('id_customer', $userId . '|' . $userFirstName . '|' . $hmac);
        }
    }

    public function hookActionAdminLoginControllerLoginAfter()
    {
        // Context
        $this->context = Context::getContext();
        if ($this->context->employee !== null) {
            // Encode the customer ID in a hash with a secret key to be stored in a cookie and decrypted later with a nodejs app
            $secret = Configuration::get('RETRO_CONNEXION_SECRET_KEY');
            $userId = $this->context->employee->id;
            $userFirstName = $this->context->employee->firstname;
            $hmac = hash_hmac('sha512', $userId, $secret);
            setcookie('id_employee', $userId . '|' . $userFirstName . '|' . $hmac);
        }
    }


    /*public function hookActionGenerateSecureKey()
    {
        // Generate a new random key with a sha512 hash
        $algorithm = 'sha512';
        // Generate a random string.
        $data = openssl_random_pseudo_bytes(32);
        //var_dump($data);
        //die();
        // Generate a hash using the sha512 algorithm.
        return hash_hmac($algorithm, $data, $this->key);
    }*/

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'RETROCONNEXION_LIVE_MODE' => Configuration::get('RETROCONNEXION_LIVE_MODE', true),
            'RETROCONNEXION_ACCOUNT_EMAIL' => Configuration::get('RETROCONNEXION_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'RETROCONNEXION_ACCOUNT_PASSWORD' => Configuration::get('RETROCONNEXION_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitRetroConnexionModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'RETROCONNEXION_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'RETROCONNEXION_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'RETROCONNEXION_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }
}
