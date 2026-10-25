<?php

namespace App\Livewire;

use App\Contract\CartServiceInterface;
use App\Data\CartData;
use App\Data\CheckoutData;
use App\Data\CustomerData;
use App\Data\RegionData;
use App\Data\ShippingData;
use App\Rules\ValidatePaymentMethodHash;
use App\Rules\ValidShippingHash;
use App\Services\CheckoutService;
use App\Services\PaymentMethodQueryService;
use App\Services\RegionQueryService;
use App\Services\ShippingMethodService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\LaravelData\DataCollection;

#[Title('Checkout')]
class Checkout extends Component
{
    public function render()
    {
        return view('livewire.checkout');
    }

    public array $data = [
        'full_name' => null,
        'email' => null,
        'phone_number' => null,
        'street_address' => null,
        'destination_region_code' => null,
        'shipping_hash' => null,
        'payment_method_hash' => null,
    ];

    public array $region_selector = [
        'keyword' => null,
        'region_selected' => null,
    ];

    public array $summaries = [
        'sub_total' => 0,
        'sub_total_formatted' => '-',
        'shipping_total' => 0,
        'shipping_total_formatted' => '-',
        'grant_total' => 0,
        'grant_total_formatted' => '-',
    ];

    public array $shipping_selector = [
        'shipping_method' => null
    ];

    public array $payment_method_selector = [
        'payment_method_selected' => null
    ];

    public function mount()
    {
        if(!Gate::inspect('is_stock_available')->allowed()) {
            return redirect()->route('cart');
        }

        if($this->cart->total_quantity <= 0) {
            return redirect()->route('cart');
        }

        $this->calculatedTotal();
    }

    public function rules()
    {
        return [
            'data.full_name' => ['required', 'string', 'min:3', 'max:255'],
            'data.email' => ['required', 'email', 'min:3', 'max:255'],
            'data.phone_number' => ['required', 'string', 'min:3', 'max:20'],
            'data.street_address' => ['required', 'string', 'min:3', 'max:255'],
            'data.destination_region_code' => ['required', 'exists:regions,code'],
            'data.shipping_hash' => ['required', new ValidShippingHash()],
            'data.payment_method_hash' => ['required', new ValidatePaymentMethodHash()]
        ];
    }

    public function messages()
    {
        return [
            'data.full_name.required' => 'Full Name perlu diisi',
            'data.email.required' => 'Email perlu diisi',
            'data.phone_number.required' => 'Phone Number perlu diisi',
            'data.street_address.required' => 'Street Address perlu diisi',
            'data.destination_region_code.exists' => 'Lokasi tujuan tidak valid',
            'data.destination_region_code.required' => 'Lokasi tujuan perlu diisi',
            'data.payment_method_hash.required' => 'Metode pembayaran perlu diisi',
        ];
    }

    public function calculatedTotal()
    {
        // isi dari $this->cart->all adalah object CartData dan berisi total, total_weight, total_quantity
        // sedangkan $this->cart->items adalah array of object CartItemData yang berisi product_id, name, short_desc, price, price_formatted, quantity, total_price, total_price_formatted, weight, cover_url
        data_set($this->summaries, 'sub_total', $this->cart->total);
        data_set($this->summaries, 'sub_total_formatted', $this->cart->total_formatted);

        $shipping_cost = $this->shipping_method?->cost ?? 0;
        data_set($this->summaries, 'shipping_total', 0);
        data_set($this->summaries, 'shipping_total_formatted', Number::currency($shipping_cost, 'IDR'));

        $grand_total = $this->cart->total + $shipping_cost;
        data_set($this->summaries, 'grant_total', $grand_total);
        data_set($this->summaries, 'grant_total_formatted', Number::currency($grand_total, 'IDR'));
    }

    // Selama ada get + Property maka akan otomatis di inject ke dalam view, sehingga tidak perlu di deklarasikan di render() yang berarti bisa dipanggil langsung dengan $this->cart
    #[Computed]
    public function cart() : CartData
    {
        return app(CartServiceInterface::class)->all();
    }

    #[Computed]
    public function regions() : DataCollection
    {

        if(!data_get($this->region_selector, 'keyword')) {
            $data = [];
            return new DataCollection(RegionData::class, []);
        }

        return app(RegionQueryService::class)->searchRegionByName(
            data_get($this->region_selector, 'keyword')
        );
    }

    #[Computed]
    public function region() : ?RegionData
    {
        $region_selected = data_get($this->region_selector, 'region_selected');
        if(!$region_selected) {
            return null;
        }

        return app(RegionQueryService::class)->searchRegionByCode($region_selected);
    }

    public function updatedRegionSelectorRegionSelected(?string $data)
    {
        data_set($this->data, 'destination_region_code', $data);
    }

    #[Computed]
    public function shippingMethods() : DataCollection|Collection {
        if(!data_get($this->data, 'destination_region_code')){
            return new DataCollection(ShippingData::class, []);
        }

        $origin_code = config('shipping.shipping_origin_code');

        return collect(app(ShippingMethodService::class)->getShippingMethods(
                app(RegionQueryService::class)->searchRegionByCode($origin_code),
                app(RegionQueryService::class)->searchRegionByCode(data_get($this->data, 'destination_region_code')),
                $this->cart
            )->toCollection()->groupBy('service')
        );
    }

    #[Computed]
    public function shippingMethod() : ?ShippingData
    {
        if(
            empty(data_get($this->data, 'shipping_hash')) ||
            empty(data_get($this->data, 'destination_region_code'))
        ){
            return null;
        }

        $data = app(ShippingMethodService::class)->getShippingMethod(
            data_get($this->data, 'shipping_hash'),
        );

        if(!$data){
            $this->addError('data.shipping_hash', 'Metode pengiriman tidak valid');
            redirect()->route('checkout');
        }

        return $data;
    }

    public function updatedShippingSelectorShippingMethod(?string $value)
    {
        data_set($this->data, 'shipping_hash', $value);
        $this->calculatedTotal();
    }

    #[Computed]
    public function paymentMethods() : DataCollection
    {
        return app(PaymentMethodQueryService::class)->getPaymentMethods();
    }

    public function updatedPaymentMethodSelectorPaymentMethodSelected(?string $value)
    {
        data_set($this->data, 'payment_method_hash', $value);
    }

    public function placeAnOrder(
        CartServiceInterface $cart
    )
    {
        $validated = $this->validate();
        $shipping_method = app(ShippingMethodService::class)->getShippingMethod(
            data_get($validated, 'data.shipping_hash')
        );

        $payment_method = app(PaymentMethodQueryService::class)->getPaymentMethodByHash(
            data_get($validated, 'data.payment_method_hash')
        );

        $checkout = CheckoutData::from([
            'customer' => CustomerData::from([
                'fullname' => data_get($validated, 'data.full_name'),
                'email' => data_get($validated, 'data.email'),
                'phone' => data_get($validated, 'data.phone_number')
            ]),
            'address_line' => data_get($validated, 'data.street_address'),
            'origin' => $shipping_method->origin,
            'destination' => $shipping_method->destination,
            'cart' => $this->cart,
            'shipping' => $shipping_method,
            'payment' => $payment_method,
        ]);

        $service = app(CheckoutService::class);
        $sales_order = $service->makeAnOrder($checkout);
        $cart->clear();

        return redirect()->route('order-confirmed', $sales_order->trx_id);
    }
}
