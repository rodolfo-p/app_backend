<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 09/11/18
 * Time: 10:16 AM
 */

namespace App\Http\Controllers;

namespace App\Http\Controllers\Almacen;

use App\Http\Controllers\Controller;
use App\Http\Data\util\IdGenerador;
use App\Models\Almacen\Almacen;
use App\Models\Configuracion\TipoComprobante;
use App\Models\Venta\FlujoSerie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;


class AlmAlmacenController extends Controller
{


    public function listar_almacenes()
    {
        return response()->json(['success' => true,
            'data' => Almacen::all(),
            'message' => 'Lista de Almacenes'], 200);

    }

    public function listar_almacen($id)
    {
        return response()->json(['success' => true,
            'data' => Almacen::find($id),
            'message' => 'Lista de Almacenes'], 200);


    }
    public function eliminar_almacen($id)
    {
        Almacen::find($id)->delete();
        return response()->json(['success' => true,
            'data' => Almacen::all(),
            'message' => 'Lista de Almacenes'], 200);
    }

    public function editar_almacen(Request $request, $id)
    {
        request()->validate([
            'alm_almacen_nombre' => 'required',
        ]);
        Almacen::find($id)->update($request->all());
        return response()->json(['success' => true,
            'data' => Almacen::all(),
            'message' => 'Lista de Almacenes'], 200);
    }



    public function insertar_almacen(Request $request)
    {
        request()->validate([
            'alm_almacen_nombre' => 'required',

        ]);
        $alm_almacen_id = IdGenerador::generaId();
        $almacen = new Almacen($request->all());
        $almacen->alm_almacen_id = $alm_almacen_id;
        $almacen->save();
        if (is_null(Almacen::all()->first())) {
            foreach (TipoComprobante::all() as $tipoComprobante) {
                if ($tipoComprobante->doc_tipo_comprobante_codigo != '99') {
                    if ($tipoComprobante->doc_tipo_comprobante_codigo == '01') {
                        $vent_serie = 'F001';
                    } elseif ($tipoComprobante->doc_tipo_comprobante_codigo == '03') {
                        $vent_serie = 'B001';
                    } elseif ($tipoComprobante->doc_tipo_comprobante_codigo == '09') {
                        $vent_serie = 'T001';
                    } elseif ($tipoComprobante->doc_tipo_comprobante_codigo == '07') {
                        $vent_serie = 'B701';
                        $flujo_serie = new FlujoSerie();
                        $flujo_serie->vent_serie = 'F701';
                        $flujo_serie->vent_tipo_comprobante_id = $tipoComprobante->doc_tipo_comprobante_id;
                        $flujo_serie->vent_numero = '0';
                        $flujo_serie->vent_num_cajero = 1;
                        $flujo_serie->vent_almacen_id = $alm_almacen_id;
                        $flujo_serie->save();

                    } elseif ($tipoComprobante->doc_tipo_comprobante_codigo == '08') {
                        $vent_serie = 'B801';
                        $flujo_serie = new FlujoSerie();
                        $flujo_serie->vent_serie = 'F801';
                        $flujo_serie->vent_tipo_comprobante_id = $tipoComprobante->doc_tipo_comprobante_id;
                        $flujo_serie->vent_numero = '0';
                        $flujo_serie->vent_num_cajero = 1;
                        $flujo_serie->vent_almacen_id = $alm_almacen_id;
                        $flujo_serie->save();
                    }
                    $flujo_serie = new FlujoSerie();
                    $flujo_serie->vent_serie = $vent_serie;
                    $flujo_serie->vent_tipo_comprobante_id = $tipoComprobante->doc_tipo_comprobante_id;
                    $flujo_serie->vent_numero = '0';
                    $flujo_serie->vent_num_cajero = 1;
                    $flujo_serie->vent_almacen_id = $alm_almacen_id;
                    $flujo_serie->save();


                }
            }
        }
        return response()->json(['success' => true,
            'data' => Almacen::all(),
            'message' => 'Lista de Almacenes'], 200);
    }


}
