<?php

namespace YiiFactoryGirl;

/**
 * Class FactoryData
 * Used to represent all properties of a file under the factory base path
 * @package YiiFactoryGirl
 */
class FactoryData extends \CComponent
{
    public $tableName;
    public $className;
    public $attributes;
    public $aliases;

    /**
     * @param string $className
     * @param array $attributes default attributes array
     * @param array $aliases array of arrays where the key is alias name and the properties are
     * @throws FactoryException
     */
    public function __construct($className, array $attributes = array(), array $aliases = array()) {
        $this->className = $className;
        try {
            $this->tableName = $className::model()->tableName();
        } catch (\CException $e) {
            throw new FactoryException(\Yii::t(Factory::LOG_CATEGORY, 'Unable to call {class}::model()->tableName().', array(
                '{class}' => $className
            )));
        }
        $this->attributes = $attributes;
        $this->aliases = $aliases;
    }

    public function getAttributes($args = array(), $alias = null)
    {
        $attributes = $this->attributes;
        if ($alias !== null) {
            if (!isset($this->aliases[$alias])) {
                throw new FactoryException(\Yii::t(Factory::LOG_CATEGORY, 'Alias "{alias}" not found for class "{class}"', array(
                    '{alias}' => $alias,
                    '{class}' => $this->className,
                )));
            }
            $attributes = array_merge($attributes, $this->aliases[$alias]);
        }
        $attributes = array_merge($attributes, $args);
        foreach ($attributes as $key => $value) {
            $attributes[$key] = Sequence::get($value);
        }
        return $attributes;
    }

    public static function fromFile($path, $suffix) {
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        $fileName = end($parts);
        if (!substr($fileName, -(strlen($suffix)) === $suffix || !is_file($path))) {
            throw new FactoryException(\Yii::t(Factory::LOG_CATEGORY, '"{file}" does not seem to be factory data file.', array(
                '{file}' => $path
            )));
        }

        // determine class name
        $className = str_replace($suffix, '', $fileName);

        // load actual config
        $config = require $path;
        if (!is_array($config) || !isset($config['attributes']) || !is_array($config['attributes'])) {
            throw new FactoryException(\Yii::t(Factory::LOG_CATEGORY, '"{path}" expected to return config array with "attributes" inside.', array(
                '{path}' => $path,
            )));
        }

        // load attributes and assume the rest of the config is aliases
        $attributes = $config['attributes'];
        unset($config['attributes']);
        $aliases = $config;
        return new self($className, $attributes, $aliases);
    }
}