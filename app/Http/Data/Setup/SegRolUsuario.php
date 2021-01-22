<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 19/08/18
 * Time: 06:59 AM
 */

namespace App\Http\Data\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\util\IdGenerador;


class SegRolUsuario extends Controller
{
    protected $connection = 'mysql';

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /** listar roles asignados  a un usuario */
    public static function lista_roles_asignados_a_usuario($seg_usuario_id)
    {
        try {
            $query = "select a.seg_rol_id,a.seg_rol_nombre,
(select count(b.seg_rol_id) 
from seg_rol_usuario as b 
where b.seg_rol_id=a.seg_rol_id 
and b.user_id=
(select id 
from users 
where id= '".$seg_usuario_id."') ) as asignado
from seg_rol as a
group by a.seg_rol_id,a.seg_rol_nombre
";
            $response = DB::select($query);
        } catch (\Exception $e) {
            $response = $e;
        }
        return $response;

    }

    /** listar roles asignados  a un usuario */


    /** asignar roles a un usuario */
    public static function insertar_roles_usuario($seg_rol_id, $user_id)
    {
        try {
            $query = "delete from seg_rol_usuario 
                  where  user_id='$user_id'";
            DB::delete($query);
            foreach ($seg_rol_id as $rol) {
                DB::table('seg_rol_usuario')->insert(
                    array('seg_rol_usuario_id' => IdGenerador::generaIdMombre($rol),
                        'user_id' => $user_id,
                        'seg_rol_id' => $rol)
                );
            }
        } catch (\Exception $e) {
            $response = $e;
        }

    }
    /** asignar roles a un usuario */


}