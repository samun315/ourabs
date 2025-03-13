<?php

namespace App\Services\Report;

use App\Models\Payment\Account;
use App\Models\Report\TransactionHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class TransactionHistoryService
{
    public function getTransactionHistoryList(Request $request): JsonResponse
    {
        $date = $request->input('date');
        $type = $request->input('type');

        $query = TransactionHistory::query()->leftJoin('users', function ($join) {
            $join->on('transaction_histories.user_id', '=', 'users.id');
        })->where(function ($query) use ($date, $type) {
            if ($type) {
                $query->where('type', '=', $type);
            }

            if ($date) {
                $date = explode(' - ', $date);

                if (count($date) == 2) {
                    $startDate = Carbon::createFromFormat('d M, Y', trim($date[0]))->startOfDay()->format('Y-m-d H:i:s');
                    $endDate = Carbon::createFromFormat('d M, Y', trim($date[1]))->endOfDay()->format('Y-m-d H:i:s');
                    $query->whereBetween('transaction_histories.created_at', [$startDate, $endDate]);
                }
            }
        })
            ->select('transaction_histories.*', 'users.full_name');

        return Datatables::of($query)
            ->addIndexColumn()
            ->make(true);
    }

    public function getTotalDiamondList(Request $request): JsonResponse
    {
        $searchKeyword = $request->input('search');
        $roleId = $request->input('role_id');

        $query = Account::query()->leftJoin('users', 'accounts.user_id', '=', 'users.id')
            ->leftJoin('user_roles', 'users.role_id', '=', 'user_roles.role_id')
            ->where(function ($query) use ($searchKeyword, $roleId) {
                $query->where(function ($q) use ($searchKeyword) {
                    $q->where('users.full_name', 'like', "%$searchKeyword%")
                        ->orWhere('user_roles.role_name', 'like', "%$searchKeyword%");
                });

                if ($roleId) {
                    $query->where('users.role_id', '=', $roleId);
                }
            })
            ->select('accounts.*', 'users.full_name', 'user_roles.role_name');

        return Datatables::of($query)
            ->addIndexColumn()
            ->make(true);
    }
}
