@extends('layouts.app')

@section('title', 'Set up Titan company')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6 bg-slate-950">
    <div class="w-full max-w-3xl bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="p-8 md:p-10 border-b border-slate-200">
            <p class="text-xs font-semibold tracking-[0.24em] uppercase text-violet-600">Titan Zero</p>
            <h1 class="mt-3 text-3xl font-bold text-slate-950">Choose your company context</h1>
            <p class="mt-3 text-slate-600">Titan Zero and WorkCore require an authorised company before conversations or operational records can be used.</p>
        </div>

        <div class="p-8 md:p-10 space-y-8">
            @if ($memberships->isNotEmpty())
                <section>
                    <h2 class="text-lg font-semibold text-slate-900">Existing companies</h2>
                    <div class="mt-4 grid gap-3">
                        @foreach ($memberships as $membership)
                            <form method="POST" action="{{ route('titan.company.setup.store') }}" class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 p-4">
                                @csrf
                                <input type="hidden" name="action" value="activate">
                                <input type="hidden" name="target_company_id" value="{{ $membership->company_id }}">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $membership->company->name }}</p>
                                    <p class="text-sm text-slate-500">{{ ucfirst($membership->role_key) }}</p>
                                </div>
                                <button class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">Open company</button>
                            </form>
                        @endforeach
                    </div>
                </section>
            @endif

            <section>
                <h2 class="text-lg font-semibold text-slate-900">Create a company</h2>
                <form method="POST" action="{{ route('titan.company.setup.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                    @csrf
                    <input type="hidden" name="action" value="create">
                    <label class="md:col-span-2 text-sm font-medium text-slate-700">
                        Company name
                        <input name="name" required maxlength="255" value="{{ old('name') }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-violet-500 focus:outline-none" placeholder="Example Services Pty Ltd">
                    </label>
                    <label class="text-sm font-medium text-slate-700">
                        Timezone
                        <input name="timezone" value="{{ old('timezone', 'Australia/Melbourne') }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                    </label>
                    <label class="text-sm font-medium text-slate-700">
                        Currency
                        <input name="currency" value="{{ old('currency', 'AUD') }}" maxlength="3" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 uppercase">
                    </label>
                    <input type="hidden" name="country_code" value="AU">
                    <div class="md:col-span-2">
                        <button class="w-full rounded-xl bg-violet-600 px-5 py-3 font-semibold text-white hover:bg-violet-700">Create company and continue</button>
                    </div>
                </form>
            </section>

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
