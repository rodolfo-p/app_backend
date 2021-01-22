<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 21/09/18
 * Time: 01:18 PM
 */

namespace App\Http\Data\util;

use Exception;
use App\Http\Controllers\Controller;
use Request;
use Illuminate\Support\Facades\DB;

class IdGenerador extends Controller
{


    public static function generaIdMombre($nombre)
    {
        $fecha_actual = getdate();
        $random = rand(1000, 10000);
        $random2 = rand(100, 199);
        $id = substr(strtoupper($nombre), 0, 2) . $fecha_actual[0] . $random . $random2;
        return $id;
    }



    public static function generaId()
    {
        $fecha_actual = getdate();
        $random = rand(1000, 10000);
        $random2 = rand(1000, 10000);
        $id = $fecha_actual[0] . $random . $random2;
        return $id;
    }

    public static function generaSerie($usuario_id, $vent_venta_tipo_comprobante_codigo)


    {

        if ($vent_venta_tipo_comprobante_codigo != '99') {

            $consultaSerie = "select a.vent_serie, a.vent_tipo_comprobante_id,
 a.vent_num_cajero, a.vent_almacen_id , b.doc_tipo_comprobante_codigo
from vent_flujo_serie a, doc_tipo_comprabante b
where a.vent_num_cajero= (select vent_num_cajero
from vent_usuario_serie where vent_usuario_user_id= '" . $usuario_id . "')
and a.vent_tipo_comprobante_id=b.doc_tipo_comprobante_id
and doc_tipo_comprobante_codigo= '" . $vent_venta_tipo_comprobante_codigo . "'";
            $ResultconsultaSerie = DB::select($consultaSerie);
            $serie = $ResultconsultaSerie[0];
        } else {

            $consultaSerie = "select a.vent_serie, a.vent_tipo_comprobante_id,
 a.vent_num_cajero, a.vent_almacen_id , b.doc_tipo_comprobante_codigo
from vent_flujo_serie a, doc_tipo_comprabante b
where a.vent_num_cajero= (select vent_num_cajero
from vent_usuario_serie where vent_usuario_user_id= '" . $usuario_id . "')
and a.vent_tipo_comprobante_id=b.doc_tipo_comprobante_id
and doc_tipo_comprobante_codigo= '03'";
            $ResultconsultaSerie = DB::select($consultaSerie);
            $serieData = $ResultconsultaSerie[0];

            $consultaSerieNotaVenta = "select cont_periodo_anio as vent_serie, 'NV152121212121212' as vent_tipo_comprobante_id
                                        from cont_periodo where cont_periodo_estado= '1'";
            $ResultSerieNotaVenta = DB::select($consultaSerieNotaVenta);
            $serieNotaVenta=$ResultSerieNotaVenta[0];
            $serieNotaVenta->vent_serie=$ResultSerieNotaVenta[0]->vent_serie . '-' . $serieData->vent_num_cajero;
            $serie = $serieNotaVenta;
        }


        return $serie;

    }


    public static function generaSerieNotaCredito($usuario_id, $vent_venta_tipo_comprobante_codigo, $vent_venta_tipo_comprobante_referenciado)
    {
        if ($vent_venta_tipo_comprobante_codigo != '99' && $vent_venta_tipo_comprobante_referenciado == "01") {
            $consultaSerie = "select a.vent_serie, a.vent_tipo_comprobante_id,
 a.vent_num_cajero, a.vent_almacen_id , b.doc_tipo_comprobante_codigo
from vent_flujo_serie a, doc_tipo_comprabante b
where a.vent_num_cajero= (select vent_num_cajero
from vent_usuario_serie where vent_usuario_user_id= '" . $usuario_id . "')
and a.vent_tipo_comprobante_id=b.doc_tipo_comprobante_id
and doc_tipo_comprobante_codigo= '" . $vent_venta_tipo_comprobante_codigo . "' and a.vent_serie like 'F%'";
            $ResultconsultaSerie = DB::select($consultaSerie);
            $serie = $ResultconsultaSerie[0];

        } elseif ($vent_venta_tipo_comprobante_codigo != '99' && $vent_venta_tipo_comprobante_referenciado == "03") {
            $consultaSerie = "select a.vent_serie, a.vent_tipo_comprobante_id,
 a.vent_num_cajero, a.vent_almacen_id , b.doc_tipo_comprobante_codigo
from vent_flujo_serie a, doc_tipo_comprabante b
where a.vent_num_cajero= (select vent_num_cajero
from vent_usuario_serie where vent_usuario_user_id= '" . $usuario_id . "')
and a.vent_tipo_comprobante_id=b.doc_tipo_comprobante_id
and doc_tipo_comprobante_codigo= '" . $vent_venta_tipo_comprobante_codigo . "' and a.vent_serie like 'B%'";
            $ResultconsultaSerie = DB::select($consultaSerie);
            $serie = $ResultconsultaSerie[0];
        } else {

            $consultaSerieNotaVenta = "select cont_periodo_anio as vent_serie, 'NV152121212121212' as vent_tipo_comprobante_id
                                        from cont_periodo where cont_periodo_estado= '1'";
            $ResultSerieNotaVenta = DB::select($consultaSerieNotaVenta);
            $serie = $ResultSerieNotaVenta[0];
        }


        return $serie;
    }

    public static function generaNumeroVenta($vent_venta_serie, $vent_venta_almacen_id,  $vent_venta_tipo_comprobante_codigo, $cont_periodo_anio)
    {
        if ($vent_venta_tipo_comprobante_codigo != 99) {
            $sql = "select max(vent_venta_numero) as  numero from vent_venta where vent_venta_serie= '" . $vent_venta_serie . "'";
            $Resultado = DB::select($sql);

            if (count($Resultado) >= 1) {
                $numero_venta = str_pad($Resultado[0]->numero + 1, 8, "0", STR_PAD_LEFT);

            } else {
                $numero_venta = '00000001';
            }
        } else {

            $sqlNV = "select max(vent_venta_numero) as  numero from vent_venta
            where vent_venta_almacen_id= '" . $vent_venta_almacen_id . "'
           and vent_venta_serie= '" . $vent_venta_serie . "'";
            $ResultadoNV = DB::select($sqlNV);
            if (count($ResultadoNV) >= 1) {
                $numero_venta = str_pad($ResultadoNV[0]->numero + 1, 8, "0", STR_PAD_LEFT);

            } else {
                $numero_venta = '00000001';
            }
        }

        return $numero_venta;
    }


    public static function generaNumeroGuia($vent_venta_serie, $vent_venta_almacen_id, $cont_periodo_id, $vent_venta_tipo_comprobante_codigo, $cont_periodo_anio)
    {

        $sql = "select max(guia_remision_numero_comprobante) as  numero from guia_remision where guia_remision_seria_comprobante= '" . $vent_venta_serie . "'";
        $Resultado = DB::select($sql);

        if (count($Resultado) >= 1) {
            $numero = str_pad($Resultado[0]->numero + 1, 8, "0", STR_PAD_LEFT);

        } else {
            $numero = '00000001';
        }


        return $numero;
    }

    public static function generaNumeroPago()
    {
        $consultaNumeroPago = "select max(vent_pago_numero_pago) as numero from vent_pago";
        $ResultconsultaNumeroPago = DB::select($consultaNumeroPago);
        if (count($ResultconsultaNumeroPago) >= 1) {
            $numero_pago = str_pad($ResultconsultaNumeroPago[0]->numero + 1, 8, "0", STR_PAD_LEFT);
        } else {
            $numero_pago = '00000001';
        }
        return $numero_pago;
    }

    /*** consulta quenera numaro de Item del detalle de venta **/
    public static function generaNumeroItem($numero)
    {
        if ($numero == 0) {
            $numero_item = '001';
        } else {
            $numero_item = str_pad($numero + 1, 3, "0", STR_PAD_LEFT);
        }
        return $numero_item;
    }

    /*** consulta para cardex promedio**/
    public static function calculaStock($vent_venta_almacen_id, $vent_venta_detalle_producto_id, $anio)
    {
        try {
            $CalculaStock = "select  round(sum(precio)/sum(cantidad) ,2) precio_alm from (

                                                              select
                                                                b.comp_compra_detalle_producto_id as producto_id,
                                                                b.comp_compra_detalle_cantidad cantidad,
                                                                b.comp_compra_detalle_precio as precio
                                                              from
                                                                comp_compra a, comp_compra_detalle b
                                                              where a.comp_compra_id=b.comp_compra_compra_id
                                                                and   a.comp_compra_periodo_id in (

                                                                select cont_periodo_id from cont_periodo
                                                                where cont_periodo_anio ='" . $anio . "'
                                                              )
                                                                and   a.comp_compra_almacen_id='" . $vent_venta_almacen_id . "'
                                                                and   b.comp_compra_detalle_producto_id='" . $vent_venta_detalle_producto_id . "'

                                                              union ALL

                                                              select
                                                                b.vent_venta_detalle_producto_id producto_id,
                                                                -b.vent_venta_detalle_cantidad  cantidad,
                                                                -b.vent_venta_detalle_cantidad*b.vent_venta_detalle_costo_almacen  as precio
                                                              from  vent_venta a, vent_venta_detalle b
                                                              where a.vent_venta_id=b.vent_venta_detalle_venta_id
                                                                and   a.vent_venta_periodo_id in (

                                                                select cont_periodo_id from cont_periodo
                                                                where cont_periodo_anio ='" . $anio . "'
                                                              )
                                                                and   a.vent_venta_almacen_id='" . $vent_venta_almacen_id . "'
                                                                and   b.vent_venta_detalle_producto_id='" . $vent_venta_detalle_producto_id . "'
                                                                and   a.vent_venta_tipo_comprobante_id != (select doc_tipo_comprobante_id from doc_tipo_comprabante where doc_tipo_comprobante_codigo='07')
                                                              union all

                                                              select
                                                                b.vent_venta_detalle_producto_id producto_id,
                                                                b.vent_venta_detalle_cantidad  cantidad,
                                                                b.vent_venta_detalle_cantidad*b.vent_venta_detalle_costo_almacen  as precio
                                                              from  vent_venta a, vent_venta_detalle b
                                                              where a.vent_venta_id=b.vent_venta_detalle_venta_id
                                                                and   a.vent_venta_periodo_id in (

                                                                select cont_periodo_id from cont_periodo
                                                                where cont_periodo_anio ='" . $anio . "'
                                                              )
                                                                and   a.vent_venta_almacen_id='" . $vent_venta_almacen_id . "'
                                                                and   b.vent_venta_detalle_producto_id='" . $vent_venta_detalle_producto_id . "'
                                                                and   a.vent_venta_tipo_comprobante_id = (select doc_tipo_comprobante_id from doc_tipo_comprabante where doc_tipo_comprobante_codigo='07')
                                                                union all
                                                              select
                                                                alm_almacen_producto_id,
                                                                alm_cantidad,
                                                                alm_precio
                                                              from  alm_ingreso_almacen
                                                              where alm_almacen_id='" . $vent_venta_almacen_id . "'
                                                                and   alm_almacen_producto_id='" . $vent_venta_detalle_producto_id . "'
                                                                and   alm_periodo_id in (

                                                                select cont_periodo_id from cont_periodo
                                                                where cont_periodo_anio ='" . $anio . "'
                                                              )
                                                              union all

                                                              select
                                                                alm_almacen_producto_id,
                                                                -alm_cantidad,
                                                                -alm_precio
                                                              from  alm_salida_almacen
                                                              where alm_almacen_id='" . $vent_venta_almacen_id . "'
                                                                and   alm_almacen_producto_id='" . $vent_venta_detalle_producto_id . "'
                                                                and   alm_periodo_id in (

                                                                select cont_periodo_id from cont_periodo
                                                                where cont_periodo_anio ='" . $anio . "'
                                                              )

                                                            ) stock
group by producto_id";
            $ResultadoCalculaStock = DB::select($CalculaStock);

        } catch (Exception $exception) {

        }
        if (count($ResultadoCalculaStock) >= 1) {
            $stock = $ResultadoCalculaStock[0]->precio_alm;
        } else {
            $stock = 0;

        }

        return $stock;

    }


    public static function cuenta_detalle($vent_venta_detalle_tipo_operacion)
    {
        if ($vent_venta_detalle_tipo_operacion) {

        } else {
        }
    }
}
