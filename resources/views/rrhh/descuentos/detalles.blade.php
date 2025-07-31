@extends('adminlte::page')

@section('title', 'Detalles del Descuento')

@section('content_header')
    <h2 class="text-center"><b>Detalles del Descuento para {{ $persona->Nombres }} ({{ $periodo->NombrePeriodo }})</b></h2>
@stop

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-success text-center">
            <b>Adelantos</b>
        </div>
        <div class="card-body">
            @if ($adelantos->isEmpty())
                <p class="text-center">No hay adelantos registrados en este periodo.</p>
            @else
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Documento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($adelantos as $adelanto)
                            <tr>
                                <td>{{ $adelanto->idAdelanto }}</td>
                                <td>{{ date('d/m/Y', strtotime($adelanto->FechaDeDeposito)) }}</td>
                                <td>{{ $adelanto->MontoAAdelantar }}</td>
                                <td>
                                    @if ($adelanto->LinkDeSolicitud)
                                        <a href="{{ asset('storage/'.$adelanto->LinkDeSolicitud) }}" target="_blank">Ver documento</a>
                                    @else
                                        No disponible
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header bg-success text-center">
            <b>Préstamos</b>
        </div>
        <div class="card-body">
            @if ($prestamos->isEmpty())
                <p class="text-center">No hay préstamos registrados en este periodo.</p>
            @else
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prestamos as $prestamo)
                            <tr>
                                <td>{{ $prestamo->idPrestamo }}</td>
                                <td>{{ date('d/m/Y', strtotime($prestamo->fecha)) }}</td>
                                <td>{{ $prestamo->monto }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header bg-success text-center">
            <b>Otros Descuentos</b>
        </div>
        <div class="card-body">
            @if ($otros->isEmpty())
                <p class="text-center">No hay otros descuentos registrados en este periodo.</p>
            @else
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($otros as $otro)
                            <tr>
                                <td>{{ $otro->idOtrosDescuentos }}</td>
                                <td>{{ date('d/m/Y', strtotime($otro->fecha)) }}</td>
                                <td>{{ $otro->monto }}</td>
                                <td>{{ $otro->motivo }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="mt-3 text-center">
        <a href="{{ route('descargar.pdf', ['idPersona' => $persona->idPersona, 'idPeriodo' => $periodo->idPeriodo]) }}" class="btn btn-primary btn-block">Descargar PDF</a>
    </div>
</div>
@stop

@section('js')
    <script>
        console.log('Detalles del Descuento');
    </script>
@stop
