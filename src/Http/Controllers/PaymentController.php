<?php

namespace Webkul\Stripe\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Stripe\Checkout\Session;
use Stripe\Refund;
use Stripe\Stripe;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     *
     * @var OrderRepository
     * @var InvoiceRepository
     */
    public function __construct(
        protected OrderRepository   $orderRepository,
        protected InvoiceRepository $invoiceRepository,
    )
    {
        //
    }

    protected function initStripeSdk()
    {
        $cart = Cart::getCart();
        $stripeProviderClass = app(Config::get('payment_methods.stripe.class'));
        $key = $stripeProviderClass->getConfigData('api_key');
        $sandboxUserIds = explode(",", $stripeProviderClass->getConfigData('sandbox_allowed_users') ?? "");
        if ($stripeProviderClass->getConfigData('sandbox') || in_array($cart->customer_id, $sandboxUserIds)) {
            $key = $stripeProviderClass->getConfigData('sandbox_api_key');
        }
        Stripe::setApiKey($key);
    }

    /**
     * Redirects to the Stripe server.
     */
    public function redirect(): RedirectResponse
    {
        try {

            $this->validateOrder();
            $this->initStripeSdk();
            $cart = Cart::getCart();
            $checkoutSession = Session::create([
                'line_items' => [[
                    'price_data' => [
                        'currency' => $cart->global_currency_code,
                        'product_data' => [
                            'name' => 'Stripe Checkout Payment order id - ' . $cart->id,
                        ],
                        'unit_amount' => $cart->grand_total * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('stripe.success', ['cartId' => $cart->id]),
                'cancel_url' => route('stripe.cancel'),
                'metadata' => [
                    'cart_id' => $cart->id,
                    "customer_email" => $cart->customer_email,
                    "customer_first_name" => $cart->customer_first_name,
                    "customer_last_name" => $cart->customer_last_name,
                    "customer_id" => $cart->customer_id,
                ]
            ]);
            session(['checkout_' . $cart->id => $checkoutSession->id]);
            Cart::deActivateCart();
            return redirect()->away($checkoutSession->url);
        } catch (\Exception $exception) {
            session()->flash('error', $exception->getMessage());
            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Place an order and redirect to the success page.
     */
    public function success($cartId, Request $request): RedirectResponse
    {
        try {
            Cart::activateCart($cartId);
            $this->initStripeSdk();
            $sessionId = session()->get('checkout_' . $cartId);
            if (empty($sessionId)) {
                throw new \Exception('session not found');
            }
            Cart::collectTotals();
            $cart = Cart::getCart();
            $paymentSession = Session::retrieve($sessionId);
            if ($paymentSession->status != 'complete') {
                throw new \Exception('payment not completed');
            }
            $request->mergeIfMissing(['orderData' => ['session_id' => $paymentSession->id, 'payment_intent' => $paymentSession->payment_intent]]);
            $data = (new OrderResource($cart))->jsonSerialize();
            $order = $this->orderRepository->create($data);
//            dd($order);
//            $this->orderRepository->update(['status' => 'processing'], $order->id);
//            if ($order->canInvoice()) {
//                $this->invoiceRepository->create($this->prepareInvoiceData($order));
//            }
            Cart::removeCart($cart);
            session()->flash('order_id', $order->id);
            return redirect()->route('shop.checkout.onepage.success');
        } catch (\Exception $exception) {
            session()->flash('error', $exception->getMessage());
            return redirect()->route('shop.checkout.cart.index');
        }

    }

    protected function prepareInvoiceData($order)
    {
        $invoiceData = ['order_id' => $order->id];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }

    public function failure()
    {
        session()->flash('error', __('stripe::app.stripe.shop.payment_failed'));
        return redirect()->route('shop.checkout.cart.index');
    }

    protected function validateOrder()
    {
        $cart = Cart::getCart();

        $minimumOrderAmount = (float)core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?: 0;

        if (!Cart::haveMinimumOrderAmount()) {
            throw new \Exception(trans('shop::app.checkout.cart.minimum-order-message', ['amount' => core()->currency($minimumOrderAmount)]));
        }

        if (
            $cart->haveStockableItems()
            && !$cart->shipping_address
        ) {
            throw new \Exception(trans('shop::app.checkout.cart.check-shipping-address'));
        }

        if (!$cart->billing_address) {
            throw new \Exception(trans('shop::app.checkout.cart.check-billing-address'));
        }

        if (
            $cart->haveStockableItems()
            && !$cart->selected_shipping_rate
        ) {
            throw new \Exception(trans('shop::app.checkout.cart.specify-shipping-method'));
        }

        if (!$cart->payment) {
            throw new \Exception(trans('shop::app.checkout.cart.specify-payment-method'));
        }
    }

    public function cancel($order)
    {
        $this->initStripeSdk();
        $payment = $this->isValidStripePayment($order);
        if ($payment) {
            Refund::create(['payment_intent' => $payment->additional['payment_intent']]);
        }
    }

    public function refund($refund)
    {
        $this->initStripeSdk();
        $payment = $this->isValidStripePayment($refund?->order);
        if ($payment) {
            Refund::create(['payment_intent' => $payment->additional['payment_intent'], 'amount' => ($refund->order->base_grand_total - $refund->base_grand_total) * 100]);
        }
    }


    protected function isValidStripePayment($order)
    {
        $payment = $order?->payment;
        if ($payment?->method == "stripe" && $payment?->additional['payment_intent']) {
            return $payment;
        }
        return false;
    }
}
