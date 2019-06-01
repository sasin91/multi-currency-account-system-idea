<?php

namespace App;

use App\Events\CreateAccount;
use App\Projectors\AccountProjector;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use Ramsey\Uuid\Uuid;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Account
 * @package App
 *
 * @property Collection $ledgers
 * @property User $owner
 */
class Account extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'uuid',
        'owner_id',
        'type',
        'description',
        'points'
    ];

    protected $casts = [
        'points' => 'integer'
    ];

    /**
     * Create the account through the AccountProjector
     *
     * @see AccountProjector#createAccount
     * @param array $attributes
     * @return string
     * @throws \Exception
     */
    public static function createThroughEventProjector(array $attributes): string
    {
        if (!isset($attributes['uuid'])) {
            $attributes['uuid'] = (string)Uuid::uuid4();
        }

        /**
         * Fire an event that'll cause the AccountProjector to create the actual Account while keeping a record of the event,
         * So it'll get recreated exactly as it was if replayed.
         */
        event(new CreateAccount($attributes));

        return $attributes['uuid'];
    }

    /**
     * Find the account by a stored event
     *
     * @param object $event
     * @throws ModelNotFoundException
     * @return Account|Model
     */
    public static function findByEvent($event)
    {
        return self::query()->where('uuid', data_get($event, 'accountUuid'))->firstOrFail();
    }

    /**
     * The User that owns the Account
     *
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The stored events involving this Account
     *
     * @return HasMany
     */
    public function events(): HasMany
    {
        return $this->hasMany(config('event-projector.stored_event_model'), 'event_properties->accountUuid', 'uuid');
    }

    /**
     * A tab for each supported currency
     *
     * @return HasMany
     */
    public function ledgers(): HasMany
    {
        return $this->hasMany(AccountLedger::class, 'account_id', 'id');
    }
}
