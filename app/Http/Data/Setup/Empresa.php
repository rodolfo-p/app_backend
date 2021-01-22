<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 07/01/18
 * Time: 12:05 AM
 */

namespace App\Http\Data\Setup;

use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\util\IdGenerador;

class Empresa extends Controller
{


    public static function empresa()
    {
        $result = new \stdClass();
        try {
            $sql = "select emp_empresa_id,
 emp_empresa_ruc,
 emp_empresa_razon_social,
 emp_empresa_nombre_comercial,
 emp_empresa_telefono,
 emp_empresa_direccion,
 emp_empresa_codigo_ubigeo,
 emp_empresa_direccion_departamento,
 emp_empresa_direccion_provincia,
 emp_empresa_direccion_distrito,
 emp_empresa_codigopais,
 emp_empresa_usuariosol,
 emp_empresa_clavesol ,
 emp_empresa_tipoproceso,
 emp_empresa_llave_ruc_dni,
 emp_empresa_formato_doc_imp,
 emp_empresa_regimen_tributario,
 emp_empresa_email,
 emp_empresa_delivery,
 emp_empresa_firma_digital_passwd,
 emp_empresa_logo_url,
 emp_empresa_firma_digital,
 emp_empresa_calculo_total,
 emp_empresa_ose
   from emp_empresa";
            $Query = DB::select($sql);
            if (count($Query) >= 1) {
                $result = $Query[0];
            }
        } catch (\Exception $exception) {

        }
        return $result;
    }


    public static function listar_nombre_departamento($id)
    {
        try {
            $sql = "select desc_dep_sunat from ubigeo where cod_dep_sunat= '" . $id . "' group by desc_dep_sunat";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }

        return $Query[0]->desc_dep_sunat;

    }

    public static function listar_nombre_provincia($id)
    {
        try {
            $sql = "select desc_prov_sunat from ubigeo where cod_prov_sunat= '" . $id . "' group by desc_prov_sunat";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            $Query = $e;
        }
        return $Query[0]->desc_prov_sunat;

    }

    public static function listar_nombre_distrito($id)
    {
        try {
            $sql = "select desc_ubigeo_sunat from ubigeo where cod_ubigeo_sunat= '" . $id . "' group by desc_ubigeo_sunat";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            $Query = $e;
        }
        return $Query[0]->desc_ubigeo_sunat;
    }
}
