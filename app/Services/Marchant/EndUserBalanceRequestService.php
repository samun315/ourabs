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
            $userId = $data['request_by'];
            $amount = (int) $data['amount'];

            $fromUserInfo = User::query()->where('id', $userId)->first();

            $account = Account::query()->where('user_id', $userId)->first();

            if (!$account || $amount > $account->current_balance) {
                return response()->json([
                    'error' => 'Insufficient balance'
                ], 400);
            }

            $url = "https://bot.ahlna.co/api/orders/create/";

            $headers = [
                "accept: application/json",
                "Authorization: Api-Key FT4TQAm_QWBTraJcAkS42KNE15x2jIO551-FXTetrgU",
                "Content-Type: application/json",
                "X-CSRFTOKEN: H8R9XdGUyeQw2BPIE5VYbt1toezPY2Pr5F8uhIpMQnTqf0d5Qj1cQxQGJo415cF3"
            ];
            // Generate UUID
            $uuid = Str::uuid()->toString();
            Uuid::create(['uuid' => $uuid]);

            // Calculate quantity safely
            if ($amount % 10 !== 0) {
                return response()->json([
                    'error' => 'Amount must be divisible by 10'
                ], 400);
            }

            //$quantity = $amount / 10;
            $quantity = 1;

            $productIdMap = [
                1 => "316b285c-eb4c-4d7b-b302-0715a3e41c1a",
                10 => "4cfa4c27-d4a5-41bc-91e0-459547d393f7",
                50 => "a195345c-7a8b-4659-b28a-ae6cd7ab01db",
                100 => "422b5c0d-f1bd-4704-a8e4-87095868cb1c",
                200 => "28e04953-4965-4db2-aa58-6c58a72127d0",
                500 => "ea823835-85c3-43ef-a258-a9248ee6c3bd",
                1000 => "cc60a49d-e80a-4dcb-9a18-f870aefd5ba0",
                2000 => "f45d4fb4-8d2a-45d7-8a14-c059222472fd",
                5000 => "58c0d287-6185-4e93-830c-e13366f26f99",
                10000 => "4dcf4b3e-484e-43c2-aa4b-9db59fb968cc",
                20000 => "922f7554-5775-435d-93f5-9c2a7f7ae5d5",
            ];

            $productId = $productIdMap[$amount];

            // Prepare API request
            $apiPayload = [
                "items" => [
                    [
                        "product_id" => $productId,
                        "quantity"   => $quantity,
                        "id_user"    => $data['imo_id'],
                        "amount"     => 10
                    ]
                ]
            ];

            $response = $this->fetchApiData(
                $url,
                $apiPayload,
                $headers,
                'POST'
            );

            $result = json_decode($response, true);

            if (!isset($result['order_id']) || !isset($result['status'])) {
                DB::rollBack();
                return response()->json([
                    'error' => 'API transaction failed',
                    'response' => $result
                ], 400);
            }

            // Deduct balance
            $account->update([
                'current_balance' => $account->current_balance - $amount,
                'updated_by' => loggedInUserId()
            ]);

            // Store API transaction
            ApiDiamondTransaction::create([
                'imo_id'     => $data['imo_id'],
                'product' => 'imo',
                'uuid'    => $uuid,
                'user'    => 'soft.technology2024@gmail.com',
                'pass'    => 'Ahnaf2018$',
                'amount'  => $amount,
                'order_id'   => $result['order_id'],
                'result'     => $result['status'],
                'api_key'    => 'FT4TQAm_QWBTraJcAkS42KNE15x2jIO551-FXTetrgU',
                'created_by' => loggedInUserId()
            ]);

            // Store user balance history
            $balance = EndUserBalance::create([
                ...$data,
                'user_id'  => $userId,
                'status'   => 'Processing',
                'order_id' => $result['order_id']
            ]);

            DB::commit();

            return $balance;
        } catch (Exception $exception) {
            DB::rollBack();
            report($exception);
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    //**************start for old api*************//
    // public function storeBalance(array $data): Model|JsonResponse
    // {
    //     DB::beginTransaction();
    //     try {
    //         $data['user_id'] = $data['request_by'];
    //         $data['status'] = 'Processing';

    //         $fromUserInfo = User::query()->where('id', $data['user_id'])->first();

    //         $account = Account::query()->where('user_id', $data['user_id'])->first();

    //         if ($account && $data['amount'] <= $account->current_balance) {

    //             $from_user['current_balance'] = $account?->current_balance - $data['amount'];
    //             $from_user['updated_by'] = loggedInUserId();

    //             // generate uuid and store
    //             $uuidData['uuid'] = Str::uuid()->toString();

    //             Uuid::query()->create($uuidData);

    //             // set api parameter
    //             $params = [
    //                 'key'     => '_vrjxzntE5mSBSKYVQPRZrLuN82ntTeEhlaF_CwtYdw',
    //                 'product' => 'imo',
    //                 'id'      => $data['imo_id'],
    //                 'user'    => 'soft.technology2024@gmail.com',
    //                 'pass'    => 'Ahnaf2018$',
    //                 'amount'  => (int)$data['amount'],
    //                 'uuid'    => $uuidData['uuid'],
    //             ];

    //             // api url
    //             $url = 'https://www.m7-sys.com/new?' . http_build_query($params);

    //             $getData = $this->fetchApiData($url);

    //             // make api diamond transaction data and store
    //             $result = json_decode($getData, true);
    //             $params['imo_id'] = $data['imo_id'];
    //             $params['order_id'] = $result['order_id'];
    //             $params['result'] = $result['result'];
    //             $params['api_key'] = '_vrjxzntE5mSBSKYVQPRZrLuN82ntTeEhlaF_CwtYdw';
    //             $params['created_by'] = loggedInUserId();

    //             $diamondTransaction = ApiDiamondTransaction::query()->create($params);

    //             if ($result['result'] == 'success') {

    //                 $account->update($from_user);
    //                 $data['order_id'] = $result['order_id'];

    //                 $balance = EndUserBalance::query()->create($data);
    //             }
    //         }

    //         DB::commit();

    //         return $balance;
    //     } catch (Exception $exception) {
    //         DB::rollBack();
    //         report($exception);
    //         return response()->json(['error' => 'Something went wrong'], 500);
    //     }
    // }
    //**************end for old api*************//

    public function storeBalanceFromApi(array $data): Model|JsonResponse|array
    {
        DB::beginTransaction();
        try {
            $data['user_id'] = $data['request_by'];
            $data['status'] = 'Processing';

            $fromUserInfo = User::query()->where('id', $data['user_id'])->first();

            $account = Account::query()->where('user_id', $data['user_id'])->first();

            if ($account && $data['amount'] <= $account->current_balance) {

                $from_user['current_balance'] = $account?->current_balance - $data['amount'];
                $from_user['updated_by'] = $data['user_id'];

                // generate uuid and store
                $uuidData['uuid'] = Str::uuid()->toString();

                Uuid::query()->create($uuidData);

                // set api parameter
                $params = [
                    'key'     => '_vrjxzntE5mSBSKYVQPRZrLuN82ntTeEhlaF_CwtYdw',
                    'product' => 'imo',
                    'id'      => $data['imo_id'],
                    'user'    => 'soft.technology2024@gmail.com',
                    'pass'    => 'Ahnaf2018$',
                    'amount'  => (int)$data['amount'],
                    'uuid'    => $uuidData['uuid'],
                ];

                // api url
                $url = 'https://www.m7-sys.com/new?' . http_build_query($params);

                $getData = $this->fetchApiData($url);

                // make api diamond transaction data and store
                $result = json_decode($getData, true);
                $params['imo_id'] = $data['imo_id'];
                $params['order_id'] = $result['order_id'];
                $params['result'] = $result['result'];
                $params['api_key'] = '_vrjxzntE5mSBSKYVQPRZrLuN82ntTeEhlaF_CwtYdw';
                $params['created_by'] = $data['user_id'];

                $diamondTransaction = ApiDiamondTransaction::query()->create($params);

                if ($result['result'] == 'success') {

                    $account->update($from_user);
                    $data['order_id'] = $result['order_id'];

                    $balance = EndUserBalance::query()->create($data);

                    $result['result'] = 'Processing';
                }
            }

            DB::commit();

            return $result;
        } catch (Exception $exception) {
            DB::rollBack();
            report($exception);
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    // public function fetchApiData(string $url): string
    // {
    //     $ch = curl_init();

    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    //     curl_setopt($ch, CURLOPT_HEADER, FALSE);

    //     $response = curl_exec($ch);
    //     curl_close($ch);

    //     return $response;
    // }

    public function fetchApiData(string $url, array $data = [], array $headers = [], string $method = 'GET'): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($method === 'POST' && !empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return "Curl Error: " . $error;
        }

        curl_close($ch);

        return $response;
    }
}
