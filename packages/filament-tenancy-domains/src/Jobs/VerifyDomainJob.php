<?php

namespace BeegoodIT\FilamentTenancyDomains\Jobs;

use BeegoodIT\FilamentTenancyDomains\Domain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerifyDomainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Domain $domain
    ) {}

    public function handle(): void
    {
        $this->domain->verify();
    }
}
