<?php
/**
 * 2015-2017 Urb-it
 *
 * NOTICE OF LICENSE
 *
 *
 *
 * Do not edit or add to this file if you wish to upgrade Urb-it to newer
 * versions in the future. If you wish to customize Urb-it for your
 * needs please refer to https://urb-it.com for more information.
 *
 * @author    Urb-it SA <parissupport@urb-it.com>
 * @copyright 2015-2017 Urb-it SA
 * @license  http://www.gnu.org/licenses/
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
require_once dirname(__FILE__) . '/Model/Feed/Inventory.php';
require_once dirname(__FILE__) . '/Model/Feed/Fields/Factory.php';
require_once dirname(__FILE__) . '/Helper/UrbitHelperForm.php';

/**
 * Class Urbitinventoryfeed
 */
class Urbitinventoryfeed extends Module
{
    const NAME = 'urbitinventoryfeed';

    /**
     * @var bool
     */
    protected $config_form = false;

    /**
     * @var array
     */
    protected $fields = array();

    /**
     * Urbit_inventoryfeed constructor.
     */
    public function __construct()
    {
        $this->name = 'urbitinventoryfeed';
        $this->tab = 'administration';
        $this->version = '1.0.3';
        $this->author = 'Urbit';
        $this->need_instance = 1;

        $this->fields = array(
            'factory' => new UrbitInventoryfeedFieldsFactory(),
        );

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Urbit Inventory Feed');
        $this->description = $this->l('Urbit Inventory Feed Module');
    }

    /**
     * @return Module
     */
    public static function getInstance()
    {
        return Module::getInstanceByName(static::NAME);
    }

    /**
     * @return bool
     */
    public function install()
    {
        Configuration::updateValue('URBITINVENTORYFEED_LIVE_MODE', false);

        return parent::install()
            && $this->registerHook('header')
            && $this->registerHook('backOfficeHeader');
    }

    /**
     * @return mixed
     */
    public function uninstall()
    {
        Configuration::deleteByName('URBITINVENTORYFEED_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        $output = '';
        $this->context->smarty->assign('active', 'intro');

        if (((bool)Tools::isSubmit('submitUrbitinventoryfeedModule')) == true) {
            $output = $this->postProcess();
            $this->context->smarty->assign('active', 'account');
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        //link to controller (for ajax call)
        $this->context->smarty->assign('controllerlink', $this->context->link->getModuleLink('urbitinventoryfeed', 'feed', array()));

        $config = $this->renderForm();
        $this->context->smarty->assign(
            array(
             'config' => $config,
             'urbitinventoryfeed_img_path'  => $this->_path.'views/img/',
             )
        );

        return $output . $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new UrbitInventoryfeedUrbitHelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitUrbitinventoryfeedModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $valueArray = $this->getConfigFormValues();
        $valueArray['URBITINVENTORYFEED_TAGS_IDS[]'] = explode(',', Configuration::get('URBITINVENTORYFEED_TAGS_IDS', null));
        $valueArray['URBITINVENTORYFEED_FILTER_CATEGORIES[]'] = explode(',', Configuration::get('URBITINVENTORYFEED_FILTER_CATEGORIES', null));
        $valueArray['URBITINVENTORYFEED_PRODUCT_ID_FILTER[]'] = explode(',', Configuration::get('URBITINVENTORYFEED_PRODUCT_ID_FILTER', null));


        $helper->tpl_vars = array(
            'fields_value' => $valueArray,
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm($this->getInventoryFeedConfigForm());
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
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Live mode'),
                        'name'    => 'URBITINVENTORYFEED_LIVE_MODE',
                        'is_bool' => true,
                        'desc'    => $this->l('Use this module in live mode'),
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'col'    => 3,
                        'type'   => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc'   => $this->l('Enter a valid email address'),
                        'name'   => 'URBITINVENTORYFEED_ACCOUNT_EMAIL',
                        'label'  => $this->l('Email'),
                    ),
                    array(
                        'type'  => 'password',
                        'name'  => 'URBITINVENTORYFEED_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * return options for categories selects
     */
    protected function getCategoriesOptions()
    {
        $categories = Category::getNestedCategories(null, $this->context->language->id);

        $resultArray = array();

        foreach ($categories as $category) {
            $arr = array();
            $resultArray = array_merge($resultArray, $this->getCategoryInfo($category, $arr, ''));
        }

        return $resultArray;
    }

    /**
     * @param $category
     * @param $arr
     * @param $pref
     * @return array
     */
    protected function getCategoryInfo($category, $arr, $pref)
    {
        $arr[] = array(
            'id'   => $category['id_category'],
            'name' => $pref . $category['name'],
        );

        if (array_key_exists('children', $category)) {
            foreach ($category['children'] as $child) {
                $arr = $this->getCategoryInfo($child, $arr, $pref . $category['name'] . ' / ');
            }
        }

        return $arr;
    }

    /**
     * @param $withNotSetted
     * @return array
     */
    protected function getProductsOptions($withNotSetted = false)
    {
        $optionsForProductSelect = array();

        if ($withNotSetted) {
            $optionsForProductSelect[] = array(
                'id'   => '',
                'name' => 'Not Setted',
            );
        }

        $products = Product::getProducts($this->context->language->id, 0, 0, 'id_product', 'ASC');

        foreach ($products as $product) {
            $optionsForProductSelect[] = array('id' => $product['id_product'], 'name' => $product['id_product'] . ' : ' . $product['name']);
        }

        return $optionsForProductSelect;
    }

    /**
     * return options for tags selects
     * @return array
     */
    protected function getTagsOptions()
    {
        $optionsForTagSelect = array();

        $tags = Tag::getMainTags($this->context->language->id);

        foreach ($tags as $tag) {
            $optionsForTagSelect[] = array(
                'id'   => $tag['name'],
                'name' => $tag['name'],
            );
        }

        return $optionsForTagSelect;
    }

    protected function getCacheOptions()
    {
        return array(
            array(
                'id'   => 0.00000001,
                'name' => 'DISABLE CACHE',
            ),
            array(
                'id'   => 60,
                'name' => '1 hour',
            ),
            array(
                'id'   => 45,
                'name' => '45 min',
            ),
            array(
                'id'   => 30,
                'name' => '30 min',
            ),
            array(
                'id'   => 15,
                'name' => '15 min',
            ),
            array(
                'id'   => 5,
                'name' => '5 min',
            ),
        );
    }

    protected function getCountriesOptions($withNotSetted = false)
    {
        $optionsForTaxesSelect = array();

        if ($withNotSetted) {
            $optionsForTaxesSelect[] = array(
                'id'   => '',
                'name' => 'Not Setted',
            );
        }

        $countries = Country::getCountries($this->context->language->id);

        foreach ($countries as $country) {
            $optionsForTaxesSelect[] = array('id' => $country['id_country'], 'name' => $country['name']);
        }

        return $optionsForTaxesSelect;
    }

    /**
     * @return array
     */
    protected function getInventoryFeedConfigForm()
    {
        $optionsForCategorySelect = $this->getCategoriesOptions();
        $optionsForTagSelect = $this->getTagsOptions();
        $optionsForCacheSelect = $this->getCacheOptions();
        $optionsForTaxes = $this->getCountriesOptions(true);
        $optionsForProductFilter = $this->getProductsOptions(true);

        $fields_form = array();

        //Feed Cache
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Feed Cache'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => array(
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Cache duration'),
                    'name'    => 'URBITINVENTORYFEED_CACHE_DURATION',
                    'options' => array(
                        'query' => $optionsForCacheSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                    'hint' => $this->l('The extension uses caching system to reduce a site load and speed up the plug-in during the generation of the feed, so feed is created and saved to file at specific time intervals. The refresh interval is specified on the  \'Cache duration \' drop-down list.'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        // Product Filters
        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Inventory Filter'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => array(
                array(
                    'type'     => 'select',
                    'label'    => $this->l('Categories'),
                    'name'     => 'URBITINVENTORYFEED_FILTER_CATEGORIES[]',
                    'id'       => 'urbitinventoryfeed-filter-categories',
                    'multiple' => true,
                    'options'  => array(
                        'query' => $optionsForCategorySelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'    => 'fixed-width-xxl',
                    'hint' => $this->l('Filter by Categories using this multiselect lists where you can select several options (by using Ctrl+filter\'s name).
If there is no selected filter parameter (categories or tags or the number of products for filtering is zero), the system skips the filtering by this parameter.'),
                ),
                array(
                    'type'     => 'select',
                    'label'    => $this->l('Tags'),
                    'name'     => 'URBITINVENTORYFEED_TAGS_IDS[]',
                    'id'       => 'urbitinventoryfeed-filter-tags',
                    'multiple' => true,
                    'options'  => array(
                        'query' => $optionsForTagSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'    => 'fixed-width-xxl',
                    'hint' =>     $this->l('Filter by Tags using this multiselect lists where you can select several options (by using Ctrl+filter\'s name).
                     If there is no selected filter parameter (categories or tags or the number of products for filtering is zero), the system skips the filtering by this parameter.'),
                ),
                array(
                    'type'  => 'text',
                    'label' => $this->l('Minimal Stock'),
                    'name'  => 'URBITINVENTORYFEED_MINIMAL_STOCK',
                    'id'    => 'urbitinventoryfeed-filter-minimal-stock',
                    'class' => 'fixed-width-xxl',
                    'hint' => $this->l('Filter your product export by stock amount'),
                ),
                array(
                    'type'    => 'urbit_product_id_filter',
                    'label'   => $this->l('Product ID'),
                    'name'    => 'URBITINVENTORYFEED_PRODUCT_ID_FILTER_NEW',
                    'options' => array(
                        'query' => $this->fields['factory']->getOptions(),
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'   => 'fixed-width-xxl',
                    'hint' => $this->l('Select your Product ID Filter'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        //Taxes
        $fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('Taxes'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => array(
                array(
                    'type'     => 'select',
                    'label'    => $this->l('Country'),
                    'name'     => 'URBITINVENTORYFEED_TAX_COUNTRY',
                    'multiple' => false,
                    'options'  => array(
                        'query' => $optionsForTaxes,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                    'class'    => 'fixed-width-xxl',
                    'hint' => $this->l('Select your Country in the drop down menu'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        //Inventory Dimentions
        $fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->l('Product Fields - Product Dimensions'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => $this->fields['factory']->getInputs(),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        //Inventory
        $fields_form[4]['form'] = array(
            'legend' => array(
                'title' => $this->l('Product Fields - Inventory'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => $this->fields['factory']->getInventoryInputs(),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        //Prices
        $fields_form[5]['form'] = array(
            'legend' => array(
                'title' => $this->l('Product Fields - Prices'),
                'icon'  => 'icon-cogs',
            ),
            'input'  => $this->fields['factory']->getPriceInputs(),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        return $fields_form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array_merge(
            array(
                'URBITINVENTORYFEED_CACHE_DURATION'    => Configuration::get('URBITINVENTORYFEED_CACHE_DURATION', null),
                'URBITINVENTORYFEED_FILTER_CATEGORIES' => explode(',', Configuration::get('URBITINVENTORYFEED_FILTER_CATEGORIES', null)),
                'URBITINVENTORYFEED_TAGS_IDS'          => explode(',', Configuration::get('URBITINVENTORYFEED_TAGS_IDS', null)),
                'URBITINVENTORYFEED_TAX_COUNTRY'       => Configuration::get('URBITINVENTORYFEED_TAX_COUNTRY', null),
                'URBITINVENTORYFEED_MINIMAL_STOCK'     => Configuration::get('URBITINVENTORYFEED_MINIMAL_STOCK', null),
                'URBITINVENTORYFEED_PRODUCT_ID_FILTER' => Configuration::get('URBITINVENTORYFEED_PRODUCT_ID_FILTER', null),
            ),
            $this->fields['factory']->getInputsConfig(),
            $this->fields['factory']->getPriceInputsConfig(),
            $this->fields['factory']->getInventoryInputsConfig()
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            if (in_array($key, array('URBITINVENTORYFEED_TAGS_IDS', 'URBITINVENTORYFEED_FILTER_CATEGORIES', 'URBITINVENTORYFEED_PRODUCT_ID_FILTER'))) {
                if ($value = Tools::getValue($key)) {
                    Configuration::updateValue($key, implode(',', $value));
                } else {
                    Configuration::updateValue($key, null);
                }
            } else {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }
        if (Tools::getValue('URBITINVENTORYFEED_MINIMAL_STOCK') == null) {
            $this->context->controller->errors[] = $this->l('Filter your product export by stock amount');
        }

        if (empty($this->context->controller->errors)) {
                return $this->displayConfirmation($this->l('Settings updated'));
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->_path . 'views/js/multiselect.min.js');
    }
}
