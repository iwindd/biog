<?php

use yii\db\Migration;

/**
 * Class m260224_094000_create_licenses_table
 */
class m260224_094000_create_licenses_table extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->createTable('{{%licenses}}', [
      'id' => $this->primaryKey(),
      'name' => $this->string()->notNull(),
      'code' => $this->string(50)->unique()->notNull(),
      'description' => $this->text(),
      'url' => $this->string(),
      'created_at' => $this->dateTime(),
      'updated_at' => $this->dateTime(),
    ]);

    // Add some default CC licenses
    $this->batchInsert('{{%licenses}}', ['name', 'code', 'description', 'url', 'created_at', 'updated_at'], [
      ['Creative Commons Attribution (CC BY)', 'CC_BY', 'This license lets others distribute, remix, tweak, and build upon your work, even commercially, as long as they credit you for the original creation.', 'https://creativecommons.org/licenses/by/4.0/', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
      ['Creative Commons Attribution-ShareAlike (CC BY-SA)', 'CC_BY-SA', 'This license lets others remix, tweak, and build upon your work even for commercial purposes, as long as they credit you and license their new creations under the identical terms.', 'https://creativecommons.org/licenses/by-sa/4.0/', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
      ['Creative Commons Attribution-NonCommercial (CC BY-NC)', 'CC_BY-NC', 'This license lets others remix, tweak, and build upon your work non-commercially, and although their new works must also acknowledge you and be non-commercial, they don’t have to license their derivative works on the same terms.', 'https://creativecommons.org/licenses/by-nc/4.0/', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
      ['Creative Commons Attribution-NonCommercial-ShareAlike (CC BY-NC-SA)', 'CC_BY-NC-SA', 'This license lets others remix, tweak, and build upon your work non-commercially, as long as they credit you and license their new creations under the identical terms.', 'https://creativecommons.org/licenses/by-nc-sa/4.0/', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
      ['Creative Commons Attribution-NoDerivatives (CC BY-ND)', 'CC_BY-ND', 'This license lets others reuse the work for any purpose, including commercially; however, it cannot be shared with others in adapted form, and credit must be provided to you.', 'https://creativecommons.org/licenses/by-nd/4.0/', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
      ['Creative Commons Attribution-NonCommercial-NoDerivatives (CC BY-NC-ND)', 'CC_BY-NC-ND', 'This license is the most restrictive of our six main licenses, only allowing others to download your works and share them with others as long as they credit you, but they can’t change them in any way or use them commercially.', 'https://creativecommons.org/licenses/by-nc-nd/4.0/', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
      ['Public Domain (CC0)', 'CC0', 'CC0 enables scientists, educators, artists and other creators and owners of copyright- or database-protected content to waive those interests in their works and thereby place them as completely as possible in the public domain, so that others may freely build upon, enhance and reuse the works for any purposes without restriction under copyright or database law.', 'https://creativecommons.org/publicdomain/zero/1.0/', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')],
    ]);

    $this->addColumn('{{%content}}', 'license_id', $this->integer()->null());

    // Add foreign key for table `content` to `licenses`
    $this->addForeignKey(
      '{{%fk-content-license_id}}',
      '{{%content}}',
      'license_id',
      '{{%licenses}}',
      'id',
      'SET NULL'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropForeignKey(
      '{{%fk-content-license_id}}',
      '{{%content}}'
    );

    $this->dropColumn('{{%content}}', 'license_id');
    $this->dropTable('{{%licenses}}');
  }
}
