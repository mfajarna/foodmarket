<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transactions;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;
use Sentry\Tracing\Transaction;

class transactionController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $food_id = $request->input('food_id');
        $status = $request->input('status');

      

        if($id){
            $transaction = Transactions::with(['food','user'])->find($id);

            if($transaction)
            {
                return ResponseFormatter::success(
                    $transaction,
                    'Data Transaksi berhasil diambil'
                );
            }
            else {
                return ResponseFormatter::error(
                    null,
                    'Data Transaksi tidak ada',
                    404
                );
            }
        }

        $transaction = Transactions::with(['food','user'])->where('user_id', Auth::user()->id); // memanggil saat user sedang login

        if($food_id)
        {
            $transaction->where('food_id', $food_id);
        }
        if($status)
        {
            $transaction->where('status', $status);
        }
       

        return ResponseFormatter::success(
            $transaction->paginate($limit),
            'Data List Transaksi Berhasil Diambil'
        );
    }

    public function update(Request $request, $id)
    {
        // Ambil data
        $transaction = Transactions::findOrFail($id);

        // Update Data
        $transaction->update($request->all());

        return ResponseFormatter::success($transaction, 'Transaksi Berhasil Diperbaharui');
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'food_id' => 'required|exists:food,id',
            'user_id' => 'required|exists:users,id',
            'quantity' => 'required',
            'total' => 'required',
            'status' => 'required'
        ]);

        $transaction = Transactions::create([
            'food_id' => $request->food_id,
            'user_id' => $request->user_id,
            'quantity' => $request->quantity,
            'total' => $request->total,
            'status' => $request->status,
            'payment_url' => '',
        ]);

        // Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        // Panggil Transaksi yang tadi dibuat
        $transaction = Transactions::with(['food','user'])->find($transaction->id);
        

        // Membuat Transaksi Midtrans

        $midtrans = [
            'transactions_details' => [
                'order_id' => $transaction->id,
                'gross_amount' => (int) $transaction->total,
            ],
            'costumer_details' => [
                'first_name' => $transaction->user->name,
                'email' => $transaction->user->email,
            ],
            'enabled_payments' => ['gopay','bank_transfer'],
            'vtweb' => []
        ];

        // Memanggil Midtrans
        try{
            // Ambil halaman payment midtrans
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;
            $transaction->payment_url = $paymentUrl;


            // Mengembalikan data ke API
            return ResponseFormatter::success($transaction, 'Transaksi Berhasil');
        }catch(Exception $err){
            return ResponseFormatter::error($err->getMessage(), 'Transaksi Gagal');
        }
      }
}
