<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<!-- jQuery y Moment.js (si aún no están en tu proyecto) -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <!-- JS del Date Range Picker -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

</head>
<body>

<div class="card">
    <div class="card-header">
        <b>Registro por router</b>
    </div>
    <div class="card-body">
        <div class="form-group">
            <form action="{{route('buscarpor.estacion')}}" method="POST">
                @csrf
                <select class="form-control" name="idEstacion" id="" onchange="this.form.submit()" >
                    <option hidden value="">Seleccione Estación</option>
                    @foreach ($estaciones as $estacion)
                        <option value="{{$estacion->idEstacionDeTrabajo}}">{{$estacion->NombreEstacionDeTrabajo}}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>

@if (isset($empleados) && count($empleados) > 0)
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <strong><i class="fas fa-info-circle"></i> ¡Recuerda!</strong> Se registrará tareo a todos los trabajadores que tengan check
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="card">
    <div class="card-header bg-primary">
        Empleados en estación: <b>{{$nombre_estacion->NombreEstacionDeTrabajo}}</b>
    </div>
    <form id="form_router" action="{{route('tareo.router')}}" method="POST">
        @csrf
    <input hidden type="number" name="idEstacion" value="{{$idEstacion}}">
    <div class="card-body">
        <button type="button" id="marcarDesmarcarTodos" class="btn btn-primary">Marcar/Desmarcar Todos</button>
        <table class="table table-bordered" id="empleados_estacion">
            <thead>
                <tr>
                    <th class="p-1"></th>
                    <th class="p-1">Nombres</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($empleados as $item)
                <tr>
                    <td class="text-center p-1"><input class="trabajador-checkbox" name="contratos[]" checked type="checkbox" value="{{$item->idContrato}}"></td>
                    <td class="p-1">{{ strtoupper($item->Nombres.' '.$item->ApellidoPaterno.' '.$item->ApellidoMaterno) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
    <div class="card-body">
        <div class="alert alert-info" role="alert">
          No se encontraron empleados.
        </div>
      </div>
    @endif

@if (isset($empleados) && count($empleados) > 0)
<div class="custom-control custom-switch mt-2">
    <input type="checkbox" class="custom-control-input" data-toggle="collapse" data-target="#card_otros" id="SwitchOtros">
    <label class="custom-control-label" for="SwitchOtros">Registrar tareos de trabajadores de otra estación</label>
</div>
<div class="collapse" id="card_otros">
<div class="card mt-2">
    <div class="card-header bg-primary">
        Registro de Empleados de otra estación en <b>{{$nombre_estacion->NombreEstacionDeTrabajo}}</b>
    </div>
    <div class="card-body">
        <div class="form-group">
        <label for="">Busque trabajador:</label>
        <select class="js-example-basic-multiple form-control" name="contratos[]" multiple="multiple" id="select-empleados">
            @foreach ($no_empleados as $item)
                <option value="{{$item->idContrato}}">{{ strtoupper($item->Nombres.' '.$item->ApellidoPaterno.' '.$item->ApellidoMaterno) }}</option>
            @endforeach
        </select>
    </div>
    </div>
</div>
</div>
<div class="card mt-3">
    <div class="span-cambio card-header bg-primary">
        <b>Datos de Tareo</b>
    </div>
    <div class="card-body">

            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="customSwitch1">
                <label class="custom-control-label" for="customSwitch1">Tareo Nocturno</label>
            </div>
            <input hidden type="number" id="horario" name="horario" value="0">
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="inputCampo1">Fecha: <span class="text-danger">*</span></label>
                            <div class="col">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="span-cambio input-group-text bg-primary"><i class="fas fa-calendar-alt"></i></span>
                                        <input required name="fecha" type="date" class="form-control">
                                    </div>
                                    <small class="form-text text-muted">Fecha de inicio para registrar tareo</small>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="col-md-4">
                            <label for="inputCampo1">N° de días trabajados: <span class="text-danger">*</span></label>
                            <div class="col">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="span-cambio input-group-text bg-primary"><i class="fas fa-person-booth"></i></span>
                                        <input required name="dias_trabajados" type="number" class="form-control" value="0">
                                    </div>
                                    <small class="form-text text-muted">Cantidad de días de trabajo normal</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="inputCampo3">N° de descansos programados:</label>
                            <div class="col">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="span-cambio input-group-text bg-primary"><i class="fas fa-bed"></i></span>
                                        <input required name="dias_descansos" type="number" class="form-control" value="0">
                                    </div>
                                    <small class="form-text text-muted">Cantidad de descansos programados, campo NO obligatorio</small>
                                </div>
                            </div>
                        </div> --}}
                        <div class="col-md-4">
                            <div class="col">
                                <div class="form-group">
                                    <label for="dias_descansos_seleccionados">Selecciona los días de descanso:</label>
                                    <div id="contenedor-fechas">
                                        <div class="input-group mb-2">
                                            <input type="date" name="dias_descanso[]" class="form-control" required>
                                            <input type="date" name="dias_descanso[]" class="form-control" required>

                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-danger btn-sm eliminar-fecha" title="Eliminar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-secondary" id="agregar_fecha">
                                            <i class="fas fa-plus"></i> Agregar fecha
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" id="limpiar_fechas">
                                            <i class="fas fa-trash"></i> Limpiar todas
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="inputCampo3">N° de descansos programados:</label>
                            <div class="col">
                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="fecha_inicio">Fecha de inicio:</label>
                                        <input type="date" id="fecha_inicio" name="fecha_inicio_descanso" class="form-control">
                                    </div>

                                    <div class="form-group">
                                        <label for="fecha_fin">Fecha de fin:</label>
                                        <input type="date" id="fecha_fin" name="fecha_fin_descanso" class="form-control">
                                    </div>
                                    <small class="form-text text-muted">Cantidad de descansos programados, campo NO obligatorio</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group row">
                        {{-- <div class="col-md-6">
                            <label for="inputCampo1">Rango de descanso<span class="text-danger">*</span></label>
                            <div class="col">
                                <!-- Después del bloque donde está "Selecciona los días de descanso" -->
                                <div class="form-group mt-3">
                                    <label for="rango_fechas">Selecciona un rango de fechas:</label>
                                    <input type="text" class="form-control" id="rango_fechas" name="rango_fechas" autocomplete="off" />
                                </div>
                            </div>
                        </div> --}}
                        <div class="col-md-6">
                            <label for="inputCampo1">Hora entrada: <span class="text-danger">*</span></label>
                            <div class="col">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="span-cambio input-group-text bg-primary"><i class="fas fa-building"></i></span>
                                        <input required name="hora_inicio" type="time" class="form-control">
                                    </div>
                                    <small class="form-text text-muted"></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="inputCampo3">Hora salida: <span class="text-danger">*</span></label>
                            <div class="col">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="span-cambio input-group-text bg-primary"><i class="fas fa-building"></i></span>
                                        <input required name="hora_fin" type="time" class="form-control">
                                    </div>
                                    <small class="form-text text-muted"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success">Registrar Tareo <i class="fas fa-save"></i></button>
        </form>
    </div>
</div>
@endif

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("marcarDesmarcarTodos").addEventListener("click", function() {
            var checkboxes = document.querySelectorAll('input.trabajador-checkbox');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = !checkbox.checked;
            });
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const contenedor = document.getElementById("contenedor-fechas");
        const btnAgregar = document.getElementById("agregar_fecha");
        const btnLimpiar = document.getElementById("limpiar_fechas");

        // Delegación de eventos para botones de eliminar
        contenedor.addEventListener("click", function (e) {
            if (e.target.closest('.eliminar-fecha')) {
                e.target.closest('.input-group').remove();
            }
        });

        btnAgregar.addEventListener("click", function () {
            const grupo = document.createElement("div");
            grupo.className = "input-group mb-2";

            grupo.innerHTML = `
                <input type="date" name="dias_descanso[]" class="form-control" required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-danger btn-sm eliminar-fecha" title="Eliminar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            contenedor.appendChild(grupo);
        });

        btnLimpiar.addEventListener("click", function () {
            contenedor.innerHTML = '';
            // Agrega al menos un campo vacío de nuevo
            btnAgregar.click();
        });
    });
</script>
<!-- CSS del Date Range Picker -->
<script>
$(function() {
    $('#rango_fechas').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD',
            separator: ' - ',
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar',
            fromLabel: 'Desde',
            toLabel: 'Hasta',
            customRangeLabel: 'Personalizado',
            weekLabel: 'S',
            daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
            monthNames: [
                'Enero','Febrero','Marzo','Abril','Mayo','Junio',
                'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
            ],
            firstDay: 1
        },
        opens: 'right'
    });
});
</script>

</body>
</html>
