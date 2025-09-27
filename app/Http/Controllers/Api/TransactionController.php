<?php

namespace App\Http\Controllers\Api;

use App\Enums\TransferType;
use App\Exceptions\Transaction\InsufficientFundsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\TransferRequest;
use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Models\User;
use App\Services\Factories\TransferServiceFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class TransactionController extends Controller
{
    public function index(): TransactionCollection
    {
        $user = Auth::user();

        $transactions = $user->transactions()
            ->with(['sender:id,name', 'receiver:id,name'])
            ->latest()
            ->paginate(20);

        return new TransactionCollection($transactions, $user->balance);
    }

    public function store(TransferRequest $request, TransferServiceFactory $transferServiceFactory): ?JsonResponse
    {
        $transferType = TransferType::INTERNAL;

        $sender = Auth::user();

        $validatedData = $request->validated();
        $receiver = User::findOrFail($validatedData['receiver_id']);
        $amount = $validatedData['amount'];

        try {
            $transferService = $transferServiceFactory->make($transferType);

            $newTransaction = $transferService->execute($sender, $receiver, $amount);

            return response()->json([
                'message' => 'Transfer successful.',
                'data' => new TransactionResource($newTransaction),
            ], HttpResponse::HTTP_CREATED);
        } catch (InsufficientFundsException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            Log::error('Transaction failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'An unexpected error occurred during the transfer.',
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
