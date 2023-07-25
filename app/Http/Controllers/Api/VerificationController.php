<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VerificationController extends Controller
{
    public function sendingSmsCodeToPhone(Request $request)
    {
        $data = $request->validate([
            'phone_code' => ['bail', 'required', 'regex:/^\+\d{1,5}$/'],
            'phone_number' => ['bail', 'required', 'numeric', 'max_digits:15'],
        ]);

        $phone = $data['phone_code'] . $data['phone_number'];

        $code = mt_rand(100000, 999999);

        Cache::put("sms_code_$phone", $code, now()->addMinutes(15));

        return response()->json([
            'code' => $code,
        ]);

//        $request = Http::get('https://smsc.ru/sys/send.php', [
//            'login' => config('smsc.login'),
//            'psw' => config('smsc.password'),
//            'phones' => $phone,
//            'mes' => "Your verification code is $code"
//        ]);
//
//        return response()->json([
//            $request->body()
//        ]);
    }
}
