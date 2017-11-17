<?php

require_once dirname(__FILE__) . '/FieldCalculated.php';
require_once dirname(__FILE__) . '/FieldAttribute.php';
require_once dirname(__FILE__) . '/FieldDB.php';
require_once dirname(__FILE__) . '/FieldFeature.php';

/**
 * Class Urbit_Inventoryfeed_Fields_Factory
 */
class Urbit_Inventoryfeed_Fields_Factory
{
    /**
     * @var array
     */
    protected $_inputs = [
        'URBIT_INVENTORYFEED_ATTRIBUTE_ID' => 'Id',
    ];

    /**
     * @var array
     */
    protected $_priceInputs = [
        'URBIT_INVENTORYFEED_REGULAR_PRICE_CURRENCY' => 'Regular Price Currency',
        'URBIT_INVENTORYFEED_REGULAR_PRICE_VALUE'    => 'Regular Price Value',
        'URBIT_INVENTORYFEED_REGULAR_PRICE_VAT'      => 'Regular Price VAT',

        'URBIT_INVENTORYFEED_SALE_PRICE_CURRENCY'  => 'Sale Price Currency',
        'URBIT_INVENTORYFEED_SALE_PRICE_VALUE'     => 'Sale Price Value',
        'URBIT_INVENTORYFEED_SALE_PRICE_VAT'       => 'Sale Price VAT',
        'URBIT_INVENTORYFEED_PRICE_EFFECTIVE_DATE' => 'Price effective date',
    ];

    /**
     * @var array
     */
    protected $_inventoryInputs = [
        'URBIT_INVENTORYFEED_INVENTORY_LOCATION' => 'Location',
        'URBIT_INVENTORYFEED_INVENTORY_QUANTITY' => 'Quantity',
    ];

    protected $_inventoryListInputs = [
        'URBIT_INVENTORYFEED_SCHEMA'           => 'Schema',
        'URBIT_INVENTORYFEED_CONTENT_LANGUAGE' => 'Content language',
        'URBIT_INVENTORYFEED_CONTENT_TYPE'     => 'Content type',
        'URBIT_INVENTORYFEED_CREATED_AT'       => 'Created at',
        'URBIT_INVENTORYFEED_UPDATED_AT'       => 'Updated at',
        'URBIT_INVENTORYFEED_TARGET_COUNTRY'   => 'Target countries (comma separated)',
        'URBIT_INVENTORYFEED_VERSION'          => 'Version',
        'URBIT_INVENTORYFEED_FEED_FORMAT'      => 'Feed format - encoding',
    ];

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
    public function getInventoryListInputs()
    {
        return $this->_generateTextInputs($this->_inventoryListInputs);
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
        return Configuration::get($name, null);
    }

    /**
     * @return array
     */
    public function getInputsConfig()
    {
        $config = [];

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
        $config = [];

        foreach ($this->_priceInputs as $key => $name) {
            $config[$key] = $this->getInputConfig($key);
        }

        return $config;
    }

    /**
     * @return array
     */
    public function getInventoryListInputsConfig()
    {
        $config = [];

        foreach ($this->_inventoryListInputs as $key => $name) {
            $config[$key] = $this->getInputConfig($key);
        }

        return $config;
    }

    /**
     * @return array
     */
    public function getInventoryInputsConfig()
    {
        $config = [];

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
            [[
                'id'   => 'empty',
                'name' => static::getModule()->l('------ None------'),
            ]],
            Urbit_Inventoryfeed_Fields_FieldCalculated::getOptions(),
            Urbit_Inventoryfeed_Fields_FieldDB::getOptions(),
            Urbit_Inventoryfeed_Fields_FieldAttribute::getOptions(),
            Urbit_Inventoryfeed_Fields_FieldFeature::getOptions(),
            [[
                'id'   => 'none',
                'name' => static::getModule()->l('------ None------'),
            ]]
        );
    }

    /**
     * @param array $inputOptions
     * @return array
     */
    protected function _generateInputs($inputOptions)
    {
        $inputs = [];

        foreach ($inputOptions as $key => $name) {
            $inputs[] = [
                'type'    => 'select',
                'label'   => static::getModule()->l($name),
                'name'    => $key,
                'options' => [
                    'query' => $this->getOptions(),
                    'id'    => 'id',
                    'name'  => 'name',
                ],
                'class'   => 'fixed-width-xxl',
            ];
        }

        return $inputs;
    }

    /**
     * @param array $inputOptions
     * @return array
     */
    protected function _generateTextInputs($inputOptions)
    {
        $inputs = [];

        foreach ($inputOptions as $key => $name) {
            $inputs[] = [
                'type'  => 'text',
                'label' => static::getModule()->l($name),
                'name'  => $key,
                'class' => 'fixed-width-xxl',
            ];
        }

        return $inputs;
    }

    /**
     * @param string $name
     * @return bool|mixed
     */
    public static function getFieldClassByFieldName($name)
    {
        foreach ([
            Urbit_Inventoryfeed_Fields_FieldCalculated::class,
            Urbit_Inventoryfeed_Fields_FieldDB::class,
            Urbit_Inventoryfeed_Fields_FieldAttribute::class,
            Urbit_Inventoryfeed_Fields_FieldFeature::class,
        ] as $cls) {
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
        return Urbit_inventoryfeed::getInstance();
    }
}
