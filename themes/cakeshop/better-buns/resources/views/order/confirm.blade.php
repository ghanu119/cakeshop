@extends('layouts.app')

@section('content')
    @include('order.partials._order-confirm-better-buns', ['order' => $order])
@endsection
