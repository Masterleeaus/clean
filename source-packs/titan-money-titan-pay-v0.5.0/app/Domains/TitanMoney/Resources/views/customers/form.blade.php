@extends('layouts.app')
@section('title','Customer')
@section('titan-money-title',$customer->exists?'Edit customer':'New customer')
@section('content')
@include('titanmoney::partials.header')
@php
    $preferences = old('communication_channels');
    if ($preferences === null) {
        $stored = $customer->metadata_json['communication_preferences'] ?? [];
        $preferences = $customer->exists
            ? collect($stored)->filter(fn ($enabled) => $enabled === true)->keys()->all()
            : ['sms','email'];
    }
@endphp
<main class="p-6">
    <form class="max-w-3xl mx-auto bg-white border rounded-xl p-6 grid md:grid-cols-2 gap-4" method="POST" action="{{ $customer->exists?route('titanmoney.customers.update',$customer):route('titanmoney.customers.store') }}">
        @csrf
        @if($customer->exists) @method('PUT') @endif
        @foreach([['name','Name'],['business_name','Business name'],['email','Email'],['phone','Phone'],['abn','ABN']] as $field)
            <label>{{ $field[1] }}
                <input name="{{ $field[0] }}" value="{{ old($field[0],$customer->{$field[0]}) }}" class="mt-1 w-full border rounded p-2" @if($field[0]==='name') required @endif>
            </label>
        @endforeach
        <fieldset class="md:col-span-2 rounded-xl border p-4">
            <legend class="px-2 font-semibold">Invoice and overdue follow-up channels</legend>
            <p class="mb-3 text-sm text-gray-600">Use only channels the customer has agreed to receive. Voice follow-up remains subject to company approval policy.</p>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach(['customer_app'=>'Titan Channels customer app','sms'=>'SMS','email'=>'Email','voice'=>'Automated phone call'] as $value=>$label)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="communication_channels[]" value="{{ $value }}" @checked(in_array($value,$preferences,true))>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
        <div class="md:col-span-2"><button class="px-4 py-2 bg-purple-600 text-white rounded">Save</button></div>
    </form>
</main>
@endsection
