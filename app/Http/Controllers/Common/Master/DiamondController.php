<?php

namespace App\Http\Controllers\Common\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Common\Master\DiamondRequest;
use App\Services\Common\Master\DiamondService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DiamondController extends Controller
{
    public function __construct(protected DiamondService $diamondService)
    {
    }

    public function index(Request $request):View|JsonResponse
    {
        Gate::authorize('common.master.diamond.index');

        if ($request->ajax()) {
            return $this->diamondService->getDiamondList($request);
        }
        return view('common.master.diamond.index');
    }

    public function store(DiamondRequest $request): JsonResponse
    {
        Gate::authorize('common.master.diamond.create');
        try {

            $diamond = $this->diamondService->storeDiamond($request->fields());

            return sendSuccessResponse(
                200,
                'Diamond Stored successfully.',
                'data',
                $diamond
            );
        } catch (Exception $exception) {
            return sendErrorResponse('Internal Server Error: ', $exception->getMessage(), $exception->getCode() ?? 500);
        }
    }

    
    public function edit(int $diamondId): JsonResponse
    {
        Gate::authorize('common.master.diamond.edit');

        try {
            $diamondInfo = $this->diamondService->getDiamondInfoById($diamondId);

            return sendSuccessResponse(200, '', 'diamondInfo', $diamondInfo);
        } catch (Exception $exception) {
            return sendErrorResponse('Internal Server Error: ', $exception->getMessage(), $exception->getCode() ?? 500);
        }
    }

    public function update(DiamondRequest $request, int $diamondId): JsonResponse
    {
        Gate::authorize('common.master.diamond.edit');
        try {

            $diamond = $this->diamondService->updateDiamondInfo($request->fields(), $diamondId);

            return sendSuccessResponse(
                200,
                'Diamond updated successfully.',
                'data',
                $diamond
            );
        } catch (Exception $exception) {
            return sendErrorResponse('Internal Server Error: ', $exception->getMessage(), $exception->getCode() ?? 500);
        }
    }

    public function updateStatus(Request $request): JsonResponse
    {
        Gate::authorize('common.master.diamond.edit');
        // Get currency ID and status from the request
        $diamondId = $request->input('id');
        $active = $request->input('active');

        try {
            // Update the status of the currency
            $this->diamondService->updateDiamondStatus(['active' => $active], $diamondId);

            return sendSuccessResponse(200, 'Diamond status updated successfully.');
        } catch (Exception $e) {
            // Handle any exceptions during status update
            return sendErrorResponse('Internal Server Error: ', $e->getMessage(), $e->getCode() ?? 500);
        }
    }
}
