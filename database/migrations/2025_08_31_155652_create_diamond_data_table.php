<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('diamond_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_id')->constrained()->cascadeOnDelete();
            $table->string('cut')->nullable();
            $table->string('color')->nullable();
            $table->string('clarity')->nullable();
            $table->decimal('carat_weight', 8, 3)->nullable();
            $table->string('cut_quality')->nullable();
            $table->string('lab')->nullable();
            $table->string('symmetry')->nullable();
            $table->string('polish')->nullable();
            $table->string('eye_clean')->nullable();
            $table->string('culet_size')->nullable();
            $table->string('culet_condition')->nullable();
            $table->decimal('depth_percent', 5, 2)->nullable();
            $table->decimal('table_percent', 5, 2)->nullable();
            $table->decimal('meas_length', 8, 3)->nullable();
            $table->decimal('meas_width', 8, 3)->nullable();
            $table->decimal('meas_depth', 8, 3)->nullable();
            $table->string('girdle_min')->nullable();
            $table->string('girdle_max')->nullable();
            $table->string('fluor_color')->nullable();
            $table->string('fluor_intensity')->nullable();
            $table->string('fancy_color_dominant_color')->nullable();
            $table->string('fancy_color_secondary_color')->nullable();
            $table->string('fancy_color_overtone')->nullable();
            $table->string('fancy_color_intensity')->nullable();
            $table->bigInteger('total_sales_price')->nullable();
            $table->timestamps();

            // Performance indexes for filtering and searching
            $table->index(['cut', 'color', 'clarity']);
            $table->index(['carat_weight', 'total_sales_price']);
            $table->index(['lab', 'cut_quality']);
            $table->index('upload_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('diamond_data');
    }
};
