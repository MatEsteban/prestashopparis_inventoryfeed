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
     * Write feed to file and return feed from this file
     * @return string
     */
    public function getProductsJson()
    {
        $feedHelper = new UrbitInventoryfeedFeedHelper();

        if (!$feedHelper->checkCache()) {
            $context = Context::getContext();
            $categoryFilters = UrbitInventoryfeedFeed::getCategoryFilters();
            $tagFilters = UrbitInventoryfeedFeed::getTagsFilters();

            $products = UrbitInventoryfeedFeed::getProductsFilteredByCategoriesAndTags($context->language->id, 0, 0, 'id_product', 'DESC', $categoryFilters, $tagFilters);
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
        $feedHelper = new UrbitInventoryfeedFeedHelper();

        if (!$feedHelper->checkCache()) {
            $context = Context::getContext();
            $categoryFilters = UrbitInventoryfeedFeed::getCategoryFilters();
            $tagFilters = UrbitInventoryfeedFeed::getTagsFilters();

            $products = UrbitInventoryfeedFeed::getProductsFilteredByCategoriesAndTags($context->language->id, 0, 0, 'id_product', 'DESC', $categoryFilters, $tagFilters);
            $uniqueProducts = array_unique($products, SORT_REGULAR);
            $feedHelper->generateFeed($uniqueProducts);
        }
    }
}
