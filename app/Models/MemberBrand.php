<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MemberBrand
 * 
 * @property int $id
 * @property int|null $member_id
 * @property string|null $brand
 * @property string|null $interest
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class MemberBrand extends Model
{
	use SoftDeletes;
	protected $table = 'member_brands';

	protected $casts = [
		'member_id' => 'int',
		'interest' => 'array',
    	'brands' => 'array',
	];

	protected $fillable = [
		'member_id',
		'brands',
		'interest'
	];
}
