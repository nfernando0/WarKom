<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'icon'])]
class Category extends Model
{
    protected $table = 'categories';

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }
}
