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
     * @var string the base path containing all fixtures. Defaults to null, meaning
     * the path 'protected/tests/fixtures'.
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
    public $schemas=array('');

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
            $this->basePath = \Yii::getPathOfAlias('application.tests.fixtures');
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
     * Prepares the fixtures for the whole test.
     * This method is invoked in {@link init}. It executes the database init script
     * if it exists. Otherwise, it will load all available fixtures.
     */
    public function prepare()
    {
        $initFile = $this->basePath . DIRECTORY_SEPARATOR . $this->initScript;

        $this->checkIntegrity(false);

        if (is_file($initFile)) {
            require($initFile);
        } else {
            foreach ($this->getFixtures() as $tableName => $fixturePath) {
                $this->resetTable($tableName);
                $this->loadFixture($tableName);
            }
        }
        $this->checkIntegrity(true);
    }

    /**
     * Enables or disables database integrity check.
     * This method may be used to temporarily turn off foreign constraints check.
     * @param boolean $check whether to enable database integrity check
     */
    public function checkIntegrity($check)
    {
        foreach($this->schemas as $schema) {
            $this->getDbConnection()->getSchema()->checkIntegrity($check,$schema);
        }
    }
}