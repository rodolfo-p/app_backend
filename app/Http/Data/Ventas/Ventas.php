<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 14/11/18
 * Time: 06:10 PM
 */

namespace App\Http\Data\Ventas;

use App\Http\Controllers\Controller;
use function GuzzleHttp\Psr7\str;
use Illuminate\Support\Facades\DB;

use App\Http\Data\util\IdGenerador;
use PhpParser\Node\Expr\Cast\Object_;
use Maatwebsite\Excel\Facades\Excel;

require_once __DIR__ . '/../../../../vendor/autoload.php'; // Autoload files using Composer autoload
use NumeroALetras\NumeroALetras;

class Ventas extends Controller
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
 emp_empresa_formato_doc_imp,
 emp_empresa_email,
 emp_empresa_logo_url
 from emp_empresa";
            $Query = DB::select($sql);
            if (count($Query) >= 1) {
                $result = $Query[0];
            }
        } catch (\Exception $exception) {

        }
        return $result;
    }

    public static function registrar_venta($vent_venta_total,
                                           $vent_venta_igv,
                                           $vent_venta_bi,
                                           $vent_venta_tipo_venta,
                                           $vent_venta_cliente_numero_documento,
                                           $vent_venta_user_id,
                                           $vent_venta_almacen_id,
                                           $vent_venta_tipo_comprobante_codigo,
                                           $vent_pago_pago,
                                           $vent_pago_vuelto,
                                           $productos,
                                           $precioCobradoTotal,
                                           $descuentoTotal,
                                           $vent_pago_modalidad,
                                           $vent_pago_numero_transaccion,
                                           $distribuidor,
                                           $descripcion, $doc_referenciado_serie, $doc_referenciado_numero, $datos_comprado_por)
    {
        try {
            $sql = "select cont_periodo_id, cont_periodo_anio from cont_periodo where cont_periodo_estado= 1";
            $QueryPeriodo = DB::select($sql);
            $comp_compra_periodo_id = $QueryPeriodo[0]->cont_periodo_id;
            $vent_venta_id = IdGenerador::generaId();
            $serie = IdGenerador::generaSerie($vent_venta_user_id, $vent_venta_tipo_comprobante_codigo);
            $numero_venta = IdGenerador::generaNumeroVenta($serie->vent_serie,
                $vent_venta_almacen_id,
                $vent_venta_tipo_comprobante_codigo,
                $QueryPeriodo[0]->cont_periodo_anio);

            $numero = 1;
            if ($vent_venta_tipo_venta == '03') {
                DB::table('vent_venta')->insert(
                    array('vent_venta_id' => $vent_venta_id,
                        'vent_venta_total' => $vent_venta_total,
                        'vent_venta_igv' => $vent_venta_igv,
                        'vent_venta_bi' => $vent_venta_bi,
                        'vent_venta_fecha' => date('Y-m-d H:i:s', strtotime("now")),
                        'vent_venta_tipo_venta' => '03',
                        'vent_venta_estado' => 1,
                        'vent_venta_numero' => $numero_venta,
                        'vent_venta_serie' => $serie->vent_serie,
                        'vent_venta_cliente_numero_documento' => $vent_venta_cliente_numero_documento,
                        'vent_venta_user_id' => $vent_venta_user_id,
                        'vent_venta_tipo_comprobante_id' => $serie->vent_tipo_comprobante_id,
                        'vent_venta_almacen_id' => $vent_venta_almacen_id,
                        'vent_venta_periodo_id' => $comp_compra_periodo_id,
                        'vent_venta_precio_cobrado' => $precioCobradoTotal,
                        'vent_venta_precio_descuento_total' => $descuentoTotal,
                        'vent_venta_cuenta_cliente' => '12',
                        'vent_venta_cuenta_igv' => '4011',
                        'vent_venta_precio_cobrado_letras' => NumeroALetras::convertir($precioCobradoTotal),
                        'vent_venta_estado_pago' => 1,
                        'vent_venta_distribuidor_id' => $distribuidor,
                        'vent_venta_descripcion' => $descripcion,
                        'vent_venta_estado_envio_sunat' => 2,
                        'vent_venta_comprado_por' => $datos_comprado_por ? $datos_comprado_por : null,
                        'vent_venta_comprovante_referenciado_nota' => $doc_referenciado_serie . '-' . $doc_referenciado_numero
                    )
                );
                foreach ($productos as $key => $producto) {
                    $stock = IdGenerador::calculaStock($vent_venta_almacen_id, $producto->vent_venta_detalle_producto_id, $QueryPeriodo[0]->cont_periodo_anio);
                    DB::table('vent_venta_detalle')->insert(
                        array('vent_venta_detalle_id' => IdGenerador::generaId(),
                            'vent_venta_detalle_precio_unitario' => $producto->vent_venta_detalle_precio_unitario,
                            'vent_venta_detalle_precio' => $producto->vent_venta_detalle_precio,
                            'vent_venta_detalle_cantidad' => $producto->vent_venta_detalle_cantidad,
                            'vent_venta_detalle_igv' => $producto->vent_venta_detalle_igv,
                            'vent_venta_detalle_tipo_operacion' => $producto->vent_venta_detalle_tipo_operacion,
                            'vent_venta_detalle_bi' => $producto->vent_venta_detalle_bi,
                            'vent_venta_detalle_producto_id' => $producto->vent_venta_detalle_producto_id,
                            'vent_venta_detalle_venta_id' => $vent_venta_id,
                            'vent_venta_detalle_descuento' => $producto->vent_venta_detalle_descuento,
                            'vent_venta_detalle_precio_cobro' => $producto->vent_venta_detalle_precio_descuento,
                            'vent_venta_detalle_costo_almacen' => $stock,
                            'vent_venta_detalle_cuenta_venta' => $producto->vent_venta_detalle_cuenta_venta,
                            'vent_venta_detalle_item' => $numero,
                        )
                    );
                    $numero = $numero + 1;
                }
                if ($doc_referenciado_serie != "" && $doc_referenciado_numero != "") {
                    $doc_ref = $serie->vent_serie . '-' . $numero_venta;
                    $query = "UPDATE vent_venta SET vent_venta_comprovante_referenciado_nota = '" . $doc_ref . "'
                WHERE vent_venta_serie = '" . $doc_referenciado_serie . "' and vent_venta_numero='" . $doc_referenciado_numero . "'";
                    DB::update($query);
                }

            } else if ($vent_venta_tipo_venta == '01' && $vent_pago_pago >= $precioCobradoTotal) {

                DB::table('vent_venta')->insert(
                    array('vent_venta_id' => $vent_venta_id,
                        'vent_venta_total' => $vent_venta_total,
                        'vent_venta_igv' => $vent_venta_igv,
                        'vent_venta_bi' => $vent_venta_bi,
                        'vent_venta_fecha' => date('Y-m-d H:i:s', strtotime("now")),
                        'vent_venta_tipo_venta' => $vent_venta_tipo_venta,
                        'vent_venta_estado' => 1,
                        'vent_venta_numero' => $numero_venta,
                        'vent_venta_serie' => $serie->vent_serie,
                        'vent_venta_cliente_numero_documento' => $vent_venta_cliente_numero_documento,
                        'vent_venta_user_id' => $vent_venta_user_id,
                        'vent_venta_tipo_comprobante_id' => $serie->vent_tipo_comprobante_id,
                        'vent_venta_almacen_id' => $vent_venta_almacen_id,
                        'vent_venta_periodo_id' => $comp_compra_periodo_id,
                        'vent_venta_precio_cobrado' => $precioCobradoTotal,
                        'vent_venta_precio_descuento_total' => $descuentoTotal,
                        'vent_venta_cuenta_cliente' => '12',
                        'vent_venta_cuenta_igv' => '4011',
                        'vent_venta_precio_cobrado_letras' => NumeroALetras::convertir($precioCobradoTotal),
                        'vent_venta_estado_pago' => 1,
                        'vent_venta_distribuidor_id' => $distribuidor,
                        'vent_venta_descripcion' => $descripcion,
                        'vent_venta_estado_envio_sunat' => 2,
                        'vent_venta_comprado_por' => $datos_comprado_por ? $datos_comprado_por : null,
                        'vent_venta_comprovante_referenciado_nota' => $doc_referenciado_serie . '-' . $doc_referenciado_numero
                    )
                );
                foreach ($productos as $key => $producto) {

                    $stock = IdGenerador::calculaStock($vent_venta_almacen_id, $producto->vent_venta_detalle_producto_id, $QueryPeriodo[0]->cont_periodo_anio);

                    DB::table('vent_venta_detalle')->insert(
                        array('vent_venta_detalle_id' => IdGenerador::generaId(),
                            'vent_venta_detalle_precio_unitario' => $producto->vent_venta_detalle_precio_unitario,
                            'vent_venta_detalle_precio' => $producto->vent_venta_detalle_precio,
                            'vent_venta_detalle_cantidad' => $producto->vent_venta_detalle_cantidad,
                            'vent_venta_detalle_igv' => $producto->vent_venta_detalle_igv,
                            'vent_venta_detalle_tipo_operacion' => $producto->vent_venta_detalle_tipo_operacion,
                            'vent_venta_detalle_bi' => $producto->vent_venta_detalle_bi,
                            'vent_venta_detalle_producto_id' => $producto->vent_venta_detalle_producto_id,
                            'vent_venta_detalle_venta_id' => $vent_venta_id,
                            'vent_venta_detalle_descuento' => $producto->vent_venta_detalle_descuento,
                            'vent_venta_detalle_precio_cobro' => $producto->vent_venta_detalle_precio_descuento,
                            'vent_venta_detalle_costo_almacen' => $stock,
                            'vent_venta_detalle_cuenta_venta' => $producto->vent_venta_detalle_cuenta_venta,
                            'vent_venta_detalle_item' => $numero,
                        )
                    );
                    $numero = $numero + 1;
                }

                DB::table('vent_pago')->insert(
                    array('vent_pago_id' => IdGenerador::generaId(),
                        'vent_pago_importe' => $precioCobradoTotal,
                        'vent_pago_fecha' => date('Y-m-d H:i:s', strtotime("now")),
                        'vent_pago_numero_pago' => IdGenerador::generaNumeroPago(),
                        'vent_pago_venta_id' => $vent_venta_id,
                        'vent_pago_tipo_pago' => $vent_venta_tipo_venta,
                        'vent_pago_user_id' => $vent_venta_user_id,
                        'vent_pago_pago' => $vent_pago_pago,
                        'vent_pago_vuelto' => $vent_pago_vuelto,
                        'vent_pago_cliente_documento' => $vent_venta_cliente_numero_documento,
                        'vent_pago_modalidad' => $vent_pago_modalidad,
                        'vent_pago_numero_transaccion' => $vent_pago_numero_transaccion,
                        'vent_pago_almacen_id' => $vent_venta_almacen_id,
                        'vent_pago_periodo_id' => $comp_compra_periodo_id,

                    )
                );
            } else if ($vent_pago_pago < $precioCobradoTotal && $vent_pago_pago != 0) {


                DB::table('vent_venta')->insert(
                    array('vent_venta_id' => $vent_venta_id,
                        'vent_venta_total' => $vent_venta_total,
                        'vent_venta_igv' => $vent_venta_igv,
                        'vent_venta_bi' => $vent_venta_bi,
                        'vent_venta_fecha' => date('Y-m-d H:i:s', strtotime("now")),
                        'vent_venta_tipo_venta' => '02',
                        'vent_venta_estado' => 1,
                        'vent_venta_numero' => $numero_venta,
                        'vent_venta_serie' => $serie->vent_serie,
                        'vent_venta_cliente_numero_documento' => $vent_venta_cliente_numero_documento,
                        'vent_venta_user_id' => $vent_venta_user_id,
                        'vent_venta_tipo_comprobante_id' => $serie->vent_tipo_comprobante_id,
                        'vent_venta_almacen_id' => $vent_venta_almacen_id,
                        'vent_venta_periodo_id' => $comp_compra_periodo_id,
                        'vent_venta_precio_cobrado' => $precioCobradoTotal,
                        'vent_venta_precio_descuento_total' => $descuentoTotal,
                        'vent_venta_cuenta_cliente' => '12',
                        'vent_venta_cuenta_igv' => '4011',
                        'vent_venta_precio_cobrado_letras' => NumeroALetras::convertir($precioCobradoTotal),
                        'vent_venta_estado_pago' => 0,
                        'vent_venta_distribuidor_id' => $distribuidor,
                        'vent_venta_descripcion' => $descripcion,
                        'vent_venta_estado_envio_sunat' => 2,
                        'vent_venta_comprado_por' => $datos_comprado_por ? $datos_comprado_por : null,
                        'vent_venta_comprovante_referenciado_nota' => $doc_referenciado_serie . '-' . $doc_referenciado_numero
                    )
                );


                foreach ($productos as $key => $producto) {

                    $stock = IdGenerador::calculaStock($vent_venta_almacen_id, $producto->vent_venta_detalle_producto_id, $QueryPeriodo[0]->cont_periodo_anio);

                    DB::table('vent_venta_detalle')->insert(
                        array('vent_venta_detalle_id' => IdGenerador::generaId(),
                            'vent_venta_detalle_precio_unitario' => $producto->vent_venta_detalle_precio_unitario,
                            'vent_venta_detalle_precio' => $producto->vent_venta_detalle_precio,
                            'vent_venta_detalle_cantidad' => $producto->vent_venta_detalle_cantidad,
                            'vent_venta_detalle_igv' => $producto->vent_venta_detalle_igv,
                            'vent_venta_detalle_tipo_operacion' => $producto->vent_venta_detalle_tipo_operacion,
                            'vent_venta_detalle_bi' => $producto->vent_venta_detalle_bi,
                            'vent_venta_detalle_producto_id' => $producto->vent_venta_detalle_producto_id,
                            'vent_venta_detalle_venta_id' => $vent_venta_id,
                            'vent_venta_detalle_descuento' => $producto->vent_venta_detalle_descuento,
                            'vent_venta_detalle_precio_cobro' => $producto->vent_venta_detalle_precio_descuento,
                            'vent_venta_detalle_costo_almacen' => $stock,
                            'vent_venta_detalle_cuenta_venta' => $producto->vent_venta_detalle_cuenta_venta,
                            'vent_venta_detalle_item' => $numero,
                        )
                    );
                    $numero = $numero + 1;
                }
                DB::table('vent_pago')->insert(
                    array('vent_pago_id' => IdGenerador::generaId(),
                        'vent_pago_importe' => $precioCobradoTotal,
                        'vent_pago_fecha' => date('Y-m-d H:i:s', strtotime("now")),
                        'vent_pago_numero_pago' => IdGenerador::generaNumeroPago(),
                        'vent_pago_venta_id' => $vent_venta_id,
                        'vent_pago_tipo_pago' => $vent_venta_tipo_venta,
                        'vent_pago_user_id' => $vent_venta_user_id,
                        'vent_pago_pago' => $vent_pago_pago,
                        'vent_pago_vuelto' => $vent_pago_vuelto,
                        'vent_pago_cliente_documento' => $vent_venta_cliente_numero_documento,
                        'vent_pago_modalidad' => $vent_pago_modalidad,
                        'vent_pago_numero_transaccion' => $vent_pago_numero_transaccion,
                        'vent_pago_almacen_id' => $vent_venta_almacen_id,
                        'vent_pago_periodo_id' => $comp_compra_periodo_id,

                    )
                );
            } else if ($vent_venta_tipo_venta == '02' && $vent_pago_pago >= $precioCobradoTotal) {

                DB::table('vent_venta')->insert(
                    array('vent_venta_id' => $vent_venta_id,
                        'vent_venta_total' => $vent_venta_total,
                        'vent_venta_igv' => $vent_venta_igv,
                        'vent_venta_bi' => $vent_venta_bi,
                        'vent_venta_fecha' => date('Y-m-d H:i:s', strtotime("now")),
                        'vent_venta_tipo_venta' => '01',
                        'vent_venta_estado' => 1,
                        'vent_venta_numero' => $numero_venta,
                        'vent_venta_serie' => $serie->vent_serie,
                        'vent_venta_cliente_numero_documento' => $vent_venta_cliente_numero_documento,
                        'vent_venta_user_id' => $vent_venta_user_id,
                        'vent_venta_tipo_comprobante_id' => $serie->vent_tipo_comprobante_id,
                        'vent_venta_almacen_id' => $vent_venta_almacen_id,
                        'vent_venta_periodo_id' => $comp_compra_periodo_id,
                        'vent_venta_precio_cobrado' => $precioCobradoTotal,
                        'vent_venta_precio_descuento_total' => $descuentoTotal,
                        'vent_venta_cuenta_cliente' => '12',
                        'vent_venta_cuenta_igv' => '4011',
                        'vent_venta_precio_cobrado_letras' => NumeroALetras::convertir($precioCobradoTotal),
                        'vent_venta_estado_pago' => 1,
                        'vent_venta_distribuidor_id' => $distribuidor,
                        'vent_venta_descripcion' => $descripcion,
                        'vent_venta_estado_envio_sunat' => 2,
                        'vent_venta_comprado_por' => $datos_comprado_por ? $datos_comprado_por : null,
                        'vent_venta_comprovante_referenciado_nota' => $doc_referenciado_serie . '-' . $doc_referenciado_numero
                    )
                );


                foreach ($productos as $key => $producto) {

                    $stock = IdGenerador::calculaStock($vent_venta_almacen_id, $producto->vent_venta_detalle_producto_id, $QueryPeriodo[0]->cont_periodo_anio);

                    DB::table('vent_venta_detalle')->insert(
                        array('vent_venta_detalle_id' => IdGenerador::generaId(),
                            'vent_venta_detalle_precio_unitario' => $producto->vent_venta_detalle_precio_unitario,
                            'vent_venta_detalle_precio' => $producto->vent_venta_detalle_precio,
                            'vent_venta_detalle_cantidad' => $producto->vent_venta_detalle_cantidad,
                            'vent_venta_detalle_igv' => $producto->vent_venta_detalle_igv,
                            'vent_venta_detalle_tipo_operacion' => $producto->vent_venta_detalle_tipo_operacion,
                            'vent_venta_detalle_bi' => $producto->vent_venta_detalle_bi,
                            'vent_venta_detalle_producto_id' => $producto->vent_venta_detalle_producto_id,
                            'vent_venta_detalle_venta_id' => $vent_venta_id,
                            'vent_venta_detalle_descuento' => $producto->vent_venta_detalle_descuento,
                            'vent_venta_detalle_precio_cobro' => $producto->vent_venta_detalle_precio_descuento,
                            'vent_venta_detalle_costo_almacen' => $stock,
                            'vent_venta_detalle_cuenta_venta' => $producto->vent_venta_detalle_cuenta_venta,
                            'vent_venta_detalle_item' => $numero,

                        )
                    );
                    $numero = $numero + 1;
                }
                DB::table('vent_pago')->insert(
                    array('vent_pago_id' => IdGenerador::generaId(),
                        'vent_pago_importe' => $precioCobradoTotal,
                        'vent_pago_fecha' => date('Y-m-d H:i:s', strtotime("now")),
                        'vent_pago_numero_pago' => IdGenerador::generaNumeroPago(),
                        'vent_pago_venta_id' => $vent_venta_id,
                        'vent_pago_tipo_pago' => $vent_venta_tipo_venta,
                        'vent_pago_user_id' => $vent_venta_user_id,
                        'vent_pago_pago' => $vent_pago_pago,
                        'vent_pago_vuelto' => $vent_pago_vuelto,
                        'vent_pago_cliente_documento' => $vent_venta_cliente_numero_documento,
                        'vent_pago_modalidad' => $vent_pago_modalidad,
                        'vent_pago_numero_transaccion' => $vent_pago_numero_transaccion,
                        'vent_pago_almacen_id' => $vent_venta_almacen_id,
                        'vent_pago_periodo_id' => $comp_compra_periodo_id,

                    )
                );
            } else if ($vent_venta_tipo_venta == '02' && $vent_pago_pago < $precioCobradoTotal) {
                DB::table('vent_venta')->insert(
                    array('vent_venta_id' => $vent_venta_id,
                        'vent_venta_total' => $vent_venta_total,
                        'vent_venta_igv' => $vent_venta_igv,
                        'vent_venta_bi' => $vent_venta_bi,
                        'vent_venta_fecha' => date('Y-m-d H:i:s', strtotime("now")),
                        'vent_venta_tipo_venta' => '02',
                        'vent_venta_estado' => 1,
                        'vent_venta_numero' => $numero_venta,
                        'vent_venta_serie' => $serie->vent_serie,
                        'vent_venta_cliente_numero_documento' => $vent_venta_cliente_numero_documento,
                        'vent_venta_user_id' => $vent_venta_user_id,
                        'vent_venta_tipo_comprobante_id' => $serie->vent_tipo_comprobante_id,
                        'vent_venta_almacen_id' => $vent_venta_almacen_id,
                        'vent_venta_periodo_id' => $comp_compra_periodo_id,
                        'vent_venta_precio_cobrado' => $precioCobradoTotal,
                        'vent_venta_precio_descuento_total' => $descuentoTotal,
                        'vent_venta_cuenta_cliente' => '12',
                        'vent_venta_cuenta_igv' => '4011',
                        'vent_venta_precio_cobrado_letras' => NumeroALetras::convertir($precioCobradoTotal),
                        'vent_venta_estado_pago' => 0,
                        'vent_venta_distribuidor_id' => $distribuidor,
                        'vent_venta_descripcion' => $descripcion,
                        'vent_venta_estado_envio_sunat' => 2,
                        'vent_venta_comprado_por' => $datos_comprado_por ? $datos_comprado_por : null,
                        'vent_venta_comprovante_referenciado_nota' => $doc_referenciado_serie . '-' . $doc_referenciado_numero
                    )
                );
                foreach ($productos as $key => $producto) {
                    $stock = IdGenerador::calculaStock($vent_venta_almacen_id, $producto->vent_venta_detalle_producto_id, $QueryPeriodo[0]->cont_periodo_anio);
                    DB::table('vent_venta_detalle')->insert(
                        array('vent_venta_detalle_id' => IdGenerador::generaId(),
                            'vent_venta_detalle_precio_unitario' => $producto->vent_venta_detalle_precio_unitario,
                            'vent_venta_detalle_precio' => $producto->vent_venta_detalle_precio,
                            'vent_venta_detalle_cantidad' => $producto->vent_venta_detalle_cantidad,
                            'vent_venta_detalle_igv' => $producto->vent_venta_detalle_igv,
                            'vent_venta_detalle_tipo_operacion' => $producto->vent_venta_detalle_tipo_operacion,
                            'vent_venta_detalle_bi' => $producto->vent_venta_detalle_bi,
                            'vent_venta_detalle_producto_id' => $producto->vent_venta_detalle_producto_id,
                            'vent_venta_detalle_venta_id' => $vent_venta_id,
                            'vent_venta_detalle_descuento' => $producto->vent_venta_detalle_descuento,
                            'vent_venta_detalle_precio_cobro' => $producto->vent_venta_detalle_precio_descuento,
                            'vent_venta_detalle_costo_almacen' => $stock,
                            'vent_venta_detalle_cuenta_venta' => $producto->vent_venta_detalle_cuenta_venta,
                            'vent_venta_detalle_item' => $numero,
                        )
                    );
                    $numero = $numero + 1;
                }
            }

        } catch (\Exception $exception) {
            dd($exception);
        }


        return self::venta($vent_venta_id);

    }


    public static function venta($id)
    {

        try {
            $venta = "select v.vent_venta_comprado_por , v.vent_venta_comprobante_referenciado, v.vent_venta_id, v.vent_venta_total,
  v.vent_venta_igv, v.vent_venta_bi,
  DATE_FORMAT(v.vent_venta_fecha,'%d/%m/%Y') as vent_venta_fecha,
  v. vent_venta_numero,
  v. vent_venta_serie,
  v.vent_venta_precio_descuento_total,
  v.vent_venta_precio_cobrado,
  v. vent_venta_cliente_numero_documento,
  v.vent_venta_precio_cobrado_letras,
  v.vent_venta_tipo_venta,
  alm.alm_almacen_nombre,
  alm.alm_almacen_direccion,
  alm.alm_almacen_email,
  alm.alm_almacen_telefono,
  v.vent_venta_descripcion as fise,
  v.vent_venta_distribuidor_id,
  (select concat(a.alm_distribuidor_nombres, ' ', a.alm_distribuidor_apellidos)from alm_distribuidor a where a.alm_distribuidor_id=v.vent_venta_distribuidor_id  ) as distribuidor,
  tc.doc_tipo_comprobante_codigo AS doc_tipo_comprobante_codigo,
  UPPER (tc.doc_tipo_comprobante_nombre)AS doc_tipo_comprobante_nombre,
  case
  when character_length(v. vent_venta_cliente_numero_documento)=8 then
    (select concat(seg_per_apellido_paterno,' ', seg_per_apellido_materno,' ', seg_per_nombres)
     from seg_cliente where seg_per_dni=v. vent_venta_cliente_numero_documento)
  when v. vent_venta_cliente_numero_documento ='0000000000' then 'Clientes Varios'
  else (select alm_proveedor_razon_social from alm_proveedor where alm_proveedor_ruc= v. vent_venta_cliente_numero_documento)
  end as cliente,
  case
  when character_length(v. vent_venta_cliente_numero_documento)=8 then
    (select seg_per_direccion
     from seg_cliente where seg_per_dni=v. vent_venta_cliente_numero_documento)
  when v. vent_venta_cliente_numero_documento ='0000000000' then 'xxxxxxx'
  else (select alm_proveedor_direccion from alm_proveedor where alm_proveedor_ruc= v. vent_venta_cliente_numero_documento)
  end as cliente_direccion,
   case
  when character_length(v. vent_venta_cliente_numero_documento)=8 then
    (select seg_per_telefono
     from seg_cliente where seg_per_dni=v. vent_venta_cliente_numero_documento)
  when v. vent_venta_cliente_numero_documento ='0000000000' then 'xxxxxxx'
  else (select alm_proveedor_telefono from alm_proveedor where alm_proveedor_ruc= v. vent_venta_cliente_numero_documento)
  end as cliente_telefono
from vent_venta as v ,doc_tipo_comprabante tc, alm_almacen alm
where v.vent_venta_tipo_comprobante_id=tc.doc_tipo_comprobante_id
and v.vent_venta_almacen_id=alm.alm_almacen_id


      and v.vent_venta_id= '" . $id . "'
group by v.vent_venta_id";
            $resultado_venta = DB::select($venta);
            $resultado = $resultado_venta[0];
            if ($resultado->vent_venta_tipo_venta == "03") {
                $venta_detalle = "select  vd.vent_venta_detalle_precio_unitario,
vd.vent_venta_detalle_precio,
vd.vent_venta_detalle_cantidad,
 vd.vent_venta_detalle_igv,
 vd.vent_venta_detalle_bi ,
 vd.vent_venta_detalle_precio_cobro,
 vd.vent_venta_detalle_item,
 p.alm_unidad_medida_id,
 um.alm_unidad_medida_simbolo_impresion,
 upper (p.alm_producto_marca) as alm_producto_marca,
 p.alm_producto_vehiculo,
vd.vent_venta_detalle_item,CONVERT(SUBSTRING_INDEX(vd.vent_venta_detalle_item,'-',-1),UNSIGNED INTEGER) AS num,
 upper (p.alm_producto_nombre) as alm_producto_nombre,
 p.alm_producto_modelo,
 p.alm_producto_color,
 p.alm_producto_motor,
 p.alm_producto_chasis,
 p.alm_producto_dua,
 p.alm_producto_item,
 p.alm_producto_codigo,
 pp.alm_producto_nombre as categoria
 from vent_venta_detalle as vd , alm_producto p , alm_producto pp, alm_unidad_medida um
 where vd.vent_venta_detalle_producto_id= p.alm_producto_id
 and p.Parent_alm_producto_id=pp.alm_producto_id
 and  p.alm_unidad_medida_id=um.alm_unidad_medida_id
 and vd.vent_venta_detalle_venta_id= '$resultado->vent_venta_id' order by num ";
                $resultado_venta_detalle = DB::select($venta_detalle);
                $data_detalle = $resultado_venta_detalle[0];
            } else {
                $venta_detalle = "select  vd.vent_venta_detalle_precio_unitario,
vd.vent_venta_detalle_precio,
vd.vent_venta_detalle_cantidad,
 vd.vent_venta_detalle_igv,
 vd.vent_venta_detalle_bi ,
 vd.vent_venta_detalle_precio_cobro,
 vd.vent_venta_detalle_item,
 p.alm_unidad_medida_id,
  um.alm_unidad_medida_simbolo_impresion,
 upper (p.alm_producto_marca) as alm_producto_marca,
  p.alm_producto_vehiculo,
vd.vent_venta_detalle_item,CONVERT(SUBSTRING_INDEX(vd.vent_venta_detalle_item,'-',-1),UNSIGNED INTEGER) AS num,
 upper (p.alm_producto_nombre) as alm_producto_nombre,
  p.alm_producto_modelo,
 p.alm_producto_color,
 p.alm_producto_motor,
 p.alm_producto_chasis,
 p.alm_producto_dua,
 p.alm_producto_item,
 p.alm_producto_codigo,
 pp.alm_producto_nombre as categoria
 from vent_venta_detalle as vd , alm_producto p , alm_producto pp, alm_unidad_medida um
 where vd.vent_venta_detalle_producto_id= p.alm_producto_id
 and p.Parent_alm_producto_id=pp.alm_producto_id
    and  p.alm_unidad_medida_id=um.alm_unidad_medida_id
 and vd.vent_venta_detalle_venta_id= '$resultado->vent_venta_id' order by num ";
                $resultado_venta_detalle = DB::select($venta_detalle);
                $data_detalle = $resultado_venta_detalle;
            }
            $resultado->detalle = $data_detalle;
            $resultado->empresa = self::empresa();
            $resultado->totales = self::listar_totales_comprobante($resultado->vent_venta_id);
            /***** FACTURA: DATOS OBLIGATORIOS PARA EL CÓDIGO QR *****/
            /*RUC | TIPO DE DOCUMENTO | SERIE | NUMERO | MTO TOTAL IGV | MTO TOTAL DEL COMPROBANTE | FECHA DE EMISION |TIPO DE DOCUMENTO ADQUIRENTE | NUMERO DE DOCUMENTO ADQUIRENTE |*/
            $text_qr = '2660130746|03|f001|479397|18|118|26/10/2018|6|55887744|';
            $resultado->qr = self::empresa()->emp_empresa_ruc . '|03|' . $resultado_venta[0]->vent_venta_serie . '|'
                . $resultado_venta[0]->vent_venta_numero . '|' . $resultado_venta[0]->vent_venta_igv . '|'
                . $resultado_venta[0]->vent_venta_precio_cobrado . '|' . $resultado_venta[0]->vent_venta_fecha . '|6|' . $resultado_venta[0]->vent_venta_cliente_numero_documento . '|';


        } catch (\Exception $exception) {
            dd($exception);
        }
        return $resultado;


    }

    public static function anular_venta($id)
    {
        try {
            $query = "UPDATE vent_venta
            SET vent_venta_estado = '0'
            WHERE vent_venta_id = '" . $id . "'";
            DB::update($query);

        } catch (\Exception $e) {

        }
        return 'ok';


    }

    public static function bucar_venta_por_id_para_comunicar_baja($id_venta)
    {
        try {
            $sql = "select vent_venta_serie, vent_venta_numero , date(vent_venta_fecha) as vent_venta_fecha from vent_venta where vent_venta_id = '" . $id_venta . "'";
            $query = DB::select($sql);

        } catch (\Exception $e) {

        }
        return $query[0];

    }

    /** Elimina Venta */
    public static function eliminar_venta($id)
    {
        try {
            $elimarVentaDetalleQuery = "delete from vent_venta_detalle
                where vent_venta_detalle_venta_id = '" . $id . "' ";
            DB::delete($elimarVentaDetalleQuery);

            $ElimarVentaPagoquery = "delete from vent_pago
                where vent_pago_venta_id  = '" . $id . "' ";

            if (DB::delete($ElimarVentaPagoquery)) {
                $ElimarVentaquery = "delete from vent_venta
                where vent_venta_id = '" . $id . "' ";
                DB::delete($ElimarVentaquery);

            } else {
                dd("pago no eliminado");
            }


        } catch (\Exception $e) {
            dd($e);
        }
        return 'ok';

    }

    public static function buscar_venta_por_id_para_eliminar($venta_id)
    {
        try {
            $sql = "select a.vent_venta_serie,
                    a.vent_venta_estado_envio_sunat,
                    a.vent_venta_numero,
                    (select max(vent_venta_numero) from vent_venta
                    where vent_venta_serie=a.vent_venta_serie) as numero_venta_mayor
                    from vent_venta as a where a. vent_venta_id= '" . $venta_id . "'";
            $query = DB::select($sql);

        } catch (\Exception $e) {

        }
        return $query[0];
    }


    /** Reportes de ventas por usuario */
    public static function listar_ventas_usuario_por_dia($user, $fecha_inicio, $fecha_fin, $almacen_id, $tipo_documento)
    {
        $tipo_documento_valor = "";
        if ($tipo_documento != "") {
            $tipo_documento_valor = "and v.vent_venta_tipo_comprobante_id='" . $tipo_documento . "'";
        }

        try {

            $sql = "select v.vent_venta_id, v.vent_venta_total,
  v.vent_venta_igv, vent_venta_bi,
  v.vent_venta_fecha,
  v. vent_venta_numero,
  concat(v. vent_venta_serie,'-', v. vent_venta_numero)as serie_numero,
  v. vent_venta_serie,
  v.vent_venta_tipo_venta,
  v. vent_venta_cliente_numero_documento,
  v.vent_venta_estado,
  v.vent_venta_precio_descuento_total,
  v.vent_venta_precio_cobrado,
  v.vent_venta_estado_envio_sunat,
  v.vent_venta_comprobante_referenciado,

  date (date_add(v.vent_venta_fecha, interval +7 day ))-date (SYSDATE())  as venta_plazo_envio,
  UPPER (tc.doc_tipo_comprobante_nombre)AS doc_tipo_comprobante_nombre,
  /*case
  when character_length(v. vent_venta_cliente_numero_documento)=8 then
    (select concat(seg_per_apellido_paterno,' ', seg_per_apellido_materno,' ', seg_per_nombres)
     from seg_persona where seg_per_dni=v. vent_venta_cliente_numero_documento)
  when v. vent_venta_cliente_numero_documento ='0000000000' then 'Clientes Varios'
  else (select alm_proveedor_razon_social from alm_proveedor where alm_proveedor_ruc= v. vent_venta_cliente_numero_documento)
  end as cliente,*/



 case
   when character_length(v. vent_venta_cliente_numero_documento)=8 and v.vent_venta_comprado_por is not null or character_length(v. vent_venta_cliente_numero_documento)=8 and length(v.vent_venta_comprado_por) > 0  then v.vent_venta_comprado_por
  when character_length(v. vent_venta_cliente_numero_documento)=8 and v.vent_venta_comprado_por is  null or character_length(v. vent_venta_cliente_numero_documento)=8 and length(v.vent_venta_comprado_por) = 0 then
    (select concat(seg_per_apellido_paterno,' ', seg_per_apellido_materno,' ', seg_per_nombres)
     from seg_persona where seg_per_dni=v. vent_venta_cliente_numero_documento)

  else (select alm_proveedor_razon_social from alm_proveedor where alm_proveedor_ruc= v. vent_venta_cliente_numero_documento)
  end as cliente,



  case
  when character_length(v. vent_venta_cliente_numero_documento)=8 then
    (select seg_per_direccion
     from seg_persona where seg_per_dni=v. vent_venta_cliente_numero_documento)
  else (select alm_proveedor_direccion from alm_proveedor where alm_proveedor_ruc= v. vent_venta_cliente_numero_documento)
  end as cliente_direccion
from vent_venta as v ,doc_tipo_comprabante tc
where v.vent_venta_tipo_comprobante_id=tc.doc_tipo_comprobante_id
and v.vent_venta_user_id= '" . $user . "'
and v.vent_venta_almacen_id='" . $almacen_id . "'
and DATE(v.vent_venta_fecha) BETWEEN '" . $fecha_inicio . "' and '" . $fecha_fin . "' $tipo_documento_valor
order by v.vent_venta_fecha asc ";
            $Query = DB::select($sql);


        } catch (\Exception $exception) {
            dd($exception);
        }

        return $Query;
    }

    /** Reportes de ventas por usuario y por dia */


    public static function listar_resumen_venta_usuario_por_dia($usuario_id,
                                                                $fecha_inicio,
                                                                $fecha_fin,
                                                                $almacen_id,
                                                                $tipo_documento)
    {
        $tipo_documento_valor = "";
        if ($tipo_documento != "") {
            $tipo_documento_valor = "and vent_venta_tipo_comprobante_id='" . $tipo_documento . "'";
        }

        try {
            $sql = "select cont_periodo_id, cont_periodo_anio from cont_periodo where cont_periodo_estado= 1";
            $QueryPeriodo = DB::select($sql);
            $sql = "select
         sum(case when vent_venta_tipo_venta ='01' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07'  then vent_venta_precio_cobrado*-1 else vent_venta_precio_cobrado end*1 else vent_venta_precio_cobrado*0  end) total_contado,
         sum(case when vent_venta_tipo_venta ='01' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07'  then vent_venta_bi*-1 else vent_venta_bi end   *1 else vent_venta_bi*0 end)  bi_contado,
         sum(case when vent_venta_tipo_venta ='01' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07'  then vent_venta_igv*-1 else vent_venta_igv end *1 else vent_venta_igv*0 end) igv_contado,

         sum(case when vent_venta_tipo_venta ='02' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_precio_cobrado*-1 else vent_venta_precio_cobrado end *1 else vent_venta_precio_cobrado*0 end) total_credito,
         sum(case when vent_venta_tipo_venta ='02' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_bi*-1 else vent_venta_bi end *1 else vent_venta_bi*0 end) bi_credito,
         sum(case when vent_venta_tipo_venta ='02' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_igv*-1 else vent_venta_igv end *1 else vent_venta_igv*0 end) igv_credito,

         sum(vent_venta_precio_cobrado*case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then -1 else 1 end ) importe_total,
         sum(vent_venta_igv* case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then -1 else 1 end  ) igv_total,
         sum(vent_venta_bi*  case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then -1 else 1 end  ) base_total,

         sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='01' then vent_venta_precio_cobrado else 0 end) total_facturas,
         sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='03' then vent_venta_precio_cobrado else 0 end) total_boletass,
         sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_precio_cobrado else 0 end) total_notacreditos,
         sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='99' then vent_venta_precio_cobrado else 0 end) total_notas_ventas

from vent_venta
where DATE(vent_venta_fecha) between '" . $fecha_inicio . "' and '" . $fecha_fin . "'$tipo_documento_valor
and   vent_venta_user_id='" . $usuario_id . "'
and   vent_venta_almacen_id='" . $almacen_id . "'
and   vent_venta_periodo_id in (
  select cont_periodo_id from cont_periodo where cont_periodo_anio='" . $QueryPeriodo[0]->cont_periodo_anio . "'
)";
            $Query = DB::select($sql);


        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query[0];

    }


    public static function listar_ventas_totales_usuario_dia($fecha, $usuario, $almacen)
    {

        try {
            $sql = "select cont_periodo_id, cont_periodo_anio from cont_periodo where cont_periodo_estado= 1";
            $QueryPeriodo = DB::select($sql);
            $sql = "select
sum(vent_pago_importe) total,
  sum(case when vent_pago_modalidad='01' then vent_pago_importe else 0 end) Efectivo,
  sum(case when vent_pago_modalidad='02' then vent_pago_importe else 0 end) Visa,
  sum(case when vent_pago_modalidad='03' then vent_pago_importe else 0 end) MasterCard
from  vent_pago
where vent_pago_fecha='" . $fecha . "'
and   vent_pago_user_id='" . $usuario . "'
  and   vent_pago_almacen_id='" . $almacen . "'
  and   vent_pago_periodo_id in (
  select cont_periodo_id from cont_periodo where cont_periodo_anio='" . $QueryPeriodo[0]->cont_periodo_anio . "'
)";
            $Query = DB::select($sql);


        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query[0];
    }

    public static function listar_ventas_totales_dia($fecha)
    {

        try {
            $sql = "select cont_periodo_id, cont_periodo_anio from cont_periodo where cont_periodo_estado= 1";
            $QueryPeriodo = DB::select($sql);
            $sql = "select
sum(vent_pago_importe) total,
  sum(case when vent_pago_modalidad='01' then vent_pago_importe else 0 end) Efectivo,
  sum(case when vent_pago_modalidad='02' then vent_pago_importe else 0 end) Visa,
  sum(case when vent_pago_modalidad='03' then vent_pago_importe else 0 end) MasterCard
from  vent_pago
where vent_pago_fecha='" . $fecha . "'
  and   vent_pago_periodo_id in (
  select cont_periodo_id from cont_periodo where cont_periodo_anio='2019'
)";
            $Query = DB::select($sql);


        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query[0];
    }


    /** Reportes de ventas general por dia */
    public static function listar_resumen_venta_por_dia($fecha_inicio, $fecha_fin, $almacen_id, $tipo_documento, $distribuidor)
    {
        $tipo_documento_valor = "";
        if ($tipo_documento != "") {
            $tipo_documento_valor = "and vent_venta_tipo_comprobante_id='" . $tipo_documento . "'";
        }

        $almacen_id_valor = "";
        if ($almacen_id != "") {
            $almacen_id_valor = "and vent_venta_almacen_id= '" . $almacen_id . "'";
        }
        $distribuidor_id_valor = "";

        if ($distribuidor != "") {
            $distribuidor_id_valor = "and vent_venta_distribuidor_id='" . $distribuidor . "'";
        }
        try {
            $sql = "select cont_periodo_id, cont_periodo_anio from cont_periodo where cont_periodo_estado= 1";
            $QueryPeriodo = DB::select($sql);

            $sql = "select
         sum(case when vent_venta_tipo_venta ='01' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07'  then vent_venta_precio_cobrado*-1 else vent_venta_precio_cobrado end*1 else vent_venta_precio_cobrado*0  end) total_contado,
         sum(case when vent_venta_tipo_venta ='01' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07'  then vent_venta_bi*-1 else vent_venta_bi end   *1 else vent_venta_bi*0 end)  bi_contado,
         sum(case when vent_venta_tipo_venta ='01' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07'  then vent_venta_igv*-1 else vent_venta_igv end *1 else vent_venta_igv*0 end) igv_contado,

         sum(case when vent_venta_tipo_venta ='02' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_precio_cobrado*-1 else vent_venta_precio_cobrado end *1 else vent_venta_precio_cobrado*0 end) total_credito,
         sum(case when vent_venta_tipo_venta ='02' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_bi*-1 else vent_venta_bi end *1 else vent_venta_bi*0 end) bi_credito,
         sum(case when vent_venta_tipo_venta ='02' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_igv*-1 else vent_venta_igv end *1 else vent_venta_igv*0 end) igv_credito,

         sum(vent_venta_precio_cobrado*case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then -1 else 1 end ) importe_total,
         sum(vent_venta_igv* case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then -1 else 1 end  ) igv_total,
         sum(vent_venta_bi*  case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then -1 else 1 end  ) base_total,

         sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='01' then vent_venta_precio_cobrado else 0 end) total_facturas,
         sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='03' then vent_venta_precio_cobrado else 0 end) total_boletass,
         sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_precio_cobrado else 0 end) total_notacreditos,
         sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='99' then vent_venta_precio_cobrado else 0 end) total_notas_ventas

from vent_venta
where date(vent_venta_fecha) between '" . $fecha_inicio . "' and '" . $fecha_fin . "'$tipo_documento_valor $almacen_id_valor  $distribuidor_id_valor

and   vent_venta_periodo_id in (
  select cont_periodo_id from cont_periodo where cont_periodo_anio='" . $QueryPeriodo[0]->cont_periodo_anio . "'
)";

            //dd($sql);

            $Query = DB::select($sql);


        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query[0];
    }

    public static function listar_ventas_general_por_fecha_dia($fecha_inicio,
                                                               $fecha_fin,
                                                               $almacen,
                                                               $tipo_documento,
                                                               $distribuidor)
    {
        $tipo_documento_valor = "";
        if ($tipo_documento != "") {
            $tipo_documento_valor = "and v.vent_venta_tipo_comprobante_id='" . $tipo_documento . "'";
        }
        $almacen_id_valor = "";
        if ($almacen != "") {
            $almacen_id_valor = "and v.vent_venta_almacen_id= '" . $almacen . "'";
        }
        $distribuidor_id_valor = "";

        if ($distribuidor != "") {
            $distribuidor_id_valor = "and v.vent_venta_distribuidor_id='" . $distribuidor . "'";
        }
        try {
            $sql = "select v.vent_venta_id, v.vent_venta_total,
  v.vent_venta_igv, vent_venta_bi,
  v.vent_venta_fecha,
  v. vent_venta_numero,
  concat(v. vent_venta_serie,'-', v. vent_venta_numero)as serie_numero,
  v. vent_venta_serie,
  v.vent_venta_tipo_venta,
  v. vent_venta_cliente_numero_documento,
    v.vent_venta_estado,
   v.vent_venta_precio_descuento_total,
  v.vent_venta_precio_cobrado,
  v.vent_venta_descripcion,
   date (v.vent_venta_fecha) as venta_fecha,
  v.vent_venta_estado_envio_sunat,
	u.email,
  UPPER (tc.doc_tipo_comprobante_nombre)AS doc_tipo_comprobante_nombre,
   /*case
  when character_length(v. vent_venta_cliente_numero_documento)=8 then
    (select concat(seg_per_apellido_paterno,' ', seg_per_apellido_materno,' ', seg_per_nombres)
     from seg_persona where seg_per_dni=v. vent_venta_cliente_numero_documento)
  when v. vent_venta_cliente_numero_documento ='0000000000' then 'Clientes Varios'
  else (select alm_proveedor_razon_social from alm_proveedor where alm_proveedor_ruc= v. vent_venta_cliente_numero_documento)
  end as cliente,*/



  case
   when character_length(v. vent_venta_cliente_numero_documento)=8 and v.vent_venta_comprado_por is not null or character_length(v. vent_venta_cliente_numero_documento)=8 and length(v.vent_venta_comprado_por) > 0  then v.vent_venta_comprado_por
  when character_length(v. vent_venta_cliente_numero_documento)=8 and v.vent_venta_comprado_por is  null or character_length(v. vent_venta_cliente_numero_documento)=8 and length(v.vent_venta_comprado_por) = 0 then
    (select concat(seg_per_apellido_paterno,' ', seg_per_apellido_materno,' ', seg_per_nombres)
     from seg_persona where seg_per_dni=v. vent_venta_cliente_numero_documento)

  else (select alm_proveedor_razon_social from alm_proveedor where alm_proveedor_ruc= v. vent_venta_cliente_numero_documento)
  end as cliente,
  case
  when character_length(v. vent_venta_cliente_numero_documento)=8 then
    (select seg_per_direccion
     from seg_persona where seg_per_dni=v. vent_venta_cliente_numero_documento)
  else (select alm_proveedor_direccion from alm_proveedor where alm_proveedor_ruc= v. vent_venta_cliente_numero_documento)
  end as cliente_direccion
from vent_venta as v ,doc_tipo_comprabante tc, users u
where v.vent_venta_tipo_comprobante_id=tc.doc_tipo_comprobante_id
and v.vent_venta_user_id=u.id
and DATE(v.vent_venta_fecha) between '" . $fecha_inicio . "' and '" . $fecha_fin . "'$tipo_documento_valor
  $almacen_id_valor $distribuidor_id_valor
order by v.vent_venta_fecha asc ";
            $Query = DB::select($sql);


        } catch (\Exception $exception) {
            dd($exception);
        }

        return $Query;
    }


    public static function listar_ventas_con_fise_por_fecha_dia($fecha_inicio,
                                                                $fecha_fin,
                                                                $almacen,
                                                                $tipo_documento,
                                                                $distribuidor)
    {


        $tipo_documento_valor = "";
        if ($tipo_documento != "") {
            $tipo_documento_valor = "and v.vent_venta_tipo_comprobante_id='" . $tipo_documento . "'";
        }
        $almacen_id_valor = "";
        if ($almacen != "") {
            $almacen_id_valor = "and v.vent_venta_almacen_id= '" . $almacen . "'";
        }
        $distribuidor_id_valor = "";

        if ($distribuidor != "") {
            $distribuidor_id_valor = "and v.vent_venta_distribuidor_id='" . $distribuidor . "'";
        }
        try {
            $sql = "
and DATE(v.vent_venta_fecha) between '" . $fecha_inicio . "' and '" . $fecha_fin . "'$tipo_documento_valor
  $almacen_id_valor $distribuidor_id_valor
order by v.vent_venta_fecha asc ";
            $Query = DB::select($sql);


        } catch (\Exception $exception) {
            dd($exception);
        }

        return $Query;
    }


    public static function listar_resumen_venta_fise_por_dia($fecha_inicio, $fecha_fin, $almacen_id, $tipo_documento, $distribuidor)
    {
        $tipo_documento_valor = "";
        if ($tipo_documento != "") {
            $tipo_documento_valor = "and vent_venta_tipo_comprobante_id='" . $tipo_documento . "'";
        }

        $almacen_id_valor = "";
        if ($almacen_id != "") {
            $almacen_id_valor = "and vent_venta_almacen_id= '" . $almacen_id . "'";
        }
        $distribuidor_id_valor = "";

        if ($distribuidor != "") {
            $distribuidor_id_valor = "and vent_venta_distribuidor_id='" . $distribuidor . "'";
        }
        try {
            $sql = "select cont_periodo_id, cont_periodo_anio from cont_periodo where cont_periodo_estado= 1";
            $QueryPeriodo = DB::select($sql);

            $sql = "select
         sum(case when vent_venta_tipo_venta ='01' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07'  then vent_venta_precio_cobrado*-1 else vent_venta_precio_cobrado end*1 else vent_venta_precio_cobrado*0  end) total_contado,
         sum(case when vent_venta_tipo_venta ='01' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07'  then vent_venta_bi*-1 else vent_venta_bi end   *1 else vent_venta_bi*0 end)  bi_contado,
         sum(case when vent_venta_tipo_venta ='01' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07'  then vent_venta_igv*-1 else vent_venta_igv end *1 else vent_venta_igv*0 end) igv_contado,

         sum(case when vent_venta_tipo_venta ='02' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_precio_cobrado*-1 else vent_venta_precio_cobrado end *1 else vent_venta_precio_cobrado*0 end) total_credito,
         sum(case when vent_venta_tipo_venta ='02' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_bi*-1 else vent_venta_bi end *1 else vent_venta_bi*0 end) bi_credito,
         sum(case when vent_venta_tipo_venta ='02' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_igv*-1 else vent_venta_igv end *1 else vent_venta_igv*0 end) igv_credito,

         sum(vent_venta_precio_cobrado*case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then -1 else 1 end ) importe_total,
         sum(vent_venta_igv* case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then -1 else 1 end  ) igv_total,
         sum(vent_venta_bi*  case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then -1 else 1 end  ) base_total,

         sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='01' then vent_venta_precio_cobrado else 0 end) total_facturas,
         sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='03' then vent_venta_precio_cobrado else 0 end) total_boletass,
         sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_precio_cobrado else 0 end) total_notacreditos,
         sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='99' then vent_venta_precio_cobrado else 0 end) total_notas_ventas

from vent_venta
where date(vent_venta_fecha) between '" . $fecha_inicio . "' and '" . $fecha_fin . "'$tipo_documento_valor $almacen_id_valor  $distribuidor_id_valor
 and   vent_venta_descripcion <>\"\"
and   vent_venta_periodo_id in (
  select cont_periodo_id from cont_periodo where cont_periodo_anio='" . $QueryPeriodo[0]->cont_periodo_anio . "'
)";

            //dd($sql);

            $Query = DB::select($sql);


        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query[0];
    }


    public static function listar_venta_detalle($id)
    {
        try {
            $sql = "select  vd.vent_venta_detalle_precio_unitario,

vd.vent_venta_detalle_precio,
vd.vent_venta_detalle_cantidad,
 vd.vent_venta_detalle_igv,
 vd.vent_venta_detalle_bi ,
 vd.vent_venta_detalle_descuento,
 vd.vent_venta_detalle_precio_cobro,
 vd.vent_venta_detalle_item,
 vd.vent_venta_detalle_tipo_operacion,
 vd.vent_venta_detalle_producto_id,
 vd.vent_venta_detalle_item,CONVERT(SUBSTRING_INDEX(vd.vent_venta_detalle_item,'-',-1),UNSIGNED INTEGER) AS num,
 p.alm_producto_nombre,
 p.alm_producto_codigo,
 p.alm_producto_marca,
 p.alm_producto_modelo,
 p.alm_producto_color,
 p.alm_producto_motor,
 p.alm_producto_chasis,
 p.alm_producto_dua,
 p.alm_producto_item,
 p.alm_producto_codigo,
  pp.alm_producto_nombre as categoria,
 um.alm_unidad_medida_simbolo
 from vent_venta_detalle as vd , alm_producto as p, alm_unidad_medida as um , alm_producto pp
 where vd.vent_venta_detalle_producto_id= p.alm_producto_id
 and vd.vent_venta_detalle_venta_id
 and p.alm_unidad_medida_id=um.alm_unidad_medida_id
  and p.Parent_alm_producto_id=pp.alm_producto_id
   and vd.vent_venta_detalle_venta_id= '$id' order by num";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }


    public static function reporte_ventas_por_mes_usuario($almacen, $usuario, $mes, $anio)
    {
        try {
            $sql = "select date_format(vent_venta_fecha,'%Y-%m-%d') as vent_venta_fecha,
       sum(vent_venta_precio_cobrado) as total,
       sum(vent_venta_bi) as total_bi,
       sum(vent_venta_igv) as total_igv
from    vent_venta
where   vent_venta_user_id='" . $usuario . "'
  and   vent_venta_almacen_id='" . $almacen . "'
  and   vent_venta_periodo_id in ( select cont_periodo_id from cont_periodo where cont_periodo_anio='" . $anio . "')
  and   month(vent_venta_fecha) ='" . $mes . "'
  and   year(vent_venta_fecha) ='" . $anio . "'
group by date_format(vent_venta_fecha,'%Y-%m-%d')";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }


    public static function reporte_ventas_por_mes($almacen, $mes, $anio)
    {
        try {
            $sql = "select date_format(vent_venta_fecha,'%Y-%m-%d')as  vent_venta_fecha,
       sum(vent_venta_precio_cobrado) as total,
       sum(vent_venta_bi) as total_bi,
       sum(vent_venta_igv) as total_igv
from    vent_venta
where    vent_venta_almacen_id='" . $almacen . "'
  and   vent_venta_periodo_id in ( select cont_periodo_id from cont_periodo where cont_periodo_anio='" . $anio . "')
  and   month(vent_venta_fecha) ='" . $mes . "'
  and   year(vent_venta_fecha) ='" . $anio . "'
group by date_format(vent_venta_fecha,'%Y-%m-%d')";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }


    public static function total_reporte_ventas_por_mes_usuario($almacen, $usuario, $mes, $anio)
    {
        try {
            $sql = "select
  sum(case when vent_venta_tipo_venta ='01' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07'  then vent_venta_precio_cobrado*-1 else vent_venta_precio_cobrado end*1 else vent_venta_precio_cobrado*0  end) total_contado,
  sum(case when vent_venta_tipo_venta ='01' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07'  then vent_venta_bi*-1 else vent_venta_bi end   *1 else vent_venta_bi*0 end)  bi_contado,
  sum(case when vent_venta_tipo_venta ='01' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07'  then vent_venta_igv*-1 else vent_venta_igv end *1 else vent_venta_igv*0 end) igv_contado,

  sum(case when vent_venta_tipo_venta ='02' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_precio_cobrado*-1 else vent_venta_precio_cobrado end *1 else vent_venta_precio_cobrado*0 end) total_credito,
  sum(case when vent_venta_tipo_venta ='02' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_bi*-1 else vent_venta_bi end *1 else vent_venta_bi*0 end) bi_credito,
  sum(case when vent_venta_tipo_venta ='02' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_igv*-1 else vent_venta_igv end *1 else vent_venta_igv*0 end) igv_credito,

  sum(vent_venta_precio_cobrado*case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then -1 else 1 end ) importe_total,
  sum(vent_venta_igv* case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then -1 else 1 end  ) igv_total,
  sum(vent_venta_bi*  case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then -1 else 1 end  ) base_total,

  sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='01' then vent_venta_precio_cobrado else 0 end) total_facturas,
  sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='03' then vent_venta_precio_cobrado else 0 end) total_boletass,
  sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_precio_cobrado else 0 end) total_notacreditos,
  sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='99' then vent_venta_precio_cobrado else 0 end) total_notas_ventas,

  sum(case when (select vent_pago_modalidad from vent_pago where vent_pago_venta_id=vent_venta_id and  vent_pago_fecha=vent_venta_fecha and   month(vent_pago_fecha)='1')='01' then vent_venta_precio_cobrado else 0 end) total_efectivo,
  sum(case when (select vent_pago_modalidad from vent_pago where vent_pago_venta_id=vent_venta_id and  vent_pago_fecha=vent_venta_fecha and   month(vent_pago_fecha)='1')='02' then vent_venta_precio_cobrado else 0 end) total_visa,
  sum(case when (select vent_pago_modalidad from vent_pago where vent_pago_venta_id=vent_venta_id and  vent_pago_fecha=vent_venta_fecha and   month(vent_pago_fecha)='1')='03' then vent_venta_precio_cobrado else 0 end) total_mastercard

from vent_venta
where  vent_venta_user_id='" . $usuario . "'
  and   vent_venta_almacen_id='" . $almacen . "'
  and   vent_venta_periodo_id in ( select cont_periodo_id from cont_periodo where cont_periodo_anio='2019')
  and   month(vent_venta_fecha) ='" . $mes . "'
  and   year(vent_venta_fecha) ='" . $anio . "'";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }


    public static function total_reporte_ventas_por_mes($almacen, $mes, $anio)
    {
        try {
            $sql = "select
  sum(case when vent_venta_tipo_venta ='01' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07'  then vent_venta_precio_cobrado*-1 else vent_venta_precio_cobrado end*1 else vent_venta_precio_cobrado*0  end) total_contado,
  sum(case when vent_venta_tipo_venta ='01' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07'  then vent_venta_bi*-1 else vent_venta_bi end   *1 else vent_venta_bi*0 end)  bi_contado,
  sum(case when vent_venta_tipo_venta ='01' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07'  then vent_venta_igv*-1 else vent_venta_igv end *1 else vent_venta_igv*0 end) igv_contado,

  sum(case when vent_venta_tipo_venta ='02' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_precio_cobrado*-1 else vent_venta_precio_cobrado end *1 else vent_venta_precio_cobrado*0 end) total_credito,
  sum(case when vent_venta_tipo_venta ='02' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_bi*-1 else vent_venta_bi end *1 else vent_venta_bi*0 end) bi_credito,
  sum(case when vent_venta_tipo_venta ='02' then case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_igv*-1 else vent_venta_igv end *1 else vent_venta_igv*0 end) igv_credito,

  sum(vent_venta_precio_cobrado*case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then -1 else 1 end ) importe_total,
  sum(vent_venta_igv* case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then -1 else 1 end  ) igv_total,
  sum(vent_venta_bi*  case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then -1 else 1 end  ) base_total,

  sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='01' then vent_venta_precio_cobrado else 0 end) total_facturas,
  sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='03' then vent_venta_precio_cobrado else 0 end) total_boletass,
  sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='07' then vent_venta_precio_cobrado else 0 end) total_notacreditos,
  sum(case when (select doc_tipo_comprobante_codigo from doc_tipo_comprabante where doc_tipo_comprobante_id = vent_venta_tipo_comprobante_id )='99' then vent_venta_precio_cobrado else 0 end) total_notas_ventas,

  sum(case when (select vent_pago_modalidad from vent_pago where vent_pago_venta_id=vent_venta_id and  vent_pago_fecha=vent_venta_fecha and   month(vent_pago_fecha)='1')='01' then vent_venta_precio_cobrado else 0 end) total_efectivo,
  sum(case when (select vent_pago_modalidad from vent_pago where vent_pago_venta_id=vent_venta_id and  vent_pago_fecha=vent_venta_fecha and   month(vent_pago_fecha)='1')='02' then vent_venta_precio_cobrado else 0 end) total_visa,
  sum(case when (select vent_pago_modalidad from vent_pago where vent_pago_venta_id=vent_venta_id and  vent_pago_fecha=vent_venta_fecha and   month(vent_pago_fecha)='1')='03' then vent_venta_precio_cobrado else 0 end) total_mastercard

from vent_venta
where  vent_venta_almacen_id='" . $almacen . "'
 and   vent_venta_periodo_id in ( select cont_periodo_id from cont_periodo where cont_periodo_anio='2019')
  and   month(vent_venta_fecha) ='" . $mes . "'
  and   year(vent_venta_fecha) ='" . $anio . "'";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }

    public static function consulta_facturacion($id)
    {

        try {
            $sql = "select v.vent_venta_id, c.doc_tipo_comprobante_codigo as tipo_comprobante,
 v.vent_venta_serie as serie_comprobante,
 v.vent_venta_numero as numero_comprobante,
 v.vent_venta_estado_envio_sunat,
 v.vent_venta_nota_codigo,
 v.vent_venta_motivo_nota,
 v.vent_venta_tipo_comprobante_referenciado,
 v.vent_venta_comprobante_referenciado,
 date(v. vent_venta_fecha) as fecha_comprobante,
 'PEN' as codmoneda_comprobante,
 case
  when character_length(v. vent_venta_cliente_numero_documento)=8 and v.vent_venta_comprado_por is null || v.vent_venta_comprado_por= '' then '1'
  when v. vent_venta_cliente_numero_documento ='00000000' then '1'
  when v. vent_venta_comprado_por is not null and character_length(v. vent_venta_cliente_numero_documento)=8 then '0'
  else '6'
  end as cliente_tipodocumento,

 case
  when v.vent_venta_comprado_por is null || v.vent_venta_comprado_por= '' then v.vent_venta_cliente_numero_documento
  when v.vent_venta_comprado_por is not null then '99999999'
  end as cliente_numerodocumento,
  case
   when character_length(v. vent_venta_cliente_numero_documento)=8 and v.vent_venta_comprado_por is not null || v.vent_venta_comprado_por <> '' then (select vent_venta_comprado_por from vent_venta where vent_venta_id= v. vent_venta_id)
  when character_length(v. vent_venta_cliente_numero_documento)=8 then
    (select concat(seg_per_apellido_paterno,' ', seg_per_apellido_materno,' ', seg_per_nombres)
     from seg_persona where seg_per_dni=v. vent_venta_cliente_numero_documento)

  else (select alm_proveedor_razon_social from alm_proveedor where alm_proveedor_ruc= v. vent_venta_cliente_numero_documento)
  end as cliente_nombre,
  'PE' as cliente_pais,
  ''as cliente_ciudad,

  case
when character_length(v. vent_venta_cliente_numero_documento)=8 then
    ifnull((select seg_per_direccion
     from seg_persona where seg_per_dni=v. vent_venta_cliente_numero_documento),'-')
  when v. vent_venta_cliente_numero_documento ='00000000' then '-'
  else ifnull((select alm_proveedor_direccion from alm_proveedor where alm_proveedor_ruc= v. vent_venta_cliente_numero_documento),'-')
  end as cliente_direccion,



   case
  when character_length(v. vent_venta_cliente_numero_documento)=8 then
    ifnull((select seg_per_email
     from seg_persona where seg_per_dni=v. vent_venta_cliente_numero_documento),'-')
  when v. vent_venta_cliente_numero_documento ='00000000' then '-'
  else ifnull((select alm_proveedor_correo from alm_proveedor where alm_proveedor_ruc= v. vent_venta_cliente_numero_documento),'-')
  end as correo_electronico,


 v.vent_venta_bi as txt_subtotal_comprobante,
 v.vent_venta_igv as txt_igv_comprobante,
 v.vent_venta_precio_cobrado as txt_total_comprobante ,
 v.vent_venta_precio_cobrado_letras as txt_total_letras
 from vent_venta v, doc_tipo_comprabante c
 where v.vent_venta_tipo_comprobante_id=c.doc_tipo_comprobante_id
 and v.vent_venta_id= '" . $id . "' ";
            $Query = DB::select($sql);

            $sqlDetalle = "select
       vt.vent_venta_detalle_item as ITEM_DET,
       um.alm_unidad_medida_simbolo as UNIDAD_MEDIDA_DET,
       vt.vent_venta_detalle_cantidad as CANTIDAD_DET,
       vt.vent_venta_detalle_precio_unitario as PRECIO_DET,
       vt.vent_venta_detalle_bi as SUB_TOTAL_DET,
        '01' as PRECIO_TIPO_CODIGO,
       vt.vent_venta_detalle_igv as IGV_DET,
        '0' as ISC_DET,
       vt.vent_venta_detalle_bi as IMPORTE_DET,
       vt.vent_venta_detalle_tipo_operacion as COD_TIPO_OPERACION_DET,
       vt.vent_venta_detalle_item,
       CONVERT(SUBSTRING_INDEX(vt.vent_venta_detalle_item,'-',-1),UNSIGNED INTEGER) AS num,
case
when p.alm_producto_vehiculo=0 then
p.alm_producto_nombre
else
concat(p.alm_producto_nombre,
               ', MARCA:', p.alm_producto_marca,
               ' MODELO:', p.alm_producto_modelo,
               ' COLOR:',p.alm_producto_color,
               ' MOTOR:', p.alm_producto_motor,
               ' CHASIS:', p.alm_producto_chasis)
end as
  DESCRIPCION_DET,
       vt.vent_venta_detalle_id as CODIGO_DET,
       vt.vent_venta_detalle_bi as PRECIO_SIN_IGV_DET
from vent_venta_detalle as vt, alm_producto p,  alm_unidad_medida as um
where vt.vent_venta_detalle_producto_id=p.alm_producto_id
      and p.alm_unidad_medida_id=um.alm_unidad_medida_id
      and vt.vent_venta_detalle_venta_id='" . $Query[0]->vent_venta_id . "'
order by num";
            $QueryDetalle = DB::select($sqlDetalle);
            $Query[0]->detalle = $QueryDetalle;

        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query[0];
    }

    public static function guardar_documento_electronico($venta_id, $data, $estado_envio)
    {
        try {
            $query = "UPDATE vent_venta SET vent_venta_ruta_xml='" . $data['url_xml'] . "' , vent_venta_ruta_cdr='',
 vent_venta_hash='', vent_venta_fecha_envio_sunat='" . date('Y-m-d H:i:s') . "', vent_venta_estado_envio_sunat='" . $estado_envio . "'
                    WHERE vent_venta_id = '" . $venta_id . "'";
            DB::update($query);

        } catch (\Exception $e) {
            dd($e);

        }
        return true;
    }

    public static function listar_totales_comprobante($id_venta)
    {
        try {
            $sql = "select ROUND(sum(gravado),2) gravado, ROUND(sum(exonerado),2) exonerado, ROUND(sum(inafecto),2) inafecto from (
                select sum(vent_venta_detalle_precio) gravado, 0 exonerado, 0 inafecto
                from vent_venta_detalle
                where vent_venta_detalle_tipo_operacion = '10'
                and vent_venta_detalle_venta_id = '" . $id_venta . "'
                union all
                select 0, sum(vent_venta_detalle_precio) exonerado, 0
                from vent_venta_detalle
                where vent_venta_detalle_tipo_operacion = '20'
                and vent_venta_detalle_venta_id = '" . $id_venta . "'


                union all

                select 0, 0, sum(vent_venta_detalle_precio) inafecto
                from vent_venta_detalle
                where vent_venta_detalle_tipo_operacion = '30'
                  and vent_venta_detalle_venta_id = '" . $id_venta . "'
              )
suma;";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query[0];
    }


    public static function documento_electronico($id)
    {
        try {
            $sql = "select vent_venta_ruta_xml,
                            vent_venta_ruta_cdr,
                            vent_venta_serie
                            from vent_venta where vent_venta_id= '" . $id . "'";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query[0];

    }


    public static function comprobante($serie, $numero_venta)
    {
        try {
            $sql = "select
  v.vent_venta_id,
  v.vent_venta_igv,
  v.vent_venta_bi,
  v.vent_venta_precio_cobrado,
  v.vent_venta_fecha,
  v.vent_venta_numero,
  v.vent_venta_total,
  v.vent_venta_serie,
  v.vent_venta_tipo_venta,
  v.vent_venta_cliente_numero_documento,
  v.vent_venta_estado,
  v.vent_venta_precio_descuento_total,
  v.vent_venta_estado_envio_sunat,
  v.vent_venta_comprobante_referenciado,
  UPPER(tc.doc_tipo_comprobante_nombre) AS doc_tipo_comprobante_nombre,
  case
  when character_length(v.vent_venta_cliente_numero_documento) = 8
    then
      (select concat(seg_per_apellido_paterno, ' ', seg_per_apellido_materno, ' ', seg_per_nombres)
       from seg_persona
       where seg_per_dni = v.vent_venta_cliente_numero_documento)
  when v.vent_venta_cliente_numero_documento = '0000000000'
    then 'Clientes Varios'
  else (select alm_proveedor_razon_social
        from alm_proveedor
        where alm_proveedor_ruc = v.vent_venta_cliente_numero_documento)
  end                                   as cliente,
  case
  when character_length(v.vent_venta_cliente_numero_documento) = 8
    then
      (select seg_per_direccion
       from seg_persona
       where seg_per_dni = v.vent_venta_cliente_numero_documento)
  else (select alm_proveedor_direccion
        from alm_proveedor
        where alm_proveedor_ruc = v.vent_venta_cliente_numero_documento)
  end                                   as cliente_direccion
from vent_venta as v, doc_tipo_comprabante tc
where v.vent_venta_tipo_comprobante_id = tc.doc_tipo_comprobante_id
  and v.vent_venta_serie= '" . $serie . "' and v.vent_venta_numero= '" . $numero_venta . "'";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query[0];

    }

    public static function comprobante_detalle($id_venta)
    {

        try {
            $sql = "select
  vd.vent_venta_detalle_precio_unitario,
  vd.vent_venta_detalle_precio,
  vd.vent_venta_detalle_descuento,
  vd.vent_venta_detalle_cantidad,
  vd.vent_venta_detalle_producto_id,
  vd.vent_venta_detalle_igv,
  vd.vent_venta_detalle_bi,
  vd.vent_venta_detalle_tipo_operacion,
  vd.vent_venta_detalle_item,
  p.alm_producto_marca,
  p.alm_producto_motor,
  p.alm_producto_chasis,
  p.alm_producto_modelo,
  CONVERT(SUBSTRING_INDEX(vd.vent_venta_detalle_item, '-', -1), UNSIGNED INTEGER) AS num,
  vd.vent_venta_detalle_precio_cobro,
  concat(pp.alm_producto_nombre, ' ', p.alm_producto_nombre)                      as alm_producto_nombre
from vent_venta_detalle as vd, alm_producto p, alm_producto pp
where vd.vent_venta_detalle_producto_id = p.alm_producto_id
      and p.Parent_alm_producto_id = pp.alm_producto_id
      and vd.vent_venta_detalle_venta_id = '" . $id_venta . "'
order by num";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }


    public static function registrar_comunicacion_baja(
        $vent_venta_baja_fecha_referencia,
        $vent_venta_baja_fecha,
        $vent_venta_baja_serie,
        $vent_venta_baja_motivo,
        $vent_venta_baja_venta_serie,
        $vent_venta_baja_venta_numero,
        $vent_venta_baja_secuencia,
        $vent_venta_baja_codigo,
        $vent_venta_baja_venta_id,
        $vent_venta_baja_xml,
        $vent_venta_baja_cdr,
        $vent_venta_baja_hash_cpe, $vent_venta_baja_hash_cdr)
    {

        try {
            DB::table('vent_venta_baja')->insert(
                array('vent_venta_baja_id' => IdGenerador::generaId(),

                    'vent_venta_baja_fecha_referencia' => $vent_venta_baja_fecha_referencia,
                    'vent_venta_baja_fecha' => $vent_venta_baja_fecha,
                    'vent_venta_baja_serie' => $vent_venta_baja_serie,
                    'vent_venta_baja_motivo' => $vent_venta_baja_motivo,
                    'vent_venta_baja_venta_serie' => $vent_venta_baja_venta_serie,
                    'vent_venta_baja_venta_numero' => $vent_venta_baja_venta_numero,
                    'vent_venta_baja_secuencia' => $vent_venta_baja_secuencia,
                    'vent_venta_baja_codigo' => $vent_venta_baja_codigo,
                    'vent_venta_baja_venta_id' => $vent_venta_baja_venta_id,
                    'vent_venta_baja_xml' => $vent_venta_baja_xml,
                    'vent_venta_baja_cdr' => $vent_venta_baja_cdr,
                    'vent_venta_baja_hash_cpe' => $vent_venta_baja_hash_cpe,
                    'vent_venta_baja_hash_cdr' => $vent_venta_baja_hash_cdr,
                ));

        } catch (\Exception $exception) {

        }

        return 'ok';
    }


    public static function registrar_nota_credito($vent_venta_user_id, $vent_venta_total,
                                                  $vent_venta_igv,
                                                  $vent_venta_bi,
                                                  $vent_venta_cliente_numero_documento,
                                                  $vent_venta_almacen_id,
                                                  $vent_venta_tipo_comprobante_codigo,
                                                  $productos,
                                                  $precioCobradoTotal,
                                                  $vent_venta_comprobante_referenciado,
                                                  $vent_venta_motivo_nota, $vent_venta_nota_codigo, $vent_venta_tipo_comprobante_referenciado)

    {
        try {
            $sql = "select cont_periodo_id, cont_periodo_anio from cont_periodo where cont_periodo_estado= 1";
            $QueryPeriodo = DB::select($sql);
            $comp_compra_periodo_id = $QueryPeriodo[0]->cont_periodo_id;
            $vent_venta_id = IdGenerador::generaId();
            $serie = IdGenerador::generaSerieNotaCredito($vent_venta_user_id, $vent_venta_tipo_comprobante_codigo, $vent_venta_tipo_comprobante_referenciado);
            $numero_venta = IdGenerador::generaNumeroVenta($serie->vent_serie,
                $vent_venta_almacen_id,
                $QueryPeriodo[0]->cont_periodo_id,
                $vent_venta_tipo_comprobante_codigo,
                $QueryPeriodo[0]->cont_periodo_anio);
            $numero = 0;
            DB::table('vent_venta')->insert(
                array('vent_venta_id' => $vent_venta_id,
                    'vent_venta_total' => $vent_venta_total,
                    'vent_venta_igv' => $vent_venta_igv,
                    'vent_venta_bi' => $vent_venta_bi,
                    'vent_venta_fecha' => date('Y-m-d H:i:s', strtotime("now")),
                    'vent_venta_tipo_venta' => '02',
                    'vent_venta_estado' => 1,
                    'vent_venta_numero' => $numero_venta,
                    'vent_venta_serie' => $serie->vent_serie,
                    'vent_venta_cliente_numero_documento' => $vent_venta_cliente_numero_documento,
                    'vent_venta_user_id' => $vent_venta_user_id,
                    'vent_venta_tipo_comprobante_id' => $serie->vent_tipo_comprobante_id,
                    'vent_venta_almacen_id' => $vent_venta_almacen_id,
                    'vent_venta_periodo_id' => $comp_compra_periodo_id,
                    'vent_venta_precio_cobrado' => $precioCobradoTotal,
                    'vent_venta_precio_descuento_total' => 0.00,
                    'vent_venta_cuenta_cliente' => '12',
                    'vent_venta_cuenta_igv' => '4011',
                    'vent_venta_precio_cobrado_letras' => NumeroALetras::convertir($precioCobradoTotal),
                    'vent_venta_estado_pago' => 0,
                    'vent_venta_estado_envio_sunat' => 2,
                    'vent_venta_comprobante_referenciado' => $vent_venta_comprobante_referenciado,
                    'vent_venta_motivo_nota' => $vent_venta_motivo_nota,
                    'vent_venta_nota_codigo' => $vent_venta_nota_codigo,
                    'vent_venta_tipo_comprobante_referenciado' => $vent_venta_tipo_comprobante_referenciado
                )
            );
            foreach ($productos as $key => $producto) {
                $stock = IdGenerador::calculaStock($vent_venta_almacen_id, $producto->vent_venta_detalle_producto_id, $QueryPeriodo[0]->cont_periodo_anio);
                DB::table('vent_venta_detalle')->insert(
                    array('vent_venta_detalle_id' => IdGenerador::generaId(),
                        'vent_venta_detalle_precio_unitario' => $producto->vent_venta_detalle_precio_unitario,
                        'vent_venta_detalle_precio' => $producto->vent_venta_detalle_precio,
                        'vent_venta_detalle_cantidad' => $producto->vent_venta_detalle_cantidad,
                        'vent_venta_detalle_igv' => $producto->vent_venta_detalle_igv,
                        'vent_venta_detalle_tipo_operacion' => $producto->vent_venta_detalle_tipo_operacion,
                        'vent_venta_detalle_bi' => $producto->vent_venta_detalle_bi,
                        'vent_venta_detalle_producto_id' => $producto->vent_venta_detalle_producto_id,
                        'vent_venta_detalle_venta_id' => $vent_venta_id,
                        'vent_venta_detalle_descuento' => 0.00,
                        'vent_venta_detalle_precio_cobro' => $producto->vent_venta_detalle_precio,
                        'vent_venta_detalle_costo_almacen' => $stock,
                        'vent_venta_detalle_cuenta_venta' => 7011,
                        'vent_venta_detalle_item' => IdGenerador::generaNumeroItem($numero),
                    )
                );
                $numero = $numero + 1;
            }
            DB::table('vent_pago')->insert(
                array('vent_pago_id' => IdGenerador::generaId(),
                    'vent_pago_importe' => $precioCobradoTotal,
                    'vent_pago_fecha' => date('Y-m-d H:i:s', strtotime("now")),
                    'vent_pago_numero_pago' => IdGenerador::generaNumeroPago(),
                    'vent_pago_venta_id' => $vent_venta_id,
                    'vent_pago_tipo_pago' => 01,
                    'vent_pago_user_id' => $vent_venta_user_id,
                    'vent_pago_pago' => $precioCobradoTotal,
                    'vent_pago_vuelto' => 0.00,
                    'vent_pago_cliente_documento' => $vent_venta_cliente_numero_documento,
                    'vent_pago_modalidad' => 01,
                    'vent_pago_numero_transaccion' => "",
                    'vent_pago_almacen_id' => $vent_venta_almacen_id,
                    'vent_pago_periodo_id' => $comp_compra_periodo_id,

                )
            );
        } catch (\Exception $exception) {
            dd($exception);
        }

    }

}
