<?php
declare(strict_types = 1);
use Origin\Migration\Migration;

class AddIsDefaultToHostsMigration extends Migration
{
    public function change() : void
    {
        $this->addColumn('hosts', 'is_default', 'boolean', ['default' => false]);
    }
}
