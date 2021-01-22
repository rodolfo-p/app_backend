<?php


namespace App\Http\Data\util;


use App\Http\Controllers\Controller;
use App\Models\Almacen\Compra;
use App\Models\Almacen\PagoProveedores;
use App\Models\Venta\GuiaRemision;
use App\Models\Venta\Pedido;
use App\Models\Venta\Venta;
use App\Models\Venta\VentaBaja;
use App\Models\Venta\VentaPago;

class GeneraNumero extends Controller
{
    public static function genera_numero_pago_proveedor($serie)
    {

        return str_pad(PagoProveedores::where('comp_pago_proveedores_serie', $serie)
                ->select('comp_pago_proveedores_numero_pago')
                ->max('comp_pago_proveedores_numero_pago') + 1, 8, "0", STR_PAD_LEFT);
    }

    public static function genera_numero_movimiento($serie)
    {
        return str_pad(Compra::where('comp_compra_serie', $serie)
                ->select('comp_compra_numero_venta')
                ->max('comp_compra_numero_venta') + 1, 8, "0", STR_PAD_LEFT);
    }

    public static function genera_numero_venta($serie)
    {
        return str_pad(Venta::where('vent_venta_serie', $serie)
                ->select('vent_venta_numero')
                ->max('vent_venta_numero') + 1, 8, "0", STR_PAD_LEFT);
    }

    public static function genera_numero_pago_cliente($serie)
    {
        return str_pad(VentaPago::where('vent_pago_serie', $serie)
                ->select('vent_pago_numero_pago')
                ->max('vent_pago_numero_pago') + 1, 8, "0", STR_PAD_LEFT);
    }

    public static function genera_numero_comunicacion_baja()
    {

        return str_pad(VentaBaja::select('vent_venta_baja_secuencia')
                ->max('vent_venta_baja_secuencia') + 1, 5, "0", STR_PAD_LEFT);

    }

    public static function genera_numero_guia_remision($serie)
    {
        return str_pad(GuiaRemision::where('guia_remision_serie_comprobante', $serie)
                ->select('guia_remision_numero_comprobante')
                ->max('guia_remision_numero_comprobante') + 1, 8, "0", STR_PAD_LEFT);
    }

    public static function genera_numero_pedido($serie)
    {
        return str_pad(Pedido::where('vent_pedido_serie', $serie)
                ->select('vent_pedido_numero')
                ->max('vent_pedido_numero') + 1, 8, "0", STR_PAD_LEFT);
    }

    public static function genera_numero_pago($venta_id)
    {
        return VentaPago::where('vent_pago_venta_id', $venta_id)
                ->select('vent_pago_nro_cuota')
                ->max('vent_pago_nro_cuota') + 1;
    }

}
