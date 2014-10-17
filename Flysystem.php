<?php
/**
 * Author: Eugine Terentev <eugine@terentev.net>
 */
namespace trntv\yii\flysystem;

use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;

class Flysystem extends Component
{
    public $adapters;

    private $_loadedSystems = [];

    public function init()
    {
        if($this->adapters === null){
            throw new InvalidConfigException('No adapters configured');
        }
    }

    public function getFilesystem($name)
    {
        if(!isset($this->_loadedSystems[$name])){
            $config = ArrayHelper::getValue($this->adapters, $name);
            if(!$config === null){
                throw new InvalidParamException('Unknown filesystem');
            }
            // to be continued...
        }
    }
}