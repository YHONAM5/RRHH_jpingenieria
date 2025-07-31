<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Models\Candidato;
use App\Models\Cargo;
use Illuminate\Http\Request;

class CandidatoController extends Controller
{
    public function index()
    {
        $candidatos = Candidato::all();
        $cargos = Cargo::all();
        return view('rrhh.candidatos.index', compact('candidatos', 'cargos'));
    }

    public function create()
    {
        $cargos = Cargo::all();
        return view('rrhh.candidatos.create', compact('cargos'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'idPersona' => 'required',
                'MesesDeExperiencia' => 'required|integer',
                'EtapaPrevia' => 'required',
                'LinkCurriculum' => 'required|url',
                'observacion' => 'nullable',
                'EtapaDeLlamada' => 'required',
                'EtapaDeEntrevista' => 'required',
                'EtapaDeContratacion' => 'required',
                'disponibilidad' => 'required',
                'fecha_disponibilidad' => 'required|date',
                'idCargoAOptar' => 'required',
                'id_proceso' => 'required',
            ]);

            $candidato = new Candidato();
            $candidato->idPersona = $request->input('idPersona');
            $candidato->MesesDeExperiencia = $request->input('MesesDeExperiencia');
            $candidato->EtapaPrevia = $request->input('EtapaPrevia');
            $candidato->LinkCurriculum = $request->input('LinkCurriculum');
            $candidato->observacion = $request->input('observacion');
            $candidato->EtapaDeLlamada = $request->input('EtapaDeLlamada');
            $candidato->EtapaDeEntrevista = $request->input('EtapaDeEntrevista');
            $candidato->EtapaDeContratacion = $request->input('EtapaDeContratacion');
            $candidato->disponibilidad = $request->input('disponibilidad');
            $candidato->fecha_disponibilidad = $request->input('fecha_disponibilidad');
            $candidato->idCargoAOptar = $request->input('idCargoAOptar');
            $candidato->id_proceso = $request->input('id_proceso');
            $candidato->save();

            return redirect()->route('candidatos.index')->with('success', 'Candidato registrado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error al registrar el candidato: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $candidato = Candidato::findOrFail($id);
        $cargos = Cargo::all();
        return view('rrhh.candidatos.show', compact('candidato', 'cargos'));
    }

    public function edit($id)
    {
        $candidato = Candidato::findOrFail($id);
        $cargos = Cargo::all();
        return view('rrhh.candidatos.edit', compact('candidato', 'cargos'));
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'MesesDeExperiencia' => 'required|integer',
                'EtapaPrevia' => 'required',
                'LinkCurriculum' => 'required|url',
                'observacion' => 'nullable',
                'EtapaDeLlamada' => 'required',
                'EtapaDeEntrevista' => 'required',
                'EtapaDeContratacion' => 'required',
                'disponibilidad' => 'required',
                'fecha_disponibilidad' => 'required|date',
                'idCargoAOptar' => 'required',
                'id_proceso' => 'required',
            ]);

            $candidato = Candidato::findOrFail($id);
            $candidato->MesesDeExperiencia = $request->input('MesesDeExperiencia');
            $candidato->EtapaPrevia = $request->input('EtapaPrevia');
            $candidato->LinkCurriculum = $request->input('LinkCurriculum');
            $candidato->observacion = $request->input('observacion');
            $candidato->EtapaDeLlamada = $request->input('EtapaDeLlamada');
            $candidato->EtapaDeEntrevista = $request->input('EtapaDeEntrevista');
            $candidato->EtapaDeContratacion = $request->input('EtapaDeContratacion');
            $candidato->disponibilidad = $request->input('disponibilidad');
            $candidato->fecha_disponibilidad = $request->input('fecha_disponibilidad');
            $candidato->idCargoAOptar = $request->input('idCargoAOptar');
            $candidato->id_proceso = $request->input('id_proceso');
            $candidato->save();

            return redirect()->route('candidatos.index')->with('success', 'Candidato actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error al actualizar el candidato: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $candidato = Candidato::findOrFail($id);
            $candidato->delete();
            return redirect()->route('candidatos.index')->with('success', 'Candidato eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error al eliminar el candidato: ' . $e->getMessage());
        }
    }
}