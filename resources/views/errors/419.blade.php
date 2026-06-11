@include('errors._layout', [
    'title' => __('errors.csrf_expired'),
    'actionUrl' => url()->current(),
    'actionLabel' => __('errors.refresh_page'),
])
