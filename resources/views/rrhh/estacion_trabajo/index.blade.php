@extends('adminlte::page')

@section('title', 'Tareos')
@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)

@section('content_header')
    <h2><b>ESTACIONES DE TRABAJO</b></h2>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            Listado de estaciones
        </div>
        <div class="card-body">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre Estación</th>
                        <th>Regimen</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($estaciones as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->NombreEstacionDeTrabajo }}</td>
                        <td>{{ $item->tipo }}</td>
                        <td> 
                            @if($item->tipo == 1)
                            <span><b>Activo</b></span>
                            @else
                            <span><b>INACTIVO</b></span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>                
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')

@stop