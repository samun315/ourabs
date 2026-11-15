<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Marchant\EndUserBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $hostIp = $request->ip();

        $transfer = DB::transaction(function () use ($hostIp) {
            $transfer = EndUserBalance::query()
                ->select(['balance_transfer_id', 'amount', 'imo_id', 'created_at'])
                ->where('status', 'Processing')
                ->where('order_status', 'Pending')
                ->where(function ($query) use ($hostIp) {
                    $query->whereNull('host_ip')
                        ->orWhere('host_ip', $hostIp);
                })
                ->orderByRaw('CASE WHEN host_ip = ? THEN 0 ELSE 1 END', [$hostIp])
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->first();

            if (!$transfer) {
                return null;
            }

            EndUserBalance::query()
                ->where('balance_transfer_id', $transfer->balance_transfer_id)
                ->update([
                    'order_status' => 'Processing',
                    'host_ip' => $hostIp,
                ]);

            return $transfer;
        });

        return response()->json([
            'status' => 'success',
            'data'   => $transfer,
        ], Response::HTTP_OK);
    }
}
