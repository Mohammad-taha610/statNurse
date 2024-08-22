<?php

namespace App\DTO\Payroll;

use App\DTO\Member\ProviderDTO;
use App\Entity\Nst\Member\Provider;

class InvoiceDTO
{
    public readonly int $id;
    public readonly string $invoiceNumber;
    public readonly string $payPeriod;
    public readonly float | null $amount;
    public readonly string $status;
    public readonly string $fileUrl;
    public readonly ProviderDTO $provider;


    public function __construct(
        int $id,
        string $invoiceNumber,
        string $payPeriod,
        float | null $amount,
        string $status,
        string $fileUrl,
        Provider $provider
    ) {
        $this->id = $id;
        $this->invoiceNumber = $invoiceNumber;
        $this->payPeriod = $payPeriod;
        $this->amount = $amount;
        $this->status = $status;
        $this->fileUrl = $fileUrl;
        $this->provider =  new ProviderDTO($provider);
    }
}
