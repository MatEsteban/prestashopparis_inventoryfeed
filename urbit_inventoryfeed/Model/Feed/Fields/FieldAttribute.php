<?php

class Urbit_Inventoryfeed_Fields_FieldAttribute extends Urbit_Inventoryfeed_Fields_FieldAbstract
{
    /**
     * @param Urbit_Inventoryfeed_Inventory $inventoryProduct
     * @param string $name
     * @return array
     */
    static function processAttribute(Urbit_Inventoryfeed_Inventory $inventoryProduct, $name)
    {
        $product = $inventoryProduct->getProduct();

        $id = static::getNameWithoutPrefix($name);

        $attributeCombinations = $product->getAttributeCombinations($inventoryProduct->getContext()->language->id);

        if (!empty($attributeCombinations)) {
            foreach ($attributeCombinations as $attributeCombination) {
                if ($attributeCombination['id_attribute_group'] == $id) {
                    return $attributeCombination['attribute_name'];
                }
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public static function getOptions()
    {
        $options = [];

        $options[] = [
            'id'   => 'none',
            'name' => static::getModule()->l('------ Attributes ------'),
        ];

        $attributes = Attribute::getAttributes(Context::getContext()->language->id);

        foreach ($attributes as $attribute) {
            $options[] = [
                'id'   => static::getPrefix() . $attribute['id_attribute_group'],
                'name' => static::getModule()->l($attribute['attribute_group']),
            ];
        }

        return array_unique($options, SORT_REGULAR);
    }

    /**
     * @return string
     */
    public static function getPrefix()
    {
        return 'a_';
    }

    /**
     * @param $name
     * @return mixed
     */
    public static function getNameWithoutPrefix($name)
    {
        return str_replace(static::getPrefix(), '', $name);
    }
}
