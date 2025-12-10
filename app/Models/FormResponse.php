<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormResponse extends Model
{
    use HasFactory;
	protected $guarded = [];

	public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function company()
{
    return $this->belongsTo(Company::class, 'company_id');
}
}