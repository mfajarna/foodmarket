<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
}
