<?php

use Phinx\Migration\AbstractMigration;

class FixWrongDateType extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('user_properties');
        $table
            ->changeColumn('birth', 'date', ['default' => '1930-01-01'])
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