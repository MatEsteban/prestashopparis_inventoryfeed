<?php

include_once(_PS_MODULE_DIR_ . 'urbit_inventoryfeed' . DIRECTORY_SEPARATOR . 'Helper' . DIRECTORY_SEPARATOR . 'FeedHelper.php');
include_once(_PS_MODULE_DIR_ . 'urbit_inventoryfeed' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'Feed.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Urbit_InventoryfeedFeedModuleFrontController
 */
class Urbit_InventoryfeedFeedModuleFrontController extends ModuleFrontController
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

        header('Content-Type: application/json');

        if (version_compare(_PS_VERSION_, "1.7", "<")) {
            $this->setTemplate('feedtemp.tpl');
        } else {
            $this->setTemplate('module:urbit_inventoryfeed/views/templates/front/feedtemp.tpl');
        }

        if (isset($_GET['cron'])) {
            $this->generateByCron();
        } else {
            echo $this->getProductsJson();
        }

        exit;
    }

    /**
     * Write feed to file and return feed from this file
     * @return string
     */
    public function getProductsJson()
    {
        $feedHelper = new Urbit_Inventoryfeed_FeedHelper();

        if (!$feedHelper->checkCache()) {
            $context = Context::getContext();
            $categoryFilters = Urbit_Inventoryfeed_Feed::getCategoryFilters();
            $tagFilters = Urbit_Inventoryfeed_Feed::getTagsFilters();

            $products = Urbit_Inventoryfeed_Feed::getProductsFilteredByCategoriesAndTags($context->language->id, 0, 0, 'id_product', 'DESC', $categoryFilters, $tagFilters);
            $uniqueProducts = array_unique($products, SORT_REGULAR);
            $feedHelper->generateFeed($uniqueProducts);
        }

        return $feedHelper->getDataJson();
    }

    /**
     * Write feed to file
     */
    public function generateByCron()
    {
        $feedHelper = new Urbit_Inventoryfeed_FeedHelper();

        if (!$feedHelper->checkCache()) {
            $context = Context::getContext();
            $categoryFilters = Urbit_Inventoryfeed_Feed::getCategoryFilters();
            $tagFilters = Urbit_Inventoryfeed_Feed::getTagsFilters();

            $products = Urbit_Inventoryfeed_Feed::getProductsFilteredByCategoriesAndTags($context->language->id, 0, 0, 'id_product', 'DESC', $categoryFilters, $tagFilters);
            $uniqueProducts = array_unique($products, SORT_REGULAR);
            $feedHelper->generateFeed($uniqueProducts);
        }
    }
}
