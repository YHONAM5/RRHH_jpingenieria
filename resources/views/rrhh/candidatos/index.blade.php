@extends('adminlte::page')

@section('title', 'Listado de Candidatos')
@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="row">
        <div class="col-md-6">
            <h2><b>LISTADO DE CANDIDATOS</b></h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('candidatos.create') }}" class="btn btn-primary">Agregar Candidato <i class="fas fa-plus"></i></a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-primary">
            <!-- Puedes agregar algo aquí si es necesario -->
        </div>
        <div class="card-body">
            <table id="tabla-Candidatos" class="table table-striped table-hover">
                <thead>
                    <tr class="bg-primary">
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Experiencia</th>
                        <th>Cargo a Optar</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($candidatos as $candidato)
                        <tr>
                            <td>{{ $candidato->id }}</td>
                            <td>{{ $candidato->idPersona }}</td>
                            <td>{{ $candidato->MesesDeExperiencia }}</td>
                            <td>{{ $candidato->idCargoAOptar }}</td>
                            <td>
                                <a href="{{ route('candidatos.show', $candidato->id) }}" class="btn btn-info btn-sm">Ver</a>
                                <a href="{{ route('candidatos.edit', $candidato->id) }}" class="btn btn-warning btn-sm">Editar</a>
                                <form action="{{ route('candidatos.destroy', $candidato->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#tabla-Candidatos').DataTable({
                "responsive": true,
                "autoWidth": false,
            });

            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: '{{ session('success') }}',
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ session('error') }}',
                });
            @endif
        });
    </script>
@stop