<?php

namespace App\Http\Controllers\Marchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marchant\EndUserBalanceRequest;
use App\Models\Common\Master\Diamond;
use App\Models\Payment\Account;
use App\Services\Marchant\EndUserBalanceRequestService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EndUserBalanceRequestController extends Controller
{
    public function __construct(protected EndUserBalanceRequestService $endUserBalanceRequestService) {}

    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('marchant.sub-distributor.balance.request.index');

        if ($request->ajax()) {
            return $this->endUserBalanceRequestService->getRequestInfo($request);
        }

        return view('marchant.endUserBalanceRequest.index');
    }

    public function create(): View
    {
        Gate::authorize('marchant.sub-distributor.balance.request.create');

        $userId = Auth::user()->id;
        $data['account'] = Account::query()->where('user_id', $userId)->first();
        $data['diamonds'] = Diamond::query()->where('active', 'YES')->get();

        return view('marchant.endUserBalanceRequest.create', $data);
    }

    public function store(EndUserBalanceRequest $request): RedirectResponse
    {
        Gate::authorize('marchant.sub-distributor.balance.request.create');
        try {

            $storeUserInfo = $this->endUserBalanceRequestService->storeBalance($request->fields());

            return to_route('marchant.sub-distributor.balance.request.index')->with(
                'success',
                'Balance Stored successfully.'
            );
        } catch (Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
