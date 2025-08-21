<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubSetting extends Model
{
    use HasFactory;

    protected $table = 'club_settings';

    protected $hidden = [
		'id',
		'club_id',
	];

    protected $appends = [
		'header_img_url',
		'footer_img_url',
		'logo_url',
		'cover_image_url',
	];

    protected $fillable = [
        'club_id',
        'timezone',
        'date',
        'club_privacy',
        'theme_colors',
        'text_color',
        'primary_color',
        'background_color',
        'secondary_color',
        'accent_color',
        'typography',
        'fonts',
        'header_title',
        'header_description',
        'header_img',
        'footer_text',
        'footer_img',
        'footer_description',
        'logo',
        'cover_image',
        'registration',
        'directory_visibility',
        'comments',
        'likes',
        'website_sections',
        'comment_preference',
        'reminders',
        'fb_link_option',
        'fb_link',
        'insta_link_option',
        'insta_link',
        'flickr_link_option',
        'flickr_link',
        'gdpr_privacy_policy_management',
        'cookies',
        'cookies_description',
        'data_collection_preferences',
        'data_collection_preferences_description',
        'allow_reporting',
        'allow_reporting_description',
    ];


    public function getHeaderImgUrlAttribute()
	{
		return $this->header_img ? asset('storage/' . $this->header_img) : null;
	}

	public function getFooterImgUrlAttribute()
	{
		return $this->footer_img ? asset('storage/' . $this->footer_img) : null;
	}

	public function getLogoUrlAttribute()
	{
		return $this->logo ? asset('storage/' . $this->logo) : null;
	}

	public function getCoverImageUrlAttribute()
	{
		return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
	}

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function coverImages()
    {
        return $this->hasMany(ClubCoverImage::class);
    }
}
