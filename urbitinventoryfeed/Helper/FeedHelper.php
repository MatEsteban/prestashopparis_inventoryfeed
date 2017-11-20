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
 
include_once(_PS_MODULE_DIR_ . 'urbitinventoryfeed' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'Feed.php');

/**
 * Class FeedHelper
 */
class UrbitInventoryfeedFeedHelper
{
    /**
     * @param $collection
     * @return string
     */
    public function generateFeed($collection)
    {
        $feed = new UrbitInventoryfeedFeed($collection);

        $json = json_encode($feed->toArray(), JSON_PRETTY_PRINT);

        $this->setDataJson($json);

        return $json;
    }

    /**
     * Check cache on expire. True if it still valid
     * @return bool
     */
    public function checkCache()
    {
        $cacheDuration = Configuration::get('URBITINVENTORYFEED_CACHE_DURATION', null);

        $filePath = $this->getCacheFilePath();

        return file_exists($filePath) && (time() - filemtime($filePath)) < ($cacheDuration * 60);
    }

    /**
     * Get feed cache file path
     * @return string
     */
    public function getCacheFilePath()
    {
        return dirname(__FILE__).'/../Json/inventoryfeed.json';
    }

    /**
     * Get data from cache file
     * @return mixed
     */
    public function getData()
    {
        return json_decode($this->getDataJson(), true);
    }

    /**
     * Get plain json from cache file
     * @return string
     */
    public function getDataJson()
    {
        return Tools::file_get_contents($this->getCacheFilePath());
    }

    /**
     * Set data to cache file
     * @param mixed $data
     * @return bool|int
     */
    public function setData($data)
    {
        return $this->setDataJson(json_encode($data));
    }

    /**
     * Set json data to cache file
     * @param string $json
     * @return bool|string
     */
    public function setDataJson($json)
    {
        return file_put_contents($this->getCacheFilePath(), $json);
    }
}
