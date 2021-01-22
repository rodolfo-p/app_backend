<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 28/09/18
 * Time: 05:33 PM
 */

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Http\Data\Setup\SegRolModulo;
use Illuminate\Http\Request;
use Exception;
use App\Http\Controllers\SecurityToken;


class SegRolModuloController extends Controller
{

    /** Lista los modulos asignados a un rol */
    public function listar_modulo_rol(Request $request)
    {

        $seg_rol_id = $request->input('seg_rol_id');
        $seg_modulo_id = $request->input('seg_modulo_id');
        $lista = SegRolModulo::listar_modulos_asignados_rol($seg_rol_id, $seg_modulo_id);
        $listaAccesos = [];
        foreach ($lista as $key => $acceso) {
            if ($acceso->asignado == '1') {
                $acceso->asignado = true;
            } else {
                $acceso->asignado = false;
            }
            array_push($listaAccesos, $acceso);
        }
        $jResponse['success'] = true;
        $jResponse['data'] = $listaAccesos;
        return response()->json($jResponse, 200);
    }
    /** Lista los modulos asignados a un rol */


    /** Este metodo asigna modulos a un rol*/


    public function asignar_modulos_rol(Request $request)
    {
        $params = json_decode(file_get_contents("php://input"));
        $seg_modulo_id = $params->seg_modulo_id;
        $seg_rol_id = $params->seg_rol_id;
        $Parent_seg_modulo_id = $params->Parent_seg_modulo_id;
        SegRolModulo::insertar_roles_modulos($seg_modulo_id, $seg_rol_id, $Parent_seg_modulo_id);
        return response()->json(['success' => true,
            'data' => 'ok',
            'message' => 'Operacion Correcta'], 200);
    }
    /** Este metodo asigna modulos a un rol*/


}
