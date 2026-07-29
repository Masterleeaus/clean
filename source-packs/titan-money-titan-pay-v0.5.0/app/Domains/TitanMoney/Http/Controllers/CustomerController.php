<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Http\Controllers;

use App\Domains\TitanMoney\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

final class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->when($request->q, fn ($query, $value) => $query->where(fn ($search) => $search
                ->where('name', 'like', "%{$value}%")
                ->orWhere('email', 'like', "%{$value}%")
                ->orWhere('business_name', 'like', "%{$value}%")))
            ->latest()
            ->paginate(20);

        return view('titanmoney::customers.index', compact('customers'));
    }

    public function create()
    {
        return view('titanmoney::customers.form', ['customer' => new Customer()]);
    }

    public function store(Request $request)
    {
        $customer = Customer::query()->create($this->validated($request));

        return redirect()->route('titanmoney.customers.show', $customer)->with('success', 'Customer created.');
    }

    public function show(Customer $customer)
    {
        return view('titanmoney::customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('titanmoney::customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request, $customer));

        return redirect()->route('titanmoney.customers.show', $customer)->with('success', 'Customer updated.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request, ?Customer $customer = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'abn' => ['nullable', 'string', 'max:20'],
            'billing_address_json' => ['nullable', 'array'],
            'service_address_json' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'communication_channels' => ['nullable', 'array'],
            'communication_channels.*' => [Rule::in(['customer_app', 'sms', 'email', 'voice'])],
        ]);

        $enabled = array_values(array_unique((array) ($data['communication_channels'] ?? [])));
        unset($data['communication_channels']);

        $metadata = is_array($customer?->metadata_json) ? $customer->metadata_json : [];
        $metadata['communication_preferences'] = [
            'customer_app' => in_array('customer_app', $enabled, true),
            'sms' => in_array('sms', $enabled, true),
            'email' => in_array('email', $enabled, true),
            'voice' => in_array('voice', $enabled, true),
        ];
        $data['metadata_json'] = $metadata;

        return $data;
    }
}
