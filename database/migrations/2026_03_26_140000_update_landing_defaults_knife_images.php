<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('landing_settings')->where('id', 1)->update([
            'hero_headline' => 'Welcome to PortuHub — knives & tools you can trust',
            'hero_body' => 'Discover curated pocket knives, EDC gear, and quality blades. Straightforward shopping, clear photos, fair prices.',
            'hero_image_url' => '/uploads/img_69b2fd6b18b16.jfif',
            'feature_kicker' => 'Built for daily use.',
            'feature_title' => 'Every piece is chosen for solid build quality and real-world use — whether you are at home, outdoors, or on the job.',
            'feature_cta_label' => 'View all products',
            'feature_cta_href' => '/products',
            'feature_image_url' => '/uploads/img_69b2fd6b184e4.jfif',
            'feature_caption_right' => 'Sharp selection',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('landing_settings')->where('id', 1)->update([
            'hero_headline' => 'MEET THE COLLECTION: A NEW ERA IN SHOPPING',
            'hero_body' => 'OUR CURATION FLOWS FROM QUALITY CRAFTSMANSHIP, TRUSTED SUPPLIERS, AND DESIGN THAT FITS MODERN LIFE.',
            'hero_image_url' => '/landing-hero-reference.png',
            'feature_kicker' => 'CRAFTED FOR EVERYDAY EXPLORERS.',
            'feature_title' => 'EXPERTLY SELECTED FOR THOSE WHO SHOP WITH PRECISION AND STYLE IN MIND.',
            'feature_cta_label' => 'SEE DETAILS',
            'feature_cta_href' => '/products',
            'feature_image_url' => null,
            'feature_caption_right' => 'QUALITY YOU CAN TRUST',
            'updated_at' => now(),
        ]);
    }
};
