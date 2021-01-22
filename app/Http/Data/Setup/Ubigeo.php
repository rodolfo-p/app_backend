<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 23/12/18
 * Time: 04:46 PM
 */

namespace App\Http\Data\Setup;

use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Ubigeo extends Controller
{


    public static function listar_departamentos()
    {
        try {
            $sql = "select desc_dep_sunat, cod_dep_sunat from ubigeo
where cod_dep_sunat is not null
group by desc_dep_sunat, cod_dep_sunat";
            $Query = DB::select($sql);
        } catch (Exception $exception) {
            dd($exception);
        }
        return $Query;
    }

    public static function listar_provincias($id)
    {
        try {
            $sql = "select desc_prov_sunat, cod_prov_sunat 
from ubigeo where cod_dep_sunat= '" . $id . "'
group by desc_prov_sunat, cod_prov_sunat";
            $Query = DB::select($sql);
        } catch (Exception $exception) {
            dd($exception);
        }
        return $Query;
    }

    public static function listar_distritos($id)
    {

        try {
            $sql = "select desc_ubigeo_sunat, 
cod_ubigeo_sunat 
from ubigeo 
where cod_prov_sunat='" . $id . "'";
            $Query = DB::select($sql);
        } catch (Exception $exception) {
            dd($exception);
        }
        return $Query;

    }

}