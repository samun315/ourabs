<?php

namespace App\Http\Controllers\Marchant;

use App\Http\Controllers\Controller;
use App\Services\Marchant\EndUserBalanceRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EndUserBalanceRequestController extends Controller
{
    public function __construct(protected EndUserBalanceRequestService $endUserBalanceRequestService) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return $this->endUserBalanceRequestService->getRequestInfo($request);
        }
       
        return view('marchant.endUserBalanceRequest.index');
    }
}
