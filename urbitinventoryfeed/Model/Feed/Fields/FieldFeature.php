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
 
class UrbitInventoryfeedFieldsFieldFeature extends UrbitInventoryfeedFieldsFieldAbstract
{
    /**
     * @param UrbitInventoryfeedInventory $inventoryProduct
     * @param $name
     * @return string
     */
    public static function processAttribute(UrbitInventoryfeedInventory $inventoryProduct, $name)
    {
        $features = $inventoryProduct->getProduct()->getFeatures();
        $featureValues = array();

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
    public static function getOptions()
    {
        $options = array();

        $options[] = array(
            'id'   => 'none',
            'name' => '------ Features ------',
        );

        $features = Feature::getFeatures(Context::getContext()->language->id);

        foreach ($features as $feature) {
            $options[] = array(
                'id'   => static::getPrefix() . $feature['id_feature'],
                'name' => $feature['name'],
            );
        }

        return $options;
    }

    /**
     * @return string
     */
    public static function getPrefix()
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
