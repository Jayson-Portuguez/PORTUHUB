<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('hero_headline', 500);
            $table->text('hero_body')->nullable();
            $table->string('hero_image_url', 2000)->nullable();
            $table->string('feature_kicker', 500)->nullable();
            $table->text('feature_title')->nullable();
            $table->string('feature_cta_label', 200)->nullable();
            $table->string('feature_cta_href', 500)->nullable();
            $table->string('feature_image_url', 2000)->nullable();
            $table->string('feature_caption_right', 500)->nullable();
            $table->timestamps();
        });

        DB::table('landing_settings')->insert([
            'id' => 1,
            'hero_headline' => 'MEET THE COLLECTION: A NEW ERA IN SHOPPING',
            'hero_body' => 'OUR CURATION FLOWS FROM QUALITY CRAFTSMANSHIP, TRUSTED SUPPLIERS, AND DESIGN THAT FITS MODERN LIFE.',
            'hero_image_url' => '/landing-hero-reference.png',
            'feature_kicker' => 'CRAFTED FOR EVERYDAY EXPLORERS.',
            'feature_title' => 'EXPERTLY SELECTED FOR THOSE WHO SHOP WITH PRECISION AND STYLE IN MIND.',
            'feature_cta_label' => 'SEE DETAILS',
            'feature_cta_href' => '/products',
            'feature_image_url' => null,
            'feature_caption_right' => 'QUALITY YOU CAN TRUST',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_settings');
    }
};
