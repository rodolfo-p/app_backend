<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 13/02/19
 * Time: 08:44 AM
 */

namespace App\Http\Controllers\Ventas;


use App\Http\Controllers\Controller;
use App\Http\Data\util\GeneraNumero;
use App\Http\Data\util\IdGenerador;
use App\Http\Data\Ventas\CobroClientes;
use App\Models\Almacen\Distribuidor;
use App\Models\Configuracion\Periodo;
use App\Models\Venta\FechaPago;
use App\Models\Venta\Venta;
use App\Models\Venta\VentaPago;
use Illuminate\Http\Request;

class VentCobroClientesController extends Controller
{

// lista ventas al credito sin ser completado el pago
    public static function listar_ventas_al_credito_sin_cancelar(Request  $request)
    {
        $ventas_con_deudas = Venta::listar_ventas_por_cobrar($request->input('dato'));
        return response()->json(['success' => true,
            'data' => $ventas_con_deudas,
            'message' => 'Lista de ventas por cobrar'], 200);
    }


    public static function listar_ventas_al_credito_calendario()
    {
        $lista = [];
        $ventas_con_deudas = Venta::listar_ventas_por_cobrar_calendario();
        foreach ($ventas_con_deudas as $data) {
            $data->vent_venta_fecha_pago_fecha = substr($data->vent_venta_fecha_pago_fecha, 0, 8) . intval(substr($data->vent_venta_fecha_pago_fecha, 8, 2));
            array_push($lista, $data);
        }
        return response()->json(['success' => true,
            'data' => $lista,
            'message' => 'Lista de ventas por cobrar'], 200);
    }


    public function listar_venta_al_credito_sin_cancelar($id)
    {
        $ventas_con_deudas = Venta::listar_venta_por_cobrar($id)[0];
        $ventas_con_deudas->pagos = VentaPago::select('vent_pago_importe',
            'vent_pago_fecha',
            'vent_pago_serie',
            'vent_pago_cliente_documento',
            'vent_pago_cliente_documento',
            'vent_pago_numero_pago')->where('vent_pago_venta_id', $id)->orderBy('vent_pago_fecha')->get();
        $ventas_con_deudas->fecha_coutas = FechaPago::where('vent_venta_fecha_pago_venta_id', $id)
            ->orderBy('vent_venta_fecha_pago_cuota')
            ->get();
        return response()->json(['success' => true,
            'data' => $ventas_con_deudas,
            'message' => 'Lista de ventas por cobrar'], 200);
    }

    public static function registrar_pago_ventas_al_credito(Request $request)
    {
        $venta_pago = new VentaPago();
        $venta_pago->vent_pago_id = IdGenerador::generaId();
        $venta_pago->vent_pago_nro_cuota = GeneraNumero::genera_numero_pago($request->input('vent_venta_id'));
        $venta_pago->vent_pago_importe = $request->input('vent_pago_importe');
        $venta_pago->vent_pago_pago = $request->input('vent_pago_importe');
        $venta_pago->vent_pago_vuelto = 0.00;
        $venta_pago->vent_pago_venta_id = $request->input('vent_venta_id');
        $venta_pago->vent_pago_tipo_pago = "02";
        $venta_pago->vent_pago_user_id = auth()->user()->id;
        $venta_pago->vent_pago_fecha = date('Y-m-d H:i:s', strtotime("now"));
        $venta_pago->vent_pago_serie = "VPO1";
        $venta_pago->vent_pago_numero_pago = GeneraNumero::genera_numero_pago_cliente($venta_pago->vent_pago_serie);
        $venta_pago->vent_pago_modalidad = "01";
        $venta_pago->vent_pago_cliente_documento = $request->input('vent_pago_cliente_documento');
        $venta = Venta::find($request->input('vent_venta_id'));
        $venta_pago->vent_pago_almacen_id = $venta->vent_venta_almacen_id;
        $venta_pago->vent_pago_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
        $venta_pago->vent_pago_comision_id = $venta->vent_venta_distribuidor_id;

        $venta_pago->vent_pago_comision_pagado = false;
        $periodo = Periodo::where('cont_periodo_estado', true)->first();
        $venta_pago->vent_pago_comision_importe = !is_null($venta->vent_venta_distribuidor_id) ?
            floatval($request->input('vent_pago_importe')) /
            (floatval($periodo->cont_periodo_igv) / 100 + 1) *
            (floatval(Distribuidor::find($venta->vent_venta_distribuidor_id)->alm_distribuidor_porcentaje_venta) / 100) : 0.00;

        $venta_pago->save();
        if ($request->input('saldo') <= $request->input('vent_pago_importe')) {
            Venta::find($request->input('vent_venta_id'))->update(array('vent_venta_estado_pago' => true));
        }
        $ventas_con_deudas = Venta::listar_ventas_por_cobrar();
        return response()->json(['success' => true,
            'data' => $ventas_con_deudas,
            'message' => 'Lista de ventas por cobrar'], 200);
    }

    public static function actualizarComentario(Request $request, $id)
    {
       // dd(array('vent_venta_fecha_pago_comentario' => $request->input('vent_venta_fecha_pago_comentario')));
        FechaPago::find($id)->update(array('vent_venta_fecha_pago_comentario' => $request->input('vent_venta_fecha_pago_comentario')));
        $lista = [];
        $ventas_con_deudas = Venta::listar_ventas_por_cobrar_calendario();
        foreach ($ventas_con_deudas as $data) {
            $data->vent_venta_fecha_pago_fecha = substr($data->vent_venta_fecha_pago_fecha, 0, 8) . intval(substr($data->vent_venta_fecha_pago_fecha, 8, 2));
            array_push($lista, $data);
        }
        return response()->json(['success' => true,
            'data' => $lista,
            'message' => 'Lista de ventas por cobrar'], 200);
    }
}
