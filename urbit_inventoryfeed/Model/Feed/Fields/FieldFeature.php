<?php

class Urbit_Inventoryfeed_Fields_FieldFeature extends Urbit_Inventoryfeed_Fields_FieldAbstract
{
    /**
     * @param Urbit_Inventoryfeed_Inventory $inventoryProduct
     * @param $name
     * @return string
     */
    static function processAttribute(Urbit_Inventoryfeed_Inventory $inventoryProduct, $name)
    {
        $features = $inventoryProduct->getProduct()->getFeatures();
        $featureValues = [];

        $id = static::getNameWithoutPrefix($name);

        foreach ($features as $feature) {
            if ($feature['id_feature'] == $id) {
                $values = FeatureValue::getFeatureValuesWithLang(Context::getContext()->language->id, $feature['id_feature']);

                if (!empty($values)) {
                    foreach ($values as $featureValue) {
                        if ($featureValue['id_feature_value'] == $feature['id_feature_value']) {
                            $featureValues[] = $featureValue['value'] ?: '';
                        }
                    };
                } else {
                    foreach (FeatureValue::getFeatureValueLang($feature['id_feature_value']) as $featureValueLang) {
                        $featureValues[] = $featureValueLang['value'] ?: '';
                    }
                }
            }
        }

        return implode(', ', $featureValues);
    }

    /**
     * @return array
     */
    static function getOptions()
    {
        $options = [];

        $options[] = [
            'id'   => 'none',
            'name' => '------ Features ------',
        ];

        $features = Feature::getFeatures(Context::getContext()->language->id);

        foreach ($features as $feature) {
            $options[] = [
                'id'   => static::getPrefix() . $feature['id_feature'],
                'name' => $feature['name'],
            ];
        }

        return $options;
    }

    /**
     * @return string
     */
    static function getPrefix()
    {
        return 'f_';
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
