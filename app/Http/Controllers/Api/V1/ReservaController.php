<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\MetodoPago;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReservaResource;
use App\Models\Reserva;
use App\Services\ReservaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ReservaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = Reserva::with(['cliente', 'profesional.user', 'servicio', 'zona', 'calificacion']);

        if ($user->esProfesional() && $user->profesional) {
            $query->where('profesional_id', $user->profesional->id);
        } else {
            $query->where('cliente_id', $user->id);
        }

        $reservas = $query->orderByDesc('fecha_hora')->get();

        return ReservaResource::collection($reservas);
    }

    public function show(Request $request, int $id): ReservaResource
    {
        $reserva = Reserva::with(['cliente', 'profesional.user', 'servicio', 'zona', 'calificacion'])
            ->findOrFail($id);

        $this->autorizarAcceso($request, $reserva);

        return new ReservaResource($reserva);
    }

    public function store(Request $request, ReservaService $service): JsonResponse
    {
        $request->validate([
            'profesional_id' => 'required|integer|exists:profesionales,id',
            'servicio_id' => 'required|integer|exists:servicios,id',
            'zona_id' => 'required|integer|exists:zonas,id',
            'fecha_hora' => 'required|date',
            'direccion' => 'required|string|max:255',
            'notas' => 'nullable|string|max:1000',
        ]);

        $reserva = $service->pedir($request->user(), $request->all());
        $reserva->load(['cliente', 'profesional.user', 'servicio', 'zona', 'calificacion']);

        return (new ReservaResource($reserva))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function aceptar(Request $request, int $id, ReservaService $service): ReservaResource
    {
        $reserva = Reserva::findOrFail($id);
        $profesional = $this->obtenerProfesional($request);

        $reserva = $service->aceptar($reserva, $profesional);
        $reserva->load(['cliente', 'profesional.user', 'servicio', 'zona', 'calificacion']);

        return new ReservaResource($reserva);
    }

    public function rechazar(Request $request, int $id, ReservaService $service): ReservaResource
    {
        $reserva = Reserva::findOrFail($id);
        $profesional = $this->obtenerProfesional($request);

        $reserva = $service->rechazar($reserva, $profesional, $request->input('motivo'));
        $reserva->load(['cliente', 'profesional.user', 'servicio', 'zona', 'calificacion']);

        return new ReservaResource($reserva);
    }

    public function cancelar(Request $request, int $id, ReservaService $service): ReservaResource
    {
        $reserva = Reserva::findOrFail($id);

        $reserva = $service->cancelar($reserva, $request->user(), $request->input('motivo'));
        $reserva->load(['cliente', 'profesional.user', 'servicio', 'zona', 'calificacion']);

        return new ReservaResource($reserva);
    }

    public function completar(Request $request, int $id, ReservaService $service): ReservaResource
    {
        $request->validate([
            'metodo_pago' => 'required|string|in:efectivo,nequi,transferencia,otro',
            'valor_pagado' => 'required|integer|min:0',
        ]);

        $reserva = Reserva::findOrFail($id);
        $profesional = $this->obtenerProfesional($request);

        $reserva = $service->completar(
            $reserva,
            $profesional,
            MetodoPago::from($request->input('metodo_pago')),
            (int) $request->input('valor_pagado'),
        );
        $reserva->load(['cliente', 'profesional.user', 'servicio', 'zona', 'calificacion']);

        return new ReservaResource($reserva);
    }

    public function calificar(Request $request, int $id, ReservaService $service): ReservaResource
    {
        $request->validate([
            'estrellas' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:1000',
        ]);

        $reserva = Reserva::findOrFail($id);

        $service->calificar(
            $reserva,
            $request->user(),
            (int) $request->input('estrellas'),
            $request->input('comentario'),
        );
        $reserva->load(['cliente', 'profesional.user', 'servicio', 'zona', 'calificacion']);

        return new ReservaResource($reserva);
    }

    private function autorizarAcceso(Request $request, Reserva $reserva): void
    {
        $user = $request->user();
        $esCliente = $user->id === $reserva->cliente_id;
        $esProfesional = $user->profesional && $user->profesional->id === $reserva->profesional_id;

        abort_unless($esCliente || $esProfesional || $user->esAdmin(), Response::HTTP_FORBIDDEN, 'No tienes permiso para ver esta reserva.');
    }

    private function obtenerProfesional(Request $request): \App\Models\Profesional
    {
        $user = $request->user();
        abort_unless($user->esProfesional() && $user->profesional, Response::HTTP_FORBIDDEN, 'Solo profesionales pueden realizar esta acción.');

        return $user->profesional;
    }
}
