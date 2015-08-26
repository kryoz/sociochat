<?php

use Phinx\Migration\AbstractMigration;

class ExtendMusicUrl extends AbstractMigration
{

    public function change()
    {
        $table = $this->table('music_info');
        $table
            ->changeColumn('url', 'string', ['limit' => 512, 'default' => null, 'null' => true])
            ->update();
    }

    /**
     * Migrate Up.
     */
    public function up()
    {

    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
