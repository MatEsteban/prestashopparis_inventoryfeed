<?php

require_once dirname(__FILE__) . '/FieldAbstract.php';
require_once dirname(__FILE__) . '/Factory.php';

/**
 * Class Urbit_Inventoryfeed_Fields_FieldCalculated
 */
class Urbit_Inventoryfeed_Fields_FieldCalculated extends Urbit_Inventoryfeed_Fields_FieldAbstract
{
    const FUNCTION_PREFIX = 'getProduct';

    /**
     * @param Urbit_Inventoryfeed_Inventory $inventoryProduct
     * @param string $name
     * @return
     */
    public static function processAttribute(Urbit_Inventoryfeed_Inventory $inventoryProduct, $name)
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
        $options = [];

        $options[] = [
            'id'   => 'none',
            'name' => Urbit_inventoryfeed::getInstance()->l('------ Calculated ------'),
        ];

        $methods = (new ReflectionClass(static::class))->getMethods();

        foreach ($methods as $method) {
            if (strpos($method->getName(), static::FUNCTION_PREFIX) !== false) {
                $name = str_replace(static::FUNCTION_PREFIX, '', $method->getName());

                if (!empty($name)) {
                    $options[] = [
                        'id'   => static::getPrefix() . $name,
                        'name' => $name,
                    ];
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
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return int|string
     */
    protected function getLocation(Urbit_Inventoryfeed_Inventory $feedProduct)
    {
        return Urbit_Inventoryfeed_Fields_Factory::processAttribute($feedProduct, 'URBIT_INVENTORYFEED_LOCATION') ?: $this->getProductLocation($feedProduct);
    }

    /**
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return int|mixed
     */
    protected function getQuantity(Urbit_Inventoryfeed_Inventory $feedProduct)
    {
        return Urbit_Inventoryfeed_Fields_Factory::processAttribute($feedProduct, 'URBIT_INVENTORYFEED_QUANTITY') ?
            Urbit_Inventoryfeed_Fields_Factory::processAttribute($feedProduct, 'URBIT_INVENTORYFEED_QUANTITY') :
            $this->getProductQuantity($feedProduct)
        ;
    }

    /**
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return mixed
     */
    protected function getProductTaxRate(Urbit_Inventoryfeed_Inventory $feedProduct)
    {
        $product = $feedProduct->getProduct();

        $taxCountry = Configuration::get('URBIT_INVENTORYFEED_TAX_COUNTRY');

        $taxRate = null;
        $defaultCountryTax = null;

        $groupId = $product->getIdTaxRulesGroup();
        $rules = TaxRule::getTaxRulesByGroupId($feedProduct->getContext()->language->id, $groupId);

        foreach ($rules as $rule) {
            if ($rule['id_country'] == $taxCountry) {
                $taxRate = $rule['rate'];
            }
        }

        return $taxRate;
    }

    /**
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @param bool $useReduction
     * @return string
     */
    protected function getPrice(Urbit_Inventoryfeed_Inventory $feedProduct, $taxRate, $useReduction = true)
    {
        $useTax = $taxRate ? false : true;
        $price = Product::getPriceStatic($feedProduct->getProduct()->id, $useTax, ($feedProduct->getCombId() ? $feedProduct->getCombId() : null), 6, null, false, $useReduction);
        $priceWithTax = ($taxRate) ? $price + ($price * ($taxRate / 100)) : $price;

        return number_format($priceWithTax, 2, '.', '');
    }

    /**
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @param $taxRate
     * @param bool $useReduction
     * @return array
     */
    protected function getSaleDate(Urbit_Inventoryfeed_Inventory $feedProduct, $taxRate, $useReduction = true)
    {
        $useTax = $taxRate ? false : true;
        $sp = null;

        Product::getPriceStatic(
            $feedProduct->getProduct()->id, $useTax, ($feedProduct->getCombId() ? $feedProduct->getCombId() : null),
            6, null, false, $useReduction, null, null, null, null, null, $sp
        );

        return [
            'from' => $sp['from'],
            'to'   => $sp['to'],
        ];
    }

    /**
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return array
     */
    protected function getProductInventory(Urbit_Inventoryfeed_Inventory $feedProduct)
    {
        $inventory = [[
            'location' => $this->getLocation($feedProduct),
            'quantity' => $this->getQuantity($feedProduct),
        ]];

        return $inventory;
    }

    /**
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return string
     */
    protected function getProductId(Urbit_Inventoryfeed_Inventory $feedProduct)
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
    protected function getProductDescription(Urbit_Inventoryfeed_Inventory $feedProduct)
    {
        return $feedProduct->getProduct()->description[Context::getContext()->language->id];
    }

    /**
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return string
     */
    protected function getProductName(Urbit_Inventoryfeed_Inventory $feedProduct)
    {
        $context = Context::getContext();
        $name = $feedProduct->getProduct()->name[$context->language->id];

        // combination
        if (!empty($feedProduct->getCombination())) {
            $attributeResume = $feedProduct->getProduct()->getAttributesResume($context->language->id);
            foreach ($attributeResume as $attributesSet) {
                if ($attributesSet['id_product_attribute'] == $feedProduct->getCombId()) {
                    $productAttrs = $feedProduct->getProduct()->getAttributeCombinationsById(
                        $attributesSet['id_product_attribute'], $context->language->id
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
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return array
     */
    protected function getProductCategories(Urbit_Inventoryfeed_Inventory $feedProduct)
    {
        $product = $feedProduct->getProduct();
        $categories = [];

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

            $categories[] = [
                'id'       => $category['id_category'],
                'name'     => $category['name'],
                'parentId' => $parentId,

            ];
        }

        return $categories;
    }

    /**
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return array
     */
    protected function getProductImages(Urbit_Inventoryfeed_Inventory $feedProduct)
    {
        $product = $feedProduct->getProduct();

        $linkRewrite = $product->link_rewrite;

        $additional_images = [];
        $image = null;
        $coverImageId = null;

        // combination
        if (!empty($this->combination)) {
            $combinationImagesIds = $product->getCombinationImages($feedProduct->getContext()->language->id);

            if (isset($combinationImagesIds[$feedProduct->getCombId()])) {
                $combinationImagesIds = $combinationImagesIds[$feedProduct->getCombId()];

                if (!empty($combinationImagesIds)) {
                    foreach ($combinationImagesIds as $combinationImagesId) {
                        $additional_images[] = $feedProduct->getContext()->link->getImageLink($linkRewrite[1], $combinationImagesId['id_image'], 'large_default');
                    }
                //if combination hasn't own image
                } else {
                    $coverImageId = Product::getCover($product->id)['id_image'];
                    $image = $feedProduct->getContext()->link->getImageLink($linkRewrite[1], $coverImageId, 'large_default');
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

                $additional_image_link = 'http://' . $link->getImageLink($linkRewrite[1], $imageId, 'large_default');
                $additional_images[] = $additional_image_link;
            }

            if ($coverImageId) {
                $image = $feedProduct->getContext()->link->getImageLink($linkRewrite[1], $coverImageId, 'large_default');
            }
        }

        return [
            'additional_image_links' => $additional_images,
            'image_link'             => $image,
        ];
    }

    /**
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return array
     */
    protected function getProductAttributes(Urbit_Inventoryfeed_Inventory $feedProduct)
    {
        $product = $feedProduct->getProduct();
        $attributes = [];

        $additionalAttributes = Configuration::get('URBIT_PRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE');

        //check product features
        $FrontFeatures = $product->getFrontFeatures($feedProduct->getContext()->language->id);

        if (!empty($FrontFeatures)) {
            foreach ($FrontFeatures as $frontFeature) {
                if (in_array('f' . $frontFeature['id_feature'], explode(',', $additionalAttributes))) {
                    $attributes[] = [
                        'name'  => $frontFeature['name'],
                        'type'  => 'string',
                        'value' => $frontFeature['value'],
                    ];
                }
            }
        }

        //check product attributes
        $attributeCombinations = $product->getAttributeCombinations($feedProduct->getContext()->language->id);

        if (!empty($attributeCombinations)) {
            foreach ($attributeCombinations as $attributeCombination) {
                if (in_array('a' . $attributeCombination['id_attribute_group'], explode(',', $additionalAttributes)) && $attributeCombination['id_product_attribute'] == $feedProduct->getCombId()) {
                    $attributes[] = [
                        'name'  => $attributeCombination['group_name'],
                        'type'  => 'string',
                        'value' => $attributeCombination['attribute_name'],
                    ];
                }
            }
        }

        if (!empty($attributes)) {
            return $attributes;
        }

        return [];
    }

    /**
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return string
     */
    protected function getProductNameOld(Urbit_Inventoryfeed_Inventory $feedProduct)
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
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return array
     */
    protected function getProductBrands(Urbit_Inventoryfeed_Inventory $feedProduct)
    {
        $product = $feedProduct->getProduct();
        $brands = [];

        if ($product->id_manufacturer != "0") {
            $brands[] = [
                'name' => Manufacturer::getNameById($product->id_manufacturer),
            ];
        }

        return $brands;
    }

    /**
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return string
     */
    protected function getProductCurrency(Urbit_Inventoryfeed_Inventory $feedProduct)
    {
        return Context::getContext()->currency->iso_code;
    }

    /**
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return string
     */
    protected function getProductRegularPrice(Urbit_Inventoryfeed_Inventory $feedProduct)
    {
        $taxRate = $this->getProductTaxRate($feedProduct);

        return $this->getPrice($feedProduct, $taxRate, false);
    }

    /**
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return string
     */
    protected function getProductSalePrice(Urbit_Inventoryfeed_Inventory $feedProduct)
    {
        $taxRate = $this->getProductTaxRate($feedProduct);

        return $this->getPrice($feedProduct, $taxRate, true);
    }

    /**
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return string
     */
    protected function getProductPriceEffectiveDate(Urbit_Inventoryfeed_Inventory $feedProduct)
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
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return int|string
     */
    protected function getProductLocation(Urbit_Inventoryfeed_Inventory $feedProduct)
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
     * @param Urbit_Inventoryfeed_Inventory $feedProduct
     * @return int
     */
    protected function getProductQuantity(Urbit_Inventoryfeed_Inventory $feedProduct)
    {
        return $feedProduct->getCombId() ?
            $feedProduct->getCombination()['quantity'] :
            Product::getQuantity($feedProduct->getProduct()->id)
        ;
    }
}
