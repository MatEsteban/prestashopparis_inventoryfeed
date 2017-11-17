<?php

include_once(_PS_MODULE_DIR_ . 'urbit_inventoryfeed' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'Feed' . DIRECTORY_SEPARATOR . 'Inventory.php');

/**
 * Class Feed
 */
class Urbit_Inventoryfeed_Feed
{
    /**
     * Schedule intervals
     */
    const SCHEDULE_INTERVAL_5MIN = '5MIN';
    const SCHEDULE_INTERVAL_15MIN = '15MIN';
    const SCHEDULE_INTERVAL_30MIN = '30MIN';
    const SCHEDULE_INTERVAL_45MIN = '45MIN';
    const SCHEDULE_INTERVAL_HOURLY = 'HOURLY';

    const SCHEDULE_INTERVAL_5MIN_TIME = 5;
    const SCHEDULE_INTERVAL_15MIN_TIME = 15;
    const SCHEDULE_INTERVAL_30MIN_TIME = 30;
    const SCHEDULE_INTERVAL_45MIN_TIME = 45;
    const SCHEDULE_INTERVAL_HOURLY_TIME = 60;

    const FEED_VERSION = '2017-06-28-1';

    /**
     * Valid products for using in feed
     * @var array
     */
    protected $data = [];

    /**
     * Collection of shop's products
     * @var array
     */
    protected $collection = [];

    /**
     * Prestashop Context
     * @var object
     */
    protected $context = null;

    /**
     * Feed constructor.
     * @param $collection
     */
    public function __construct($collection)
    {
        $this->collection = $collection;
        $this->context = Context::getContext();
    }

    /**
     * Process products to use in feed
     */
    protected function process()
    {
        $inventory = [];

        foreach ($this->collection as $product) {
            //get all combinations of product
            $combinations = $this->getCombinations($product['id_product']);

            if (empty($combinations) && $product['name'] != '') { //simple product
                $feedInventory = new Urbit_Inventoryfeed_Inventory($product);

                if ($feedInventory->process()) {
                    $inventory[] = $feedInventory->toArray();
                }
            } else { //product with variables
                foreach ($combinations as $combId => $combination) {
                    if ($combination['quantity'] <= 0) {
                        continue;
                    }

                    $feedInventory = new Urbit_Inventoryfeed_Inventory($product, $combId, $combination);

                    if ($feedInventory->process()) {
                        $inventory[] = $feedInventory->toArray();
                    }
                }
            }
        }

        $this->data = $inventory;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if (empty($this->data)) {
            $this->process();
        }

        $lang = (version_compare(_PS_VERSION_, "1.7", "<")) ? $this->context->language->language_code : $this->context->language->locale;
        $version = $this->getFeedVersion();

        $feedArray = [
            '$schema'            => Configuration::get('URBIT_INVENTORYFEED_SCHEMA', null) ?: "https://raw.githubusercontent.com/urbitassociates/urbit-merchant-feeds/master/schemas/inventory/{$version}/inventory.json",
            'content_language'   => Configuration::get('URBIT_INVENTORYFEED_CONTENT_LANGUAGE', null) ?: $lang,
            'attribute_language' => Configuration::get('URBIT_INVENTORYFEED_CONTENT_LANGUAGE', null) ?: $lang,
            'content_type'       => Configuration::get('URBIT_INVENTORYFEED_CONTENT_TYPE', null) ?: 'inventory',
            'target_country'     => Configuration::get('URBIT_INVENTORYFEED_TARGET_COUNTRY', null)
                ? explode(",", Configuration::get('URBIT_INVENTORYFEED_TARGET_COUNTRY', null)) : [$lang],
            'version'            => Configuration::get('URBIT_INVENTORYFEED_VERSION', null) ?: $version,
            'feed_format'        => [
                "encoding" => Configuration::get('URBIT_INVENTORYFEED_FEED_FORMAT', null) ?: "UTF-8",
            ],
            'schedule'           => [
                'interval' => $this->getIntervalText(),
            ],
        ];

        if ($created_at = Configuration::get('URBIT_INVENTORYFEED_CREATED_AT', null)) {
            $feedArray['created_at'] = $created_at;
        }

        if ($updated_at = Configuration::get('URBIT_INVENTORYFEED_UPDATED_AT', null)) {
            $feedArray['updated_at'] = $updated_at;
        }

        $feedArray['entities'] = $this->data;

        return $feedArray;
    }

    /**
     * return all combinations(variants) of product
     * @param $productId
     * @return array Combinations with quantity, price, attributes information
     */
    public function getCombinations($productId)
    {
        $context = Context::getContext();
        $productEntity = new Product($productId);

        $infoArray = [];

        //get all variants of product
        $combinations = $productEntity->getAttributeCombinations($context->language->id);

        foreach ($combinations as $combination) {
            if (!array_key_exists($combination['id_product_attribute'], $infoArray)) {
                $infoArray[$combination['id_product_attribute']] = [
                    'quantity'   => $combination['quantity'],
                    'reference'  => $combination['reference'],
                    'price'      => number_format((float)Product::getPriceStatic($productId, true, $combination['id_product_attribute']), 2, '.', ''),
                    'attributes' => [$combination['group_name'] => $combination['attribute_name']],
                ];
            } else {
                $infoArray[$combination['id_product_attribute']]['attributes'][$combination['group_name']] = $combination['attribute_name'];
            }
        }

        return $infoArray;
    }

    /**
     * Get categories filters from config
     * @return array|null
     */
    public static function getCategoryFilters()
    {
        $filterValue = Configuration::get('URBIT_INVENTORYFEED_FILTER_CATEGORIES', null);

        return $filterValue ? explode(',', $filterValue) : null;
    }

    /**
     * Get tags filters from config
     * @return array|null
     */
    public static function getTagsFilters()
    {
        $filterValue = Configuration::get('URBIT_INVENTORYFEED_TAGS_IDS', null);

        return $filterValue ? explode(',', $filterValue) : null;
    }

    /**
     * Get Product collection filtered by categories and tags
     * @param $id_lang
     * @param $start
     * @param $limit
     * @param $order_by
     * @param $order_way
     * @param bool $categoriesArray
     * @param bool $tagsArray
     * @param bool $only_active
     * @param Context|null $context
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
    public static function getProductsFilteredByCategoriesAndTags(
        $id_lang, $start, $limit, $order_by, $order_way,
        $categoriesArray = false, $tagsArray = false, $only_active = false, Context $context = null
    ) {
        if (!$context) {
            $context = Context::getContext();
        }

        $front = in_array($context->controller->controller_type, ['front', 'modulefront']);

        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
            die(Tools::displayError());
        }

        if (in_array($order_by, ['id_product', 'price', 'date_add', 'date_upd'])) {
            $order_by_prefix = 'p';
        } elseif ($order_by == 'name') {
            $order_by_prefix = 'pl';
        } elseif ($order_by == 'position') {
            $order_by_prefix = 'c';
        }

        if (strpos($order_by, '.') > 0) {
            $order_by = explode('.', $order_by);
            $order_by_prefix = $order_by[0];
            $order_by = $order_by[1];
        }

        $sql = 'SELECT p.*, product_shop.*, pl.* , m.`name` AS manufacturer_name, s.`name` AS supplier_name
				FROM `' . _DB_PREFIX_ . 'product` p
				' . Shop::addSqlAssociation('product', 'p') . '
				LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
				LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
				LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON (s.`id_supplier` = p.`id_supplier`)' .
            ($categoriesArray ? 'LEFT JOIN `' . _DB_PREFIX_ . 'category_product` c ON (c.`id_product` = p.`id_product`)' : '') . ' ' .
            ($tagsArray ? 'LEFT JOIN `' . _DB_PREFIX_ . 'product_tag` pt ON (pt.`id_product` = p.`id_product`)' : '') . ' ' .
            ($tagsArray ? 'LEFT JOIN `' . _DB_PREFIX_ . 'tag` t ON (pt.`id_tag` = t.`id_tag`)' : '') . '
				WHERE pl.`id_lang` = ' . (int)$id_lang .
            ($categoriesArray ? ' AND c.`id_category` in (' . implode(',', $categoriesArray) . ')' : '') .
            ($tagsArray ? ' AND t.`name` in ("' . implode(',', $tagsArray) . '")' : '') .
            ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '') .
            ($only_active ? ' AND product_shop.`active` = 1' : '') . '
				ORDER BY ' . (isset($order_by_prefix) ? pSQL($order_by_prefix) . '.' : '') . '`' . pSQL($order_by) . '` ' . pSQL($order_way) .
            ($limit > 0 ? ' LIMIT ' . (int)$start . ',' . (int)$limit : '')
        ;

        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if ($order_by == 'price') {
            Tools::orderbyPrice($rq, $order_way);
        }

        foreach ($rq as &$row) {
            $row = Product::getTaxesInformations($row);
        }

        return ($rq);
    }

    /**
     * Get schedule interval value
     * @return string
     */
    public function getIntervalText()
    {
        $cacheDuration = Configuration::get('URBIT_INVENTORYFEED_CACHE_DURATION', null);

        if (!$cacheDuration) {
            return static::SCHEDULE_INTERVAL_HOURLY;
        }

        foreach ([
            self::SCHEDULE_INTERVAL_5MIN_TIME   => self::SCHEDULE_INTERVAL_5MIN,
            self::SCHEDULE_INTERVAL_15MIN_TIME  => self::SCHEDULE_INTERVAL_15MIN,
            self::SCHEDULE_INTERVAL_30MIN_TIME  => self::SCHEDULE_INTERVAL_30MIN,
            self::SCHEDULE_INTERVAL_45MIN_TIME  => self::SCHEDULE_INTERVAL_45MIN,
            self::SCHEDULE_INTERVAL_HOURLY_TIME => self::SCHEDULE_INTERVAL_HOURLY,
        ] as $time => $val) {
            if ($cacheDuration <= $time) {
                return $val;
            }
        }

        return static::SCHEDULE_INTERVAL_HOURLY;
    }

    /**
     * @return string
     */
    public function getFeedVersion()
    {
        return static::FEED_VERSION;
    }
}
