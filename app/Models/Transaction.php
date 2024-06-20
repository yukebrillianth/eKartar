<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, SoftDeletes, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'type',
        'expense_id',
        'value',
        'contribution_id',
        'transactionable_id',
        'transactionable_type'
    ];

    /**
     * `Casting`
     *
     * @var array
     */
    protected $casts = [
        'type' => TransactionType::class,
    ];

    /**
     * ULID
     *
     * @var string
     */
    protected $keyType = 'string';
    public $incrementing = false;

    public static function booted()
    {
        static::creating(function ($model) {
            $model->id = Str::ulid();
        });
    }

    public function balance(): HasOne
    {
        return $this->hasOne(Balance::class);
    }

    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }
}
