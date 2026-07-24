<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('menu.header_menu_id', null);
        $this->migrator->add('menu.mega_menu_id', null);
        $this->migrator->add('menu.footer_menu_1_id', null);
        $this->migrator->add('menu.footer_menu_2_id', null);
    }
};
