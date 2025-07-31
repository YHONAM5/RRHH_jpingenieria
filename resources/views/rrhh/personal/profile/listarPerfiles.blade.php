<!-- listarPerfiles.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Listado de Perfiles</h1>
        @foreach ($perfiles as $perfil)
            <div class="perfil">
               <h3>Licencias sin goce de haber:</h3>
                <ul>
                    @foreach ($perfil->licenciasSinGoceDeHaber as $licencia)
                        <li>Inicio: {{ $licencia->FechaDeInicioSinGoceDeHaber }}, Fin: {{ $licencia->FechaDeFinSinGoceDeHaber }}</li>
                    @endforeach
                </ul>

                <h3>Licencias con goce de haber:</h3>
                <ul>
                    @foreach ($perfil->licenciasConGoceDeHaber as $licencia)
                        <li>Inicio: {{ $licencia->FechaDeInicioConGoceDeHaber }}, Fin: {{ $licencia->FechaDeFinConGoceDeHaber }}</li>
                    @endforeach
                </ul>

                <h3>Descansos médicos:</h3>
                <ul>
                    @foreach ($perfil->descansosMedicos as $descanso)
                        <li>Inicio: {{ $descanso->FechaDeInicioDescansoMedico }}, Fin: {{ $descanso->FechaDeFinDescansoMedico }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
@endsection