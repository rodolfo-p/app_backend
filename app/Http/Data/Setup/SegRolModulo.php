<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 07/01/18
 * Time: 12:44 AM
 */

namespace App\Http\Data\Setup;

use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\util\IdGenerador;

class SegRolModulo extends Controller
{
    /**  ==========listar modulos asignados a un rol =================*/
    public static function listar_modulos_asignados_rol($seg_rol_id, $seg_modulo_id)
    {
        try {
            $sql = "select a.seg_modulo_id,a.seg_modulo_nombre, 
(select count(b.seg_modulo_id)
from seg_rol_modulo as b  
where b.seg_modulo_id=a.seg_modulo_id 
and seg_rol_id ='" . $seg_rol_id . "') as asignado
from seg_modulo as a 
where a.seg_modulo_nivel= '1' 
and a.Parent_seg_modulo_id= '" . $seg_modulo_id . "'

group by a.seg_modulo_id,a.seg_modulo_nombre";
            $response= DB::select($sql);


        } catch (Exception $e) {
            $response=$e;

        }
        return $response;

    }
    /**  ==========listar accesos asignados a un rol =================*/


    /**  ==========Asignar rol modulo =================*/
    public static function insertar_roles_modulos($seg_modulo_id, $seg_rol_id, $Parent_seg_modulo_id)
    {
        try {
            $queryDelete = "delete from seg_rol_modulo
        where seg_rol_id= '$seg_rol_id' and seg_modulo_id in (
        select seg_modulo_id from seg_modulo where Parent_seg_modulo_id='" . $Parent_seg_modulo_id . "')";
            DB::delete($queryDelete);
            foreach ($seg_modulo_id as $modulo) {
                DB::table('seg_rol_modulo')->insert(
                    array('seg_rol_modulo_id' => IdGenerador::generaId(),
                        'seg_rol_id' => $seg_rol_id,
                        'seg_modulo_id' => $modulo)
                );
            }
        } catch (Exception $e) {
            dd($e);
        }

    }
    /**  ==========Asignar rol modulo =================*/


}