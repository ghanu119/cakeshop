<?php

namespace App\Http\Middleware;

use App\Services\CustomerContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerAuthenticated
{
    public function __construct(
        private CustomerContext $customerContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->customerContext->effectiveCustomer() !== null) {
            return $next($request);
        }

        return redirect()->guest(route('home', ['auth' => 1]));
    }
}
