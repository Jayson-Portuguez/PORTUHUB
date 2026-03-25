<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingSetting extends Model
{
    protected $table = 'landing_settings';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'hero_headline',
        'hero_body',
        'hero_image_url',
        'feature_kicker',
        'feature_title',
        'feature_cta_label',
        'feature_cta_href',
        'feature_image_url',
        'feature_caption_right',
    ];

    public static function singleton(): self
    {
        return static::query()->findOrFail(1);
    }
}
