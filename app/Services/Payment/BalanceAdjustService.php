<?php

namespace App\Services\Payment;

use App\Models\Payment\Account;
use App\Models\Payment\BalanceAdjust;
use App\Models\Payment\PaymentGateway;
use App\Models\Report\TransactionHistory;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class BalanceAdjustService
{

    public function storeAdjustBalance(array $data): bool|JsonResponse
    {
        DB::beginTransaction();
        try {
            $account = Account::query()->where('user_id', $data['user_id'])->first();
            $fromUser = User::query()->where('id',loggedInUserId())->first();
            $toUser = User::query()->where('id',$data['user_id'])->first();

            $data['before_balance'] = $account->current_balance;

            $transactionHistory = [
                'user_id' => $data['user_id'],
                'type' => 'TRANSFER',
                'refference' => $fromUser?->email .' TO '.$toUser?->email,
                'amount' => $data['balance'],
                'before_balance' => $account->current_balance,
                'refference_table' => 'balance_adjust',
                'created_by' => loggedInUserId(),
            ];

            if ($data['type'] == 'add') {
                $data['type'] = 'credit';
                $data['after_balance'] = $data['before_balance'] + $data['balance'];

                $transactionHistory['after_balance'] =  $data['after_balance'];

                $currentAccount['current_balance'] = $account->current_balance + $data['balance'];
            } else {
                if ($data['before_balance'] >= $data['balance']) {
                    $data['type'] = 'debit';
                    $data['after_balance'] = $data['before_balance'] - $data['balance'];

                    $transactionHistory['after_balance'] =  $data['after_balance'];

                    $currentAccount['current_balance'] = $account->current_balance - $data['balance'];
                }
            }
            $updateAccount = $account->update($currentAccount);

            $balanceAdjust = BalanceAdjust::query()->create($data);

            $transactionHistory['refference_id'] =  $balanceAdjust->id;

            if (!empty($transactionHistory)) {
                TransactionHistory::query()->create($transactionHistory);
            }

            DB::commit();

            return $updateAccount;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function getBalanceAdjustList(Request $request): JsonResponse
    {
        $searchKeyword = $request->input('search');

        $query = BalanceAdjust::query()
            ->leftJoin('users', 'balance_adjust.user_id', '=', 'users.id')
            ->where(function ($query) use ($searchKeyword) {
                $query->where(function ($q) use ($searchKeyword) {
                    $q->where('type', 'like', "%$searchKeyword%")
                        ->orWhere('users.full_name', 'like', "%$searchKeyword%")
                        ->orWhere('users.email', 'like', "%$searchKeyword%")
                        ->orWhere('users.phone', 'like', "%$searchKeyword%");
                });
            })
            ->select('balance_adjust.*', 'users.role_id', 'users.full_name', 'users.phone', 'users.email')->latest();

        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('user', function ($row) {
                $name = $row->full_name ?? '';
                $phone = $row->phone ?? '';
                $email = $row->email ?? '';
                $user = $name . '<br>' . $phone . '<br>' . $email;
                return $user;
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" data-id="' . $row->id . '" class="btn-show showBalanceAdjustBtn"><i class="fas fa-eye"></i><span class="btn-show-text"> Show</span></button>';
            })
            ->rawColumns(['user', 'action'])
            ->make(true);
    }
}
