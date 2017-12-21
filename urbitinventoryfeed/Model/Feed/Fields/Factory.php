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

require_once dirname(__FILE__) . '/FieldCalculated.php';
require_once dirname(__FILE__) . '/FieldAttribute.php';
require_once dirname(__FILE__) . '/FieldDB.php';
require_once dirname(__FILE__) . '/FieldFeature.php';


/**
 * Class UrbitInventoryfeedFieldsFactory
 */
class UrbitInventoryfeedFieldsFactory
{
    /**
     * @var array
     */
    protected $_inputs = array(
        'URBITINVENTORYFEED_ATTRIBUTE_ID' => 'Id',
    );

    /**
     * @var array
     */
    protected $_priceInputs = array(
        'URBITINVENTORYFEED_REGULAR_PRICE_CURRENCY' => 'Regular Price Currency',
        'URBITINVENTORYFEED_REGULAR_PRICE_VALUE'    => 'Regular Price Value',
        'URBITINVENTORYFEED_REGULAR_PRICE_VAT'      => 'Regular Price VAT',

        'URBITINVENTORYFEED_SALE_PRICE_CURRENCY'  => 'Sale Price Currency',
        'URBITINVENTORYFEED_SALE_PRICE_VALUE'     => 'Sale Price Value',
        'URBITINVENTORYFEED_SALE_PRICE_VAT'       => 'Sale Price VAT',
        'URBITINVENTORYFEED_PRICE_EFFECTIVE_DATE' => 'Price effective date',
    );

    /**
     * @var array
     */
    protected $_inventoryInputs = array(
        'URBITINVENTORYFEED_INVENTORY_LOCATION' => 'Location',
        'URBITINVENTORYFEED_INVENTORY_QUANTITY' => 'Quantity',
    );


    /**
     * @param $product
     * @param $name
     * @return mixed
     */
    public static function processAttribute($product, $name)
    {
        $inputConfig = static::getInputConfig($name);

        if (empty($inputConfig) || $inputConfig == 'none' || $inputConfig == 'empty') {
            return false;
        }

        $cls = static::getFieldClassByFieldName($inputConfig);

        return $cls::processAttribute($product, $inputConfig);
    }

    /**
     * @param $product
     * @param $name
     * @return mixed
     */
    public static function processAttributeByKey($product, $name)
    {
        $cls = static::getFieldClassByFieldName($name);

        return $cls::processAttribute($product, $name);
    }

    /**
     * @return array
     */
    public function getInputs()
    {
        return $this->_generateInputs($this->_inputs);
    }

    /**
     * @return array
     */
    public function getPriceInputs()
    {
        return $this->_generateInputs($this->_priceInputs);
    }



    /**
     * @return array
     */
    public function getInventoryInputs()
    {
        return $this->_generateInputs($this->_inventoryInputs);
    }

    /**
     * @param $name
     * @return string
     */
    public static function getInputConfig($name)
    {
        return self::getConfigValue($name);
    }

    /**
     * @return array
     */
    public function getInputsConfig()
    {
        $config = array();

        foreach ($this->_inputs as $key => $name) {
            $config[$key] = $this->getInputConfig($key);
        }

        return $config;
    }

    /**
     * @return array
     */
    public function getPriceInputsConfig()
    {
        $config = array();

        foreach ($this->_priceInputs as $key => $name) {
            $config[$key] = $this->getInputConfig($key);
        }

        return $config;
    }


    /**
     * @return array
     */
    public function getInventoryInputsConfig()
    {
        $config = array();

        foreach ($this->_inventoryInputs as $key => $name) {
            $config[$key] = $this->getInputConfig($key);
        }

        return $config;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return array_merge(
            array(array(
                'id'   => 'empty',
                'name' => static::getModule()->l('------ None------'),
            )),
            UrbitInventoryfeedFieldsFieldCalculated::getOptions(),
            UrbitInventoryfeedFieldsFieldDB::getOptions(),
            UrbitInventoryfeedFieldsFieldAttribute::getOptions(),
            UrbitInventoryfeedFieldsFieldFeature::getOptions(),
            array(array(
                'id'   => 'none',
                'name' => static::getModule()->l('------ None------'),
            ))
        );
    }

    /**
     * @param array $inputOptions
     * @return array
     */
    protected function _generateInputs($inputOptions)
    {
        $inputs = array();

        foreach ($inputOptions as $key => $name) {
            $inputs[] = array(
                'type'    => 'select',
                'label'   => static::getModule()->l($name),
                'name'    => $key,
                'options' => array(
                    'query' => $this->getOptions(),
                    'id'    => 'id',
                    'name'  => 'name',
                ),
                'class'   => 'fixed-width-xxl',
            );
        }

        return $inputs;
    }

    /**
     * @param array $inputOptions
     * @return array
     */
    protected function _generateTextInputs($inputOptions)
    {
        $inputs = array();

        foreach ($inputOptions as $key => $name) {
            $inputs[] = array(
                'type'  => 'text',
                'label' => static::getModule()->l($name),
                'name'  => $key,
                'class' => 'fixed-width-xxl',
            );
        }

        return $inputs;
    }

    /**
     * @param string $name
     * @return bool|mixed
     */
    public static function getFieldClassByFieldName($name)
    {
        foreach (array(
            UrbitInventoryfeedFieldsFieldCalculated::class,
            UrbitInventoryfeedFieldsFieldDB::class,
            UrbitInventoryfeedFieldsFieldAttribute::class,
            UrbitInventoryfeedFieldsFieldFeature::class,
        ) as $cls) {
            $prefix = $cls::getPrefix();
            if (preg_match("/^{$prefix}/", $name)) {
                return $cls;
            }
        }

        return false;
    }

    /**
     * @return Module
     */
    public static function getModule()
    {
        return Urbitinventoryfeed::getInstance();
    }

    /**
     * Get value from ps_configuration for this key
     * If multistore enable => get config value only for current store
     * @param $key
     * @return string
     */
    protected static function getConfigValue($key)
    {
        return (version_compare(_PS_VERSION_, '1.5', '>') && Shop::isFeatureActive()) ?
            Configuration::get($key, null, null, Context::getContext()->shop->id) :
            Configuration::get($key, null);
    }
}
