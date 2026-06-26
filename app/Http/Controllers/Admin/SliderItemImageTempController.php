<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Services\ProductImageTempService;
use App\Services\SliderItemImageTempService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class SliderItemImageTempController extends Controller
{
    public function __construct(
        private SliderItemImageTempService $tempService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->can('slider_items.create') && ! $user->can('slider_items.update')) {
            abort(403);
        }

        $maxKb = (int) (ProductImageTempService::MAX_BYTES / 1024);
        $maxLabel = number_format(ProductImageTempService::MAX_BYTES / 1024 / 1024, 0).' MB';
        $sizeExceededMessage = __('Image size exceeds the maximum of :max.', ['max' => $maxLabel]);

        $this->assertValidImageUpload($request, $sizeExceededMessage);

        $validated = $request->validate([
            'image' => ['required', 'image', "max:{$maxKb}"],
        ], [
            'image.required' => __('Please choose an image to upload.'),
            'image.uploaded' => $sizeExceededMessage,
            'image.image' => __('Please upload a valid image file.'),
            'image.max' => $sizeExceededMessage,
        ]);

        $result = $this->tempService->store($validated['image'], $user);

        return ApiResponse::success($result);
    }

    public function destroy(Request $request, string $token): JsonResponse
    {
        $user = $request->user();
        if (! $user->can('slider_items.create') && ! $user->can('slider_items.update')) {
            abort(403);
        }

        $this->tempService->delete($token, $user);

        return ApiResponse::success(null, __('Image removed.'));
    }

    private function assertValidImageUpload(Request $request, string $sizeExceededMessage): void
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file instanceof UploadedFile && ! $file->isValid()) {
                $message = in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                    ? $sizeExceededMessage
                    : __('The image failed to upload. Please try again.');

                throw ValidationException::withMessages(['image' => [$message]]);
            }

            return;
        }

        if ($request->has('image')) {
            throw ValidationException::withMessages(['image' => [$sizeExceededMessage]]);
        }
    }
}
