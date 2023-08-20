<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\CustomerLoginRequest;
use App\Http\Requests\Api\Auth\CustomerRegisterRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;

class CustomerAuthController extends Controller
{
    public function register(CustomerRegisterRequest $request)
    {
        $customer = Customer::create([
            'store_id' => Store::current()->id,
            'phone' => $request->get('phone'),
        ]);

        return response()->json([
            'data' => new CustomerResource($customer),
            'token' => $customer->createToken(config('app.name'))->accessToken,
        ]);
    }

    public function login(CustomerLoginRequest $request)
    {
        $customer = Customer::wherePhone($request->get('phone'))
            ->whereStoreId(Store::current()->id)
            ->whereActive(true)
            ->first();

        if ($customer) {
            return response()->json([
                'data' => new CustomerResource($customer),
                'token' => $customer->createToken(config('app.name'))->accessToken
            ]);
        }

        return response()->json(['message' => __('validation2.auth.text1')], 401);
    }

    public function logout()
    {
        /** @var Customer $customer */
        $customer = Auth::user();

        $customer->tokens()->delete();

        return response()->json();
    }
}
