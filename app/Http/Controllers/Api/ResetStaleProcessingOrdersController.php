<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Marchant\EndUserBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ResetStaleProcessingOrdersController extends Controller
{
    public function __invoke(Request $request)
    {
        $incomingKey = $request->header('api-key');
        $expectedKey = '983790a329e6cb8c7a06ee61e23d9670bd3f22d347e5afebc08dadd91bdbe4ff';

        if (!$incomingKey || $incomingKey !== $expectedKey) {
            return response()->json([
                'message' => 'Unauthorized'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $updated = EndUserBalance::query()
            ->where('status', 'Processing')
            ->where('order_status', 'Processing')
            ->where('updated_at', '<', now()->subMinutes(5))
            ->update(['order_status' => 'Pending']);

        if ($updated > 0) {
            Log::info('Reset stale processing orders', ['count' => $updated]);
        }

        return response()->json([
            'status' => 'success',
            'message' => $updated > 0
                ? "Reset {$updated} stale processing order(s) to Pending."
                : 'No stale processing orders found.',
            'updated' => $updated,
        ], Response::HTTP_OK);
    }
}
