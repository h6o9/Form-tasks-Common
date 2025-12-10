<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormHasField extends Model
{
    use HasFactory;
	protected $guarded = [];

	public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    // 🔗 Relationship with FormField
    public function formField()
    {
        return $this->belongsTo(FormField::class, 'form_field_id', 'id');
    }
}
