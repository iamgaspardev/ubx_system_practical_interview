<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('upload_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chunk_id')->constrained('upload_chunks')->cascadeOnDelete();
            $table->integer('row_number');
            $table->string('column_name')->nullable();
            $table->text('error_message');
            $table->text('row_data');
            $table->enum('error_type', ['validation', 'database', 'format'])->default('validation');
            $table->timestamps();

            $table->index(['upload_id', 'error_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('upload_errors');
    }
};