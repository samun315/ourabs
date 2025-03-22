<?php

namespace App\Http\Controllers\Report;

use App\Constant\Report\TransactionTypeConstant;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Common\Master\UserRole;
use App\Services\Report\TransactionHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TransactionHistoryController extends Controller
{
    public function __construct(protected TransactionHistoryService $transactionHistoryService) {}


    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('report.transaction.history.index');
        if ($request->ajax()) {
            return $this->transactionHistoryService->getTransactionHistoryList($request);
        }

        $data['transactionTypes'] = TransactionTypeConstant::TRANSACTION_TYPES;

        return view('report.transactionHistory.index', $data);
    }

    public function totalDiamond(Request $request): View|JsonResponse
    {
        Gate::authorize('report.total.diamond.index');

        $data['userRoles'] = UserRole::query()->whereNot('role_id', 1)->orderBy('role_id', 'ASC')->select(['role_id', 'role_name'])->get();

        if ($request->ajax()) {
            return $this->transactionHistoryService->getTotalDiamondList($request);
        }

        return view('report.diamond.index', $data);
    }

    public function getTransfer():View|JsonResponse
    {
        return view('report.transfer.index');
    }
}
