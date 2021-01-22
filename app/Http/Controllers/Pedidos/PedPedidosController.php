<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 07/02/19
 * Time: 11:47 AM
 */

namespace App\Http\Controllers\Pedidos;

use App\Http\Data\util\GeneraNumero;
use App\Http\Data\util\IdGenerador;
use App\Http\Data\util\Pagination;
use App\Models\Almacen\Distribuidor;
use App\Models\Configuracion\Periodo;
use App\Models\Venta\Pedido;
use App\Models\Venta\PedidoDetalle;
use App\Models\Venta\VentaPago;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;


class PedPedidosController extends Controller
{

    public function listar_pedidos(Request $request)
    {
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Pedido::all(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de pedidos'], 200);
    }

    public function listar_pedido($id)
    {
        $pedido = Pedido::find($id);
        $pedido->detalle = PedidoDetalle::where('vent_pedido_detalle_pedido_id', $id)->get();
        $pedido->pago = is_null(VentaPago::where('vent_pago_venta_id', $pedido->vent_pedido_id)->first()) ? new \stdClass() : VentaPago::where('vent_pago_venta_id', $pedido->vent_pedido_id)->first();
        return response()->json(['success' => true,
            'data' => $pedido,
            'message' => 'Lista de pedido'], 200);
    }

    public function registrar_pedido(Request $request)
    {
        $pedido = new Pedido($request->all());
        $vent_pedido_id = IdGenerador::generaId();
        $pedido->vent_pedido_id = $vent_pedido_id;
        $pedido->vent_pedido_usuario = auth()->user()->id;
        $pedido->vent_pedido_fecha = date('Y-m-d');
        $pedido->vent_pedido_serie = 'P001';
        $pedido->vent_pedido_numero = GeneraNumero::genera_numero_pedido($pedido->vent_pedido_serie);
        $pedido->vent_pedido_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
        $pedido->vent_pedido_estado = true;
        if ($request->input('vent_pago_importe') <= $request->input('vent_pedido_importe')) {
            $pedido->vent_pedido_estado_pago = true;
        } else {
            $pedido->vent_pedido_estado_pago = true;
        }
        $pedido->save();
        foreach ($request->input('detalle') as $item) {
            $pedido_detalle = new PedidoDetalle($item);
            $pedido_detalle->vent_pedido_detalle_id = IdGenerador::generaId();
            $pedido_detalle->vent_pedido_detalle_pedido_id = $vent_pedido_id;
            $pedido_detalle->save();
        }
        $importe = $request->input('vent_pago_importe');
        if (floatval($importe) > 0.00) {
            $venta_pago = new VentaPago();
            $venta_pago->vent_pago_id = IdGenerador::generaId();
            $venta_pago->vent_pago_importe = $request->input('vent_pago_importe');
            $venta_pago->vent_pago_pago = $request->input('vent_pago_pago');
            $venta_pago->vent_pago_vuelto = $request->input('vent_pago_vuelto');
            $venta_pago->vent_pago_venta_id = $vent_pedido_id;
            $venta_pago->vent_pago_tipo_pago = $request->input('vent_pago_tipo_pago');
            $venta_pago->vent_pago_user_id = auth()->user()->id;
            $venta_pago->vent_pago_fecha = date('Y-m-d H:i:s', strtotime("now"));
            $venta_pago->vent_pago_serie = 'VP01' . $request->input('vent_num_cajero');
            $venta_pago->vent_pago_numero_pago = GeneraNumero::genera_numero_pago_cliente($venta_pago->vent_pago_serie);
            $venta_pago->vent_pago_modalidad = $request->input('vent_pago_modalidad');
            $venta_pago->vent_pago_numero_transaccion = $request->input('vent_pago_numero_transaccion');
            $venta_pago->vent_pago_cliente_documento = $request->input('vent_pedido_numero_documento_cliente');
            $venta_pago->vent_pago_almacen_id = $request->input('vent_pedido_almacen_id');
            $venta_pago->vent_pago_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
            $venta_pago->save();

        }
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Pedido::all(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de pedidos'], 200);
    }


    public function actualizar_pedido(Request $request, $id)
    {
        $pedido = new Pedido($request->all());
        $pedido->vent_pedido_usuario = auth()->user()->id;
        $pedido->vent_pedido_fecha = date('Y-m-d');
        $pedido->vent_pedido_serie = 'P001';
        $pedido->vent_pedido_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
        $pedido->vent_pedido_estado = true;
        if ($request->input('vent_pago_importe') <= $request->input('vent_pedido_importe')) {
            $pedido->vent_pedido_estado_pago = true;
        } else {
            $pedido->vent_pedido_estado_pago = true;
        }

        Pedido::find($id)->update($pedido->toArray());
        PedidoDetalle::where('vent_pedido_detalle_pedido_id', $id)->delete();
        foreach ($request->input('detalle') as $item) {
            $pedido_detalle = new PedidoDetalle($item);
            $pedido_detalle->vent_pedido_detalle_id = IdGenerador::generaId();
            $pedido_detalle->vent_pedido_detalle_pedido_id = $id;
            $pedido_detalle->save();
        }

        VentaPago::where('vent_pago_venta_id', $id)->delete();
        $importe = $request->input('vent_pago_importe');
        if (floatval($importe) > 0.00) {
            $venta_pago = new VentaPago();
            $venta_pago->vent_pago_id = IdGenerador::generaId();
            $venta_pago->vent_pago_importe = $request->input('vent_pago_importe');
            $venta_pago->vent_pago_pago = $request->input('vent_pago_pago');
            $venta_pago->vent_pago_vuelto = $request->input('vent_pago_vuelto');
            $venta_pago->vent_pago_venta_id = $id;
            $venta_pago->vent_pago_tipo_pago = $request->input('vent_pago_tipo_pago');
            $venta_pago->vent_pago_user_id = auth()->user()->id;
            $venta_pago->vent_pago_fecha = date('Y-m-d H:i:s', strtotime("now"));
            $venta_pago->vent_pago_serie = 'VP01' . $request->input('vent_num_cajero');
            $venta_pago->vent_pago_numero_pago = GeneraNumero::genera_numero_pago_cliente($venta_pago->vent_pago_serie);
            $venta_pago->vent_pago_modalidad = $request->input('vent_pago_modalidad');
            $venta_pago->vent_pago_numero_transaccion = $request->input('vent_pago_numero_transaccion');
            $venta_pago->vent_pago_cliente_documento = $request->input('vent_pedido_numero_documento_cliente');
            $venta_pago->vent_pago_almacen_id = $request->input('vent_pedido_almacen_id');
            $venta_pago->vent_pago_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
            $venta_pago->save();

        }
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Pedido::all(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de pedidos'], 200);
    }

    public function eliminar_pedido(Request $request, $id)
    {
        PedidoDetalle::where('vent_pedido_detalle_pedido_id', $id)->delete();
        Pedido::find($id)->delete();
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Pedido::all(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de pedidos'], 200);
    }

    public function listar_pedidos_por_cobrar()
    {
        return response()->json(['success' => true,
            'data' => Pedido::listar_pedidos_por_cobrar(),
            'message' => 'Lista de pedidos'], 200);
    }

    public function listar_pedido_por_cobrar($id)
    {
        $pedido = Pedido::listar_pedido_por_cobrar($id)[0];
        $pedido->pagos = VentaPago::select('vent_pago_importe',
            'vent_pago_fecha',
            'vent_pago_serie',
            'vent_pago_cliente_documento',
            'vent_pago_cliente_documento',
            'vent_pago_numero_pago')->where('vent_pago_venta_id', $id)->orderBy('vent_pago_fecha')->get();
        return response()->json(['success' => true,
            'data' => $pedido,
            'message' => 'Lista de pedidos'], 200);
    }


    public static function registrar_pago_pedido_al_credito(Request $request)
    {
        $venta_pago = new VentaPago();
        $venta_pago->vent_pago_id = IdGenerador::generaId();
        $venta_pago->vent_pago_importe = $request->input('vent_pago_importe');
        $venta_pago->vent_pago_pago = $request->input('vent_pago_importe');
        $venta_pago->vent_pago_vuelto = 0.00;
        $venta_pago->vent_pago_venta_id = $request->input('vent_pedido_id');
        $venta_pago->vent_pago_tipo_pago = "02";
        $venta_pago->vent_pago_user_id = auth()->user()->id;
        $venta_pago->vent_pago_fecha = date('Y-m-d H:i:s', strtotime("now"));
        $venta_pago->vent_pago_serie = "P0O1";
        $venta_pago->vent_pago_numero_pago = GeneraNumero::genera_numero_pago_cliente($venta_pago->vent_pago_serie);
        $venta_pago->vent_pago_modalidad = "01";
        $venta_pago->vent_pago_cliente_documento = $request->input('vent_pago_cliente_documento');
        $pedido = Pedido::find($request->input('vent_pedido_id'));
        $venta_pago->vent_pago_almacen_id = $pedido->vent_pedido_almacen_id;
        $venta_pago->vent_pago_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
        $venta_pago->save();
        $pedido = new Pedido();
        $pedido->vent_pedido_estado_entrega = $request->input('vent_pedido_estado_entrega');
        if ($request->input('saldo') <= $request->input('vent_pago_importe')) {
            $pedido->vent_pedido_estado_pago = true;

        }
        Pedido::find($request->input('vent_pedido_id'))->update($pedido->toArray());

        return response()->json(['success' => true,
            'data' => Pedido::listar_pedidos_por_cobrar(),
            'message' => 'Lista de ventas por cobrar'], 200);
    }
}
