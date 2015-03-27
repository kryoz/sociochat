<?php

use Phinx\Migration\AbstractMigration;

class UserMetrics extends AbstractMigration
{

    public function up()
    {
	    $table = $this->table('user_properties');
	    $table
		    ->addColumn('karma', 'integer', ['default' => '0'])
		    ->addColumn('messages_count', 'integer', ['default' => '0'])
		    ->addColumn('words_count', 'integer', ['default' => '0'])
		    ->addColumn('online_time', 'integer', ['default' => '0'])
		    ->addColumn('music_posts', 'integer', ['default' => '0'])
		    ->addColumn('rude_count', 'integer', ['default' => '0'])
		    ->update();

	    $sql = <<< SQL
UPDATE user_properties SET messages_count = u.messages_count
FROM users AS u
WHERE user_id = u.id
SQL;

	    $this->query($sql);

	    $table = $this->table('users');
	    $table->removeColumn('messages_count');
    }

	public function down()
	{
		$table = $this->table('users');
		$table->addColumn('messages_count', 'integer', ['default' => '0'])
			->update();

		$sql = <<< SQL
UPDATE users SET messages_count = p.messages_count
FROM user_properties AS p
WHERE p.user_id = users.id
SQL;

		$this->query($sql);

		$table = $this->table('user_properties');
		$table
			->removeColumn('karma')
			->removeColumn('messages_count')
			->removeColumn('words_count')
			->removeColumn('online_time')
			->removeColumn('music_posts')
			->removeColumn('rude_count')
			->update();
	}
}