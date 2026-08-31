<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MailService
{
    public function send(string $email, string $subject, string $content, string $uuid): bool
    {
        $this->mailer($uuid)->html($this->layout($content), function ($message) use ($email, $subject): void {
            $message->to($email)->subject($subject);
            $this->addDebugCopy($message);
        });
        return true;
    }

    public function sendMany(array $emails, string $subject, string $content, string $uuid): bool
    {
        $emails = array_values(array_unique(array_filter($emails, fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))));
        abort_unless($emails !== [], 422, 'No hay destinatarios válidos.');
        $this->mailer($uuid)->html($this->layout($content), function ($message) use ($emails, $subject): void {
            $message->to($emails)->subject($subject);
            $this->addDebugCopy($message);
        });
        return true;
    }

    public function check(string $email, string $uuid): array
    {
        $this->mailer($uuid)->html($this->layout('<h1>Prueba SMTP exitosa</h1><p>La configuración de correo está funcionando correctamente.</p>'), function ($message) use ($email): void {
            $message->to($email)->subject('Prueba SMTP exitosa');
            $this->addDebugCopy($message);
        });

        return ['msg' => 'success'];
    }

    public function passwordReset(string $email, string $option, string $uuid, string $appUrl): array
    {
        $connection = (new Company())->getConnectionByUUID($uuid);
        $user = $connection->table('users')->where('activo', 1)->where('email', $email)->first(['id', 'nombre']);
        if (!$user) return ['msg' => 'not_found'];

        $token = Str::random(64);
        $connection->table('users')->where('id', $user->id)->update(['token' => $token, 'acceso_sistema' => 0]);
        $link = rtrim($appUrl, '/').'/resetPassword/'.$token.'/'.base64_encode($email).'/'.base64_encode($option).'/'.base64_encode($uuid);
        $this->mailer($uuid)->html($this->layout('<h1>Restablecer contraseña</h1><p>Hola '.e($user->nombre).'. Usa el siguiente enlace para continuar:</p><p><a href="'.e($link).'">Cambiar contraseña</a></p>'), function ($message) use ($email): void {
            $message->to($email)->subject('Solicitud de cambio de contraseña');
            $this->addDebugCopy($message);
        });

        return ['msg' => 'success'];
    }

    public function activation(int $userId, string $uuid, string $appUrl): array
    {
        $connection = (new Company())->getConnectionByUUID($uuid);
        $user = $connection->table('users')->where('id', $userId)->first(['email', 'nombre']);
        if (!$user) return ['msg' => 'not_found'];
        $token = Str::random(64);
        $connection->table('users')->where('id', $userId)->update(['token' => $token, 'acceso_sistema' => 0]);
        $link = rtrim($appUrl, '/').'/resetPassword/'.$token.'/'.base64_encode($user->email).'/'.base64_encode('activacion').'/'.base64_encode($uuid);
        $this->send($user->email, 'Activación de usuario', '<h1>Usuario creado</h1><p>Hola '.e($user->nombre).'. Activa tu cuenta aquí:</p><p><a href="'.e($link).'">Activar usuario</a></p>', $uuid);
        return ['msg' => 'success'];
    }

    private function mailer(string $uuid): Mailer
    {
        $smtp = (new Company())->getConnectionByUUID($uuid)->table('smtp')->first();
        abort_unless($smtp, 422, 'No se ha configurado SMTP.');
        config([
            'mail.mailers.tenant_smtp' => [
                'transport' => 'smtp',
                'host' => $smtp->servidor,
                'port' => (int) $smtp->puerto,
                'encryption' => $smtp->seguridad_smtp ?: null,
                'username' => $smtp->correo,
                'password' => $smtp->password,
                'timeout' => 10,
            ],
            'mail.from.address' => $smtp->correo,
            'mail.from.name' => config('app.name', 'Ikaros Management'),
        ]);
        Mail::purge('tenant_smtp');
        return Mail::mailer('tenant_smtp');
    }

    private function addDebugCopy(object $message): void
    {
        $debugRecipient = (string) config('mail.debug_cc', '');
        if (filter_var($debugRecipient, FILTER_VALIDATE_EMAIL)) {
            $message->cc($debugRecipient);
        }
    }

    private function layout(string $content): string
    {
        return '<!doctype html><html lang="es"><body style="font-family:Arial,sans-serif;max-width:600px;margin:40px auto;padding:24px">'.$content.'</body></html>';
    }
}
