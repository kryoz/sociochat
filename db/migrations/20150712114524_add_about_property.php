<?php

use Phinx\Migration\AbstractMigration;

class AddAboutProperty extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('user_properties');
        $table
            ->addColumn('about', 'string', ['limit' => 255, 'null' => true])
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