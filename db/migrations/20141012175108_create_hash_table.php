<?php

use Phinx\Migration\AbstractMigration;

class CreateHashTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('hashes', ['id' => true, 'primary_key' => ['id']]);
        $table
            ->addColumn('username', 'string', ['limit' => 20])
            ->addColumn('date', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('message', 'string', ['limit' => 1024])
            ->save();

        $sql = <<< SQL
-- Add the new tsvector column
ALTER TABLE hashes ADD COLUMN hashes_tsv tsvector;

-- Create a function that will generate a tsvector from text data found in both the
-- title and body columns, but give a higher relevancy rating 'A' to the title data
CREATE FUNCTION hashes_generate_tsvector() RETURNS trigger AS $$
  begin
    new.hashes_tsv :=
      setweight(to_tsvector(coalesce(new.message,'')), 'A') ||
      setweight(to_tsvector(coalesce(new.username,'')), 'B');
    return new;
  end
$$ LANGUAGE plpgsql;

-- When articles row data is inserted or updated, execute the function
-- that generates the tsvector data for that row
CREATE TRIGGER tsvector_indexes_upsert_trigger BEFORE INSERT OR UPDATE
ON hashes
FOR EACH ROW EXECUTE PROCEDURE hashes_generate_tsvector();

-- When the migration is run, create tsvector data for all the existing records
UPDATE hashes SET hashes_tsv =
setweight(to_tsvector(coalesce(message,'')), 'A') ||
setweight(to_tsvector(coalesce(username,'')), 'B');

-- Create an index for the tsv column that is specialised for tsvector data
CREATE INDEX hashes_tsv_idx ON hashes USING gin(hashes_tsv);
SQL;

        $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('hashes');
    }
}
