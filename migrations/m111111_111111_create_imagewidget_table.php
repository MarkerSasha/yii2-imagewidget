<?php

use yii\db\Migration;

/**
 * Handles the creation of table `imagewidget`.
 */
class m111111_111111_create_imagewidget_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('{{%imagewidget}}', [
            'id' => $this->primaryKey(),
            'group' => $this->string(64),
            'original_name' => $this->string(64)->unique()->notNull(),
            'hash' => $this->string(64)->unique()->notNull(),
            'extension' => $this->string(4)->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%imagewidget}}');
    }
}
