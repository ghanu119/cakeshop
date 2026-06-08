@php
    $variant = $variant ?? 'default';
    $colors = match ($variant) {
        'success' => ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#bbf7d0'],
        'warning' => ['bg' => '#fef3c7', 'text' => '#92400e', 'border' => '#fde68a'],
        'danger' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'border' => '#fecaca'],
        'primary' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'border' => '#bfdbfe'],
        default => ['bg' => '#f5f5f4', 'text' => '#44403c', 'border' => '#e7e5e4'],
    };
@endphp
<span style="display:inline-block;padding:4px 12px;border-radius:9999px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;background-color:{{ $colors['bg'] }};color:{{ $colors['text'] }};border:1px solid {{ $colors['border'] }};">
    {{ $label }}
</span>
