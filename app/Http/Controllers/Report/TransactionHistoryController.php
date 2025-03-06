<?php

namespace App\Http\Controllers\Report;

use App\Constant\Report\TransactionTypeConstant;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
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

        return view('report.transactionHistory.index',$data);
    }

}
