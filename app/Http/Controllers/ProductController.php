<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::all();
        return view('product.index', compact('products'));
    }

    public function checkout(Request $request)
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $cus = $stripe->customers->create([
            'name' => 'Kajal MondaL',
            'email' => 'kajalmondal148@gmail.com',
        ]);
        //   Log::info('Cus : '.print_r($cus,1));
        $products = Product::all();
        $line_items = [];
        $totalPrice = 0;
        foreach ($products as $product) {
            $totalPrice += $product->price * 100;
            $line_items[] = [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $product->name,
                            // 'image' => $product->image,
                        ],
                        'unit_amount' => $product->price * 100,
                    ],
                    'quantity' => 1,
                ]
            ];
        }
        $checkout_session = $stripe->checkout->sessions->create([
            'customer' => $cus,
            'line_items' => $line_items,
            'mode' => 'payment',
            'success_url' => route('checkout.success', [], true) . "?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => route('checkout.cancel', [], true),
        ]);
        $order = new Order();
        $order->status = 'unpaid';
        $order->total_price = $totalPrice;
        $order->seesion_id = $checkout_session->id;
        $order->save();

        return redirect($checkout_session->url);
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));

        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId);
            if (!$session) {
                throw new NotFoundHttpException;
            }
            $customer = $stripe->customers->retrieve($session->customer);
            $order = Order::where('seesion_id', $session->id)->first();
            if (!$order) {
                throw new NotFoundHttpException;
            }
            if($order->status === 'unpaid'){
                $order->status = 'paid';
                $order->save();
            }
            return view('product.checkout_success', compact('customer'));
        } catch (\Throwable $th) {
            throw new NotFoundHttpException;
        }
    }
    public function cancel() {}

    public function webhook()
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));

        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            return response('',400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('',400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $sessionId = $session->id;
                $order = Order::where('seesion_id', $session->id)->first();
                Log::info("Here I ");
                if ($order && $order->status == 'unpaid') {
                    $order->status = 'paid';
                    $order->save();
                }
                
            default:
                echo 'Received unknown event type ' . $event->type;
        }
        return response('',200);

    }
}
