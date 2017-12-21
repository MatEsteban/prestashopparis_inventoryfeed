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

include_once(_PS_MODULE_DIR_ . 'urbitinventoryfeed' . DIRECTORY_SEPARATOR . 'Helper' . DIRECTORY_SEPARATOR . 'FeedHelper.php');
include_once(_PS_MODULE_DIR_ . 'urbitinventoryfeed' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'Feed.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class UrbitInventoryfeedFeedModuleFrontController
 */
class UrbitInventoryfeedFeedModuleFrontController extends ModuleFrontController
{
    /**
     * @var
     */
    protected $_products;

    /**
     *  init function
     */
    public function initContent()
    {
        parent::initContent();

        if ($this->ajax) {
            $result = Tools::getValue('configValues') ?
                $this->getAjaxOptionsForResultFilter() : $this->getAjaxOptionsForProductFilter();

            die(Tools::jsonEncode($result));
        }

        $token = Tools::getValue('token');

        if (version_compare(_PS_VERSION_, '1.5', '>') && Shop::isFeatureActive()) {
            $id_shop = $this->context->shop->id;
            $tokenInConfig = Configuration::get('URBITINVENTORYFEED_FEED_TOKEN', null, null, $id_shop);

            $allTokens_raw = $this->getAllTokensOfShop();

            $allTokens = array();

            foreach ($allTokens_raw as $allTokens_subraw) {
                $allTokens[$allTokens_subraw['token']] = $allTokens_subraw['token'];
            }
        } else {
            $tokenInConfig = Configuration::get('URBITINVENTORYFEED_FEED_TOKEN');
            $allTokens[$tokenInConfig]=$tokenInConfig;
        }

        if ($token == '' || ($token != $tokenInConfig && !in_array($token, $allTokens))) {
            die("<?xml version='1.0' encoding='utf-8'?><error>Invalid Token</error>");
        }

        header('Content-Type: application/json');

        if (version_compare(_PS_VERSION_, "1.7", "<")) {
            $this->setTemplate('feedtemp.tpl');
        } else {
            $this->setTemplate('module:urbitinventoryfeed/views/templates/front/feedtemp.tpl');
        }

        if (Tools::getIsset(Tools::getValue(array('cron')))) {
            $this->generateByCron();
        } else {
            echo $this->getProductsJson();
        }

        exit;
    }

    /**
     * Get options for dynamic left selectbox
     * Used by AJAX
     * @return array
     */
    protected function getAjaxOptionsForProductFilter()
    {
        $categoryFilters = Tools::getValue('categoriesFromAjax');
        $tagFilters = Tools::getValue('tagsFromAjax');
        $minimalStockFilter = Tools::getValue('minimalStockFromAjax');

        $optionsForProductMultiSelect = array();

        $products = $this->getProductsFilteredByStandardFilters($categoryFilters, $tagFilters, $minimalStockFilter);

        $uniqueProducts = array_unique($products, SORT_REGULAR);

        foreach ($uniqueProducts as $product) {
            $optionsForProductMultiSelect[] = array('id' => $product['id_product'], 'name' => $product['id_product'] . ' : ' . $product['name']);
        }

        return $optionsForProductMultiSelect;
    }

    /**
     * Get options for result right selectbox
     * Used by AJAX
     * @return array
     */
    protected function getAjaxOptionsForResultFilter()
    {
        $resultFilter = UrbitInventoryfeedFeed::getProductFilters();

        $optionsForProductMultiSelect = array();

        if ($resultFilter) {
            $products = $this->getProductsFilteredByResultFilter($resultFilter);

            $uniqueProducts = array_unique($products, SORT_REGULAR);

            foreach ($uniqueProducts as $product) {
                $optionsForProductMultiSelect[] = array('id' => $product['id_product'], 'name' => $product['id_product'] . ' : ' . $product['name']);
            }
        }

        return $optionsForProductMultiSelect;
    }

    /**
     * Write feed to file and return feed from this file
     * @return string
     */
    public function getProductsJson()
    {
        $feedHelper = new UrbitInventoryfeedFeedHelper();

        if (!$feedHelper->checkCache()) {
            $feedHelper->generateFeed($this->getFilteredProductCollection());
        }

        return $feedHelper->getDataJson();
    }

    /**
     * Write feed to file
     */
    public function generateByCron()
    {
        $feedHelper = new UrbitInventoryfeedFeedHelper();

        if (!$feedHelper->checkCache()) {
            $feedHelper->generateFeed($this->getFilteredProductCollection());
        }
    }

    /**
     * Return product collection (without duplicates) filtered by standard filters from config (category, tag, minimal stock) OR result filter (if result filter != NULL)
     * @return array
     */
    protected function getFilteredProductCollection()
    {
        $categoryFilters = UrbitInventoryfeedFeed::getCategoryFilters()[0] != 'none' ? UrbitInventoryfeedFeed::getCategoryFilters() : null;
        $tagFilters = UrbitInventoryfeedFeed::getTagsFilters()[0] != 'none' ? UrbitInventoryfeedFeed::getTagsFilters() : null;
        $minimalStockFilter = UrbitInventoryfeedFeed::getMinimalStockFilter();
        $resultFilter = UrbitInventoryfeedFeed::getProductFilters()[0] != 'none' ?  UrbitInventoryfeedFeed::getProductFilters() : null;

        $products = ($resultFilter) ?
            $this->getProductsFilteredByResultFilter($resultFilter) :
            $this->getProductsFilteredByStandardFilters($categoryFilters, $tagFilters, $minimalStockFilter);

        return array_unique($products, SORT_REGULAR);
    }

    /**
     * Helper function
     * Get product collection filtered by result filter
     * @param $resultFilterValue
     * @return array
     */
    protected function getProductsFilteredByResultFilter($resultFilterValue)
    {
        return UrbitInventoryfeedFeed::getFilteredProducts(
            Context::getContext()->language->id,
            0,
            0,
            'id_product',
            'ASC',
            false,
            false,
            false,
            $resultFilterValue
        );
    }

    /**
     * Helper function
     * Get product collection filtered by standard filters
     * @param $categoryFilterValue
     * @param $tagFilterValue
     * @param $minimalStockFilterValue
     * @return array
     */
    protected function getProductsFilteredByStandardFilters($categoryFilterValue, $tagFilterValue, $minimalStockFilterValue)
    {
        return $products = UrbitInventoryfeedFeed::getFilteredProducts(
            Context::getContext()->language->id,
            0,
            0,
            'id_product',
            'ASC',
            $categoryFilterValue,
            $tagFilterValue,
            $minimalStockFilterValue
        );
    }

    /**
     * Gets all token of a given shop
     * @param int $id_shop
     * @return array
     */
    public function getAllTokensOfShop()
    {
        $id_shop = $this->context->shop->id;
        $id_shop_group = (int) $this->context->shop->id_shop_group;
        $res = array();
        $tokenGeneral = $this->getTokenValue($id_shop);

        if ($tokenGeneral) {
            $res[] = array(
                'id_shop' => $id_shop,
                'id_shop_group' => null,
                'token' => $tokenGeneral,
                'id_lang' => Configuration::get('PS_LANG_DEFAULT'),
                'id_currency' => false
            );
        }

        $shopLanguages = Language::getLanguages(true, $id_shop);
        $shopCurrencies = Currency::getCurrenciesByIdShop($id_shop);

        foreach ($shopLanguages as $currentLang) {
            $idLang = $currentLang['id_lang'];

            foreach ($shopCurrencies as $currentCurrency) {
                $idCurrency = $currentCurrency['id_currency'];
                $token = $this->getTokenValue($id_shop, $id_shop_group, $idCurrency, $idLang);

                if ($token) {
                    $res[] = array(
                        'id_shop' => $id_shop,
                        'id_shop_group' => $id_shop_group,
                        'token' => $token,
                        'id_lang' => $idLang,
                        'id_currency' => $idCurrency
                    );
                }
            }
        }

        return $res;
    }

    /**
     * Get the configured token
     * @param int $id_shop the shop context
     * @param int $id_currency (optionnal) the currency
     * @param int $id_lang (optionnal) the lang
     * @return string
     */
    public function getTokenValue($id_shop, $id_shop_group = null, $id_currency = false, $id_lang = false)
    {
        $key = 'URBITINVENTORYFEED_FEED_TOKEN';

        if ($id_currency && $id_lang) {
            $key .= '_'.$id_currency.'_'.$id_lang;
        }

        if ($id_shop) {
            return Configuration::get($key, null, $id_shop_group, $id_shop);
        } else {
            return Configuration::get($key);
        }
    }
}
