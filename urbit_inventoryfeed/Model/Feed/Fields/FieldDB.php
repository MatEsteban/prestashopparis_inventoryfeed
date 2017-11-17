<?php

class Urbit_Inventoryfeed_Fields_FieldDB extends Urbit_Inventoryfeed_Fields_FieldAbstract
{
    /**
     * @param Urbit_Inventoryfeed_Inventory $inventoryProduct
     * @param string $name
     * @return mixed
     * @internal param Urbit_Inventoryfeed_Inventory $product
     */
    public static function processAttribute(Urbit_Inventoryfeed_Inventory $inventoryProduct, $name)
    {
        return $inventoryProduct->getProductAttribute(static::getNameWithoutPrefix($name));
    }

    /**
     * @return array
     */
    public static function getOptions()
    {
        $options = [
            [
                'id' => 'none',
                'name' => static::getModule()->l('------ Db Fields ------')
            ],
        ];

        foreach (Product::$definition['fields'] as $key => $field) {
            $options[] = [
                'id' => static::getPrefix() . $key,
                'name' => static::getModule()->l($key)
            ];
        }

        return $options;
    }

    /**
     * @param $name
     * @return mixed
     */
    public static function getNameWithoutPrefix($name)
    {
        return str_replace(static::getPrefix(), '', $name);
    }

    /**
     * @return string
     */
    public static function getPrefix()
    {
        return 'db_';
    }
}
