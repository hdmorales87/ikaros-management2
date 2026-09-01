<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ContractService;
use App\Services\FileManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContractController extends Controller
{
    public function __construct(private readonly ContractService $contractService, private readonly FileManagerService $fileManagerService)
    {
    }

    public function indexForClient(Request $request, int $client): JsonResponse
    {
        return $this->index($request, $client, 'cliente');
    }

    public function indexForProvider(Request $request, int $provider): JsonResponse
    {
        return $this->index($request, $provider, 'proveedor');
    }

    public function formOptionsForClient(Request $request, int $client): JsonResponse
    {
        return $this->formOptions($request, $client, 'cliente');
    }

    public function formOptionsForProvider(Request $request, int $provider): JsonResponse
    {
        return $this->formOptions($request, $provider, 'proveedor');
    }

    public function notifications(Request $request): JsonResponse
    {
        return response()->json($this->contractService->notifications((string) $request->header('x-uuid', '')));
    }

    public function paymentsForClient(Request $request, int $client, int $contract): JsonResponse
    {
        return $this->payments($request, $client, $contract, 'cliente');
    }

    public function paymentsForProvider(Request $request, int $provider, int $contract): JsonResponse
    {
        return $this->payments($request, $provider, $contract, 'proveedor');
    }

    public function storeForClient(Request $request, int $client): JsonResponse
    {
        return $this->store($request, $client, 'cliente');
    }

    public function storeForProvider(Request $request, int $provider): JsonResponse
    {
        return $this->store($request, $provider, 'proveedor');
    }

    public function updateForClient(Request $request, int $client, int $contract): JsonResponse
    {
        return $this->update($request, $client, $contract, 'cliente');
    }

    public function updateForProvider(Request $request, int $provider, int $contract): JsonResponse
    {
        return $this->update($request, $provider, $contract, 'proveedor');
    }

    public function deactivateForClient(Request $request, int $client, int $contract): JsonResponse
    {
        return $this->deactivate($request, $client, $contract, 'cliente');
    }

    public function deactivateForProvider(Request $request, int $provider, int $contract): JsonResponse
    {
        return $this->deactivate($request, $provider, $contract, 'proveedor');
    }

    public function storePaymentForClient(Request $request, int $client, int $contract): JsonResponse
    {
        return $this->storePayment($request, $client, $contract, 'cliente');
    }

    public function storePaymentForProvider(Request $request, int $provider, int $contract): JsonResponse
    {
        return $this->storePayment($request, $provider, $contract, 'proveedor');
    }

    public function deactivatePaymentForClient(Request $request, int $client, int $contract, int $payment): JsonResponse
    {
        return $this->deactivatePayment($request, $client, $contract, $payment, 'cliente');
    }

    public function deactivatePaymentForProvider(Request $request, int $provider, int $contract, int $payment): JsonResponse
    {
        return $this->deactivatePayment($request, $provider, $contract, $payment, 'proveedor');
    }

    public function attachmentsForClient(Request $request, int $client, int $contract): JsonResponse
    {
        return $this->attachments($request, $client, $contract, 'cliente');
    }

    public function attachmentsForProvider(Request $request, int $provider, int $contract): JsonResponse
    {
        return $this->attachments($request, $provider, $contract, 'proveedor');
    }

    public function storeAttachmentForClient(Request $request, int $client, int $contract): JsonResponse
    {
        return $this->storeAttachment($request, $client, $contract, 'cliente');
    }

    public function storeAttachmentForProvider(Request $request, int $provider, int $contract): JsonResponse
    {
        return $this->storeAttachment($request, $provider, $contract, 'proveedor');
    }

    public function downloadAttachmentForClient(Request $request, int $client, int $contract, int $attachment): StreamedResponse
    {
        return $this->downloadAttachment($request, $client, $contract, $attachment, 'cliente');
    }

    public function downloadAttachmentForProvider(Request $request, int $provider, int $contract, int $attachment): StreamedResponse
    {
        return $this->downloadAttachment($request, $provider, $contract, $attachment, 'proveedor');
    }

    private function index(Request $request, int $thirdPartyId, string $type): JsonResponse
    {
        $query = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'string', 'max:100'],
        ]);
        $result = $this->contractService->paginate(
            $thirdPartyId,
            $type,
            (string) $request->header('x-uuid', ''),
            trim((string) ($query['search'] ?? '')),
            (int) ($query['per_page'] ?? 25),
            (string) ($query['sort'] ?? ''),
        );

        return $result !== null
            ? response()->json($result)
            : response()->json(['message' => 'Tercero no encontrado.'], 404);
    }

    private function payments(Request $request, int $thirdPartyId, int $contractId, string $type): JsonResponse
    {
        $result = $this->contractService->payments($thirdPartyId, $contractId, $type, (string) $request->header('x-uuid', ''));

        return $result !== null
            ? response()->json($result)
            : response()->json(['message' => 'Contrato no encontrado.'], 404);
    }

    private function formOptions(Request $request, int $thirdPartyId, string $type): JsonResponse
    {
        $result = $this->contractService->formOptions($thirdPartyId, $type, (string) $request->header('x-uuid', ''));

        return $result
            ? response()->json($result)
            : response()->json(['message' => 'Tercero no encontrado.'], 404);
    }

    private function store(Request $request, int $thirdPartyId, string $type): JsonResponse
    {
        $result = $this->contractService->create($thirdPartyId, $type, $this->contractData($request, true), (string) $request->header('x-uuid', ''));

        return $result
            ? response()->json($result, 201)
            : response()->json(['message' => 'Tercero no encontrado.'], 404);
    }

    private function update(Request $request, int $thirdPartyId, int $contractId, string $type): JsonResponse
    {
        $result = $this->contractService->update($thirdPartyId, $contractId, $type, $this->contractData($request, false), (string) $request->header('x-uuid', ''));

        return $result
            ? response()->json($result)
            : response()->json(['message' => 'Contrato no encontrado.'], 404);
    }

    private function deactivate(Request $request, int $thirdPartyId, int $contractId, string $type): JsonResponse
    {
        $deactivated = $this->contractService->deactivate($thirdPartyId, $contractId, $type, (string) $request->header('x-uuid', ''));

        return $deactivated
            ? response()->json(['message' => 'Contrato desactivado.'])
            : response()->json(['message' => 'Contrato no encontrado.'], 404);
    }

    private function storePayment(Request $request, int $thirdPartyId, int $contractId, string $type): JsonResponse
    {
        $data = $request->validate([
            'numero_factura' => ['nullable', 'string', 'max:255'],
            'fecha_factura' => ['nullable', 'date'],
            'valor' => ['nullable', 'numeric', 'min:0'],
        ]);
        $result = $this->contractService->createPayment($thirdPartyId, $contractId, $type, $this->jwtUserId($request), $data, (string) $request->header('x-uuid', ''));

        return $result
            ? response()->json($result, 201)
            : response()->json(['message' => 'Contrato no encontrado.'], 404);
    }

    private function deactivatePayment(Request $request, int $thirdPartyId, int $contractId, int $paymentId, string $type): JsonResponse
    {
        $deactivated = $this->contractService->deactivatePayment($thirdPartyId, $contractId, $paymentId, $type, (string) $request->header('x-uuid', ''));

        return $deactivated
            ? response()->json(['message' => 'Pago desactivado.'])
            : response()->json(['message' => 'Pago no encontrado.'], 404);
    }

    private function attachments(Request $request, int $thirdPartyId, int $contractId, string $type): JsonResponse
    {
        $result = $this->contractService->attachments($thirdPartyId, $contractId, $type, (string) $request->header('x-uuid', ''));

        return $result !== null
            ? response()->json($result)
            : response()->json(['message' => 'Contrato no encontrado.'], 404);
    }

    private function storeAttachment(Request $request, int $thirdPartyId, int $contractId, string $type): JsonResponse
    {
        $data = $request->validate(['file' => ['required', 'file', 'max:5120']]);
        $result = $this->contractService->uploadAttachment($thirdPartyId, $contractId, $type, $this->jwtUserId($request), $data['file'], (string) $request->header('x-uuid', ''));

        return $result
            ? response()->json($result, 201)
            : response()->json(['message' => 'Contrato no encontrado.'], 404);
    }

    private function downloadAttachment(Request $request, int $thirdPartyId, int $contractId, int $attachmentId, string $type): StreamedResponse
    {
        $uuid = (string) $request->header('x-uuid', '');
        $attachment = $this->contractService->attachment($thirdPartyId, $contractId, $attachmentId, $type, $uuid);
        abort_unless($attachment, 404, 'Adjunto no encontrado.');
        $path = $this->fileManagerService->path($uuid, 'terceros_contratos_adjuntos', $attachment['nombre_archivo']);
        abort_unless(Storage::disk('public')->exists($path), 404, 'Archivo no encontrado.');

        return Storage::disk('public')->download($path, $attachment['nombre_archivo']);
    }

    private function contractData(Request $request, bool $creating): array
    {
        return $request->validate([
            'nombre' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'tipo_contrato' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'integer', 'min:1'],
            'objeto_contrato' => ['nullable', 'string', 'max:5000'],
            'id_moneda' => ['nullable', 'integer', 'min:1'],
            'monto' => ['nullable', 'numeric', 'min:0'],
            'iva' => ['nullable', 'numeric', 'min:0'],
            'id_responsable_ejecucion' => ['nullable', 'integer', 'min:1'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'id_plan_pago' => ['nullable', 'integer', 'min:1'],
            'numero_pagos' => ['nullable', 'integer', 'min:1'],
            'id_responsable_pago' => ['nullable', 'integer', 'min:1'],
            'nombre_responsable_pago' => ['nullable', 'string', 'max:255'],
            'email_responsable_pago' => ['nullable', 'email', 'max:255'],
            'renovacion_automatica' => ['nullable', 'in:true,false'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function jwtUserId(Request $request): int
    {
        $token = str_replace('Bearer ', '', (string) $request->header('Authorization'));
        $identity = (new \App\Helpers\JwtAuth())->checkToken($token, true);

        return is_object($identity) ? (int) ($identity->sub ?? 0) : 0;
    }
}
