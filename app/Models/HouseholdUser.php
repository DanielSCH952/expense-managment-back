<?php

namespace App\Models;

use App\HouseholdRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class HouseholdUser extends Pivot
{
    protected $table = 'household_user';

    public const UPDATED_AT = null;

    protected $casts = [
        'role' => HouseholdRole::class,
    ];
}
