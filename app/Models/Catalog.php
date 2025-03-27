<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

	protected $fillable = [
		'name',
		'catalog_type',
		'icon'
	];

	public function pages()
	{
		return $this->hasMany(Page::class, 'page_type_id');
	}

	public function clubNews()
	{
		return $this->hasMany(ClubNews::class, 'news_type_id');
	}
}
