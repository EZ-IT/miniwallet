<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $sender_id
 * @property int $receiver_id
 * @property string $amount
 * @property string $commission_fee
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $receiver
 * @property-read User $sender
 *
 * @method static Builder<static>|Transaction newModelQuery()
 * @method static Builder<static>|Transaction newQuery()
 * @method static Builder<static>|Transaction query()
 * @method static Builder<static>|Transaction whereAmount($value)
 * @method static Builder<static>|Transaction whereCommissionFee($value)
 * @method static Builder<static>|Transaction whereCreatedAt($value)
 * @method static Builder<static>|Transaction whereId($value)
 * @method static Builder<static>|Transaction whereReceiverId($value)
 * @method static Builder<static>|Transaction whereSenderId($value)
 * @method static Builder<static>|Transaction whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Transaction extends Model
{
    /**
     * The canonical number of decimal places to use for all monetary calculations
     * and storage, reflecting the precision in the database schema.
     */
    public const int PRECISION = 4;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'amount',
        'commission_fee',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
