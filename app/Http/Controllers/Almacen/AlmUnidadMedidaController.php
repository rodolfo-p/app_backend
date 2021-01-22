<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 29/10/18
 * Time: 02:57 PM
 */

namespace App\Http\Controllers;

namespace App\Http\Controllers\Almacen;

use App\Http\Controllers\Controller;
use App\Http\Data\util\IdGenerador;
use App\Models\Almacen\Producto;
use App\Models\Almacen\UnidadMedida;
use Illuminate\Http\Request;
use Validator;

class AlmUnidadMedidaController extends Controller
{

    public function listar_unidades_medidas()
    {
        return response()->json(['success' => true,
            'data' => UnidadMedida::all(),
            'message' => 'Lista de Unidades de Medida'], 200);
    }

    public function registrar_unidad_medida(Request $request)
    {
        request()->validate([
            'alm_unidad_medida_nombre' => 'required',
            'alm_unidad_medida_simbolo' => 'required',
        ]);
        $unidad_medadida = new UnidadMedida($request->all());
        $unidad_medadida->alm_unidad_medida_simbolo_impresion = is_null($request->input('alm_unidad_medida_simbolo_impresion'))
            ? $request->input('alm_unidad_medida_simbolo')
            : $request->input('alm_unidad_medida_simbolo_impresion');

        $unidad_medadida->alm_unidad_medida_id = IdGenerador::generaId();
        $unidad_medadida->save();
        return response()->json(['success' => true,
            'data' => UnidadMedida::all(),
            'message' => 'Lista de Unidades de Medida'], 200);

    }

    public function listar_unidad_pedida($id)
    {
        return response()->json(['success' => true,
            'data' => UnidadMedida::find($id),
            'message' => 'Lista de Unidad de Medida'], 200);
    }

    public function actualizar_unidad_medida(Request $request, $id)
    {
        request()->validate([
            'alm_unidad_medida_nombre' => 'required',
            'alm_unidad_medida_simbolo' => 'required',
            'alm_unidad_medida_estado' => 'required',
        ]);
        UnidadMedida::find($id)->update($request->all());
        return response()->json(['success' => true,
            'data' => UnidadMedida::all(),
            'message' => 'Lista de Unidades de Medida'], 200);

    }


    public function eliminar_unidad_medida($id)
    {
        UnidadMedida::find($id)->delete();
        return response()->json(['success' => true,
            'data' => UnidadMedida::all(),
            'message' => 'Lista de Unidades de Medida'], 200);
    }

    public function listar_unidad_medida_activo()
    {
        return response()->json(['success' => true,
            'data' => UnidadMedida::where('alm_unidad_medida_estado', true)->get(),
            'message' => 'Lista de Unidades de Medida'], 200);
    }

}
