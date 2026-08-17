<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(){Schema::create('topics',function(Blueprint $table){$table->id();$table->string('title');$table->string('slug')->unique();$table->string('module')->index();$table->text('excerpt')->nullable();$table->longText('content');$table->unsignedInteger('sort_order')->default(0);$table->boolean('is_published')->default(false)->index();$table->timestamps();});} public function down(){Schema::dropIfExists('topics');} };
