<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $payment_id
 * @property string $name
 * @property float $price
 * @property int $quantity
 * @property string $buyable_type
 * @property int $buyable_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Azuriom\Plugin\Shop\Models\Payment $payment
 * @property \Azuriom\Plugin\Shop\Models\Package|\Azuriom\Plugin\Shop\Models\Offer|null $buyable
 */
class PaymentItem extends Model
{
    use HasTablePrefix;

    /**
     * The table prefix associated with the model.
     */
    protected string $prefix = 'shop_';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'price', 'quantity',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'float',
    ];

    /**
     * Get the payment associated to this payment item.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the purchased model.
     */
    public function buyable()
    {
        return $this->morphTo('buyable');
    }

    public function deliver(): void
    {
        $this->buyable?->deliver($this->payment->user, $this->quantity, $this);
    }

    public function formatPrice(): string
    {
        $currency = $this->payment->isWithSiteMoney()
            ? money_name($this->price)
            : currency_display($this->payment->currency);

        return $this->price.' '.$currency;
    }
}
