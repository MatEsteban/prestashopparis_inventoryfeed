<?php

abstract class Urbit_Inventoryfeed_Fields_FieldAbstract
{
    /**
     * @return Module
     */
    public static function getModule()
    {
        return Urbit_inventoryfeed::getInstance();
    }
}
