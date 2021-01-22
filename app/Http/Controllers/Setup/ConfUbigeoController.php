<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 23/12/18
 * Time: 04:52 PM
 */

namespace App\Http\Controllers\Setup;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use Mail;

use App\Http\Data\Setup\Ubigeo;
use Illuminate\Support\Facades\DB;

class ConfUbigeoController extends Controller
{

    public function listar_departamentos()
    {

        return response()->json(['success' => true,
            'data' => DB::table('ubigeo')
                ->select('desc_dep_sunat', 'cod_dep_sunat')
                ->whereNotNull('cod_dep_sunat')
                ->groupBy('desc_dep_sunat', 'cod_dep_sunat')
                ->get(),
            'message' => 'Lista de Departamentos'], 200);
    }

    public function listar_provincias($id)
    {
        return response()->json(['success' => true,
            'data' => DB::table('ubigeo')
                ->select('desc_prov_sunat', 'cod_prov_sunat')
                ->where('cod_dep_sunat', $id)
                ->groupBy('desc_prov_sunat', 'cod_prov_sunat')
                ->get(),
            'message' => 'Lista de Provincias'], 200);
    }

    public function listar_distritos($id)
    {
        return response()->json(['success' => true,
            'data' => DB::table('ubigeo')
                ->select('desc_ubigeo_sunat', 'cod_ubigeo_sunat')
                ->where('cod_prov_sunat', $id)
                ->get(),
            'message' => 'Lista de Distritos'], 200);

    }
}
