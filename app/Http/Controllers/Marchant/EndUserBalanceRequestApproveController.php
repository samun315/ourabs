<?php

namespace App\Http\Controllers\Marchant;

use App\Http\Controllers\Controller;
use App\Services\Marchant\EndUserBalanceApproveService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EndUserBalanceRequestApproveController extends Controller
{
    public function __construct(protected EndUserBalanceApproveService $endUserBalanceApproveService) {}

    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('marchant.sub-distributor.balance.request.approve.index');
        if ($request->ajax()) {
            return $this->endUserBalanceApproveService->getBalanceInfo($request);
        }
       
        return view('marchant.endUserBalanceApprove.index');
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        Gate::authorize('marchant.sub-distributor.balance.request.approve.index');
   
        try {
            // Update the status of the currency
            $whitelist = $this->endUserBalanceApproveService->updateBalanceRequestStatus($request, $id);

            return sendSuccessResponse(200, 'Balance status updated successfully.');
        } catch (Exception $e) {
            // Handle any exceptions during status update
            return sendErrorResponse('Internal Server Error: ', $e->getMessage(), $e->getCode() ?? 500);
        }
    }
}
