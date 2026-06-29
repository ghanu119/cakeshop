@if($storefrontPromoCoupon ?? null)
    <div class="border-b border-amber-200 bg-amber-50 px-4 py-2.5 text-center text-sm text-amber-900">
        <span class="font-semibold">{{ $storefrontPromoCoupon->label }}:</span>
        {{ $storefrontPromoBadge }} {{ __('on eligible orders') }}
    </div>
@endif
