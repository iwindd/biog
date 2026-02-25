<?php

use yii\db\Migration;

/**
 * Handles the creation of table `short_url`.
 */
class m260225_025500_create_short_url_table extends Migration
{
  /**
   * @inheritdoc
   */
  public function up()
  {
    $this->createTable('short_url', [
      'id' => $this->primaryKey(),
      'code' => $this->string(255)->notNull()->unique(),
      'target_url' => $this->text()->notNull(),
      'created_at' => $this->integer(),
      'created_by' => $this->integer(),
      'updated_at' => $this->integer(),
    ]);
    
    $this->addForeignKey('fk_short_url_created_by', 'short_url', 'created_by', 'user', 'id', 'CASCADE');
  }

  /**
   * @inheritdoc
   */
  public function down()
  {
    $this->dropForeignKey('fk_short_url_created_by', 'short_url');
    $this->dropTable('short_url');
  }
}
