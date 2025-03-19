<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PaymentGatewayRequest;
use App\Models\Common\Master\Currency;
use App\Models\Payment\PaymentGateway;
use App\Services\Payment\PaymentGatewayService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PaymentGatewayController extends Controller
{

    public function __construct(protected PaymentGatewayService $paymentGatewayService) {}

    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('payment.manual.gateway.index');

        if ($request->ajax()) {
            return $this->paymentGatewayService->getPaymentGateway($request);
        }

        $data['currencyCodes'] = Currency::query()->where('active', 'YES')->get();

        return view('payment.paymentGateway.index', $data);
    }

    public function store(PaymentGatewayRequest $request): JsonResponse
    {
        Gate::authorize('payment.manual.gateway.index');

        try {

            $storeGatewayInfo = $this->paymentGatewayService->storeGateway($request->fields());

            return sendSuccessResponse(
                200,
                'Payment Gateway Stored successfully.',
                'data',
                $storeGatewayInfo
            );
        } catch (Exception $exception) {
            return sendErrorResponse('Internal Server Error: ', $exception->getMessage(), $exception->getCode() ?? 500);
        }
    }

    public function getGatewayDetails(int $id): JsonResponse
    {
        try {
            $gatewayInfo = PaymentGateway::query()->where('id',$id)->first();

            return sendSuccessResponse(200, '', 'gatewayInfo', $gatewayInfo);
        } catch (Exception $exception) {
            return sendErrorResponse('Internal Server Error: ', $exception->getMessage(), $exception->getCode() ?? 500);
        }
    }

    public function edit(int $id): JsonResponse
    {
        Gate::authorize('payment.manual.gateway.edit');

        try {
            $gatewayInfo = PaymentGateway::query()->where('id',$id)->first();

            return sendSuccessResponse(200, '', 'gatewayInfo', $gatewayInfo);
        } catch (Exception $exception) {
            return sendErrorResponse('Internal Server Error: ', $exception->getMessage(), $exception->getCode() ?? 500);
        }
    }

    public function update(PaymentGatewayRequest $request, int $gatewayId): JsonResponse
    {
        Gate::authorize('payment.manual.gateway.edit');

        try {

            $updateGatewayInfo = $this->paymentGatewayService->updateGatewayInfo($request->fields(), $gatewayId);

            return sendSuccessResponse(
                200,
                'Payment Gateway updated successfully.',
                'data',
                $updateGatewayInfo
            );
        } catch (Exception $exception) {
            return sendErrorResponse('Internal Server Error: ', $exception->getMessage(), $exception->getCode() ?? 500);
        }
    }


    public function destroy(int $gatewayId): JsonResponse|bool
    {
        Gate::authorize('payment.manual.gateway.destroy');

        try {
            $gatewayItem = PaymentGateway::query()->where('id', $gatewayId)->first();

            if (!empty($gatewayItem)) {
                $deleteItem = $gatewayItem->delete();
                return sendSuccessResponse(200, 'Successfully deleted gateway', 'data', $deleteItem);
            }
            return true;
        } catch (Exception $exception) {
            return sendErrorResponse('Internal Server Error: ', $exception->getMessage(), $exception->getCode() ?? 500);
        }
    }


    public function updateStatus(Request $request): JsonResponse
    {

        $paymentGatewayId = $request->input('id');
        $active = $request->input('active');

        try {

            $this->paymentGatewayService->updatePaymentGatewayStatus(['active' => $active], $paymentGatewayId);

            return sendSuccessResponse(200, 'Payment Gateway status updated successfolly.');
        } catch (Exception $e) {
            return sendErrorResponse('Internal Server Error: ', $e->getMessage(), $e->getCode() ?? 500);
        }
    }

}
