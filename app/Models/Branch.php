<?php

namespace App\Models;

use App\Models\Base\Branch as BaseBranch;

class Branch extends BaseBranch
{
	protected $fillable = [
		'name',
		'address',
		'email',
		'phone'
    ];

    public function getFullNameAttribute() {
        return $this->name . ' | ' . $this->address;
    }
}
