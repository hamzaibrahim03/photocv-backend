<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Class Catalog
 * 
 * @property int $id
 * @property string|null $name
 * @property string|null $catalog_type
 * @property string|null $icon
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class Catalog extends Model
{
	use SoftDeletes;
	protected $table = 'catalog';
	protected $appends = ['icon_url'];

	protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

	protected $fillable = [
		'name',
		'catalog_type',
		'icon'
	];

	public function getIconUrlAttribute()
	{
		if (!$this->icon) {
			return null;
		}

		// If already a full URL, return it as-is
		if (Str::startsWith($this->icon, ['http://', 'https://'])) {
			return $this->icon;
		}

		// Otherwise, prepend the public storage path
		return asset('storage/' . $this->icon);
	}

	public function pages()
	{
		return $this->hasMany(Page::class, 'page_type_id');
	}

	public function clubNews()
	{
		return $this->hasMany(ClubNews::class, 'news_type_id');
	}

	public function bookingsByType()
    {
        return $this->hasMany(Booking::class, 'booking_type_id');
    }

    public function bookingsByLeadSource()
    {
        return $this->hasMany(Booking::class, 'lead_source_id');
    }

	public function gears()
	{
		return $this->hasMany(Gear::class, 'category_id');
	}
}
