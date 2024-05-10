<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Contribution extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'created_by',
        'image_path'
    ];

    /**
     * UUID
     *
     * @var string
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Holds the methods' names of Eloquent Relations 
     * to fall on delete cascade or on restoring
     * 
     * @var array
     */
    protected static $relations_to_cascade = ['withdrawls'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($resource) {
            foreach (static::$relations_to_cascade as $relation) {
                foreach ($resource->{$relation}()->get() as $item) {
                    $item->delete();
                }
            }
        });

        static::restoring(function ($resource) {
            foreach (static::$relations_to_cascade as $relation) {
                foreach ($resource->{$relation}()->withTrashed()->get() as $item) {
                    $item->restore();
                }
            }
        });
    }

    public static function booted()
    {
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Mendapatkan semua data nilai penarikan per rumah
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function withdrawls(): HasMany
    {
        return $this->hasMany(Withdrawl::class);
    }
}