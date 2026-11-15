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

        $hostIp = $this->resolveHostIp($request);

        $transfer = DB::transaction(function () use ($hostIp) {
            $query = EndUserBalance::query()
                ->select(['balance_transfer_id', 'amount', 'imo_id', 'created_at'])
                ->where('status', 'Processing')
                ->where('order_status', 'Pending');

            if ($hostIp) {
                $query->where(function ($q) use ($hostIp) {
                    $q->whereNull('host_ip')
                        ->orWhere('host_ip', $hostIp);
                })->orderByRaw('CASE WHEN host_ip = ? THEN 0 ELSE 1 END', [$hostIp]);
            } else {
                // No IPv4: only unassigned orders, FIFO
                $query->whereNull('host_ip');
            }

            $transfer = $query
                ->orderBy('created_at', 'asc')
                ->lock('FOR UPDATE SKIP LOCKED')
                ->first();

            if (!$transfer) {
                return null;
            }

            $update = ['order_status' => 'Processing'];

            if ($hostIp) {
                $update['host_ip'] = $hostIp;
            }

            $affected = EndUserBalance::query()
                ->where('balance_transfer_id', $transfer->balance_transfer_id)
                ->where('order_status', 'Pending')
                ->update($update);

            if ($affected === 0) {
                return null; // lost the race; do not return this order
            }

            return $transfer;
        });

        return response()->json([
            'status' => 'success',
            'data'   => $transfer,
        ], Response::HTTP_OK);
    }

    /**
     * Resolve IPv4 only. Returns null when no IPv4 is available (never stores IPv6).
     */
    private function resolveHostIp(Request $request): ?string
    {
        return collect($request->ips())
            ->push($request->ip())
            ->filter()
            ->unique()
            ->map(function (string $ip) {
                $normalized = strtolower($ip);

                // IPv4-mapped IPv6 (::ffff:192.0.2.1) → 192.0.2.1
                if (str_starts_with($normalized, '::ffff:')) {
                    $mapped = substr($ip, 7);
                    if (filter_var($mapped, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                        return $mapped;
                    }
                }

                return $ip;
            })
            ->first(fn (string $ip) => (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4));
    }
}
