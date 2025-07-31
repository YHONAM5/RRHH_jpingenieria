@extends('adminlte::page')

@section('title', 'Listado Personal Inactivo')
@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="row">
        <div class="col-md-6">
            <h2><b>LISTADO DE PERSONAL INACTIVO</b></h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ url('personal') }}" class="btn btn-primary">Personal Activo <i class="fas fa-eye"></i></a>
        </div>
    </div>
@stop

@section('content')

        <div class="card">
            <div class="card-header bg-primary">

            </div>
            <div class="card-body">
                {{-- PERSONAL INACTIVO--}}
                <table id="tabla-Empleados" class="table table-striped table-hover">
                    <thead>
                        <tr class="bg-primary">
                            <th>#</th>
                            <th>Nombres y Apellidos</th>
                            <th>Descanso Médico</th>
                            <th>Licencia con Goce</th>
                            <th>Licencia sin Goce</th>
                            <th>Doc. Contrato</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($empleadosInactivos as $item)
                                <tr>
                                    <td></td>
                                    <td><a href="{{url('personal/perfil/'.$item->idContrato)}}">{{ strtoupper($item->ApellidoPaterno.' '.$item->ApellidoMaterno.' '.$item->Nombres) }}</a></td>
                                    <td>
                                        @foreach ($item->descansosMedicos as $descanso)
                                            {{ $descanso->FechaDeInicioDescansoMedico }} - {{ $descanso->FechaDeFinDescansoMedico }} <br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach ($item->licenciasConGoce as $licencia)
                                            {{ $licencia->FechaDeInicioConGoceDeHaber }} - {{ $licencia->FechaDeFinConGoceDeHaber }} <br>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach ($item->licenciasSinGoce as $licencia)
                                            {{ $licencia->FechaDeInicioSinGoceDeHaber }} - {{ $licencia->FechaDeFinSinGoceDeHaber }} <br>
                                        @endforeach
                                    </td>
                                    <td class="text-center">
                                        @if ($item->contratopdf)
                                        <a class="btn btn-primary btn-sm" href="{{ asset('storage').'/'.$item->contratopdf }}" target="_blank">Ver documento</a>   
                                        @else
                                        <button class="btn btn-success btn-sm btn-subir-contrato" data-toggle="modal" data-target="#subirContrato" data-empleado="{{ $item->idEmpleado }}">Subir</button>
                                        @endif
                                    </td>
                                </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @include('rrhh.personal.modales.descanso_medico')
    @include('rrhh.personal.modales.licencia_goce')
    @include('rrhh.personal.modales.licencia_singoce')
    @include('rrhh.personal.modales.otros_documentos')
    @include('rrhh.personal.modales.subirContrato')
@stop

@section('js')
    <script src="{{asset('js/personal/modales.js')}}"></script>
    <script>
        //Rutas para ajax
        const verPersonaUrl = '{{ route("verPersona") }}';
        const guardarContrato = "{{ route('guardarDocContra') }}";
        const descansoMedico = "{{ route('descanso.medico') }}";
        const licenciaConGoce ="{{ route('licencia.Goce') }}";
        const licenciaSinGoce = "{{ route('licencia.Sin.goce') }}";
        const otrosDocumentos = "{{ route('otros.documentos') }}";

        //TOKEN CSRF
        const csrfToken = "{{ csrf_token() }}";
    </script>
@stop