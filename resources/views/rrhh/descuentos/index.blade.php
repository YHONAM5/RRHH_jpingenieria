@extends('adminlte::page')

@section('title', 'Tareos')
@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h2><b>DESCUENTOS</b></h2>
        <!-- Bot贸n para ir a la ruta de bonos -->
        <a href="{{ url('bonos') }}" class="btn btn-primary">
            <i class="fas fa-money-check-alt"></i> Bono / Reintegro
        </a>
    </div>
@stop

@section('content')
        <div class="card px-2">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="home-tab" data-toggle="tab" data-target="#reporte" type="button" role="tab" aria-controls="home" aria-selected="true">Reporte de descuentos</button>
                </li>
                <li class="nav-item ml-1" role="presentation">
                  <button class="nav-link" id="profile-tab" data-toggle="tab" data-target="#registro" type="button" role="tab" aria-controls="profile" aria-selected="false">Registro de descuentos</button>
                </li>
            </ul>
    
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active my-4" id="reporte" role="tabpanel" aria-labelledby="home-tab">
                    @include('rrhh.descuentos.complementos.reporte')
                </div>
                <div class="tab-pane fade my-4" id="registro" role="tabpanel" aria-labelledby="profile-tab">
                    @include('rrhh.descuentos.complementos.registro')
                </div>
                <div class="tab-pane fade my-4" id="hora_extra" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="container">
                    </div>
                </div>
            </div>
        </div>
@include('rrhh.descuentos.modales.registro_descuentos')
@stop

@section('js')
    <script src="{{asset('js/descuentos/reporte.js')}}"></script>
    <script src="{{asset('js/descuentos/registro.js')}}"></script>
    <script>
          const csrfToken = '{{ csrf_token() }}';
    </script>
@stop