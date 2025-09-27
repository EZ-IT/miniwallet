<?php

namespace App\Services;

use App\Enums\TransferType;
use App\Events\TransactionCompleted;
use App\Exceptions\Transaction\InsufficientFundsException;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Contracts\TransferServiceInterface;
use Illuminate\Support\Facades\DB;
use RoundingMode;
use Throwable;

class TransferService implements TransferServiceInterface
{
    /**
     * A safe buffer to add to our canonical precision when performing intermediate
     * calculations. This ensures that multiplications are lossless and rounding
     * is accurate for any realistic input precisions.
     */
    private const int CALCULATION_PRECISION_BUFFER = 5;

    /**
     * Executes a financial transfer between two users.
     *
     * This method is atomic and will roll back all changes if any step fails.
     * It ensures data integrity by using pessimistic locking to prevent race conditions.
     *
     * @throws InsufficientFundsException|Throwable
     */
    public function execute(User $sender, User $receiver, string $amount): Transaction
    {
        $commissionRate = config('wallet.commission_rate', '0.0');

        bcscale(Transaction::PRECISION + self::CALCULATION_PRECISION_BUFFER);

        $unroundedFee = bcmul($amount, (string) $commissionRate);
        $commissionFee = bcround($unroundedFee, Transaction::PRECISION, RoundingMode::HalfAwayFromZero);
        $totalCharge = bcadd($amount, $commissionFee);

        $newTransaction = DB::transaction(static function () use ($sender, $receiver, $amount, $commissionFee, $totalCharge) {
            // Lock sender's row.
            $sender = User::where('id', $sender->id)->lockForUpdate()->first();

            $isInsufficientFunds = bccomp((string) $sender->balance, $totalCharge) === -1; // -1 means balance < totalCharge
            if ($isInsufficientFunds) {
                throw new InsufficientFundsException('Insufficient funds to cover the amount and commission fee.');
            }

            // Lock receiver's row.
            $receiver = User::where('id', $receiver->id)->lockForUpdate()->first();

            $sender->decrement('balance', (float) $totalCharge);
            $receiver->increment('balance', (float) $amount);

            return Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'commission_fee' => $commissionFee,
            ]);
        });

        $newTransaction->load(['sender', 'receiver']);

        event(new TransactionCompleted($newTransaction, $newTransaction->sender));

        event(new TransactionCompleted($newTransaction, $newTransaction->receiver));

        return $newTransaction;
    }

    public function getType(): string
    {
        return TransferType::INTERNAL->value;
    }
}
