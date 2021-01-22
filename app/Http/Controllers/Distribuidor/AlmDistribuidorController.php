<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 26/02/19
 * Time: 08:24 AM
 */

namespace App\Http\Controllers\Distribuidor;


use App\Http\Data\util\IdGenerador;
use App\Models\Almacen\Distribuidor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use Mail;
use Validator;


class AlmDistribuidorController extends Controller
{


    public function listar_distribuidores()
    {
        return response()->json(['success' => true,
            'data' => Distribuidor::all(),
            'message' => 'Lista Distribuidores'], 201);
    }


    public function listar_distribuidor($id)
    {
        return response()->json(['success' => true,
            'data' => Distribuidor::find($id),
            'message' => 'Lista Distribuidores'], 201);
    }

    public function insertar_distribuidor(Request $request)
    {
        request()->validate([
            'alm_distribuidor_nombres' => 'required',
            'alm_distribuidor_apellidos' => 'required',
            'alm_distribuidor_numero_doc' => 'required',

        ]);
        $distribuidor = new Distribuidor($request->all());
        $distribuidor->alm_distribuidor_id = IdGenerador::generaId();
        $distribuidor->save();
        return response()->json(['success' => true,
            'data' => Distribuidor::all(),
            'message' => 'Lista Distribuidores'], 201);
    }

    public function editar_distribuidor(Request $request, $id)
    {
        request()->validate([
            'alm_distribuidor_nombres' => 'required',
            'alm_distribuidor_apellidos' => 'required',
            'alm_distribuidor_numero_doc' => 'required',
            'alm_distribuidor_estado' => 'required',
            'alm_distribuidor_vehiculo' => 'required',

        ]);
        Distribuidor::find($id)->update($request->all());
        return response()->json(['success' => true,
            'data' => Distribuidor::all(),
            'message' => 'Lista Distribuidores'], 201);
    }

    public function listar_distribuidores_activos()
    {
        return response()->json(['success' => true,
            'data' => Distribuidor::where('alm_distribuidor_estado', true)->get(),
            'message' => 'Lista Distribuidores'], 201);
    }

    public function eliminar_distribuidor($id)
    {
        Distribuidor::find($id)->delete();
        return response()->json(['success' => true,
            'data' => Distribuidor::all(),
            'message' => 'Lista Distribuidores'], 201);
    }

}
