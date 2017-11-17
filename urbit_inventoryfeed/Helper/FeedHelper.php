<?php

include_once(_PS_MODULE_DIR_ . 'urbit_inventoryfeed' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'Feed.php');

/**
 * Class FeedHelper
 */
class Urbit_Inventoryfeed_FeedHelper
{
    /**
     * @param $collection
     * @return string
     */
    public function generateFeed($collection)
    {
        $feed = new Urbit_Inventoryfeed_Feed($collection);

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
        $cacheDuration = Configuration::get('URBIT_INVENTORYFEED_CACHE_DURATION', null);

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
        return file_get_contents($this->getCacheFilePath());
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
