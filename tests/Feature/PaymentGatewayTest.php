<?php

namespace Tests\Feature;

use App\Http\Controllers\PaymentController;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Support\Payments\TwoCheckoutGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $package = Package::where('price', 2450)->firstOrFail();
        $this->booking = $package->bookings()->create([
            'full_name' => 'Harpreet Kaur', 'email' => 'h@example.ca', 'adults' => 2, 'start_date' => now()->addMonths(2),
            'total_estimate' => 4900, 'currency' => 'USD', 'exchange_rate' => 1,
        ]);
    }

    /** @param array<string, string> $settings */
    private function enable(string $gateway, array $settings, array $secrets = []): void
    {
        Setting::put(["payment_{$gateway}_enabled" => '1']);
        foreach ($settings as $field => $value) {
            Setting::put(["payment_{$gateway}_{$field}" => $value]);
        }
        foreach ($secrets as $field => $value) {
            Setting::putSecret("payment_{$gateway}_{$field}", $value);
        }
    }

    private function pay(string $gateway, string $type = 'deposit')
    {
        return $this->post(PaymentController::payLink($this->booking), ['gateway' => $gateway, 'type' => $type]);
    }

    private function latestPayment(): Payment
    {
        return Payment::latest('id')->firstOrFail();
    }

    public function test_payment_page_requires_access(): void
    {
        $this->get(route('bookings.pay', $this->booking))->assertNotFound();
        $this->get(PaymentController::payLink($this->booking))->assertOk()->assertSee('Online payment isn’t available yet', false);
    }

    public function test_only_enabled_and_configured_gateways_are_offered(): void
    {
        Setting::put(['payment_paypal_enabled' => '1']); // no credentials
        $this->enable('coinpayments', ['merchant_id' => 'M1'], ['ipn_secret' => 'secret']);

        $this->get(PaymentController::payLink($this->booking))->assertSee('CoinPayments')->assertDontSee('PayPal balance');
        $this->pay('paypal')->assertSessionHasErrors('gateway');
    }

    public function test_stripe_checkout_and_return(): void
    {
        $this->enable('stripe', [], ['secret_key' => 'sk_test_123', 'webhook_secret' => 'whsec_abc']);
        Http::fake([
            'api.stripe.com/v1/checkout/sessions' => Http::response(['id' => 'cs_1', 'url' => 'https://checkout.stripe.com/c/cs_1']),
            'api.stripe.com/v1/checkout/sessions/cs_1' => fn () => Http::response([
                'id' => 'cs_1', 'payment_status' => 'paid', 'client_reference_id' => $this->latestPayment()->reference,
                'amount_total' => 122500, 'currency' => 'usd', 'payment_intent' => 'pi_1',
            ]),
        ]);

        $this->pay('stripe')->assertRedirect('https://checkout.stripe.com/c/cs_1');
        $payment = $this->latestPayment();
        $this->assertSame('1225.00', $payment->amount);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'checkout/sessions') && $r['line_items'][0]['price_data']['unit_amount'] === 122500
            && $r->header('Idempotency-Key')[0] === $payment->reference);

        $this->get(route('payments.return', $payment).'?session_id=cs_1')->assertRedirect();
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('deposit_paid', $this->booking->fresh()->payment_status);
        $this->assertSame('1225.00', $this->booking->fresh()->amount_paid);
    }

    public function test_stripe_webhook_requires_a_valid_signature(): void
    {
        $this->enable('stripe', [], ['secret_key' => 'sk_test_123', 'webhook_secret' => 'whsec_abc']);
        $payment = $this->booking->payments()->create(['gateway' => 'stripe', 'type' => 'full', 'amount' => 4900, 'currency' => 'USD', 'base_amount' => 4900]);
        $body = json_encode(['type' => 'checkout.session.completed', 'data' => ['object' => [
            'id' => 'cs_2', 'payment_status' => 'paid', 'client_reference_id' => $payment->reference, 'amount_total' => 490000, 'currency' => 'usd',
        ]]]);
        $time = time();

        $this->call('POST', '/webhooks/payments/stripe', [], [], [], ['HTTP_STRIPE_SIGNATURE' => "t={$time},v1=forged", 'CONTENT_TYPE' => 'application/json'], $body)->assertStatus(400);
        $this->assertSame('pending', $payment->fresh()->status);

        $signature = hash_hmac('sha256', "{$time}.{$body}", 'whsec_abc');
        $this->call('POST', '/webhooks/payments/stripe', [], [], [], ['HTTP_STRIPE_SIGNATURE' => "t={$time},v1={$signature}", 'CONTENT_TYPE' => 'application/json'], $body)->assertOk();
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $this->booking->fresh()->payment_status);
    }

    public function test_paypal_order_is_captured_on_return(): void
    {
        $this->enable('paypal', ['mode' => 'sandbox', 'client_id' => 'cid'], ['client_secret' => 'sec']);
        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'tok']),
            'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response(['id' => 'ORDER1', 'links' => [['rel' => 'payer-action', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=ORDER1']]], 201),
            'api-m.sandbox.paypal.com/v2/checkout/orders/ORDER1/capture' => Http::response(['status' => 'COMPLETED', 'purchase_units' => [['payments' => ['captures' => [
                ['id' => 'CAP1', 'status' => 'COMPLETED', 'amount' => ['currency_code' => 'USD', 'value' => '1225.00']],
            ]]]]], 201),
        ]);

        $this->pay('paypal')->assertRedirect('https://www.sandbox.paypal.com/checkoutnow?token=ORDER1');
        $payment = $this->latestPayment();

        $this->get(route('payments.return', $payment).'?token=WRONG');
        $this->assertSame('pending', $payment->fresh()->status);

        $this->get(route('payments.return', $payment).'?token=ORDER1');
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('CAP1', $payment->fresh()->gateway_reference);
    }

    public function test_paypal_charges_in_base_currency_when_guest_currency_is_unsupported(): void
    {
        $this->booking->update(['currency' => 'PKR', 'exchange_rate' => 282]);
        $this->enable('paypal', ['mode' => 'sandbox', 'client_id' => 'cid'], ['client_secret' => 'sec']);
        Http::fake([
            '*/v1/oauth2/token' => Http::response(['access_token' => 'tok']),
            '*/v2/checkout/orders' => Http::response(['id' => 'O2', 'links' => [['rel' => 'payer-action', 'href' => 'https://paypal.test/a']]], 201),
        ]);

        $this->pay('paypal');

        $this->assertSame('USD', $this->latestPayment()->currency);
        $this->assertSame('1225.00', $this->latestPayment()->amount);
    }

    public function test_authorize_net_hosted_form_and_signed_webhook(): void
    {
        $this->enable('authorizenet', ['mode' => 'sandbox', 'login_id' => 'login', 'currency' => 'USD'], ['transaction_key' => 'tk', 'signature_key' => 'ABC123']);
        Http::fake(function ($request) {
            $body = $request->data();
            if (isset($body['getHostedPaymentPageRequest'])) {
                return Http::response("\xEF\xBB\xBF".json_encode(['token' => 'TOKEN1', 'messages' => ['resultCode' => 'Ok']]));
            }

            return Http::response(json_encode(['transaction' => [
                'transId' => '6001', 'responseCode' => 1, 'authAmount' => 1225.00,
                'order' => ['invoiceNumber' => Payment::latest('id')->first()->reference],
            ]]));
        });

        $this->pay('authorizenet')->assertOk()->assertSee('https://test.authorize.net/payment/payment')->assertSee('TOKEN1');
        $payment = $this->latestPayment();
        $this->assertLessThanOrEqual(20, strlen($payment->reference));

        $body = json_encode(['eventType' => 'net.authorize.payment.authcapture.created', 'payload' => ['id' => '6001']]);
        $this->call('POST', '/webhooks/payments/authorizenet', [], [], [], ['HTTP_X_ANET_SIGNATURE' => 'sha512=BAD', 'CONTENT_TYPE' => 'application/json'], $body)->assertStatus(400);

        $signature = strtoupper(hash_hmac('sha512', $body, 'ABC123'));
        $this->call('POST', '/webhooks/payments/authorizenet', [], [], [], ['HTTP_X_ANET_SIGNATURE' => "sha512={$signature}", 'CONTENT_TYPE' => 'application/json'], $body)->assertOk();
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('6001', $payment->fresh()->gateway_reference);
    }

    public function test_two_checkout_signed_buy_link_and_ipn(): void
    {
        $this->enable('2checkout', ['mode' => 'live', 'merchant_code' => 'MERCH'], ['secret_word' => 'word', 'secret_key' => 'key']);

        $response = $this->pay('2checkout', 'full');
        $url = $response->headers->get('Location');
        $this->assertStringStartsWith('https://secure.2checkout.com/checkout/buy?', $url);
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $payment = $this->latestPayment();
        $this->assertSame($payment->reference, $params['order-ext-ref']);
        $this->assertSame('4900.00', $params['price']);
        $this->assertSame(app(TwoCheckoutGateway::class)->buyLinkSignature($params), $params['signature']);

        $ipn = ['REFNO' => '9001', 'REFNOEXT' => $payment->reference, 'ORDERSTATUS' => 'COMPLETE', 'CURRENCY' => 'USD',
            'IPN_TOTALGENERAL' => '4900.00', 'IPN_PID' => ['1'], 'IPN_PNAME' => ['Trip'], 'IPN_DATE' => '20261010120000'];
        $this->post('/webhooks/payments/2checkout', $ipn + ['SIGNATURE_SHA2_256' => 'forged'])->assertStatus(400);

        $signed = $ipn + ['SIGNATURE_SHA2_256' => hash_hmac('sha256', TwoCheckoutGateway::serialize($ipn), 'key')];
        $this->post('/webhooks/payments/2checkout', $signed)->assertOk()->assertSee('<sig algo="sha256"', false);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $this->booking->fresh()->payment_status);
    }

    public function test_coinpayments_form_and_ipn(): void
    {
        $this->enable('coinpayments', ['merchant_id' => 'M1'], ['ipn_secret' => 'ipnsecret']);

        $this->pay('coinpayments')->assertOk()->assertSee('https://www.coinpayments.net/index.php')->assertSee('_pay_simple');
        $payment = $this->latestPayment();

        $fields = ['ipn_mode' => 'hmac', 'merchant' => 'M1', 'invoice' => $payment->reference, 'status' => '100',
            'currency1' => 'USD', 'amount1' => '1225.00', 'currency2' => 'BTC', 'txn_id' => 'CPTX1'];
        $body = http_build_query($fields);
        $post = fn (string $hmac) => $this->call('POST', '/webhooks/payments/coinpayments', $fields, [], [], ['HTTP_HMAC' => $hmac, 'CONTENT_TYPE' => 'application/x-www-form-urlencoded'], $body);

        $post('forged')->assertStatus(400);
        $post(hash_hmac('sha512', $body, 'ipnsecret'))->assertOk()->assertSee('IPN OK');
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('BTC', $payment->fresh()->payload['coin']);
    }

    public function test_underpaid_notifications_do_not_mark_payment_paid(): void
    {
        $this->enable('coinpayments', ['merchant_id' => 'M1'], ['ipn_secret' => 'ipnsecret']);
        $this->pay('coinpayments');
        $payment = $this->latestPayment();
        $fields = ['ipn_mode' => 'hmac', 'merchant' => 'M1', 'invoice' => $payment->reference, 'status' => '100', 'currency1' => 'USD', 'amount1' => '10.00', 'txn_id' => 'X'];
        $body = http_build_query($fields);

        $this->call('POST', '/webhooks/payments/coinpayments', $fields, [], [], ['HTTP_HMAC' => hash_hmac('sha512', $body, 'ipnsecret')], $body)->assertOk();

        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_admin_payment_settings_store_secrets_encrypted(): void
    {
        $this->actingAs(User::first());

        $this->put('/admin/payments', ['deposit_percent' => 30, 'stripe' => ['enabled' => '1', 'secret_key' => 'sk_test_SECRET']])->assertSessionHasNoErrors();

        $this->assertNotSame('sk_test_SECRET', Setting::get('payment_stripe_secret_key'));
        $this->assertSame('sk_test_SECRET', Setting::secret('payment_stripe_secret_key'));
        $this->get('/admin/payments')->assertOk()->assertDontSee('sk_test_SECRET')->assertSee('saved — leave blank to keep');

        // Blank keeps the saved secret.
        $this->put('/admin/payments', ['deposit_percent' => 30, 'stripe' => ['enabled' => '1', 'secret_key' => '']]);
        $this->assertSame('sk_test_SECRET', Setting::secret('payment_stripe_secret_key'));
        $this->assertSame('30', setting('deposit_percent'));
    }

    public function test_admin_booking_page_shows_payments_and_link(): void
    {
        $this->actingAs(User::first());
        $this->booking->payments()->create(['gateway' => 'stripe', 'type' => 'deposit', 'amount' => 1225, 'currency' => 'USD', 'base_amount' => 1225])->markPaid('pi_9');

        $this->get("/admin/bookings/{$this->booking->reference}")->assertOk()->assertSee('pi_9')->assertSee('Copy payment link');
    }
}
