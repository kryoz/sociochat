<?php

use Phinx\Migration\AbstractMigration;

class MakeUniqIndexOnTrackId extends AbstractMigration
{
	public function change()
	{
		$table = $this->table('music_info');
		$table
			->removeIndex('track_id')
			->addIndex(['track_id'], ['unique' => true])
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