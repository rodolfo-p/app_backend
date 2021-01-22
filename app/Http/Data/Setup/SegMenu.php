<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 28/09/18
 * Time: 04:44 PM
 */

namespace App\Http\Data\Setup;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SegMenu extends Controller
{


    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    /**  ==========metodos para la lista de menu =================*/
    public static function listar_menu_por_usuario_padre($userid)
    {
        $sql = "select seg_modulo_nombre as title,seg_modulo_icono as icon,seg_modulo_url as link, Parent_seg_modulo_id 
from seg_modulo where Parent_seg_modulo_id in(
select Parent_seg_modulo_id from seg_modulo where seg_modulo_id in(
 select seg_modulo_id from seg_rol_modulo where seg_rol_id in (
 select seg_rol_id from seg_rol_usuario where user_id= '" . $userid . "'))
) and seg_modulo_nivel='0'
and seg_modulo_estado= '1'
order by seg_modulo_orden
";
        $Query = DB::select($sql);
        return $Query;
    }

    public static function listar_menu_por_usuario_hijo($userid, $Parent_seg_modulo_id)
    {
        $sql = "select rm.seg_modulo_id, m.seg_modulo_nombre  as title,m.seg_modulo_icono as icon ,m.seg_modulo_url as link ,m.Parent_seg_modulo_id from seg_rol_modulo as rm, seg_modulo m 
where seg_rol_id in (select seg_rol_id from seg_rol_usuario where user_id= '" . $userid . "')
and rm.seg_modulo_id=m.seg_modulo_id
and m.Parent_seg_modulo_id= '" . $Parent_seg_modulo_id . "'
and m.seg_modulo_nivel= '1'
and m.seg_modulo_estado= '1'
group by rm.seg_modulo_id";
        $Query = DB::select($sql);
        return $Query;
    }

    /**  ==========metodos para la lista de menu =================*/

}