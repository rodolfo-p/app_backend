<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 22/12/18
 * Time: 06:44 PM
 */

namespace App\Http\Controllers\Setup;

use App\Http\Data\util\IdGenerador;
use App\Models\Venta\FlujoSerie;
use App\Models\Venta\UsuarioSerie;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use Mail;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Http\Data\Setup\GeneraSerie;

class ConfSerieController extends Controller
{

    public function genera_serie($idAlmacen)
    {
        FlujoSerie::genera_serie($idAlmacen);
        $series = FlujoSerie::select('vent_flujo_serie.vent_serie',
            'vent_flujo_serie.vent_tipo_comprobante_id',
            'vent_flujo_serie.vent_num_cajero',
            'vent_flujo_serie.vent_almacen_id',
            'doc_tipo_comprabante.doc_tipo_comprobante_nombre',
            'alm_almacen.alm_almacen_nombre',
            'doc_tipo_comprabante.doc_tipo_comprobante_nombre')
            ->leftJoin('doc_tipo_comprabante', 'vent_flujo_serie.vent_tipo_comprobante_id', 'doc_tipo_comprabante.doc_tipo_comprobante_id')
            ->leftJoin('alm_almacen', 'vent_flujo_serie.vent_almacen_id', 'alm_almacen.alm_almacen_id')
            ->where('alm_almacen.alm_almacen_id', $idAlmacen)
            ->orderBy('vent_flujo_serie.vent_num_cajero', 'vent_flujo_serie.vent_serie')
            ->get();
        return response()->json(
            ['success' => true,
                'data' => $series,
                'message' => 'Operacion Correcta'],
            200);

    }

    public function ver_series($idAlmacen)
    {
        $series = FlujoSerie::select('vent_flujo_serie.vent_serie',
            'vent_flujo_serie.vent_tipo_comprobante_id',
            'vent_flujo_serie.vent_num_cajero',
            'vent_flujo_serie.vent_almacen_id',
            'doc_tipo_comprabante.doc_tipo_comprobante_nombre',
            'alm_almacen.alm_almacen_nombre',
            'doc_tipo_comprabante.doc_tipo_comprobante_nombre')
            ->leftJoin('doc_tipo_comprabante', 'vent_flujo_serie.vent_tipo_comprobante_id', 'doc_tipo_comprabante.doc_tipo_comprobante_id')
            ->leftJoin('alm_almacen', 'vent_flujo_serie.vent_almacen_id', 'alm_almacen.alm_almacen_id')
            ->where('alm_almacen.alm_almacen_id', $idAlmacen)
            ->orderBy('vent_flujo_serie.vent_num_cajero', 'vent_flujo_serie.vent_serie')
            ->get();
        return response()->json(
            ['success' => true,
                'data' => $series,
                'message' => 'Operacion Correcta'],
            200);
    }

    public function ver_series_asignados_usuario($id)
    {
        $lista_series_asignados = array();
        $usuario_id = auth()->user()->id;
        foreach (FlujoSerie::select(
            DB::raw("
            vent_num_cajero,
            vent_almacen_id,
            (select count(*) from vent_usuario_serie as b where b.vent_usuario_user_id=$usuario_id and b.vent_almacen_id=vent_flujo_serie.vent_almacen_id and b.vent_num_cajero=vent_flujo_serie.vent_num_cajero ) as asignado
            ")
        )->where('vent_almacen_id', $id)
                     ->groupBy('vent_flujo_serie.vent_num_cajero', 'vent_flujo_serie.vent_almacen_id')
                     ->get() as $item) {
            $item->asignado = $item->asignado >= 1 ? true : false;

            $item->series = FlujoSerie::select('vent_serie')->where('vent_num_cajero', $item->vent_num_cajero)->get();

            array_push($lista_series_asignados, $item);
        }
        return response()->json(
            ['success' => true,
                'data' => $lista_series_asignados,
                'message' => 'Operacion Correcta'],

            200);
    }

    public function asignar_serie_usuario(Request $request)
    {
        request()->validate([
            'vent_num_cajero' => 'required',
            'vent_almacen_id' => 'required',
        ]);
        $vent_num_cajero = $request->input('vent_num_cajero');
        $vent_almacen_id = $request->input('vent_almacen_id');
        UsuarioSerie::where('vent_usuario_user_id', auth()->user()->id)->delete();
        $usuario_serie = new UsuarioSerie($request->all());
        $usuario_serie->vent_usuario_serie_id = IdGenerador::generaId();
        $usuario_serie->vent_usuario_user_id = auth()->user()->id;
        $usuario_serie->save();
        $lista_series_asignados = array();
        $usuario_id = auth()->user()->id;
        foreach (FlujoSerie::select(
            DB::raw("
            vent_num_cajero,
            vent_almacen_id,
            (select count(*) from vent_usuario_serie as b where b.vent_usuario_user_id=$usuario_id and b.vent_almacen_id=vent_flujo_serie.vent_almacen_id and b.vent_num_cajero=vent_flujo_serie.vent_num_cajero ) as asignado
            ")
        )->where('vent_almacen_id', $vent_almacen_id)
                     ->groupBy('vent_flujo_serie.vent_num_cajero', 'vent_flujo_serie.vent_almacen_id')
                     ->get() as $item) {
            $item->asignado = $item->asignado >= 1 ? true : false;

            $item->series = FlujoSerie::select('vent_serie')->where('vent_num_cajero', $item->vent_num_cajero)->get();

            array_push($lista_series_asignados, $item);
        }
        return response()->json(
            ['success' => true,
                'data' => $lista_series_asignados,
                'message' => 'Operacion Correcta'],

            200);
    }

    public function eliminar_series($cajeroId)
    {


        $almacen_id = FlujoSerie::where('vent_num_cajero', $cajeroId)->first()->vent_almacen_id;
        UsuarioSerie::where('vent_num_cajero', $cajeroId)->delete();
        FlujoSerie::where('vent_num_cajero', $cajeroId)->delete();
        $series = FlujoSerie::select('vent_flujo_serie.vent_serie',
            'vent_flujo_serie.vent_tipo_comprobante_id',
            'vent_flujo_serie.vent_num_cajero',
            'vent_flujo_serie.vent_almacen_id',
            'doc_tipo_comprabante.doc_tipo_comprobante_nombre',
            'alm_almacen.alm_almacen_nombre',
            'doc_tipo_comprabante.doc_tipo_comprobante_nombre')
            ->leftJoin('doc_tipo_comprabante', 'vent_flujo_serie.vent_tipo_comprobante_id', 'doc_tipo_comprabante.doc_tipo_comprobante_id')
            ->leftJoin('alm_almacen', 'vent_flujo_serie.vent_almacen_id', 'alm_almacen.alm_almacen_id')
            ->where('alm_almacen.alm_almacen_id', $almacen_id)
            ->orderBy('vent_flujo_serie.vent_num_cajero', 'vent_flujo_serie.vent_serie')
            ->get();
        return response()->json(
            ['success' => true,
                'data' => $series,
                'message' => 'Operacion Correcta'],
            200);
    }

}
