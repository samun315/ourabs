<?php

namespace App\Http\Controllers\Marchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marchant\TransferRequest;
use App\Models\Payment\Account;
use App\Models\User;
use App\Services\Marchant\TransferService;
use App\Services\Marchant\TransferSubDistributorService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransferSubDistributorController extends Controller
{
    public function __construct(protected TransferSubDistributorService $transferSubDistributorService) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->transferSubDistributorService->getTransferBalanceInfo($request);
        }
        $userRole = 3;
        $userId = Auth::user()->id;

        $data['emails'] = User::where('role_id', $userRole)
            ->where('id', '!=', $userId)
            ->get();

        $data['account'] = Account::query()->where('user_id', $userId)->first();

        return view('marchant.transferSubDistributor.index', $data);
    }

    public function store(TransferRequest $request): JsonResponse
    {
        try {

            $storeUserInfo = $this->transferSubDistributorService->storeTransferBalance($request->fields());

            if ($storeUserInfo) {
                return sendSuccessResponse(
                    200,
                    'Transfer Balance Created successfully.',
                    'data',
                    $storeUserInfo
                );
            } else {
                return sendErrorResponse('Insufficient balance to your account', '', 500);
            }
        } catch (Exception $exception) {
            return sendErrorResponse('Internal Server Error: ', $exception->getMessage(), $exception->getCode() ?? 500);
        }
    }

    public function allTransfer(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->transferSubDistributorService->getAllTransferBalanceInfo($request);
        }
  
        return view('marchant.transferSubDistributorApprove.index');
    }
}
