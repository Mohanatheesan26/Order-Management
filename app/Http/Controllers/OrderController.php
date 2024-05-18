<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'order_value' => 'required|numeric|min:0',
        ]);

        $order = new Order();
        $order->customer_name = $request->customer_name;
        $order->order_value = $request->order_value;
        $order->process_id = rand(1, 10); // Random Process ID between 1-10
        $order->order_status = 'Processing';
        $order->save();

        // Prepare data for the third-party API
        $data = [
            'Order_ID' => $order->id,
            'Customer_Name' => $order->customer_name,
            'Order_Value' => $order->order_value,
            'Order_Date' => $order->created_at->toDateTimeString(),
            'Order_Status' => $order->order_status,
            'Process_ID' => $order->process_id,
        ];

        // Send data to the third-party API
        Http::post('https://wibip.free.beeceptor.com/order', $data);

        return response()->json([
            'Order_ID' => $order->id,
            'Process_ID' => $order->process_id,
            'Status' => 'Order Created'
        ]);
    }
}
