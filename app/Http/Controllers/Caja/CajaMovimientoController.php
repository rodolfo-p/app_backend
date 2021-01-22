<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 10/12/18
 * Time: 09:52 AM
 */

namespace App\Http\Controllers\Caja;


use App\Http\Data\util\IdGenerador;
use App\Http\Data\util\Pagination;
use App\Models\Configuracion\Periodo;
use App\Models\Venta\CajaMovimiento;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use Mail;
use Validator;
class CajaMovimientoController extends Controller
{

    public function lista_caja_movimientos(Request $request)
    {

        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(CajaMovimiento::select('caja_movimiento_id',
                'caja_movimiento_monto',
                'caja_movimiento_user',
                'caja_movimiento_almacen_id',
                'caja_movimiento_fecha',
                'caja_movimiento_periodo_id',
                'caja_movimiento_tipo',
                'caja_movimiento_doc_identidad',
                'caja_movimiento_descripcion',
                'caja_movimiento_serie_ref',
                'caja_movimiento_numero_ref', 'alm_almacen.alm_almacen_nombre')
                ->leftJoin('alm_almacen', 'caja_movimiento.caja_movimiento_almacen_id', 'alm_almacen.alm_almacen_id')->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de caja movimientos'], 200);
    }

    public function registrar_caja_movimientos(Request $request)
    {
        $caja_movimiento = new CajaMovimiento($request->all());
        $caja_movimiento->caja_movimiento_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->caja_movimiento_periodo_id;
        $caja_movimiento->caja_movimiento_user = auth()->user()->id;
        $caja_movimiento->caja_movimiento_fecha = date('Y-m-d');
        $caja_movimiento->caja_movimiento_id = IdGenerador::generaId();
        $caja_movimiento->save();
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(CajaMovimiento::select('caja_movimiento_id',
                'caja_movimiento_monto',
                'caja_movimiento_user',
                'caja_movimiento_almacen_id',
                'caja_movimiento_fecha',
                'caja_movimiento_periodo_id',
                'caja_movimiento_tipo',
                'caja_movimiento_doc_identidad',
                'caja_movimiento_descripcion',
                'caja_movimiento_serie_ref',
                'caja_movimiento_numero_ref', 'alm_almacen.alm_almacen_nombre')
                ->leftJoin('alm_almacen', 'caja_movimiento.caja_movimiento_almacen_id', 'alm_almacen.alm_almacen_id')->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de movimientos'], 200);

    }

    public function lista_caja_movimiento($id)
    {

        return response()->json(['success' => true,
            'data' => CajaMovimiento::find($id),
            'message' => 'Lista de movimientos'], 200);

    }

    public function editar_caja_movimiento(Request $request, $id)
    {
        $caja_movimiento = new CajaMovimiento($request->all());
        $caja_movimiento->caja_movimiento_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->caja_movimiento_periodo_id;
        CajaMovimiento::find($id)->update($caja_movimiento->toArray());
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(CajaMovimiento::select('caja_movimiento_id',
                'caja_movimiento_monto',
                'caja_movimiento_user',
                'caja_movimiento_almacen_id',
                'caja_movimiento_fecha',
                'caja_movimiento_periodo_id',
                'caja_movimiento_tipo',
                'caja_movimiento_doc_identidad',
                'caja_movimiento_descripcion',
                'caja_movimiento_serie_ref',
                'caja_movimiento_numero_ref', 'alm_almacen.alm_almacen_nombre')
                ->leftJoin('alm_almacen', 'caja_movimiento.caja_movimiento_almacen_id', 'alm_almacen.alm_almacen_id')->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de movimientos'], 200);

    }

    public function eliminar_caja_movimiento(Request $request, $id)
    {
        CajaMovimiento::find($id)->delete();
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(CajaMovimiento::select('caja_movimiento_id',
                'caja_movimiento_monto',
                'caja_movimiento_user',
                'caja_movimiento_almacen_id',
                'caja_movimiento_fecha',
                'caja_movimiento_periodo_id',
                'caja_movimiento_tipo',
                'caja_movimiento_doc_identidad',
                'caja_movimiento_descripcion',
                'caja_movimiento_serie_ref',
                'caja_movimiento_numero_ref', 'alm_almacen.alm_almacen_nombre')
                ->leftJoin('alm_almacen', 'caja_movimiento.caja_movimiento_almacen_id', 'alm_almacen.alm_almacen_id')->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de movimientos'], 200);
    }

    public function listar_movimiento_por_fecha(Request $request)
    {

        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(CajaMovimiento::listar_movimiento_por_fecha($request->input('almacen_id'), $request->input('fecha')), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de movimientos'], 200);
    }

    public function listar_movimiento_por_fecha_total(Request $request)
    {
        $data = new \stdClass();
        $data->ingresos = 0.00;
        $data->salidas = 0.00;
        $data->total = 0.00;
        foreach (CajaMovimiento::listar_movimiento_por_fecha($request->input('almacen_id'), $request->input('fecha')) as $item) {
            if ($item->tipo == "INGRESO") {
                $data->ingresos = $data->ingresos + floatval($item->importe);
            } else {
                $data->salidas = $data->salidas + floatval($item->importe);
            }
            $data->total = $data->ingresos - $data->salidas;
        }

        return response()->json(['success' => true,
            'data' => $data,
            'message' => 'Lista de movimientos'], 200);
    }


    public function listar_movimiento_por_fecha_usuario(Request $request)
    {
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(CajaMovimiento::listar_movimiento_por_fecha_usuario($request->input('almacen_id'), $request->input('fecha'), auth()->user()->id), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de movimientos'], 200);
    }

    public function listar_movimiento_por_fecha_total_usuario(Request $request)
    {
        $data = new \stdClass();
        $data->ingresos = 0.00;
        $data->salidas = 0.00;
        $data->total = 0.00;
        foreach (CajaMovimiento::listar_movimiento_por_fecha_usuario($request->input('almacen_id'), $request->input('fecha'), auth()->user()->id) as $item) {
            if ($item->tipo == "INGRESO") {
                $data->ingresos = $data->ingresos + floatval($item->importe);
            } else {
                $data->salidas = $data->salidas + floatval($item->importe);
            }
            $data->total = $data->ingresos - $data->salidas;
        }

        return response()->json(['success' => true,
            'data' => $data,
            'message' => 'Lista de movimientos'], 200);
    }

    public function listar_movimiento_por_mes(Request $request)
    {
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(CajaMovimiento::listar_movimiento_por_mes($request->input('almacen_id'), $request->input('mes'), $request->input('anio')), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de movimientos'], 200);
    }

    public function listar_movimiento_por_mes_total(Request $request)
    {
        $data = new \stdClass();
        $data->ingresos = 0.00;
        $data->salidas = 0.00;
        $data->total = 0.00;
        foreach (CajaMovimiento::listar_movimiento_por_mes($request->input('almacen_id'), $request->input('mes'), $request->input('anio')) as $item) {
            if ($item->tipo == "INGRESO") {
                $data->ingresos = $data->ingresos + floatval($item->importe);
            } else {
                $data->salidas = $data->salidas + floatval($item->importe);
            }
            $data->total = $data->ingresos - $data->salidas;
        }

        return response()->json(['success' => true,
            'data' => $data,
            'message' => 'Lista de movimientos'], 200);
    }


    public function listar_movimiento_por_mes_usuario(Request $request)
    {
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(CajaMovimiento::listar_movimiento_por_mes_usuario($request->input('almacen_id'), $request->input('mes'), $request->input('anio'), auth()->user()->id), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de movimientos'], 200);
    }

    public function listar_movimiento_por_mes_total_usuario(Request $request)
    {
        $data = new \stdClass();
        $data->ingresos = 0.00;
        $data->salidas = 0.00;
        $data->total = 0.00;
        foreach (CajaMovimiento::listar_movimiento_por_mes_usuario($request->input('almacen_id'), $request->input('mes'), $request->input('anio'), auth()->user()->id) as $item) {
            if ($item->tipo == "INGRESO") {
                $data->ingresos = $data->ingresos + floatval($item->importe);
            } else {
                $data->salidas = $data->salidas + floatval($item->importe);
            }
            $data->total = $data->ingresos - $data->salidas;
        }

        return response()->json(['success' => true,
            'data' => $data,
            'message' => 'Lista de movimientos'], 200);
    }



}
