<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasSeo, LogsActivity;

    protected $guarded = ['id'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function publicUrl(): string
    {
        return route('blog.category', $this);
    }

    public function isPubliclyVisible(): bool
    {
        return true;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
