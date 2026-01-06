<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

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
        'header_img_thumb_url',
        'header_img_medium_url',
        'header_img_large_url',
        'footer_img_url',
        'footer_img_thumb_url',
        'footer_img_medium_url',
        'footer_img_large_url',
        'logo_url',
        'logo_thumb_url',
        'logo_medium_url',
        'logo_large_url',
        'favicon_url',
        'favicon_thumb_url',
        'club_banner_url',
        'club_banner_thumb_url',
        'club_banner_medium_url',
        'club_banner_large_url',
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
        'favicon',
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

    /**
     * Helper method to get image URL for specific size
     */
    private function getSizeUrl($path, $size = 'original')
    {
        if (!$path) {
            return null;
        }

        $filename = basename($path);
        
        // Determine base folder from the path
        $dir = dirname($path);
        
        // Handle different image types
        if (str_contains($path, 'club_logos')) {
            $baseFolder = 'club_logos';
        } elseif (str_contains($path, 'club_banners')) {
            $baseFolder = 'club_banners';
        } elseif (str_contains($path, 'uploads/clubs/footer')) {
            $baseFolder = 'uploads/clubs/footer';
        } elseif (str_contains($path, 'uploads/clubs/header')) {
            $baseFolder = 'uploads/clubs/header';
        } elseif (str_contains($path, 'uploads/clubs/favicon')) {
            $baseFolder = 'uploads/clubs/favicon';
        } elseif (str_contains($path, 'uploads/clubs/cover')) {
            $baseFolder = 'uploads/clubs/cover';
        } else {
            $baseFolder = dirname($path);
        }
        
        // For original size, use the stored path
        if ($size === 'original') {
            $sizePath = $path;
        } else {
            $sizePath = $baseFolder . '/' . $size . '/' . $filename;
        }
        
        return Storage::disk('public')->exists($sizePath)
            ? URL::to('storage/' . $sizePath)
            : null;
    }

    // Header Image URLs
    public function getHeaderImgUrlAttribute()
    {
        return $this->getSizeUrl($this->header_img);
    }
    
    public function getHeaderImgThumbUrlAttribute()
    {
        return $this->getSizeUrl($this->header_img, 'thumb');
    }
    
    public function getHeaderImgMediumUrlAttribute()
    {
        return $this->getSizeUrl($this->header_img, 'medium');
    }
    
    public function getHeaderImgLargeUrlAttribute()
    {
        return $this->getSizeUrl($this->header_img, 'large');
    }

    // Footer Image URLs
    public function getFooterImgUrlAttribute()
    {
        return $this->getSizeUrl($this->footer_img);
    }
    
    public function getFooterImgThumbUrlAttribute()
    {
        return $this->getSizeUrl($this->footer_img, 'thumb');
    }
    
    public function getFooterImgMediumUrlAttribute()
    {
        return $this->getSizeUrl($this->footer_img, 'medium');
    }
    
    public function getFooterImgLargeUrlAttribute()
    {
        return $this->getSizeUrl($this->footer_img, 'large');
    }

    // Logo URLs
    public function getLogoUrlAttribute()
    {
        return $this->getSizeUrl($this->logo);
    }
    
    public function getLogoThumbUrlAttribute()
    {
        return $this->getSizeUrl($this->logo, 'thumb');
    }
    
    public function getLogoMediumUrlAttribute()
    {
        return $this->getSizeUrl($this->logo, 'medium');
    }
    
    public function getLogoLargeUrlAttribute()
    {
        return $this->getSizeUrl($this->logo, 'large');
    }

    // Favicon URLs
    public function getFaviconUrlAttribute()
    {
        return $this->getSizeUrl($this->favicon);
    }
    
    public function getFaviconThumbUrlAttribute()
    {
        return $this->getSizeUrl($this->favicon, 'thumb');
    }

    // Club Banner URLs
    public function getClubBannerUrlAttribute()
    {
        return $this->getSizeUrl($this->club_banner);
    }
    
    public function getClubBannerThumbUrlAttribute()
    {
        return $this->getSizeUrl($this->club_banner, 'thumb');
    }
    
    public function getClubBannerMediumUrlAttribute()
    {
        return $this->getSizeUrl($this->club_banner, 'medium');
    }
    
    public function getClubBannerLargeUrlAttribute()
    {
        return $this->getSizeUrl($this->club_banner, 'large');
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
