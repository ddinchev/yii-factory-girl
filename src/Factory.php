<?php

namespace YiiFactoryGirl;

class Factory extends \CApplicationComponent
{
    /**
     * @var string the name of the initialization script that would be executed before the whole test set runs.
     * Defaults to 'init.php'. If the script does not exist, every table with a fixture file will be reset.
     */
    public $initScript = 'init.php';
    /**
     * @var string the suffix for fixture initialization scripts.
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
     * so that fixture data can be populated into the database without causing problem.
     */
    public $schemas = array('');

    /**
     * @var \CDbConnection
     */
    protected $_db;

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
     * Returns the database connection used to load fixtures.
     * @throws \CException if {@link connectionID} application component is invalid
     * @return \CDbConnection the database connection
     */
    public function getDbConnection()
    {
        if ($this->_db === null) {
            $this->_db = \Yii::app()->getComponent($this->connectionID);
            if (!$this->_db instanceof \CDbConnection) {
                throw new \CException(Yii::t('yii', 'CDbTestFixture.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.',
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
            foreach ($this->getFactories() as $tableName => $factoryPath) {
                $this->resetTable($tableName);
                // $this->loadFactory($tableName);
            }
        }
        $this->checkIntegrity(true);
    }

    /**
     * Resets the table to the state that it contains no fixture data.
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
    public function build($class, array $args, $alias = null)
    {

    }

    /**
     * Returns \CActiveRecord instance that is saved.
     * @param $class
     * @param array $args
     * @param null $alias
     * @return \CActiveRecord
     */
    public function create($class, array $args, $alias = null)
    {

    }

    /**
     * Returns array of attributes that can be set to a \CActiveRecord model
     * @param $class
     * @param $args
     * @param $alias
     * @return array
     */
    public function attributes($class, $args, $alias)
    {

    }

    /**
     * Returns the class attributes used to construct the model.
     * @return array
     */
    protected function getFactory()
    {

    }
}