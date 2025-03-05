<?php

namespace App\Services\Marchant;

use App\Http\Requests\Marchant\EndUserBalanceRequest;
use App\Models\Marchant\BalanceRequest;
use App\Models\Marchant\EndUserBalance;
use App\Models\Marchant\RequestWhitelist;
use App\Models\Payment\Account;
use Exception;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class EndUserBalanceRequestService
{
    public function getRequestInfo(Request $request): JsonResponse|Model|Builder
    {
        $searchKeyword = $request->input('search');

        $query = EndUserBalance::query()
            ->where(function ($query) use ($searchKeyword) {
                $query->where(function ($q) use ($searchKeyword) {
                    $q->where('phone_number', 'like', "%$searchKeyword%")
                        ->orWhere('imo_id', 'like', "%$searchKeyword%")
                        ->orWhere('amount', 'like', "%$searchKeyword%")
                        ->orWhere('status', 'like', "%$searchKeyword%");
                });
                $query->where('created_by', loggedInUserId());
            })->latest();

        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {

                $showBtn = '<button data-id="' . $row->balance_transfer_id . '" class="btn btn-success btn-sm ms-lg-2 showEndUserBalanceBtn" data-bs-toggle="modal" data-bs-target="#showModal">Show</button>';

                return $showBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function storeBalance(array $data): Model|JsonResponse
    {
        DB::beginTransaction();
        try {
            $data['user_id'] = $data['request_by'];

            $account = Account::query()->where('user_id', $data['user_id'])->first();

            if ($account && $data['amount'] < $account->current_balance) {

                $from_user['current_balance'] = $account?->current_balance - $data['amount'];
                $from_user['updated_by'] = loggedInUserId();

                $account->update($from_user);

                $balance = EndUserBalance::query()->create($data);
            }

            DB::commit();
            
            return $balance;
        } catch (Exception $exception) {
            DB::rollBack();
            report($exception);
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
}
