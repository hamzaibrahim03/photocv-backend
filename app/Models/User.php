<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User
 * 
 * @property int $id
 * @property string|null $role
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $tag_line
 * @property string|null $about
 * @property string|null $email
 * @property string|null $password
 * @property string|null $profile_image
 * @property string|null $address
 * @property string|null $ciy
 * @property string|null $postcode
 * @property string|null $country
 * @property string|null $phone
 * @property string|null $county
 * @property string|null $bio
 * @property string|null $status
 * @property string|null $account_status
 * @property Carbon|null $approval_date
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class User extends Model
{
	use SoftDeletes, HasRoles, HasApiTokens;
	protected $table = 'users';

	protected $casts = [
		'approval_date' => 'datetime',
		'created_by' => 'int',
		'updated_by' => 'int',
		'deleted_by' => 'int'
	];

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'username',
		'first_name',
		'last_name',
		'tag_line',
		'about',
		'email',
		'password',
		'profile_image',
		'address',
		'ciy',
		'postcode',
		'country',
		'phone',
		'county',
		'bio',
		'status',
		'account_status',
		'approval_date',
		'created_by',
		'updated_by',
		'deleted_by'
	];
}
