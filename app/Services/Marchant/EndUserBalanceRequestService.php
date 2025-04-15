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
            $data['status'] = 'Processing';

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
                $params['created_by'] = loggedInUserId();

                $diamondTransaction = ApiDiamondTransaction::query()->create($params);

                if ($result['result'] == 'success') {

                    $account->update($from_user);
                    $data['order_id'] = $result['order_id'];

                    $balance = EndUserBalance::query()->create($data);
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
