@php
    $appMessages = [
        'csrf_expired' => __('errors.csrf_expired'),
        'session_expired' => __('errors.session_expired'),
        'forbidden' => __('errors.forbidden'),
        'validation_failed' => __('errors.validation_failed'),
        'not_found' => __('errors.not_found'),
        'database' => __('errors.database'),
        'server' => __('errors.server'),
        'too_many_requests' => __('errors.too_many_requests'),
        'maintenance' => __('errors.maintenance'),
        'offline' => __('errors.offline'),
    ];
@endphp
<script>
    window.__appMessages = @json($appMessages);
</script>
