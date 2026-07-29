<?php

declare(strict_types=1);

namespace App\Domains\TitanPay\Http\Controllers;

use App\Domains\Company\Models\Company;
use App\Domains\Company\Support\CurrentCompany;
use App\Domains\TitanMoney\Support\DecimalParser;
use App\Domains\TitanPay\Enums\CollectionStatus;
use App\Domains\TitanPay\Services\CollectionService;
use App\Domains\TitanPay\Services\PaymentClaimService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

final class PublicPaymentController extends Controller
{
    public function show(string $token, CollectionService $service, CurrentCompany $context)
    {
        $session = $service->findPublic($token);
        $context->set(Company::findOrFail($session->company_id));

        if ($session->isExpired() && $session->status !== CollectionStatus::Completed) {
            $session->update(['status' => CollectionStatus::Expired]);
        }
        if ($session->status === CollectionStatus::Pending) {
            $session->update(['status' => CollectionStatus::Opened, 'opened_at' => now()]);
        }

        return view('titanpay::public.show', compact('session', 'token'));
    }

    public function initiate(Request $request, string $token, CollectionService $service, CurrentCompany $context)
    {
        $session = $service->findPublic($token);
        $context->set(Company::findOrFail($session->company_id));
        $data = $request->validate(['method' => ['required', Rule::in($session->allowed_methods_json ?? [])]]);
        $result = $service->initiate($session, $data['method'], $token);

        if ($result['initiation']->redirectUrl) {
            return redirect()->away($result['initiation']->redirectUrl);
        }

        return view('titanpay::public.show', [
            'session' => $result['session'],
            'token' => $token,
            'initiation' => $result['initiation'],
        ]);
    }

    public function returned(Request $request, string $token, CollectionService $service, CurrentCompany $context)
    {
        $session = $service->findPublic($token);
        $context->set(Company::findOrFail($session->company_id));
        $paypalOrderId = $request->query('provider') === 'paypal'
            ? (string) $request->query('token', '')
            : '';

        return view('titanpay::public.return', compact('session', 'token', 'paypalOrderId'));
    }

    public function capturePayPal(Request $request, string $token, CollectionService $service, CurrentCompany $context)
    {
        $session = $service->findPublic($token);
        $context->set(Company::findOrFail($session->company_id));
        $data = $request->validate(['order_id' => 'required|string|max:255']);
        $service->capturePayPal($session, $data['order_id']);

        return redirect()->route('titanpay.public.return', ['token' => $token])
            ->with('success', 'PayPal capture requested. Titan Pay is waiting for verified webhook confirmation.');
    }

    public function claim(
        Request $request,
        string $token,
        CollectionService $collections,
        PaymentClaimService $claims,
        CurrentCompany $context,
    ) {
        $session = $collections->findPublic($token);
        $context->set(Company::findOrFail($session->company_id));
        $data = $request->validate([
            'method' => 'required|in:cash,payid,bank_transfer',
            'amount' => 'required|decimal:0,2|min:0.01',
            'reference' => 'nullable|string|max:255',
            'payer_name' => 'nullable|string|max:255',
            'payer_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:2000',
            'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:'.config('titanpay.max_evidence_kb', 5120),
        ]);
        $data['amount_minor'] = DecimalParser::minorUnits((string) $data['amount']);
        $claims->submit($session, $data, $request->file('evidence'));

        return back()->with('success', 'Payment claim submitted. It remains pending until verified.');
    }
}
