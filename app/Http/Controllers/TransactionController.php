<?php

namespace App\Http\Controllers;

use App\Enums\TransferType;
use App\Exceptions\Transaction\InsufficientFundsException;
use App\Http\Requests\TransferRequest;
use App\Http\Resources\TransactionResource;
use App\Models\User;
use App\Services\Factories\TransferServiceFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class TransactionController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $incomingSum = $user->receivedTransactions()->sum('amount');
        $outgoingSum = $user->sentTransactions()->sum('amount');
        $incomingCount = $user->receivedTransactions()->count();
        $outgoingCount = $user->sentTransactions()->count();

        return Inertia::render('transaction/Index', [
            'history' => TransactionResource::collection($user->transactions()->with(['sender:id,name', 'receiver:id,name'])->latest()->paginate(20)),
            'stats' => [
                'incoming' => (float)$incomingSum,
                'outgoing' => (float)$outgoingSum,
                'incoming_count' => $incomingCount,
                'outgoing_count' => $outgoingCount,
            ],
        ]);
    }

    public function store(TransferRequest $request, TransferServiceFactory $transferServiceFactory): RedirectResponse
    {
        $transferType = TransferType::INTERNAL;
        $sender = Auth::user();

        $validatedData = $request->validated();
        $receiver = User::findOrFail($validatedData['receiver_id']);
        $amount = $validatedData['amount'];

        try {
            $transferService = $transferServiceFactory->make($transferType);
            $transferService->execute($sender, $receiver, $amount);

            return back()->with('success', 'Transaction sent successfully!');
        } catch (InsufficientFundsException $e) {
            $errors = new MessageBag(['amount' => $e->getMessage()]);

            return back()->withErrors($errors);
        } catch (Throwable $e) {
            Log::error('Web transaction failed: ' . $e->getMessage());

            $errors = new MessageBag(['amount' => 'An unexpected error occurred. Please try again later.']);

            return back()->withErrors($errors);
        }
    }
}
