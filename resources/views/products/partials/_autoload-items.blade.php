@foreach($products as $product)
    @include('products._card', ['product' => $product])
@endforeach
