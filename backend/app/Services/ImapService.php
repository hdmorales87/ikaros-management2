<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;

class ImapService
{
    public function __construct(private readonly SolicitudService $solicitudService)
    {
    }

    public function test(string $uuid): array
    {
        $connection = $this->connection($uuid);
        $account = $connection->table('imap')->first(['servidor', 'correo', 'password', 'puerto', 'tls']);
        if (!$account) return ['msg' => 'not_found'];
        $client = $this->client($account);
        $client->connect();
        $folders = $client->getFolders();
        return ['msg' => 'success', 'folders' => $folders->count()];
    }

    public function sync(string $uuid): array
    {
        $connection = $this->connection($uuid);
        $account = $connection->table('imap')->where('activo', 1)->first(['id', 'servidor', 'correo', 'password', 'puerto', 'tls']);
        if (!$account) return ['msg' => 'not_found', 'processed' => 0, 'failed' => 0];

        $company = Company::query()->where('uuid', $uuid)->where('activo', 1)->first(['documento']);
        if (!$company) return ['msg' => 'company_not_found', 'processed' => 0, 'failed' => 0];

        $client = $this->client($account);
        $client->connect();
        $inbox = $client->getFolderByPath('INBOX');
        if (!$inbox) throw new \RuntimeException('No se encontró la carpeta INBOX.');

        $processed = 0;
        $failed = 0;
        foreach ($inbox->messages()->unseen()->get() as $message) {
            try {
                $this->createRequestFromMessage($connection, $account, $company->documento, $uuid, $message);
                $message->setFlag('Seen');
                $processed++;
            } catch (\Throwable $exception) {
                report($exception);
                $failed++;
            }
        }
        $client->disconnect();

        return ['msg' => 'success', 'processed' => $processed, 'failed' => $failed];
    }

    public function client(object $account): Client
    {
        return (new ClientManager(config('imap')))->make([
            'host' => $account->servidor,
            'port' => (int) $account->puerto,
            'protocol' => 'imap',
            'encryption' => $this->encryption($account->tls ?? null),
            'validate_cert' => true,
            'username' => $account->correo,
            'password' => $account->password,
            'authentication' => null,
        ]);
    }

    private function encryption(mixed $tls): string|false
    {
        return match ((string) $tls) {
            'true', 'tls' => 'tls',
            'false', 'notls', '' => false,
            default => 'ssl',
        };
    }

    private function createRequestFromMessage(Connection $connection, object $account, string $document, string $uuid, object $message): void
    {
        $subjectParts = array_map('trim', explode(':', (string) $message->getSubject(), 3));
        if (count($subjectParts) < 2 || $subjectParts[0] !== $document || $subjectParts[1] === '') {
            throw new \InvalidArgumentException('El asunto debe usar el formato documento:palabra_clave:asunto.');
        }

        $rule = $connection->table('imap_reglas')
            ->where('id_imap', $account->id)
            ->where('palabra_clave', $subjectParts[1])
            ->where('activo', 1)
            ->first(['tipo', 'impacto', 'urgencia', 'id_area', 'id_categoria', 'id_subcategoria', 'asunto_default']);
        if (!$rule) throw new \InvalidArgumentException('No hay una regla IMAP activa para la palabra clave recibida.');

        $from = $message->getFrom()->first();
        $email = strtolower((string) ($from->mail ?? ''));
        $userId = (int) $connection->table('users')->where('activo', 1)->whereRaw('LOWER(email) = ?', [$email])->value('id');
        if ($userId === 0) throw new \InvalidArgumentException('El remitente no corresponde a un usuario activo.');

        $module = match ($rule->tipo) {
            'incidencia' => 'incidencias',
            'problema' => 'problemas',
            'servicio' => 'servicios',
            default => throw new \InvalidArgumentException('La regla IMAP tiene un tipo de solicitud inválido.'),
        };
        $description = $message->getHTMLBody() ?: $message->getTextBody();
        $requestId = $this->solicitudService->create([
            'modulo' => $module,
            'urgencia' => (int) $rule->urgencia,
            'impacto' => (int) $rule->impacto,
            'area' => (int) $rule->id_area,
            'categoria' => (int) $rule->id_categoria,
            'subcategoria' => (int) $rule->id_subcategoria,
            'asunto' => $subjectParts[2] ?? $rule->asunto_default,
            'descripcion' => mb_substr(strip_tags((string) $description), 0, 1500),
        ], $uuid, $userId);

        $this->storeAttachments($connection, $message, $uuid, $requestId, $module, $userId);
        $this->solicitudService->assign($requestId, $rule->tipo, $uuid);
    }

    private function storeAttachments(Connection $connection, object $message, string $uuid, int $requestId, string $module, int $userId): void
    {
        $table = $module === 'servicios' ? 'servicios' : 'incidencias';
        $directory = 'imap/'.$uuid.'/'.$table.'_adjuntos';
        $disk = Storage::disk('public');
        $usedBytes = collect($disk->allFiles('imap/'.$uuid))->sum(fn (string $path) => $disk->size($path));
        $quotaBytes = (int) Company::query()->where('uuid', $uuid)->value('cuota_almacenamiento') * 1048576;
        $maxSize = (int) config('imap.max_attachment_size');
        $allowedExtensions = array_map('strtolower', config('imap.allowed_extensions'));

        foreach ($message->getAttachments() as $index => $attachment) {
            $content = (string) $attachment->getContent();
            $size = strlen($content);
            $originalName = (string) $attachment->getName();
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if ($content === '' || $size > $maxSize || !in_array($extension, $allowedExtensions, true)) {
                report(new \RuntimeException('Adjunto IMAP rechazado: '.$originalName));
                continue;
            }
            if ($quotaBytes > 0 && $usedBytes + $size > $quotaBytes) {
                report(new \RuntimeException('Cuota de almacenamiento excedida para adjunto IMAP: '.$originalName));
                continue;
            }

            $name = $requestId.'_'.($index + 1).'_'.Str::random(12).'.'.$extension;
            $disk->put($directory.'/'.$name, $content);
            $connection->table($table.'_adjuntos')->insert([
                'id_maestro' => $requestId,
                'fecha' => now(),
                'id_usuario' => $userId,
                'nombre_archivo' => $name,
            ]);
            $usedBytes += $size;
        }
    }

    private function connection(string $uuid): Connection
    {
        abort_if($uuid === '', 422, 'El identificador de empresa es obligatorio.');
        return (new Company())->getConnectionByUUID($uuid);
    }
}
