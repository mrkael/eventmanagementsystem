<?php

namespace App\Services\Core;

use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class ETicketPdfService
{
    public function generate(Registration $registration, string $rawToken): string
    {
        $registration->loadMissing('event', 'ticket');

        $qrPng = $this->qrPng($rawToken);

        $pdf = Pdf::loadView('pdf.eticket', compact('registration', 'qrPng'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }

    private function qrPng(string $payload): string
    {
        $result = (new Builder(
            writer: new PngWriter(),
            validateResult: false,
            data: $payload,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 12,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        ))->build();

        return 'data:image/png;base64,' . base64_encode($result->getString());
    }
}
