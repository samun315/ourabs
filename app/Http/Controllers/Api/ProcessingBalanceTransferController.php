<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Marchant\EndUserBalance;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProcessingBalanceTransferController extends Controller
{
    public function index(Request $request)
    {
        $incomingKey = $request->header('api-key');
        $expectedKey = '983790a329e6cb8c7a06ee61e23d9670bd3f22d347e5afebc08dadd91bdbe4ff';

        if (!$incomingKey || $incomingKey !== $expectedKey) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $transfer = EndUserBalance::query()
            ->select(['balance_transfer_id', 'amount', 'imo_id','created_at'])
            ->where('status', 'Processing')
            ->orderBy('created_at', 'asc')
            ->first();

        return response()->json([
            'status' => 'success',
            'data'   => $transfer,
        ], Response::HTTP_OK);
    }
}
