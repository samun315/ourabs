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
            $data['status'] = 'Transferred';

            $fromUserInfo = User::query()->where('id', $data['user_id'])->first();

            $account = Account::query()->where('user_id', $data['user_id'])->first();
            
            if ($account && $data['amount'] <= $account->current_balance) {

                $from_user['current_balance'] = $account?->current_balance - $data['amount'];
                $from_user['updated_by'] = loggedInUserId();

                // generate uuid and store
                $uuidData['uuid'] = Str::uuid()->toString();

                Uuid::query()->create($uuidData);

                // set api parameter
                $params = [
                    'key'     => 'gMstVvRZ_tGG4QpBi410_1LTlsESyQcReqd0t9VLX-g',
                    'product' => 'imo',
                    'id'      => $data['imo_id'],
                    'user'    => 'sivarsivar648@gmail.com',
                    'pass'    => 'Abc@%23$000',
                    'amount'  => (int)$data['amount'],
                    'uuid'    => $uuidData['uuid'],
                ];

                // api url
                $url = 'https://private-anon-381aec7222-m7card.apiary-proxy.com/new?' . http_build_query($params);

                $getData = $this->fetchApiData($url);

                // make api diamond transaction data and store
                $result = json_decode($getData, true);
                $params['imo_id'] = $data['imo_id'];
                $params['order_id'] = $result['order_id'];
                $params['result'] = $result['result'];
                $params['api_key'] = 'gMstVvRZ_tGG4QpBi410_1LTlsESyQcReqd0t9VLX-g';
                $params['created_by'] = loggedInUserId();

                $diamondTransaction = ApiDiamondTransaction::query()->create($params);

                if ($result['result'] == 'success') {

                    $account->update($from_user);

                    $balance = EndUserBalance::query()->create($data);

                    // Transaction history make data
                    $transactionHistory = [
                        'user_id' => $data['user_id'],
                        'type' => 'REQUEST',
                        'refference' => $fromUserInfo?->email . ' TO ' . $data['imo_id'],
                        'amount' => $data['amount'],
                        'before_balance' => $account?->current_balance + $data['amount'],
                        'after_balance' => $account?->current_balance,
                        'refference_table' => 'end_user_balance_transfers',
                        'refference_id' => $balance->balance_transfer_id,
                        'created_by' => loggedInUserId(),
                    ];


                    if (!empty($transactionHistory)) {
                        TransactionHistory::query()->create($transactionHistory);
                    }
                }
            }

            DB::commit();

            return $balance;
        } catch (Exception $exception) {
            DB::rollBack();
            report($exception);
            return response()->json(['error' => 'Something went wrong'], 500);
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
