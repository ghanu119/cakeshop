<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckPincodeRequest;
use App\Services\ServiceablePincodeService;
use Illuminate\Http\JsonResponse;

class PincodeCheckController extends Controller
{
    public function __construct(
        private ServiceablePincodeService $pincodeService
    ) {}

    public function check(CheckPincodeRequest $request): JsonResponse
    {
        $result = $this->pincodeService->checkResponse($request->validated('pincode'));

        return response()->json($result);
    }
}
