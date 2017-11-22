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

require_once dirname(__FILE__) . '/FieldAbstract.php';
require_once dirname(__FILE__) . '/Factory.php';

/**
 * Class UrbitInventoryfeedFieldsFieldCalculated
 */
class UrbitInventoryfeedFieldsFieldCalculated extends UrbitInventoryfeedFieldsFieldAbstract
{
    const FUNCTION_PREFIX = 'getProduct';

    /**
     * @param UrbitInventoryfeedInventory $inventoryProduct
     * @param string $name
     * @return
     */
    public static function processAttribute(UrbitInventoryfeedInventory $inventoryProduct, $name)
    {
        $static = new static();
        $funcName = static::FUNCTION_PREFIX . static::getNameWithoutPrefix($name);

        return $static->{$funcName}($inventoryProduct);
    }

    /**
     * @return array
     */
    public static function getOptions()
    {
        $options = array();

        $options[] = array(
            'id'   => 'none',
            'name' => Urbitinventoryfeed::getInstance()->l('------ Calculated ------'),
        );

        $methods = (new ReflectionClass(static::class))->getMethods();

        foreach ($methods as $method) {
            if (strpos($method->getName(), static::FUNCTION_PREFIX) !== false) {
                $name = str_replace(static::FUNCTION_PREFIX, '', $method->getName());

                if (!empty($name)) {
                    $options[] = array(
                        'id'   => static::getPrefix() . $name,
                        'name' => $name,
                    );
                }
            }
        }

        return $options;
    }

    /**
     * @return string
     */
    public static function getPrefix()
    {
        return 'calc_';
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
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return int|string
     */
    protected function getLocation(UrbitInventoryfeedInventory $feedProduct)
    {
        return UrbitInventoryfeedFieldsFactory::processAttribute($feedProduct, 'URBITINVENTORYFEED_LOCATION') ?: $this->getProductLocation($feedProduct);
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return int|mixed
     */
    protected function getQuantity(UrbitInventoryfeedInventory $feedProduct)
    {
        return UrbitInventoryfeedFieldsFactory::processAttribute($feedProduct, 'URBITINVENTORYFEED_QUANTITY') ?
            UrbitInventoryfeedFieldsFactory::processAttribute($feedProduct, 'URBITINVENTORYFEED_QUANTITY') :
            $this->getProductQuantity($feedProduct)
        ;
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return mixed
     */
    protected function getProductTaxRate(UrbitInventoryfeedInventory $feedProduct)
    {
        $product = $feedProduct->getProduct();

        $taxCountry = Configuration::get('URBITINVENTORYFEED_TAX_COUNTRY');
        $shopCountryId = $feedProduct->getContext()->country->id;

        $taxRate = null;
        $defaultCountryTax = null;

        $groupId = $product->getIdTaxRulesGroup();
        $rules = TaxRule::getTaxRulesByGroupId($feedProduct->getContext()->language->id, $groupId);

        foreach ($rules as $rule) {
            if ($rule['id_country'] == $taxCountry) {
                $taxRate = $rule['rate'];
            }

            if ($rule['id_country'] == $shopCountryId) {
                $defaultCountryTax = $rule['rate'];
            }
        }

        //IMS format price Urb-it
        return $taxRate ? $taxRate * 100 : ($defaultCountryTax ? $defaultCountryTax * 100 : 0);
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @param bool $useReduction
     * @return string
     */
    protected function getPrice(UrbitInventoryfeedInventory $feedProduct, $taxRate, $useReduction = true)
    {
        $useTax = $taxRate ? false : true;
        $price = Product::getPriceStatic($feedProduct->getProduct()->id, $useTax, ($feedProduct->getCombId() ? $feedProduct->getCombId() : null), 6, null, false, $useReduction);
        $priceWithTax = ($taxRate) ? $price + ($price * ($taxRate / 10000)) : $price;

        return number_format($priceWithTax, 2, '.', '');
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @param $taxRate
     * @param bool $useReduction
     * @return array
     */
    protected function getSaleDate(UrbitInventoryfeedInventory $feedProduct, $taxRate, $useReduction = true)
    {
        $useTax = $taxRate ? false : true;
        $sp = null;

        Product::getPriceStatic(
            $feedProduct->getProduct()->id,
            $useTax,
            ($feedProduct->getCombId() ? $feedProduct->getCombId() : null),
            6,
            null,
            false,
            $useReduction,
            null,
            null,
            null,
            null,
            null,
            $sp
        );

        return array(
            'from' => $sp['from'],
            'to'   => $sp['to'],
        );
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return array
     */
    protected function getProductInventory(UrbitInventoryfeedInventory $feedProduct)
    {
        $inventory = array(array(
            'location' => $this->getLocation($feedProduct),
            'quantity' => $this->getQuantity($feedProduct),
        ));

        return $inventory;
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return string
     */
    protected function getProductId(UrbitInventoryfeedInventory $feedProduct)
    {
        if (empty($this->combination)) {
            return (string) $feedProduct->getProduct()->id;
        }

        $combination = $feedProduct->getCombination();
        $true = isset($combination['reference']) && $combination['reference'];
        $cid = $feedProduct->getCombId();

        return ($true ? $combination['reference'] : $feedProduct->getProduct()->id) . '-' . $cid;
    }

    /**
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return mixed
     */
    protected function getProductDescription(UrbitInventoryfeedInventory $feedProduct)
    {
        return $feedProduct->getProduct()->description[Context::getContext()->language->id];
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return string
     */
    protected function getProductName(UrbitInventoryfeedInventory $feedProduct)
    {
        $context = Context::getContext();
        $name = $feedProduct->getProduct()->name[$context->language->id];

        // combination
        if (!empty($feedProduct->getCombination())) {
            $attributeResume = $feedProduct->getProduct()->getAttributesResume($context->language->id);
            foreach ($attributeResume as $attributesSet) {
                if ($attributesSet['id_product_attribute'] == $feedProduct->getCombId()) {
                    $productAttrs = $feedProduct->getProduct()->getAttributeCombinationsById(
                        $attributesSet['id_product_attribute'],
                        $context->language->id
                    );

                    foreach ($productAttrs as $attribute) {
                        $name .= ' ' . $attribute['attribute_name'];
                    }
                    break;
                }
            }
        }

        return $name;
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return array
     */
    protected function getProductCategories(UrbitInventoryfeedInventory $feedProduct)
    {
        $product = $feedProduct->getProduct();
        $categories = array();

        $categoriesInfo = Product::getProductCategoriesFull($product->id);

        foreach ($categoriesInfo as $category) {
            $allCategories = Category::getCategories();
            $parentId = null;

            foreach ($allCategories as $allCategory) {
                foreach ($allCategory as $childCategory) {
                    if ($childCategory['infos']['id_category'] == $category['id_category']) {
                        $parentId = $childCategory['infos']['id_parent'];
                        break;
                    }
                }
            }

            $categories[] = array(
                'id'       => $category['id_category'],
                'name'     => $category['name'],
                'parentId' => $parentId,

            );
        }

        return $categories;
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return array
     */
    protected function getProductImages(UrbitInventoryfeedInventory $feedProduct)
    {
        $product = $feedProduct->getProduct();

        $linkRewrite = $product->link_rewrite;

        $additional_images = array();
        $image = null;
        $coverImageId = null;

        // combination
        if (!empty($this->combination)) {
            $combinationImagesIds = $product->getCombinationImages($feedProduct->getContext()->language->id);

            if (isset($combinationImagesIds[$feedProduct->getCombId()])) {
                $combinationImagesIds = $combinationImagesIds[$feedProduct->getCombId()];

                if (!empty($combinationImagesIds)) {
                    foreach ($combinationImagesIds as $combinationImagesId) {
                        $additional_images[] = $feedProduct->getContext()->link->getImageLink(
                            $linkRewrite[1],
                            $combinationImagesId['id_image'],
                            ImageType::getFormattedName('large')
                        );
                    }
                //if combination hasn't own image
                } else {
                    $coverImageId = Product::getCover($product->id)['id_image'];
                    $image = $feedProduct->getContext()->link->getImageLink(
                        $linkRewrite[1],
                        $coverImageId,
                        ImageType::getFormattedName('large')
                    );
                }
            }
        //simple product
        } else {
            $coverImageId = Product::getCover($product->id)['id_image'];

            $additionalImages = Image::getImages($feedProduct->getContext()->language->id, $product->id);

            foreach ($additionalImages as $img) {
                $imageId = (new Image((int)$img['id_image']))->id;

                if ((int) $coverImageId == $imageId) {
                    continue;
                }

                $link = new Link();

                $additional_image_link = 'http://' . $link->getImageLink(
                    $linkRewrite[1],
                    $imageId,
                    ImageType::getFormattedName('large')
                );
                $additional_images[] = $additional_image_link;
            }

            if ($coverImageId) {
                $image = $feedProduct->getContext()->link->getImageLink(
                    $linkRewrite[1],
                    $coverImageId,
                    ImageType::getFormattedName('large')
                );
            }
        }

        return array(
            'additional_image_links' => $additional_images,
            'image_link'             => $image,
        );
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return array
     */
    protected function getProductAttributes(UrbitInventoryfeedInventory $feedProduct)
    {
        $product = $feedProduct->getProduct();
        $attributes = array();

        $additionalAttributes = Configuration::get('URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE');

        //check product features
        $FrontFeatures = $product->getFrontFeatures($feedProduct->getContext()->language->id);

        if (!empty($FrontFeatures)) {
            foreach ($FrontFeatures as $frontFeature) {
                if (in_array('f' . $frontFeature['id_feature'], explode(',', $additionalAttributes))) {
                    $attributes[] = array(
                        'name'  => $frontFeature['name'],
                        'type'  => 'string',
                        'value' => $frontFeature['value'],
                    );
                }
            }
        }

        //check product attributes
        $attributeCombinations = $product->getAttributeCombinations($feedProduct->getContext()->language->id);

        if (!empty($attributeCombinations)) {
            foreach ($attributeCombinations as $attributeCombination) {
                if (in_array('a' . $attributeCombination['id_attribute_group'], explode(',', $additionalAttributes)) && $attributeCombination['id_product_attribute'] == $feedProduct->getCombId()) {
                    $attributes[] = array(
                        'name'  => $attributeCombination['group_name'],
                        'type'  => 'string',
                        'value' => $attributeCombination['attribute_name'],
                    );
                }
            }
        }

        if (!empty($attributes)) {
            return $attributes;
        }

        return array();
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return string
     */
    protected function getProductNameOld(UrbitInventoryfeedInventory $feedProduct)
    {
        $product = $feedProduct->getProduct();
        $name = $product->name[$feedProduct->getContext()->language->id];

        // combination
        if (!empty($this->combination)) {
            $attributeResume = $product->getAttributesResume($feedProduct->getContext()->language->id);

            foreach ($attributeResume as $attributesSet) {
                if ($attributesSet['id_product_attribute'] == $feedProduct->getCombId()) {
                    foreach ($product->getAttributeCombinationsById($attributesSet['id_product_attribute'], $feedProduct->getContext()->language->id) as $attribute) {
                        $name .= ' ' . $attribute['attribute_name'];
                    }

                    break;
                }
            }
        }

        return $name;
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return array
     */
    protected function getProductBrands(UrbitInventoryfeedInventory $feedProduct)
    {
        $product = $feedProduct->getProduct();
        $brands = array();

        if ($product->id_manufacturer != "0") {
            $brands[] = array(
                'name' => Manufacturer::getNameById($product->id_manufacturer),
            );
        }

        return $brands;
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return string
     */
    protected function getProductCurrency(UrbitInventoryfeedInventory $feedProduct)
    {
        return Context::getContext()->currency->iso_code;
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return string
     */
    protected function getProductRegularPrice(UrbitInventoryfeedInventory $feedProduct)
    {
        $taxRate = $this->getProductTaxRate($feedProduct);

        return $this->getPrice($feedProduct, $taxRate, false);
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return string
     */
    protected function getProductSalePrice(UrbitInventoryfeedInventory $feedProduct)
    {
        $taxRate = $this->getProductTaxRate($feedProduct);

        return $this->getPrice($feedProduct, $taxRate, true);
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return string
     */
    protected function getProductPriceEffectiveDate(UrbitInventoryfeedInventory $feedProduct)
    {
        $taxRate = $this->getProductTaxRate($feedProduct);

        $date = $this->getSaleDate($feedProduct, $taxRate, true);

        return $this->formattedSalePriceDate($date);
    }

    /**
     * @param $salePriceDateArray
     * @return null|string
     */
    protected function formattedSalePriceDate($salePriceDateArray)
    {
        if ($salePriceDateArray['from'] != '0000-00-00 00:00:00' && $salePriceDateArray['to'] != '0000-00-00 00:00:00') {
            $tz = Configuration::get('PS_TIMEZONE');
            $dtFrom = new DateTime($salePriceDateArray['from'], new DateTimeZone($tz));
            $dtTo = new DateTime($salePriceDateArray['to'], new DateTimeZone($tz));

            return $dtFrom->format('Y-m-d\TH:iO') . '/' . $dtTo->format('Y-m-d\TH:iO');
        }

        return null;
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return int|string
     */
    protected function getProductLocation(UrbitInventoryfeedInventory $feedProduct)
    {
        if (!$feedProduct->getCombId()) {
            $product = $feedProduct->getProduct();
            $location = ((isset($product->location)) && ($product->location != '')) ? $product->location : "1";
        } else {
            $combination = $feedProduct->getCombination();
            $location = ((isset($combination['location'])) && ($combination['location'] != '')) ? $combination['location'] : "1";
        }

        return $location;
    }

    /**
     * @param UrbitInventoryfeedInventory $feedProduct
     * @return int
     */
    protected function getProductQuantity(UrbitInventoryfeedInventory $feedProduct)
    {
        return $feedProduct->getCombId() ?
            $feedProduct->getCombination()['quantity'] :
            Product::getQuantity($feedProduct->getProduct()->id)
        ;
    }
}
