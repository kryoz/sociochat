<?php

use Phinx\Migration\AbstractMigration;

class MusicCache extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('music_info', ['id' => true, 'primary_key' => ['id']]);
        $table
            ->addColumn('track_id', 'string', ['limit' => 32, 'null' => false])
            ->addColumn('artist', 'string', ['limit' => 128, 'null' => false])
            ->addColumn('song', 'string', ['limit' => 128, 'null' => false])
            ->addColumn('quality', 'string', ['limit' => 3, 'null' => false])
            ->addIndex(['track_id'])
            ->save();
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