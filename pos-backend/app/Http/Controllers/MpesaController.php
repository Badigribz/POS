<?php

namespace App\Http\Controllers;

use App\Services\MpesaService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class MpesaController extends Controller
{
    public function stkPush(Request $request, MpesaService $mpesa)
    {
        $request->validate([
            'phone' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        $response = $mpesa->stkPush($request->phone, $request->amount);

        return response()->json($response);
    }

    public function callback(Request $request)
    {
        Log::info('M-PESA Callback:', $request->all());
        return response()->json(['status' => 'success']);
    }
}
