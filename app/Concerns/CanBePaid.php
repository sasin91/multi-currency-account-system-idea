<?php


namespace App\Concerns;

use DateTimeInterface;
use function defined;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait CanBePaid
 * @package App\Concerns
 *
 * @method static Builder onlyPaid($date = null)
 * @method static Builder onlyUnpaid()
 */
trait CanBePaid
{
    /**
     * Initialize the soft deleting trait for an instance.
     *
     * @return void
     */
    public function initializeCanBePaid()
    {
        $this->dates[] = $this->getPaidAtColumn();
    }

    /**
     * Get the name of the "paid at" column.
     *
     * @return string
     */
    public function getPaidAtColumn(): string
    {
        return defined('static::PAID_AT') ? static::PAID_AT : 'paid_at';
    }

    /**
     * Mark the model as paid
     *
     * @param DateTimeInterface|string|null $date
     * @return $this
     * @throws \Throwable
     */
    public function markAsPaid($date = null)
    {
        $this
            ->forceFill([$this->getPaidAtColumn() => $this->asDateTime($date)])
            ->saveOrFail();

        return $this;
    }

    /**
     * Get only the paid models
     *
     * @param Builder $query
     * @param DateTimeInterface|string|null $date
     * @return Builder
     */
    public function scopeOnlyPaid($query, $date = null)
    {
        $this->newQueryWithoutScope('onlyUnpaid');

        if ($date) {
            return $query->whereDate($this->getPaidAtColumn(), $this->asDate($date));
        }

        return $query->whereNotNull($this->getPaidAtColumn());
    }

    /**
     * Get only the unpaid models
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOnlyUnpaid($query)
    {
        $this->newQueryWithoutScope('onlyPaid');

        return $query->whereNull($this->getPaidAtColumn());
    }

    /**
     * Whether the model has been paid
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->{$this->getPaidAtColumn()} !== null;
    }

    /**
     * Whether the model is unpaid
     *
     * @return bool
     */
    public function isUnpaid(): bool
    {
        return !$this->isPaid();
    }
}
