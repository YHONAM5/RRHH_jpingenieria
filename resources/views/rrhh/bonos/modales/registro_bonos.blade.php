<div class="modal fade" id="registro_bonos" tabindex="-1" role="dialog" aria-labelledby="registroBonosLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-light font-weight-bold">Registrar Reintegro y Bono Declarado</h5>
            </div>
            <div class="modal-body">
                <form id="form-registro-bono" action="{{ route('registro.bono') }}" method="POST">
                    @csrf
                    <input hidden type="number" id="idContrato" name="idContrato">
                    <div class="form-group">
                        <label for="periodo">Seleccione el periodo: <span class="text-danger">*</span></label>
                        <select required class="form-control" name="idPeriodo" id="select-periodo">
                            <option hidden value="">Seleccione</option>
                            @foreach ($periodos as $periodo)
                                <option value="{{ $periodo->idPeriodo }}">{{ $periodo->NombrePeriodo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fechaBono">Fecha del Reintegro: <span class="text-danger">*</span></label>
                        <div class="col">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="span-cambio input-group-text bg-info"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" name="fecha" class="form-control" id="fechaBono">
                                </div>
                                <small class="form-text text-muted">Se registrará el reintegro en esta fecha.</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="montoBono">Monto del Reintegro: <span class="text-danger">*</span></label>
                        <div class="col">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="span-cambio input-group-text bg-info"><i class="fas fa-comment-dollar"></i></span>
                                    <input type="text" name="monto" class="form-control" pattern="[0-9]+(\.[0-9]{1,2})?" title="Introduce un número decimal válido con hasta 2 decimales">
                                </div>
                                <small class="form-text text-muted">Ingresa el monto correspondiente al reintegro.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Nuevo campo para Bono Declarado -->
                    <div class="form-group">
                        <label for="bonoDeclarado">Monto del Bono Declarado:</label>
                        <div class="col">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="span-cambio input-group-text bg-info"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="text" name="bono_declarado" class="form-control">
                                </div>
                                <small class="form-text text-muted">Ingresa el monto correspondiente al bono declarado.</small>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>