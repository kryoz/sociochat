<?php

use Phinx\Migration\AbstractMigration;

class AddMusicTrackUrl extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('music_info');
        $table
            ->addColumn('url', 'string', ['limit' => 255, 'default' => null, 'null' => true])
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
