<?php

namespace App\Services;

use App\Models\ServiceablePincode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServiceablePincodeService
{
    public const CACHE_KEY = 'serviceable_pincodes:active';

    public function normalize(string $raw): ?string
    {
        $digits = preg_replace('/\D/', '', $raw) ?? '';

        if (strlen($digits) !== 6) {
            return null;
        }

        return $digits;
    }

    public function isServiceable(string $raw): bool
    {
        $normalized = $this->normalize($raw);

        if ($normalized === null) {
            return false;
        }

        return array_key_exists($normalized, $this->activePincodeMap());
    }

    public function lookup(string $raw): ?ServiceablePincode
    {
        $normalized = $this->normalize($raw);

        if ($normalized === null) {
            return null;
        }

        return ServiceablePincode::query()
            ->active()
            ->forPincode($normalized)
            ->first();
    }

    /**
     * @return array<string, array{locality: string|null, city: string}>
     */
    public function activePincodeMap(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return ServiceablePincode::query()
                ->active()
                ->orderBy('pincode')
                ->get()
                ->mapWithKeys(fn (ServiceablePincode $row) => [
                    $row->pincode => [
                        'locality' => $row->locality,
                        'city' => $row->city,
                    ],
                ])
                ->all();
        });
    }

    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function list(Request $request): LengthAwarePaginator
    {
        $query = ServiceablePincode::query();

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('city')) {
            $query->where('city', $request->input('city'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return $query->orderBy('pincode')->paginate(20)->withQueryString();
    }

    public function createOrUpdate(?ServiceablePincode $pincode, array $data): ServiceablePincode
    {
        $pincode = $pincode ?? new ServiceablePincode;

        $normalized = $this->normalize($data['pincode'] ?? '');
        if ($normalized === null) {
            throw new \InvalidArgumentException(__('Pincode must be exactly 6 digits.'));
        }

        $pincode->pincode = $normalized;
        $pincode->locality = $data['locality'] ?? null;
        $pincode->city = $data['city'] ?? 'Rajkot';
        $pincode->state = $data['state'] ?? 'Gujarat';
        $pincode->is_active = (bool) ($data['is_active'] ?? true);
        $pincode->save();

        $this->flushCache();

        return $pincode;
    }

    public function delete(ServiceablePincode $pincode): void
    {
        $pincode->delete();
        $this->flushCache();
    }

    /**
     * @return array{serviceable: bool, locality: string|null, city: string|null, message: string}
     */
    public function checkResponse(string $raw): array
    {
        $normalized = $this->normalize($raw);

        if ($normalized === null) {
            return [
                'serviceable' => false,
                'locality' => null,
                'city' => null,
                'message' => __('Please enter a valid 6-digit pincode.'),
            ];
        }

        $record = $this->lookup($normalized);

        if ($record === null) {
            return [
                'serviceable' => false,
                'locality' => null,
                'city' => null,
                'message' => __('Sorry, we do not deliver to this pincode yet. Please choose Take away or contact us.'),
            ];
        }

        $locality = $record->locality;
        $city = $record->city;

        return [
            'serviceable' => true,
            'locality' => $locality,
            'city' => $city,
            'message' => $locality
                ? __('Delivering to :locality, :city', ['locality' => $locality, 'city' => $city])
                : __('Delivery available in :city', ['city' => $city]),
        ];
    }
}
