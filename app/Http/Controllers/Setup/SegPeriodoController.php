<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 09/11/18
 * Time: 10:53 AM
 */

namespace App\Http\Controllers;

namespace App\Http\Controllers\Setup;


use App\Http\Data\util\IdGenerador;
use App\Models\Configuracion\Periodo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Validator;


class SegPeriodoController extends Controller
{


    public function listar_periodos()
    {
        return response()->json(['success' => true,
            'data' => Periodo::all(),
            'message' => 'Lista de periodos'], 200);

    }

    public function listar_periodo($id)
    {
        return response()->json(['success' => true,
            'data' => Periodo::find($id),
            'message' => 'Lista periodo'], 200);
    }

    public function listar_periodo_activo()
    {
        return response()->json(['success' => true,
            'data' => Periodo::where('cont_periodo_estado', true)->first(),
            'message' => 'Periodo Activo'], 200);
    }

    public function insertar_periodo(Request $request)
    {
        request()->validate([
            'cont_periodo_periodo' => 'required',
            'cont_periodo_igv' => 'required',
            'cont_periodo_anio' => 'required',
        ]);
        $periodo = new Periodo();
        $periodo->cont_periodo_id = IdGenerador::generaId();
        $periodo->cont_periodo_periodo = $request->input('cont_periodo_periodo');
        $periodo->cont_periodo_igv = $request->input('cont_periodo_igv');
        $periodo->cont_periodo_estado = true;
        $periodo->cont_periodo_anio = $request->input('cont_periodo_anio');
        $periodo->save();
        return response()->json(['success' => true,
            'data' => Periodo::all(),
            'message' => 'Lista de periodos'], 200);

    }

    public function editar_periodo(Request $request, $id)
    {
        request()->validate([
            'cont_periodo_periodo' => 'required',
            'cont_periodo_igv' => 'required',
            'cont_periodo_estado' => 'required',
            'cont_periodo_anio' => 'required',
        ]);
        Periodo::find($id)->update($request->all()
        /*array(
            'cont_periodo_periodo' => $request->input('cont_periodo_periodo'),
            'cont_periodo_igv' => $request->input('cont_periodo_igv'),
            'cont_periodo_estado' => $request->input('cont_periodo_estado'),
            'cont_periodo_anio' => $request->input('cont_periodo_anio'),
        )*/
    );

        return response()->json(['success' => true,
            'data' => Periodo::all(),
            'message' => 'Lista de periodos'], 200);
    }

    public function eliminar_periodo($id)
    {
        Periodo::find($id)->delete();
        return response()->json(['success' => true,
            'data' => Periodo::all(),
            'message' => 'Lista de periodos'], 200);
    }

}
