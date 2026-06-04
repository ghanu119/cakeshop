<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductImageTempService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductImageTempController extends Controller
{
    public function __construct(
        private ProductImageTempService $tempService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->can('products.create') && ! $user->can('products.update')) {
            abort(403);
        }

        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $result = $this->tempService->store($validated['image'], $user);

        return response()->json($result);
    }

    public function destroy(Request $request, string $token): JsonResponse
    {
        $user = $request->user();
        if (! $user->can('products.create') && ! $user->can('products.update')) {
            abort(403);
        }

        $this->tempService->delete($token, $user);

        return response()->json(['ok' => true]);
    }
}
