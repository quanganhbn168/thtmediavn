<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SePayTransactionProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SePayWebhookController extends Controller
{
    public function __invoke(Request $request, SePayTransactionProcessor $processor): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        $payload = is_array($payload) ? $payload : [];

        $data = Validator::make($payload, [
            'id' => ['required'],
            'gateway' => ['required', 'string', 'max:100'],
            'transactionDate' => ['required', 'date'],
            'accountNumber' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:100'],
            'content' => ['required', 'string', 'max:5000'],
            'transferType' => ['required', 'in:in,out'],
            'transferAmount' => ['required', 'integer', 'min:1'],
            'referenceCode' => ['nullable', 'string', 'max:150'],
        ])->validate();

        $processor->ingestWebhook($data);

        return response()->json(['success' => true]);
    }
}
