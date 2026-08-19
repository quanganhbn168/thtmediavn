<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('contact.address', null);
        $this->migrator->add('contact.phone', null);
        $this->migrator->add('contact.email', null);
        $this->migrator->add('contact.map_embed', null);
        $this->migrator->add('contact.working_hours', null);
        $this->migrator->add('contact.facebook', null);
        $this->migrator->add('contact.instagram', null);
        $this->migrator->add('contact.youtube', null);
        $this->migrator->add('contact.tiktok', null);
        $this->migrator->add('contact.zalo', null);
    }
};
