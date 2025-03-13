<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MemberContact
 * 
 * @property int $id
 * @property int|null $member_id
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @package App\Models
 */
class MemberContact extends Model
{
	use SoftDeletes;
	protected $table = 'member_contacts';

	protected $casts = [
		'member_id' => 'int'
	];

	protected $fillable = [
		'member_id',
		'address',
		'phone',
		'email'
	];
}
