<?php

use Phinx\Migration\AbstractMigration;

class NameChangeAddIndex extends AbstractMigration
{
	public function change()
	{

		$table = $this->table('name_change_history');
		$table
			->addIndex(['name'])
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