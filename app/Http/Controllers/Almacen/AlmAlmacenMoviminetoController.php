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
use App\Http\Data\util\GeneraNumero;
use App\Http\Data\util\IdGenerador;
use App\Http\Data\util\Pagination;
use App\Models\Almacen\Almacen;
use App\Models\Almacen\Compra;
use App\Models\Almacen\CompraDetalle;
use App\Models\Almacen\PagoProveedores;
use App\Models\Almacen\Producto;
use App\Models\Almacen\Proveedor;
use App\Models\Configuracion\Periodo;
use App\Models\Configuracion\TipoComprobante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;
use Validator;


class AlmAlmacenMoviminetoController extends Controller
{
    public function registrar_movimiento(Request $request)
    {
        $compra_data = new Compra();
        $comp_compra_id = IdGenerador::generaId();
        $compra_data->comp_compra_id = $comp_compra_id;
        $compra_data->comp_compra_total = $request->input('precioTotal');
        $compra_data->comp_compra_igv = $request->input('igvTotal');
        $compra_data->comp_compra_bi = $request->input('baseImpobleTotal');
        $compra_data->comp_compra_fecha = $request->input('fecha');
        $compra_data->comp_compra_tipo_venta = $request->input('tipoPago');
        $compra_data->comp_compra_confirmado = $request->input('comp_compra_confirmado');
        $compra_data->comp_compra_estado = 'REGISTRADO';
        $compra_data->comp_compra_numero_venta = $request->input('numeroVenta');
        $compra_data->comp_compra_tipo_comprobante_id = TipoComprobante::where('doc_tipo_comprobante_codigo', $request->input('tipoDocumento'))->first()->doc_tipo_comprobante_id;
        $compra_data->comp_compra_almacen_id = $request->input('almacen') == "" ? "PR15469593836888181" : $request->input('almacen');
        $compra_data->comp_compra_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
        $compra_data->comp_compra_preveedor_id = $request->input('proveedor');
        $compra_data->comp_compra_user_id = auth()->user()->id;
        $compra_data->comp_compra_fecha_registro = date('Y-m-d H:i:s', strtotime("now"));
        $compra_data->comp_compra_estado_pago = $request->input('importe') >= $request->input('importe_total') ? true : false;
        if ($request->input('tipoDocumento') == "10") {
            $compra_data->comp_compra_serie = "I001";
            $compra_data->comp_compra_tipo = 'I';
        } elseif ($request->input('tipoDocumento') == "11") {
            $compra_data->comp_compra_serie = "S001";
            $compra_data->comp_compra_tipo = 'S';
        } elseif ($request->input('tipoDocumento') == "12") {
            $compra_data->comp_compra_serie = "T001";
            $compra_data->comp_compra_tipo = 'T';
            $compra_data->comp_compra_almacen_destino = $request->input('comp_compra_almacen_destino');
        }
        $compra_data->comp_compra_numero_venta = GeneraNumero::genera_numero_movimiento($compra_data->comp_compra_serie);
        $compra_data->save();
        $contador = 1;
        foreach ($request->input('productos') as $item) {
            $compra_detalle = new CompraDetalle($item);
            $compra_detalle->comp_compra_compra_id = $comp_compra_id;
            $compra_detalle->comp_compra_detalle_id = IdGenerador::generaId();
            $compra_detalle->comp_compra_detalle_item = $contador;
            if ($request->input('tipoDocumento') == "10") {
                $compra_detalle->comp_compra_detalle_vendido = false;
            }
            if ($request->input('tipoDocumento') == "11") {

                CompraDetalle::where('comp_compra_detalle_producto_id', $compra_detalle->comp_compra_detalle_producto_id)
                    ->where('comp_compra_detalle_serie', $compra_detalle->comp_compra_detalle_serie)->update(array('comp_compra_detalle_vendido' => true));
            }
            if ($request->input('tipoDocumento') == "12") {
                $compra_detalle->comp_compra_detalle_vendido = false;
            }
            $contador = $contador + 1;
            $compra_detalle->save();


        }
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(
                Compra::select(
                    'alm_proveedor.alm_proveedor_razon_social',
                    'comp_compra.comp_compra_total',
                    'comp_compra.comp_compra_id',
                    'comp_compra.comp_compra_fecha',
                    'comp_compra.comp_compra_fecha_registro',
                    'comp_compra.comp_compra_estado_pago',
                    'comp_compra.comp_compra_serie',
                    'alm_almacen.alm_almacen_nombre',
                    'comp_compra.comp_compra_confirmado',
                    'comp_compra.comp_compra_numero_venta',
                    'comp_compra.comp_compra_fecha_registro'
                )->leftJoin('alm_proveedor', 'comp_compra.comp_compra_preveedor_id', 'alm_proveedor.alm_proveedor_id')
                    ->leftJoin('doc_tipo_comprabante', 'comp_compra.comp_compra_tipo_comprobante_id', 'doc_tipo_comprabante.doc_tipo_comprobante_id')
                    ->leftJoin('alm_almacen', 'comp_compra.comp_compra_almacen_id', 'alm_almacen.alm_almacen_id')
                    ->whereIn('comp_compra.comp_compra_tipo', ['I', 'S', 'T'])
                    ->orderBy('comp_compra.comp_compra_fecha_registro', 'DESC')
                    ->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Registro de compras'], 200);
    }

    public function listar_movimientos(Request $request)
    {
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(
                Compra::select(
                    'alm_proveedor.alm_proveedor_razon_social',
                    'comp_compra.comp_compra_total',
                    'comp_compra.comp_compra_id',
                    'comp_compra.comp_compra_fecha',
                    'comp_compra.comp_compra_fecha_registro',
                    'comp_compra.comp_compra_estado_pago',
                    'comp_compra.comp_compra_serie',
                    'alm_almacen.alm_almacen_nombre',
                    'comp_compra.comp_compra_confirmado',
                    'comp_compra.comp_compra_numero_venta',
                    'comp_compra.comp_compra_fecha_registro'
                )->leftJoin('alm_proveedor', 'comp_compra.comp_compra_preveedor_id', 'alm_proveedor.alm_proveedor_id')
                    ->leftJoin('doc_tipo_comprabante', 'comp_compra.comp_compra_tipo_comprobante_id', 'doc_tipo_comprabante.doc_tipo_comprobante_id')
                    ->leftJoin('alm_almacen', 'comp_compra.comp_compra_almacen_id', 'alm_almacen.alm_almacen_id')
                    ->whereIn('comp_compra.comp_compra_tipo', ['I', 'S', 'T'])
                    ->orderBy('comp_compra.comp_compra_fecha_registro', 'DESC')
                    ->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Registro de compras'], 200);
    }

    public function listar_movimiento($id)
    {
        $compra = Compra::find($id);
        $compra->proveedor = !is_null(Proveedor::find($compra->comp_compra_preveedor_id)) ? Proveedor::find($compra->comp_compra_preveedor_id)->alm_proveedor_razon_social : null;
        $compra->proveedor_numero = !is_null(Proveedor::find($compra->comp_compra_preveedor_id)) ? Proveedor::find($compra->comp_compra_preveedor_id)->alm_proveedor_ruc : null;
        $compra->almacen_destino = !is_null(Almacen::find($compra->comp_compra_almacen_destino)) ? Almacen::find($compra->comp_compra_almacen_destino)->alm_almacen_nombre : null;
        $compra->tipo_comprobante_codigo = TipoComprobante::find($compra->comp_compra_tipo_comprobante_id)->doc_tipo_comprobante_codigo;
        $compra->detalle = CompraDetalle::where('comp_compra_compra_id', $id)
            ->orderBy('comp_compra_detalle_item')
            ->get();
        $compra->almacen = Almacen::find($compra->comp_compra_almacen_id);
        return response()->json(['success' => true,
            'data' => $compra,
            'message' => 'Registro de compras'], 200);
    }


    public function editar_movimiento(Request $request, $id)
    {
        $compra_data = new Compra();
        $compra_data->comp_compra_total = $request->input('precioTotal');
        $compra_data->comp_compra_igv = $request->input('igvTotal');
        $compra_data->comp_compra_bi = $request->input('baseImpobleTotal');
        $compra_data->comp_compra_fecha = $request->input('fecha');
        $compra_data->comp_compra_tipo_venta = $request->input('tipoPago');
        $compra_data->comp_compra_confirmado = $request->input('comp_compra_confirmado');
        $compra_data->comp_compra_estado = 'REGISTRADO';
        $compra_data->comp_compra_tipo_comprobante_id = TipoComprobante::where('doc_tipo_comprobante_codigo', $request->input('tipoDocumento'))->first()->doc_tipo_comprobante_id;
        $compra_data->comp_compra_almacen_id = $request->input('almacen') == "" ? "PR15469593836888181" : $request->input('almacen');
        $compra_data->comp_compra_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
        $compra_data->comp_compra_preveedor_id = $request->input('proveedor');
        $compra_data->comp_compra_user_id = auth()->user()->id;
        $compra_data->comp_compra_fecha_registro = date('Y-m-d H:i:s', strtotime("now"));
        $compra_data->comp_compra_estado_pago = $request->input('importe') >= $request->input('importe_total') ? true : false;
        if ($request->input('tipoDocumento') == "10") {
            $compra_data->comp_compra_serie = "I001";
            $compra_data->comp_compra_tipo = 'I';
            $compra_data->comp_compra_almacen_destino = null;
        } elseif ($request->input('tipoDocumento') == "11") {
            $compra_data->comp_compra_serie = "S001";
            $compra_data->comp_compra_tipo = 'S';
            $compra_data->comp_compra_almacen_destino = null;
        } elseif ($request->input('tipoDocumento') == "12") {
            $compra_data->comp_compra_serie = "T001";
            $compra_data->comp_compra_tipo = 'T';
            $compra_data->comp_compra_almacen_destino = $request->input('comp_compra_almacen_destino');
        }
        $compra_data->comp_compra_numero_venta = GeneraNumero::genera_numero_movimiento($compra_data->comp_compra_serie);
        Compra::find($id)->update($compra_data->toArray());
        CompraDetalle::where('comp_compra_compra_id', $id)->delete();
        $contador = 1;
        foreach ($request->input('productos') as $item) {
            $compra_detalle = new CompraDetalle($item);
            $compra_detalle->comp_compra_compra_id = $id;
            $compra_detalle->comp_compra_detalle_id = IdGenerador::generaId();
            $compra_detalle->comp_compra_detalle_item = $contador;
            if ($request->input('tipoDocumento') == "10") {
                $compra_detalle->comp_compra_detalle_vendido = false;
            }

            if ($request->input('tipoDocumento') == "11") {
                CompraDetalle::where('comp_compra_detalle_producto_id', $compra_detalle->comp_compra_detalle_producto_id)
                    ->where('comp_compra_detalle_serie', $compra_detalle->comp_compra_detalle_serie)->update(array('comp_compra_detalle_vendido' => true));
            }
            if ($request->input('tipoDocumento') == "12") {
                $compra_detalle->comp_compra_detalle_vendido = false;
            }
            $contador = $contador + 1;
            $compra_detalle->save();
        }
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(
                Compra::select(
                    'alm_proveedor.alm_proveedor_razon_social',
                    'comp_compra.comp_compra_total',
                    'comp_compra.comp_compra_id',
                    'comp_compra.comp_compra_fecha',
                    'comp_compra.comp_compra_fecha_registro',
                    'comp_compra.comp_compra_estado_pago',
                    'comp_compra.comp_compra_serie',
                    'alm_almacen.alm_almacen_nombre',
                    'comp_compra.comp_compra_confirmado',
                    'comp_compra.comp_compra_numero_venta',
                    'comp_compra.comp_compra_fecha_registro'
                )->leftJoin('alm_proveedor', 'comp_compra.comp_compra_preveedor_id', 'alm_proveedor.alm_proveedor_id')
                    ->leftJoin('doc_tipo_comprabante', 'comp_compra.comp_compra_tipo_comprobante_id', 'doc_tipo_comprabante.doc_tipo_comprobante_id')
                    ->leftJoin('alm_almacen', 'comp_compra.comp_compra_almacen_id', 'alm_almacen.alm_almacen_id')
                    ->whereIn('comp_compra.comp_compra_tipo', ['I', 'S', 'T'])
                    ->orderBy('comp_compra.comp_compra_fecha_registro', 'DESC')
                    ->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Registro de compras'], 200);
    }


    public function importar_ingreso(Request $request)
    {
        if ($request->hasFile('importar_archivo')) {
            Excel::load($request->file('importar_archivo')->getRealPath(), function ($reader) {
                $compra = new Compra();
                $comp_compra_id = IdGenerador::generaId();
                $compra->comp_compra_id = $comp_compra_id;
                $compra->comp_compra_estado_pago = false;
                $compra->comp_compra_serie = "I001";
                $compra->comp_compra_tipo = 'I';
                $compra->comp_compra_confirmado = false;
                $compra->comp_compra_fecha = date('Y-m-d');
                $compra->comp_compra_fecha_registro = date('Y-m-d H:i:s', strtotime("now"));
                $compra->comp_compra_tipo_comprobante_id = TipoComprobante::where('doc_tipo_comprobante_codigo', '10')->first()->doc_tipo_comprobante_id;
                $compra->comp_compra_numero_venta = GeneraNumero::genera_numero_movimiento($compra->comp_compra_serie);
                $compra->save();
                $contador = 1;
                foreach ($reader->toArray() as $key => $row) {
                    $compra_detalle = new CompraDetalle();
                    $compra_detalle->comp_compra_detalle_id = IdGenerador::generaId();
                    $compra_detalle->comp_compra_detalle_precio = $row['precio'];
                    $compra_detalle->comp_compra_detalle_precio_unitario = $row['preciounitario'];
                    $compra_detalle->comp_compra_detalle_cantidad = $row['cantidad'];
                    $compra_detalle->comp_compra_detalle_igv = $row['igv'];
                    $compra_detalle->comp_compra_detalle_bi = $row['baseimponible'];
                    $compra_detalle->comp_compra_compra_id = $comp_compra_id;
                    if (!is_null($compra_detalle->comp_compra_detalle_precio)) {
                        $producto = Producto::where('alm_producto_codigo', $row['codproducto'])->first();
                        $compra_detalle->comp_compra_detalle_producto_id = $producto->alm_producto_id;
                        $compra_detalle->comp_compra_detalle_tipo_operacion = $row['tipoafectacion'];
                        $compra_detalle->comp_compra_detalle_cuenta_compra = $row['cuentacompra'];
                        $compra_detalle->comp_compra_detalle_producto = Producto::find($producto->Parent_alm_producto_id)->alm_producto_nombre . ' ' . $producto->alm_producto_nombre . ' ' . $producto->alm_producto_marca;
                        $compra_detalle->comp_compra_detalle_serie = $row['serie'];
                        $compra_detalle->comp_compra_detalle_vendido = $compra_detalle->comp_compra_detalle_serie != null || $compra_detalle->comp_compra_detalle_serie != "" ? false : true;
                        $compra_detalle->comp_compra_detalle_serie_estado = $compra_detalle->comp_compra_detalle_serie != null || $compra_detalle->comp_compra_detalle_serie != "" ? true : false;
                        $compra_detalle->comp_compra_detalle_item = $contador;
                        $contador = $contador + 1;

                        $compra_detalle->save();
                    }

                }
            });
        }
        return response()->json(['success' => true,
            'data' => 'Ok',
            'message' => 'Registro de compras'], 200);
    }


    public function eliminar_movimineto(Request $request, $id)
    {
        CompraDetalle::where('comp_compra_compra_id', $id)->delete();
        PagoProveedores::where('comp_pago_proveedores_id_compra', $id)->delete();
        Compra::find($id)->delete();
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(
                Compra::select(
                    'alm_proveedor.alm_proveedor_razon_social',
                    'comp_compra.comp_compra_total',
                    'comp_compra.comp_compra_id',
                    'comp_compra.comp_compra_fecha',
                    'comp_compra.comp_compra_fecha_registro',
                    'comp_compra.comp_compra_estado_pago',
                    'comp_compra.comp_compra_serie',
                    'alm_almacen.alm_almacen_nombre',
                    'comp_compra.comp_compra_confirmado',
                    'comp_compra.comp_compra_numero_venta',
                    'comp_compra.comp_compra_fecha_registro'
                )->leftJoin('alm_proveedor', 'comp_compra.comp_compra_preveedor_id', 'alm_proveedor.alm_proveedor_id')
                    ->leftJoin('doc_tipo_comprabante', 'comp_compra.comp_compra_tipo_comprobante_id', 'doc_tipo_comprabante.doc_tipo_comprobante_id')
                    ->leftJoin('alm_almacen', 'comp_compra.comp_compra_almacen_id', 'alm_almacen.alm_almacen_id')
                    ->whereIn('comp_compra.comp_compra_tipo', ['I', 'S', 'T'])
                    ->orderBy('comp_compra.comp_compra_fecha_registro', 'DESC')
                    ->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Registro de compras'], 200);
    }


}
