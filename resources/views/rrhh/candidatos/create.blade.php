@extends('adminlte::page')

@section('title', 'Agregar Candidato')

@section('content_header')
    <h1>Agregar Candidato</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <form action="{{ route('candidatos.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="idPersona">Persona</label>
                    <input type="text" class="form-control" id="idPersona" name="idPersona" value="{{ old('idPersona') }}" required>
                </div>
                <div class="form-group">
                    <label for="MesesDeExperiencia">Meses de Experiencia</label>
                    <input type="number" class="form-control" id="MesesDeExperiencia" name="MesesDeExperiencia" value="{{ old('MesesDeExperiencia') }}" required>
                </div>
                <div class="form-group">
                    <label for="EtapaPrevia">Etapa Previa</label>
                    <input type="text" class="form-control" id="EtapaPrevia" name="EtapaPrevia" value="{{ old('EtapaPrevia') }}" required>
                </div>
                <div class="form-group">
                    <label for="LinkCurriculum">Link Curriculum</label>
                    <input type="url" class="form-control" id="LinkCurriculum" name="LinkCurriculum" value="{{ old('LinkCurriculum') }}" required>
                </div>
                <div class="form-group">
                    <label for="observacion">Observación</label>
                    <textarea class="form-control" id="observacion" name="observacion">{{ old('observacion') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="EtapaDeLlamada">Etapa de Llamada</label>
                    <input type="text" class="form-control" id="EtapaDeLlamada" name="EtapaDeLlamada" value="{{ old('EtapaDeLlamada') }}" required>
                </div>
                <div class="form-group">
                    <label for="EtapaDeEntrevista">Etapa de Entrevista</label>
                    <input type="text" class="form-control" id="EtapaDeEntrevista" name="EtapaDeEntrevista" value="{{ old('EtapaDeEntrevista') }}" required>
                </div>
                <div class="form-group">
                    <label for="EtapaDeContratacion">Etapa de Contratación</label>
                    <input type="text" class="form-control" id="EtapaDeContratacion" name="EtapaDeContratacion" value="{{ old('EtapaDeContratacion') }}" required>
                </div>
                <div class="form-group">
                    <label for="disponibilidad">Disponibilidad</label>
                    <input type="text" class="form-control" id="disponibilidad" name="disponibilidad" value="{{ old('disponibilidad') }}" required>
                </div>
                <div class="form-group">
                    <label for="fecha_disponibilidad">Fecha de Disponibilidad</label>
                    <input type="date" class="form-control" id="fecha_disponibilidad" name="fecha_disponibilidad" value="{{ old('fecha_disponibilidad') }}" required>
                </div>
                <div class="form-group">
                    <label for="idCargoAOptar">Cargo a Optar</label>
                    <select class="form-control" id="idCargoAOptar" name="idCargoAOptar" required>
                        @foreach($cargos as $cargo)
                            <option value="{{ $cargo->idCargo }}">{{ $cargo->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_proceso">Proceso</label>
                    <input type="text" class="form-control" id="id_proceso" name="id_proceso" value="{{ old('id_proceso') }}" required>
                </div>
                <button type="submit" class="btn btn-primary">Agregar</button>
            </form>
        </div>
    </div>
@stop