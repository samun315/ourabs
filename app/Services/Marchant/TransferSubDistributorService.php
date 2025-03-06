<?php

namespace App\Services\Marchant;

use App\Models\Marchant\Transfer;
use App\Models\Payment\Account;
use App\Models\Report\TransactionHistory;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class TransferSubDistributorService
{
    public function getTransferBalanceInfo(Request $request): JsonResponse|Model|Builder
    {
        $searchKeyword = $request->input('search');

        $query = Transfer::query()
            ->leftJoin('users as fu', 'balance_transfers.transfer_from_user', '=', 'fu.id')
            ->leftJoin('users as tu', 'balance_transfers.transfer_to_user', '=', 'tu.id')
            ->where(function ($query) use ($searchKeyword) {
                $query->where(function ($q) use ($searchKeyword) {
                    $q->where('status', 'like', "%$searchKeyword%")
                        ->orWhere('fu.email', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('fu.full_name', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('tu.full_name', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('tu.email', 'like', '%' . $searchKeyword . '%');
                });
                if (Auth::user()->role_id == 3) {
                    $query->where(['balance_transfers.transfer_to_user' => loggedInUserId(), 'balance_transfers.role_id' => 3]);
                } else if (Auth::user()->role_id == 2) {
                    $query->where(['balance_transfers.created_by' => loggedInUserId(), 'balance_transfers.role_id' => 3]);
                }
            })
            ->select('balance_transfers.*', 'fu.role_id as fu_role', 'fu.full_name as fu_name', 'fu.phone as fu_phone', 'fu.email as fu_email', 'tu.role_id as tu_role', 'tu.full_name as tu_name', 'tu.phone as tu_phone', 'tu.email as tu_email')
            ->latest();

        // dd( $query);

        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('from_user', function ($row) {
                $from_name = $row->fu_name ?? '';
                $from_phone = $row->fu_phone ?? '';
                $from_email = $row->fu_email ?? '';
                $from_user = $from_name . '<br>' . $from_phone . '<br>' . $from_email;
                return $from_user;
            })
            ->addColumn('to_user', function ($row) {
                $to_name = $row->tu_name ?? '';
                $to_phone = $row->tu_phone ?? '';
                $to_email = $row->tu_email ?? '';
                $to_user = $to_name . '<br>' . $to_phone . '<br>' . $to_email;
                return $to_user;
            })
            ->addColumn('action', function ($row) {

                $showBtn = '<button data-id="' . $row->id . '" class="btn btn-success btn-sm ms-lg-2 showOrderBtn" data-bs-toggle="modal" data-bs-target="#showModal">Show</button>';

                return $showBtn;
            })
            ->rawColumns(['action', 'from_user', 'to_user'])
            ->make(true);
    }

    public function storeTransferBalance(array $data): Transfer|JsonResponse|bool
    {
        try {
            $data['transfer_from_user'] = $data['created_by'];
            $data['role_id'] = 3;
            $data['status'] = 'Transferred';

            $fromUserAccount = Account::query()->where('user_id', $data['transfer_from_user'])->first();
            $fromUserInfo = User::query()->where('id', $data['transfer_from_user'])->first();
            $toUserInfo = User::query()->where('id', $data['transfer_to_user'])->first();

            $transactionHistoryFromUser = [
                'user_id' => $data['transfer_from_user'],
                'type' => 'TRANSFER',
                'refference' => $fromUserInfo?->email . ' TO ' . $toUserInfo?->email,
                'amount' => $data['amount'],
                'before_balance' => $fromUserAccount?->current_balance,
                'refference_table' => 'balance_transfers',
                'created_by' => loggedInUserId(),
            ];

            $transactionHistoryToUser = [
                'user_id' => $data['transfer_to_user'],
                'type' => 'TRANSFER',
                'refference' => $toUserInfo?->email . ' FROM ' . $fromUserInfo?->email,
                'amount' => $data['amount'],
                'refference_table' => 'balance_transfers',
                'created_by' => loggedInUserId(),
            ];

            if ($fromUserAccount && $data['amount'] < $fromUserAccount->current_balance) {

                $from_user['current_balance'] = $fromUserAccount?->current_balance - $data['amount'];
                $from_user['updated_by'] = loggedInUserId();

                $transactionHistoryFromUser['after_balance'] = $from_user['current_balance'];

                $fromUserAccount->update($from_user);

                $toUserAccount = Account::query()->where('user_id', $data['transfer_to_user'])->first();

                if (!empty($toUserAccount)) {
                    $to_user['current_balance'] = $toUserAccount?->current_balance + $data['amount'];
                    $to_user['updated_by'] = loggedInUserId();

                    $transactionHistoryToUser['before_balance'] = $toUserAccount->current_balance;
                    $transactionHistoryToUser['after_balance'] = $to_user['current_balance'];

                    $toUserAccount->update($to_user);
                }

                $transfer =  Transfer::create($data);

                $transactionHistoryFromUser['refference_id'] = $transfer->id;
                $transactionHistoryToUser['refference_id'] =  $transfer->id;

                if (!empty($transactionHistoryFromUser)) {
                    TransactionHistory::query()->create($transactionHistoryFromUser);
                }

                if (!empty($transactionHistoryToUser)) {
                    TransactionHistory::query()->create($transactionHistoryToUser);
                }

                return $transfer;
            }

            return true;
        } catch (Exception $exception) {
            report($exception);
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }


    public function getAllTransferBalanceInfo(Request $request): JsonResponse|Model|Builder
    {
        $searchKeyword = $request->input('search');

        $query = Transfer::query()
            ->leftJoin('users as fu', 'balance_transfers.transfer_from_user', '=', 'fu.id')
            ->leftJoin('users as tu', 'balance_transfers.transfer_to_user', '=', 'tu.id')
            ->where(function ($query) use ($searchKeyword) {
                $query->where(function ($q) use ($searchKeyword) {
                    $q->where('status', 'like', "%$searchKeyword%")
                        ->orWhere('fu.email', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('fu.full_name', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('tu.full_name', 'like', '%' . $searchKeyword . '%')
                        ->orWhere('tu.email', 'like', '%' . $searchKeyword . '%');
                });
                $query->where(['balance_transfers.role_id' => 3]);
            })
            ->select('balance_transfers.*', 'fu.role_id as fu_role', 'fu.full_name as fu_name', 'fu.phone as fu_phone', 'fu.email as fu_email', 'tu.role_id as tu_role', 'tu.full_name as tu_name', 'tu.phone as tu_phone', 'tu.email as tu_email')
            ->latest();

        // dd( $query);

        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('from_user', function ($row) {
                $from_name = $row->fu_name ?? '';
                $from_phone = $row->fu_phone ?? '';
                $from_email = $row->fu_email ?? '';
                $from_user = $from_name . '<br>' . $from_phone . '<br>' . $from_email;
                return $from_user;
            })
            ->addColumn('to_user', function ($row) {
                $to_name = $row->tu_name ?? '';
                $to_phone = $row->tu_phone ?? '';
                $to_email = $row->tu_email ?? '';
                $to_user = $to_name . '<br>' . $to_phone . '<br>' . $to_email;
                return $to_user;
            })
            ->rawColumns(['from_user', 'to_user'])
            ->make(true);
    }
}
