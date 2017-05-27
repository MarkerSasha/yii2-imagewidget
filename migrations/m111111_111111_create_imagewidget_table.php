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
            'sm_path' => $this->string(64),
            'md_path' => $this->string(64),
            'lg_path' => $this->string(64),
            'original_path' => $this->string(64)->notNull(),
            'hash' => $this->string(64)->unique()->notNull(),
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
