<?php

namespace YiiFactoryGirl;

class Factory extends \CApplicationComponent
{
    /**
     * @var string the name of the initialization script that would be executed before the whole test set runs.
     * Defaults to 'init.php'. If the script does not exist, every table with a factory file will be reset.
     */
    public $initScript = 'init.php';
    /**
     * @var string the suffix for factory initialization scripts.
     * If a table is associated with such a script whose name is TableName suffixed this property value,
     * then the script will be executed each time before the table is reset.
     */
    public $initScriptSuffix = '.init.php';
    /**
     * @var string the base path containing all factories. Defaults to null, meaning
     * the path 'protected/tests/factories'.
     */
    public $basePath;
    /**
     * @var string the ID of the database connection. Defaults to 'db'.
     * Note, data in this database may be deleted or modified during testing.
     * Make sure you have a backup database.
     */
    public $connectionID = 'db';
    /**
     * @var array list of database schemas that the test tables may reside in. Defaults to
     * array(''), meaning using the default schema (an empty string refers to the
     * default schema). This property is mainly used when turning on and off integrity checks
     * so that factory data can be populated into the database without causing problem.
     */
    public $schemas = array('');
    /**
     * @var string the suffix for the factory files where the file name is constructed
     * from the \CActiveRecord class name and the suffix. Eg. by default "UsersFactory" would
     * expect that you create a factory for the "Users" \CActiveRecord model
     */
    public $factoryFileSuffix = 'Factory';

    /**
     * @var \CDbConnection
     */
    protected $_db;
    /**
     * @var FactoryData[] (class name => FactoryData)
     */
    protected $_factoryData;

    /**
     * Initializes this application component.
     */
    public function init()
    {
        parent::init();
        if ($this->basePath === null) {
            $this->basePath = \Yii::getPathOfAlias('application.tests.factories');
        }
        $this->prepare();
    }

    /**
     * Returns the database connection used to load factories.
     * @throws \CException if {@link connectionID} application component is invalid
     * @return \CDbConnection the database connection
     */
    public function getDbConnection()
    {
        if ($this->_db === null) {
            $this->_db = \Yii::app()->getComponent($this->connectionID);
            if (!$this->_db instanceof \CDbConnection) {
                throw new \CException(\Yii::t('yii-factory-girl', '\YiiFactoryGirl\Factory.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.',
                    array('{id}' => $this->connectionID)));
            }
        }
        return $this->_db;
    }

    /**
     * Prepares the factories for the whole test.
     * This method is invoked in {@link init}. It executes the database init script
     * if it exists. Otherwise, it will load all available factories.
     */
    public function prepare()
    {
        $initFile = $this->basePath . DIRECTORY_SEPARATOR . $this->initScript;

        $this->checkIntegrity(false);

        if (is_file($initFile)) {
            require($initFile);
        } else {
            foreach ($this->loadFactoryData() as $className => $factoryData) {
                $this->resetTable($factoryData->tableName);
            }
        }
        $this->checkIntegrity(true);
    }

    /**
     * Resets the table to the state that it contains data.
     * If there is an init script named "tests/factories/TableName.init.php",
     * the script will be executed.
     * Otherwise, {@link truncateTable} will be invoked to delete all rows in the table
     * and reset primary key sequence, if any.
     * @param string $tableName the table name
     */
    public function resetTable($tableName)
    {
        $initFile = $this->basePath . DIRECTORY_SEPARATOR . $tableName . $this->initScriptSuffix;
        if (is_file($initFile)) {
            require($initFile);
        } else {
            $this->truncateTable($tableName);
        }
    }

    /**
     * Returns the information of the available factories.
     * This method will search for all PHP files under {@link basePath}.
     * If a file's name is the same as a table name, it is considered to be the factories data for that table.
     * @return FactoryData[] the information of the available factories (class name => FactoryData)
     * @throw FactoryException if there is a misbehaving file in the factory data files path
     */
    protected function loadFactoryData()
    {
        if ($this->_factoryData === null) {
            $this->_factoryData = array();
            $folder = opendir($this->basePath);
            $suffixLen = strlen($this->initScriptSuffix);
            while ($file = readdir($folder)) {
                if ($file === '.' || $file === '..' || $file === $this->initScript) {
                    continue;
                }
                $path = $this->basePath . DIRECTORY_SEPARATOR . $file;
                if (substr($file, -4) === '.php' && is_file($path) && substr($file, -$suffixLen) !== $this->initScriptSuffix) {
                    $data = new FactoryData($this, $path);
                    $this->_factoryData[$data->className] = $data;

                }
            }
            closedir($folder);
        }
        return $this->_factoryData;
    }

    /**
     * Enables or disables database integrity check.
     * This method may be used to temporarily turn off foreign constraints check.
     * @param boolean $check whether to enable database integrity check
     */
    public function checkIntegrity($check)
    {
        foreach ($this->schemas as $schema) {
            $this->getDbConnection()->getSchema()->checkIntegrity($check, $schema);
        }
    }

    /**
     * Removes all rows from the specified table and resets its primary key sequence, if any.
     * You may need to call {@link checkIntegrity} to turn off integrity check temporarily
     * before you call this method.
     * @param string $tableName the table name
     * @throws \CException if given table does not exist
     */
    public function truncateTable($tableName)
    {
        $db = $this->getDbConnection();
        $schema = $db->getSchema();
        if (($table = $schema->getTable($tableName)) !== null) {
            $db->createCommand('DELETE FROM ' . $table->rawName)->execute();
            $schema->resetSequence($table, 1);
        } else {
            throw new \CException("Table '$tableName' does not exist.");
        }
    }

    /**
     * Truncates all tables in the specified schema.
     * You may need to call {@link checkIntegrity} to turn off integrity check temporarily
     * before you call this method.
     * @param string $schema the schema name. Defaults to empty string, meaning the default database schema.
     * @see truncateTable
     */
    public function truncateTables($schema = '')
    {
        $tableNames = $this->getDbConnection()->getSchema()->getTableNames($schema);
        foreach ($tableNames as $tableName) {
            $this->truncateTable($tableName);
        }
    }

    /**
     * Returns \CActiveRecord instance that is not yet saved.
     * @param $class
     * @param array $args
     * @param null $alias
     * @return \CActiveRecord
     */
    public function build($class, array $args = array(), $alias = null)
    {
        $obj = new $class;
        $reflection = new \ReflectionObject($obj);
        $factory = $this->getFactoryData($class);
        $attributes = $factory->getAttributes($args, $alias);
        foreach ($attributes as $key => $value) {
            $property = $reflection->getProperty($key);
            $property->setAccessible(true);
            $property->setValue($value);
        }
        return $obj;
    }

    /**
     * Returns \CActiveRecord instance that is saved.
     * @param $class
     * @param array $args
     * @param null $alias
     * @return \CActiveRecord
     */
    public function create($class, array $args = array(), $alias = null)
    {
        $obj = $this->build($class, $args, $alias);

        $schema = $this->getDbConnection()->getSchema();
        $builder = $schema->getCommandBuilder();
        $table = $schema->getTable($obj->tableName());

        // make sure it gets inserted
        $this->checkIntegrity(false);

        // attributes to insert
        $attributes = $obj->getAttributes(false);
        $builder->createInsertCommand($table, $attributes)->execute();

        $primaryKey = $table->primaryKey;
        if ($table->sequenceName !== null) {
            if (is_string($primaryKey) && !isset($attributes[$primaryKey])) {
                $obj->{$primaryKey} = $builder->getLastInsertID($table);
            } elseif(is_array($primaryKey)) {
                foreach($primaryKey as $pk) {
                    if (!isset($attributes[$pk])) {
                        $obj->{$pk} = $builder->getLastInsertID($table);
                        break;
                    }
                }
            }
        }

        // re-enable foreign key check state
        $this->checkIntegrity(true);
        return $obj;
    }

    /**
     * Returns array of attributes that can be set to a \CActiveRecord model
     * @param $class
     * @param $args
     * @param $alias
     * @return array
     */
    public function attributes($class, array $args = array(), $alias)
    {
        return $this->getFactoryData($class)->getAttributes($args, $alias);
    }

    /**
     * @param string $class
     * @return FactoryData|false
     * @throws FactoryException
     */
    public function getFactoryData($class) {
        if (!isset($this->_factoryData[$class])) {
            throw new FactoryException(\Yii::t('yii-factory-girl', 'There is no {class} class loaded.', array(
                '{class}' => $class,
            )));
        }
        return $this->_factoryData[$class];
    }
}

/**
 * Class FactoryData
 * Used to represent all properties of a file under the factory base path
 * @package YiiFactoryGirl
 */
class FactoryData extends \CComponent
{
    public $fileName;
    public $tableName;
    public $className;

    protected $_attributes;
    protected $_aliases;

    public function __construct(Factory $factory, $path)
    {
        // determine just the filename
        $suffix = "$factory->factoryFileSuffix.php";
        $this->fileName = end(explode(DIRECTORY_SEPARATOR, $path));
        if (!substr($this->fileName, -(strlen($suffix)) === $suffix || !is_file($path))) {
            throw new \CException(\Yii::t('yii-factory-girl', '"{file}" does not seem to be factory data file.', array(
                '{file}' => $path
            )));
        }

        // determine class and table names
        $className = str_replace($suffix, '', $this->fileName);
        try {
            $this->tableName = $className::model()->tableName();
            $this->className = $className;
        } catch (\CException $e) {
            throw new FactoryException(\Yii::t('yii-factory-girl', 'Unable to call {class}::model()->tableName().', array(
                '{class}' => $className
            )));
        }

        // load actual config
        $config = require $path;
        if (!is_array($config) || !isset($config['attributes']) || !is_array($config['attributes'])) {
            throw new FactoryException(\Yii::t('yii-factory-girl', '"{path}" expected to return config array with "attributes" inside.', array(
                '{path}' => $path,
            )));
        }

        // load attributes and assume the rest of the config is aliases
        $this->attributes = $config['attributes'];
        unset($config['attributes']);
        $this->aliases = $config;
    }

    public function getAttributes($args = array(), $alias = null)
    {
        $attributes = array();
        if ($alias !== null) {
            $attributes = array_merge($this->_attributes, $this->_aliases[$alias]);
        }
        $attributes = array_merge($attributes, $args);
        foreach ($attributes as $key => $value) {
            $attributes[$key] = Sequence::get($value);
        }
        return $attributes;
    }

}

/**
 * Class FactoryException
 * Extension specific exceptions.
 * @package YiiFactoryGirl
 */
class FactoryException extends \CException { }