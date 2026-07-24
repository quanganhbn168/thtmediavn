<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up():void { Schema::create('contact_channels',function(Blueprint $table){$table->id();$table->string('name');$table->string('type',30)->default('phone');$table->string('value');$table->string('url')->nullable();$table->string('icon',100)->nullable();$table->boolean('is_primary')->default(false);$table->boolean('show_topbar')->default(false);$table->boolean('show_footer')->default(true);$table->boolean('show_floating')->default(false);$table->boolean('is_active')->default(true);$table->unsignedInteger('sort_order')->default(0);$table->timestamps();}); }
    public function down():void { Schema::dropIfExists('contact_channels'); }
};
