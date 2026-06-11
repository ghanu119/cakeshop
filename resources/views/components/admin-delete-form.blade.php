@props(['action', 'title', 'button' => __('Delete')])

<form
    method="post"
    action="{{ $action }}"
    class="inline"
    data-swal-confirm
    data-swal-confirm-variant="danger"
    data-swal-confirm-title="{{ $title }}"
    data-swal-confirm-yes="{{ __('Yes, delete') }}"
    data-swal-confirm-no="{{ __('Cancel') }}"
>
    @csrf
    @method('DELETE')
    <button type="submit" class="text-red-600 hover:text-red-800">{{ $button }}</button>
</form>
