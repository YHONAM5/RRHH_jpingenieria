<body>
<div class="modal fade" id="tareoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="titleModal"><b>TAREO</b></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formTareo">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            {{--ojo qui--}}
                            <input type="hidden" name="contrato" class="form-control" placeholder="Contrato">
                            <input type="hidden" name="tareo" id="tareo" class="form-control" placeholder="Tareo">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="estacion">
                                <i class="fas fa-building"></i> Estación de Trabajo:
                            </label>
                            <select name="estacion" class="form-control" required>
                                <option value="" hidden>Seleccione Estación</option>
                                @foreach ($estaciones as $item)
                                    <option value="{{$item->idEstacionDeTrabajo}}">{{$item->NombreEstacionDeTrabajo}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="condicionTareo">
                                <i class="fas fa-check-circle"></i> Condición del Tareo:
                            </label>
                            <select name="condicionTareo" id="condicionTareo" class="form-control" required>
                                <option value="" id="selector_condicion" hidden>Seleccione Condicion</option>
                                @foreach ($condicionTareo as $item)
                                    <option value="{{$item->idCondicionDeTareo}}">{{$item->NombreCondicionDeTareo}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="fecha">
                                <i class="far fa-calendar-alt"></i> Fecha:
                            </label>
                            <input type="date" class="form-control" name="fecha" required>
                        </div>
                        <div class="form-group col-md-6" id="grupoHoraIngreso">
                            <label for="horaIngreso">
                                <i class="far fa-clock"></i> Ingreso:
                            </label>
                            <input type="time" class="form-control" name="horaIngreso" id="horaIngreso">
                        </div>
                    </div>

                    <div class="form-group" id="grupoAlmuerzo">
                        <label for="inicioAlmuerzo">
                            <i class="fas fa-utensils"></i> Almuerzo:
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="time" class="form-control" name="inicioAlmuerzo" id="inicioAlmuerzo" placeholder="Inicio">
                            </div>
                            <div class="col-md-6">
                                <input type="time" class="form-control" name="finAlmuerzo" id="finAlmuerzo" placeholder="Fin">
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="grupoHoraSalida">
                        <label for="horaSalida">
                            <i class="far fa-clock"></i> Salida:
                        </label>
                        <input type="time" class="form-control" name="horaSalida" id="horaSalida">
                    </div>

                    <!-- Campos adicionales para condiciones especiales -->
                    {{-- <div class="form-group" id="grupoObservaciones" style="display: none;">
                        <label for="observaciones">
                            <i class="fas fa-comment"></i> Observaciones:
                        </label>
                        <textarea class="form-control" name="observaciones" id="observaciones" rows="3" placeholder="Ingrese observaciones adicionales"></textarea>
                    </div> --}}

                    <!-- Información adicional -->
                    <div class="alert alert-info" id="infoCondicion" style="display: none;">
                        <i class="fas fa-info-circle"></i>
                        <span id="mensajeInfo" name="mensajeInfo"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100">
                    <button type="button" id="btnGuardarTareo" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    {{-- <button type="button" id="btnEliminarTareo" class="btn btn-danger m-2">
                        <i class="fas fa-trash-alt"></i> Eliminar
                    </button> --}}
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->

</body>

