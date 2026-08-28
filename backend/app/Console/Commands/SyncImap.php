<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\ImapService;
use Illuminate\Console\Command;
use Throwable;

class SyncImap extends Command
{
    protected $signature = 'imap:sync {--uuid= : UUID de la empresa a sincronizar}';
    protected $description = 'Sincroniza los mensajes IMAP no leídos por tenant';

    public function __construct(private readonly ImapService $imapService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $uuid = (string) ($this->option('uuid') ?: '');
        $uuids = $uuid !== '' ? [$uuid] : Company::where('activo', 1)->pluck('uuid')->all();
        foreach ($uuids as $companyUuid) {
            try {
                $result = $this->imapService->sync((string) $companyUuid);
                $this->info($companyUuid.': '.$result['processed'].' procesados, '.$result['failed'].' con error.');
            } catch (Throwable $exception) {
                $this->error($companyUuid.': '.$exception->getMessage());
            }
        }
        return self::SUCCESS;
    }
}
