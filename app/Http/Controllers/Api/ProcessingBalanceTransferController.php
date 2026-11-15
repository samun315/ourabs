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

    /**
     * Prefer IPv4; fall back to IPv6 only when no IPv4 is available.
     */
    private function resolveHostIp(Request $request): string
    {
        $candidates = collect($request->ips())
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
            ->values();

        $ipv4 = $candidates->first(
            fn (string $ip) => (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
        );

        if ($ipv4) {
            return $ipv4;
        }

        return $candidates->first(
            fn (string $ip) => (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
        ) ?? (string) $request->ip();
    }
}
