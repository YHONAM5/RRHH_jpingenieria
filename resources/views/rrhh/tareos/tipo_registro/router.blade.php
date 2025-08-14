<div class="card">
    <div class="card-header">
        <b>Registro por router</b>
    </div>
    <div class="card-body">
        <div class="form-group">
            <form action="{{route('buscarpor.estacion' )}}" method="POST">
                @csrf
                <select class="form-control" name="idEstacion" id="" onchange="this.form.submit()">
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

<!-- El formulario principal comienza aquí -->
<form id="form_router" action="{{route('tareo.router')}}" method="POST">
    @csrf
    <input hidden type="number" name="idEstacion" value="{{$idEstacion}}">

    <div class="card">
        <div class="card-header bg-primary">
            Empleados en estación: <b>{{$nombre_estacion->NombreEstacionDeTrabajo}}</b>
        </div>
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
                                    <label for="dias_descansos_seleccionados">Selecciona los días de descanso:<span class="text-danger">*</span></label>
                                    <div id="contenedor-fechas">
                                        <!-- Inputs de fecha se agregarán aquí dinámicamente -->
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
                            <label for="inputCampo3">Descanso seguido fecha:<span class="text-danger">*</span> Inicio-Fin</label>
                            <div class="col">
                                <div class="form-group">
                                    <div class="form-group">

                                        <span class="span-cambio input-group-text bg-primary"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="date" name="fechas_inicio_rango[]" class="form-control">
                                    </div>
                                    <div class="form-group">

                                        <span class="span-cambio input-group-text bg-primary"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="date" name="fechas_fin_rango[]" class="form-control">
                                    </div>
                                    <div id="contenedor-fechas-rango">
                                        <!-- Inputs de rango de fechas se agregara dinamicamente-->
                                    </div>
                                    <small class="form-text text-muted">Estabesca el rango de dias descansados.</small>

                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-secondary" id="agregar_fecha_rango">
                                            <i class="fas fa-plus"></i> Agregar rango
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" id="limpiar_fechas_rango">
                                            <i class="fas fa-trash"></i> Limpiar todas
                                        </button>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="col">
                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="rango_fechas">Selecciona un rango de fechas:</label>
                                        <input type="text" name="rango_fechas" id="rango_fechas" class="form-control" autocomplete="off">
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group row">
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
        </div>
    </div>
    <!-- La etiqueta de cierre del formulario se mueve aquí -->
</form>
@else
<div class="card-body">
    <div class="alert alert-info" role="alert">
      No se encontraron empleados.
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

        const contenedorRango = document.getElementById("contenedor-fechas-rango");
        const btnAgregarRango = document.getElementById("agregar_fecha_rango");
        const btnLimpiarRango = document.getElementById("limpiar_fechas_rango");

        // Función para agregar un nuevo campo de fecha
        function agregarCampoFecha() {
            const grupo = document.createElement("div");
            grupo.className = "input-group mb-2";
            grupo.innerHTML = `
                <span class="span-cambio input-group-text bg-primary"><i class="fas fa-calendar-alt"></i></span>
                <input type="date" name="dias_descanso[]" class="form-control" required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-danger btn-sm eliminar-fecha" title="Eliminar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            contenedor.appendChild(grupo);
        }
        function agregarCampoFechaRango() {
            const grupoRango = document.createElement("div");
            grupoRango.className = "input-group-rango mb-2";
            grupoRango.innerHTML = `
                <div class="form-group">
                    <span class="span-cambio input-group-text bg-primary"><i class="fas fa-calendar-alt"></i></span>
                    <input type="date" name="fechas_inicio_rango[]" class="form-control">
                </div>
                <div class="form-group">
                    <span class="span-cambio input-group-text bg-primary"><i class="fas fa-calendar-alt"></i></span>
                    <input type="date" name="fechas_fin_rango[]" class="form-control">
                </div>
            `;
            contenedorRango.appendChild(grupoRango);
        }

        // Delegación de eventos para botones de eliminar
        contenedor.addEventListener("click", function (e) {
            if (e.target.closest('.eliminar-fecha')) {
                e.target.closest('.input-group').remove();
            }
        });
        contenedorRango.addEventListener("click", function (e) {
            if (e.target.closest('.eliminar-fecha-rango')) {
                e.target.closest('.input-group-rango').remove();
            }
        });

        btnAgregar.addEventListener("click", agregarCampoFecha);
        btnAgregarRango.addEventListener("click", agregarCampoFechaRango);

        btnLimpiar.addEventListener("click", function () {
            contenedor.innerHTML = ''; // Limpia todos los campos
        });
        btnLimpiarRango.addEventListener("click", function () {
            contenedorRango.innerHTML = ''; // Limpia todos los campos
        });
    });
</script>

