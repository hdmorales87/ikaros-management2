<?php

namespace Tests\Unit;

use App\Services\ImapService;
use App\Services\SolicitudService;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ImapServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_a_matching_rule_creates_and_assigns_a_request(): void
    {
        $connection = DB::connection();
        $this->createTables($connection);
        $connection->table('users')->insert(['id' => 7, 'email' => 'cliente@example.test', 'activo' => 1]);
        $connection->table('imap_reglas')->insert([
            'id_imap' => 1,
            'palabra_clave' => 'soporte',
            'tipo' => 'incidencia',
            'impacto' => 2,
            'urgencia' => 3,
            'id_area' => 4,
            'id_categoria' => 5,
            'id_subcategoria' => 6,
            'asunto_default' => 'Asunto por defecto',
            'activo' => 1,
        ]);

        $created = [];
        $solicitudes = Mockery::mock(SolicitudService::class);
        $solicitudes->shouldReceive('create')->once()->withArgs(function (array $data, string $uuid, int $userId) use (&$created): bool {
            $created = $data;
            return $uuid === 'tenant-uuid' && $userId === 7;
        })->andReturn(42);
        $solicitudes->shouldReceive('assign')->once()->with(42, 'incidencia', 'tenant-uuid')->andReturn(['msg' => 'success']);

        $service = new TestableImapService($solicitudes);
        $service->process($connection, (object) ['id' => 1], '900123456', 'tenant-uuid', new FakeImapMessage('900123456:soporte:Correo de prueba', 'cliente@example.test', 'Contenido de prueba'));

        $this->assertSame('incidencias', $created['modulo']);
        $this->assertSame('Correo de prueba', $created['asunto']);
        $this->assertSame('Contenido de prueba', $created['descripcion']);
    }

    private function createTables(Connection $connection): void
    {
        Schema::dropIfExists('imap_reglas');
        Schema::dropIfExists('users');
        Schema::create('users', function ($table): void {
            $table->increments('id');
            $table->string('email');
            $table->boolean('activo');
        });
        Schema::create('imap_reglas', function ($table): void {
            $table->increments('id');
            $table->integer('id_imap');
            $table->string('palabra_clave');
            $table->string('tipo');
            $table->integer('impacto');
            $table->integer('urgencia');
            $table->integer('id_area');
            $table->integer('id_categoria');
            $table->integer('id_subcategoria');
            $table->string('asunto_default');
            $table->boolean('activo');
        });
    }
}

class TestableImapService extends ImapService
{
    public function process(Connection $connection, object $account, string $document, string $uuid, object $message): void
    {
        $this->createRequestFromMessage($connection, $account, $document, $uuid, $message);
    }
}

class FakeImapMessage
{
    public function __construct(private readonly string $subject, private readonly string $email, private readonly string $body)
    {
    }

    public function getSubject(): string { return $this->subject; }
    public function getFrom(): object { return new class($this->email) { public function __construct(private readonly string $email) {} public function first(): object { return (object) ['mail' => $this->email]; } }; }
    public function getHTMLBody(): string { return ''; }
    public function getTextBody(): string { return $this->body; }
    public function getAttachments(): array { return []; }
}