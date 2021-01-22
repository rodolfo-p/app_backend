<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 15/10/18
 * Time: 11:49 AM
 */

namespace App\Http\Controllers;

namespace App\Http\Controllers\Setup;

use App\Http\Data\util\IdGenerador;
use App\Models\Configuracion\Modulo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;


class SegModuloController extends Controller
{
    public function listar_modulos_padre()
    {
        return response()->json(['success' => true,
            'data' => Modulo::where('seg_modulo_nivel', 0)->get(),
            'message' => 'Lista de Departamentos'], 200);


    }

    public function listar_modulos()
    {
        return response()->json(['success' => true,
            'data' => Modulo::all(),
            'message' => 'Lista de Departamentos'], 200);
    }

    public function registrar_modulo(Request $request)


    {

        $modulo = new Modulo($request->all());
        $modulo->seg_modulo_id = IdGenerador::generaId();
        return response()->json(['success' => true,
            'data' => Modulo::all(),
            'message' => 'Lista de Departamentos'], 200);


    }
}
