<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Prepare data for the third-party API
        $data = [
            'Order_ID' => $this->order->id,
            'Customer_Name' => $this->order->customer_name,
            'Order_Value' => $this->order->order_value,
            'Order_Date' => $this->order->created_at->toDateTimeString(),
            'Order_Status' => $this->order->order_status,
            'Process_ID' => $this->order->process_id,
        ];

        try {
            // Send data to the third-party API
            $response = Http::withOptions(['verify' => false])->post('https://wibip.free.beeceptor.com/order', $data);

            if ($response->failed()) {
                // Log the failure
                Log::error('Failed to send order to third-party API', ['order' => $this->order, 'response' => $response->body()]);
            }
        } catch (\Exception $e) {
            // Log any exception that occurs
            Log::error('Exception occurred while sending order to third-party API', ['order' => $this->order, 'error' => $e->getMessage()]);
        }
    }
}
