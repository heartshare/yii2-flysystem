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
    /** Array of filesystems configs. Config consists of:
     *  - adapter: \Closure that returns an object of a class that implements \League\Flysystem\AdapterInterface
     *  - cache: \Closure that returns an object of a class that implements \League\Flysystem\CacheInterface
     *  - options: Filesystem options that will be passed to __construct. @see https://github.com/thephpleague/flysystem
     *  - plugins: array of plugins classes
     * @var array
     */
    public $filesystems;
    public $filesystemClass = 'League\Flysystem\Filesystem';

    private $_loadedSystems = [];
    private $_mountManager;

    public function init()
    {
        if($this->adapters === null){
            throw new InvalidConfigException('No adapters configured');
        }
    }

    /**
     * @param $name
     * @return \League\Flysystem\Filesystem
     * @throws InvalidConfigException
     */
    public function getFilesystem($name)
    {
        if(!isset($this->_loadedSystems[$name])){
            $config = ArrayHelper::getValue($this->filesystems, $name);
            if($config === null){
                throw new InvalidParamException('Unknown filesystem');
            }
            if(!is_array($config)){
                throw new InvalidParamException('Invalid filesystem config');
            }
            $class = ArrayHelper::getValue($config, 'class', $this->filesystemClass);
            $adapter = isset($config['adapter']) && is_a($config['adapter'], '\Closure')? call_user_func($config['adapter'], $this) : null;
            $cache = isset($config['cache']) && is_a($config['cache'], '\Closure')? call_user_func($config['cache'], $this) : null;
            $options = ArrayHelper::getValue($config, 'options');
            $plugins = ArrayHelper::getValue($config, 'plugins');
            $this->_loadedSystems[$name] = \Yii::createObject($class, [
                $adapter,
                $cache,
                $options
            ]);
            if($plugins && is_array($plugins)){
                foreach($plugins as $class){
                    $this->_loadedSystems[$name]->addPlugin(new $class);
                }
            }

        }
        return $this->_loadedSystems[$name];
    }

    /**
     * @return null|\League\Flysystem\MountManager
     * @throws InvalidConfigException
     */
    public function getMountManager()
    {
        if($this->_mountManager === null){
            $mountFilesystems = [];
            foreach(array_keys($this->filesystems) as $name){
                $mountFilesystems[$name] = $this->getFilesystem($name);
            }
            $this->_mountManager = \Yii::createObject('\League\Flysystem\MountManager', $mountFilesystems);
        }
        return $this->_mountManager;
    }
}