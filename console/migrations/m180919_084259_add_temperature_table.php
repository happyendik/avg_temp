<?php

use yii\db\Migration;

/**
 * Class m180919_084259_add_temperature_table
 */
class m180919_084259_add_temperature_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('temperature', [
            'id' => $this->primaryKey(),
            'time' => $this->timestamp()->notNull(),
            'temperature' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('temperature');
    }
}
