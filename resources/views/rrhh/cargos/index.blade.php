@extends('adminlte::page')

@section('title', 'Listado Personal')
@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="row">
        <div class="col-md-6">
            <h2><b>LISTADO DE CARGOS</b></h2>
        </div>
        <div class="col-md-6 text-right">
            <button class="btn btn-success">Personal Inactivo <i class="fas fa-eye"></i></button>
        </div>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-header">

    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre de Cargo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cargos as $item)
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->NombreCargo }}</td>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
       
@stop

@section('js')
    <script src="{{asset('js/personal/modales.js')}}"></script>
    <script>
        //Rutas para ajax
        const verPersonaUrl = '{{ route("verPersona") }}';
        //TOKEN CSRF
        const csrfToken = "{{ csrf_token() }}";
    </script>
@stop