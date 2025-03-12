<?php

namespace App\Services\Marchant;

use App\Http\Requests\Marchant\EndUserBalanceRequest;
use App\Models\Marchant\ApiDiamondTransaction;
use App\Models\Marchant\BalanceRequest;
use App\Models\Marchant\EndUserBalance;
use App\Models\Marchant\RequestWhitelist;
use App\Models\Marchant\Uuid;
use App\Models\Payment\Account;
use App\Models\Report\TransactionHistory;
use App\Models\User;
use Exception;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class EndUserBalanceApproveService
{
    public function getBalanceInfo(Request $request): JsonResponse|Model|Builder
    {
        $searchKeyword = $request->input('search');

        $query = EndUserBalance::query()
            ->leftJoin('users', 'end_user_balance_transfers.request_by', '=', 'users.id')
            ->leftJoin('user_roles', 'users.role_id', '=', 'user_roles.role_id')
            ->where(function ($query) use ($searchKeyword) {
                $query->where(function ($q) use ($searchKeyword) {
                    $q->where('end_user_balance_transfers.phone_number', 'like', "%$searchKeyword%")
                        ->orWhere('end_user_balance_transfers.imo_id', 'like', "%$searchKeyword%")
                        ->orWhere('end_user_balance_transfers.amount', 'like', "%$searchKeyword%")
                        ->orWhere('end_user_balance_transfers.status', 'like', "%$searchKeyword%");
                });
            })->select('end_user_balance_transfers.*', 'users.role_id', 'users.full_name', 'users.phone', 'users.email', 'user_roles.role_name')
            ->latest();

        return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('user', function ($row) {
                $name = $row->full_name ?? '';
                $phone = $row->phone ?? '';
                $email = $row->email ?? '';
                $role = $row->role_name ?? '';
                $user = $name . '<br>' . $phone . '<br>' . $email . '<br> <p class="badge badge-success ">' . $role . '</p>';
                return $user;
            })
            // ->addColumn('action', function ($row) {
            //     $transferBtn = '';
            //     $cancelBtn = '';

            //     if ($row->status == 'Transferred' || $row->status == 'Cancelled') {
            //         $transferBtn = '';
            //         $cancelBtn = '';
            //     } else {
            //         $transferBtn = '<button type="button" data-id="' . $row->balance_transfer_id . '"  data-status="Transferred" class="btn btn-success btn-sm ms-lg-2 transferredBtn">Transfer</button>';
            //         $cancelBtn = '<button type="button" data-id="' . $row->balance_transfer_id . '" data-status="Cancelled" class="btn btn-danger btn-sm ms-lg-2 cancelledBtn">Cancel</button>';
            //     }

            //     $button = '<div class="btn-group" role="group" aria-label="Basic example">
            //     ' . $transferBtn . '
            //     ' . $cancelBtn . '
            //     </div>';
            //     return $button;
            // })
            ->rawColumns([ 'user'])
            ->make(true);
    }

    public function updateBalanceRequestStatus(Request $request, int $id): Model|Builder|bool
    {
        DB::beginTransaction();
        try {

            // Find the whitelist by its ID or fail if not found.
            $balance = EndUserBalance::query()->where('balance_transfer_id', $id)->first();

            $fromUserInfo = User::query()->where('id', $balance?->request_by)->first();

            $account = Account::query()->where('user_id', $balance?->request_by)->first();

            if (!empty($balance)) {
                $givenAmount = $balance->amount;
                $fromUser = $balance->request_by;

                $requestStatus = [
                    'status' => $request->input('status'),
                    'updated_by' => loggedInUserId(),
                ];

                $uuidData['uuid'] = Str::uuid()->toString();

                Uuid::query()->create($uuidData);

                if ($request->input('status') == 'Transferred') {

                    $params = [
                        'key'     => 'gMstVvRZ_tGG4QpBi410_1LTlsESyQcReqd0t9VLX-g',
                        'product' => 'imo',
                        'id'      => $balance?->imo_id,
                        'user'    => 'sivarsivar648@gmail.com',
                        'pass'    => 'Abc@%23$000',
                        'amount'  => (int)$balance?->amount,
                        'uuid'    => $uuidData['uuid'],
                    ];

                    $url = 'https://private-anon-381aec7222-m7card.apiary-proxy.com/new?' . http_build_query($params);

                    $getData = $this->fetchApiData($url);

                    $result = json_decode($getData, true);
                    $params['imo_id'] = $balance?->imo_id;
                    $params['order_id'] = $result['order_id'];
                    $params['result'] = $result['result'];
                    $params['api_key'] = 'gMstVvRZ_tGG4QpBi410_1LTlsESyQcReqd0t9VLX-g';
                    $params['created_by'] = loggedInUserId();

                    $diamondTransaction = ApiDiamondTransaction::query()->create($params);

                    if ($result['result'] == 'success') {
                        // Transaction history make data
                        $transactionHistory = [
                            'user_id' => $balance?->request_by,
                            'type' => 'REQUEST',
                            'refference' => $fromUserInfo?->email . ' TO ' . $balance?->imo_id,
                            'amount' => $balance?->amount,
                            'before_balance' => $account?->current_balance + $balance?->amount,
                            'after_balance' => $account?->current_balance,
                            'refference_table' => 'end_user_balance_transfers',
                            'refference_id' => $id,
                            'created_by' => loggedInUserId(),
                        ];

                        if (!empty($transactionHistory)) {
                            TransactionHistory::query()->create($transactionHistory);
                        }

                        // Update the whitelist with the provided data.
                        $balance->update($requestStatus);
                    }
                } else {
                    $fromUserAccount = Account::query()->where('user_id', $fromUser)->first();

                    if (!empty($fromUserAccount)) {
                        $from_user['current_balance'] = $fromUserAccount?->current_balance + $givenAmount;
                        $from_user['updated_by'] = loggedInUserId();

                        $fromUserAccount->update($from_user);
                    }
                    $balance->update($requestStatus);
                }
            }

            DB::commit();

            return $balance;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function fetchApiData(string $url): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
