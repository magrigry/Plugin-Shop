<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property string $name
 * @property int $discount
 * @property bool $is_global
 * @property bool $is_enabled
 * @property bool $min_total_spent
 * @property bool $max_total_spent
 * @property \Carbon\Carbon $start_at
 * @property \Carbon\Carbon $end_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\Package[] $packages
 *
 * @method static \Illuminate\Database\Eloquent\Builder enabled()
 * @method static \Illuminate\Database\Eloquent\Builder active()
 * @method static \Illuminate\Database\Eloquent\Builder global()
 */
class Discount extends Model
{
    use HasTablePrefix;

    /**
     * The table prefix associated with the model.
     *
     * @var string
     */
    protected $prefix = 'shop_';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'discount', 'packages', 'is_global', 'is_enabled', 'start_at', 'end_at', 'min_total_spent', 'max_total_spent'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_global' => 'boolean',
        'is_enabled' => 'boolean',
    ];

    /**
     * Get the packages affected by this discount.
     */
    public function packages()
    {
        return $this->belongsToMany(Package::class, 'shop_discount_package');
    }

    /**
     * Determine if this discount is currently active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->is_enabled && $this->start_at->isPast() && $this->end_at->isFuture();
    }

    public function isActiveOn(Package $package)
    {
        if (! $this->isActive()) {
            return false;
        }

        if (! $this->hasSpentRequirements()) {
            return false;
        }

        if ($this->is_global) {
            return true;
        }

        return $this->packages->contains($package);
    }

    public function hasSpentRequirements(): bool
    {

        if ($this->min_total_spent === null || $this->max_total_spent === null) {
            return true;
        }

        if (Auth::guest()) {
            return false;
        }

        /** @var Payment[] $payments */
        $payments = Payment::where('user_id', Auth::id())
            ->scopes(['notPending', 'withRealMoney'])
            ->get();

        $amount = 0;

        foreach ($payments as $payment) {
            $amount =+ $payment->price;
        }

        return $amount >= $this->min_total_spent && $amount <= $this->max_total_spent;
    }

    /**
     * Scope a query to only include active discounts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_enabled', true)
            ->where('start_at', '<', now())
            ->where('end_at', '>', now());
    }

    /**
     * Scope a query to only include enabled discounts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled(Builder $query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope a query to only include global discounts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGlobal(Builder $query)
    {
        return $query->where('is_global', true);
    }
}
