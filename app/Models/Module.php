<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Module extends Model
{
    protected $connection = 'mysql';

    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        'description',
        'is_active',
        'is_dynamic',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_dynamic' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Get the dynamic submodules for this module
     */
    public function dynamicSubmodules(): HasMany
    {
        return $this->hasMany(DynamicSubmodule::class);
    }

    /**
     * Generate a URL-friendly slug from a name
     */
    public static function generateSlug(string $name): string
    {
        return Str::slug($name);
    }

    /**
     * Scope to get only active modules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order modules by sort order and name
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Boot method to auto-generate slug when creating
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($module) {
            if (empty($module->slug)) {
                $module->slug = self::generateSlug($module->name);
            }
        });

        static::updating(function ($module) {
            if ($module->isDirty('name') && empty($module->slug)) {
                $module->slug = self::generateSlug($module->name);
            }
        });
    }
}