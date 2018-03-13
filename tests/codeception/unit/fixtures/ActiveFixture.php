<?php

namespace tests\codeception\unit\fixtures;

/**
 * Class ActiveFixture
 * @package tests\codeception\unit\fixtures
 */
class ActiveFixture extends \yii\test\ActiveFixture
{
    /**
     * @var array list of database schemas that the test tables may reside in. Defaults to
     * `['']`, meaning using the default schema (an empty string refers to the
     * default schema). This property is mainly used when turning on and off integrity checks
     * so that fixture data can be populated into the database without causing problem.
     */
    public $schemas = [''];


    /**
     * @inheritdoc
     */
    public function beforeLoad()
    {
        $this->checkIntegrity(false);
    }

    /**
     * @inheritdoc
     */
    public function afterLoad()
    {
        $this->checkIntegrity(true);
    }

    /**
     * @inheritdoc
     */
    public function beforeUnload()
    {
        $this->checkIntegrity(false);
    }

    /**
     * @inheritdoc
     */
    public function afterUnload()
    {
        $this->checkIntegrity(true);
    }

    /**
     * Toggles the DB integrity check.
     * @param bool $check whether to turn on or off the integrity check.
     */
    public function checkIntegrity($check)
    {
        foreach ($this->schemas as $schema) {
            $this->db->createCommand()->checkIntegrity($check, $schema)->execute();
        }
    }
}
