<?php

declare(strict_types=1);

namespace App\Domains\TitanMoney\Services;

use App\Domains\TitanMoney\Models\Invoice;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class InvoiceDocumentService
{
    /** @return array{disk:string,path:string,hash:string,html:?string} */
    public function snapshot(Invoice $invoice): array
    {
        $disk = (string) config('titanmoney.private_disk', 'local');

        if ($invoice->document_path && $invoice->document_hash) {
            return $this->existingSnapshot($invoice, $disk);
        }

        if ($invoice->document_path) {
            $snapshot = $this->existingSnapshot($invoice, $disk, false);
            $invoice->forceFill([
                'document_hash' => $snapshot['hash'],
                'template_version' => $invoice->template_version ?: 'titan-money-1.0',
            ])->saveQuietly();

            return $snapshot;
        }

        $invoice->loadMissing(['lines', 'customer']);
        $html = view('titanmoney::invoices.pdf', compact('invoice'))->render();
        $base = 'titan-money/'.$invoice->company_id.'/invoices/'.$invoice->id;
        $htmlPath = $base.'/invoice-v'.$invoice->version.'.html';
        Storage::disk($disk)->put($htmlPath, $html);
        $path = $htmlPath;

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4');
            $path = $base.'/invoice-v'.$invoice->version.'.pdf';
            Storage::disk($disk)->put($path, $pdf->output());
        }

        $contents = Storage::disk($disk)->get($path);
        $hash = hash('sha256', $contents);
        $invoice->forceFill([
            'document_path' => $path,
            'document_hash' => $hash,
            'template_version' => 'titan-money-1.0',
        ])->saveQuietly();

        return ['disk'=>$disk, 'path'=>$path, 'hash'=>$hash, 'html'=>$html];
    }

    public function download(Invoice $invoice)
    {
        $snapshot = $this->snapshot($invoice);

        return Storage::disk($snapshot['disk'])->download(
            $snapshot['path'],
            $invoice->invoice_number.'.'.pathinfo($snapshot['path'], PATHINFO_EXTENSION),
        );
    }

    /** @return array{disk:string,path:string,hash:string,html:?string} */
    private function existingSnapshot(Invoice $invoice, string $disk, bool $verifyStoredHash = true): array
    {
        $path = (string) $invoice->document_path;
        if ($path === '' || ! Storage::disk($disk)->exists($path)) {
            throw new RuntimeException('Invoice document snapshot is missing. Restore the original immutable file rather than rerendering it.');
        }

        $contents = Storage::disk($disk)->get($path);
        $hash = hash('sha256', $contents);
        if ($verifyStoredHash && ! hash_equals((string) $invoice->document_hash, $hash)) {
            throw new RuntimeException('Invoice document snapshot failed its integrity check.');
        }

        return [
            'disk' => $disk,
            'path' => $path,
            'hash' => $hash,
            'html' => str_ends_with(strtolower($path), '.html') ? $contents : null,
        ];
    }
}
