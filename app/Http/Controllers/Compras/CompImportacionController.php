<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 13/11/18
 * Time: 09:26 AM
 */

namespace App\Http\Controllers;

namespace App\Http\Controllers\Compras;


use App\Http\Data\util\GeneraNumero;
use App\Http\Data\util\IdGenerador;
use App\Http\Data\util\Pagination;
use App\Models\Almacen\Almacen;
use App\Models\Almacen\Compra;
use App\Models\Almacen\CompraDetalle;
use App\Models\Almacen\ListaPrecio;
use App\Models\Almacen\ListaPrecioDetalle;
use App\Models\Almacen\PagoProveedores;
use App\Models\Almacen\Producto;
use App\Models\Almacen\Proveedor;
use App\Models\Almacen\UnidadMedida;
use App\Models\Configuracion\Empresa;
use App\Models\Configuracion\Periodo;
use App\Models\Configuracion\TipoComprobante;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Maatwebsite\Excel\Facades\Excel;
use Validator;


class CompImportacionController extends Controller
{
    public function registrar_compra(Request $request)
    {
        $compra_data = new Compra();
        $comp_compra_id = IdGenerador::generaId();
        $compra_data->comp_compra_id = $comp_compra_id;
        $compra_data->comp_compra_total = $request->input('precioTotal');
        $compra_data->comp_compra_igv = $request->input('igvTotal');
        $compra_data->comp_compra_bi = $request->input('baseImpobleTotal');
        $compra_data->comp_compra_fecha = $request->input('fecha');
        $compra_data->comp_compra_tipo_venta = $request->input('tipoPago');
        $compra_data->comp_compra_serie = $request->input('serie');
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
        $compra_data->comp_compra_tipo = 'C';
        $compra_data->comp_compra_tipo_cambio = $request->input('comp_compra_tipo_cambio');
        $compra_data->comp_compra_flete = $request->input('comp_compra_flete');
        $compra_data->comp_compra_precio_dolar = $request->input('comp_compra_precio_dolar');
        $compra_data->comp_compra_porcentaje_utilidad = $request->input('comp_compra_porcentaje_utilidad');
        $compra_data->save();
        $contador = 1;
        foreach ($request->input('productos') as $item) {
            $compra_detalle = new CompraDetalle($item);
            if ($request->input('comp_compra_confirmado')) {
                $empresa = Empresa::all()->first();
                if (!$empresa->emp_empresa_calculo_total) {
                    $compra_detalle->comp_compra_detalle_precio_unitario = $compra_detalle->comp_compra_detalle_precio_unitario / ((Periodo::select('cont_periodo_igv')->where('cont_periodo_estado', true)->first()->cont_periodo_igv / 100) + 1);
                }
                ListaPrecioDetalle::where('alm_lista_precio_detalle_articulo_id', $compra_detalle->comp_compra_detalle_producto_id)->first();
                if (ListaPrecioDetalle::where('alm_lista_precio_detalle_articulo_id', $compra_detalle->comp_compra_detalle_producto_id)->first()) {
                    ListaPrecioDetalle::where('alm_lista_precio_detalle_articulo_id', $compra_detalle->comp_compra_detalle_producto_id)
                        ->where('alm_lista_precio_detalle_lista_precio_id', ListaPrecio::all()->first()->alm_lista_precio_id)->update(array('alm_lista_precio_detalle_precio' => $compra_detalle->comp_compra_detalle_precio_unitario));
                } else {
                    $listaPrecioDetalle = new  ListaPrecioDetalle();
                    $listaPrecioDetalle->alm_lista_precio_detalle_id = IdGenerador::generaId();
                    $listaPrecioDetalle->alm_lista_precio_detalle_lista_precio_id = ListaPrecio::all()->first()->alm_lista_precio_id;
                    $listaPrecioDetalle->alm_lista_precio_detalle_articulo_id = $compra_detalle->comp_compra_detalle_producto_id;
                    $listaPrecioDetalle->alm_lista_precio_detalle_precio = $compra_detalle->comp_compra_detalle_precio_unitario;
                    $listaPrecioDetalle->save();
                }
            }

            $compra_detalle->comp_compra_compra_id = $comp_compra_id;
            $compra_detalle->comp_compra_detalle_id = IdGenerador::generaId();
            $compra_detalle->comp_compra_detalle_vendido = false;
            $compra_detalle->comp_compra_detalle_item = $contador;
            $compra_detalle->comp_compra_detalle_fecha_registro = date('Y-m-d H:i:s', strtotime("now"));
            $compra_detalle->save();
            $contador = $contador + 1;

        }
        if (!is_null($request->input('importe')) && $request->input('importe') != "" && $request->input('importe') > 0) {
            $pago_proveedor = new PagoProveedores();
            $pago_proveedor->comp_pago_proveedores_id = IdGenerador::generaId();
            $pago_proveedor->comp_pago_proveedores_importe = $request->input('importe');
            $pago_proveedor->comp_pago_proveedores_fecha = date('Y-m-d');
            $pago_proveedor->comp_pago_proveedores_serie = 'PP01';
            $pago_proveedor->comp_pago_proveedores_numero_pago = GeneraNumero::genera_numero_pago_proveedor('PP01');
            $pago_proveedor->comp_pago_proveedores_tipo_pago = $request->input('tipoPago');
            $pago_proveedor->comp_pago_proveedores_id_compra = $comp_compra_id;
            $pago_proveedor->comp_pago_proveedores_id_user = auth()->user()->id;
            $pago_proveedor->comp_pago_proveedores_vuelto = $request->input('vuelto');
            $pago_proveedor->comp_pago_proveedores_pago = $request->input('importe');
            $pago_proveedor->comp_pago_proveedores_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
            $pago_proveedor->save();
        }

        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Compra::select(
                'alm_proveedor.alm_proveedor_razon_social',
                'comp_compra.comp_compra_total',
                'comp_compra.comp_compra_fecha',
                'comp_compra.comp_compra_id',
                'comp_compra.comp_compra_fecha_registro',
                'comp_compra.comp_compra_estado_pago',
                'comp_compra.comp_compra_serie',
                'alm_almacen.alm_almacen_nombre',
                'comp_compra.comp_compra_confirmado',
                'comp_compra.comp_compra_numero_venta',
                'pag_tipo_pago.pago_tipo_pago_nombre')
                ->leftJoin('alm_proveedor', 'comp_compra.comp_compra_preveedor_id', 'alm_proveedor.alm_proveedor_id')
                ->leftJoin('alm_almacen', 'comp_compra.comp_compra_almacen_id', 'alm_almacen.alm_almacen_id')
                ->leftJoin('pag_tipo_pago', 'comp_compra.comp_compra_tipo_venta', 'pag_tipo_pago.pago_tipo_pago_id')
                ->where('comp_compra.comp_compra_tipo', 'C')
                ->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Registro de compras'], 200);
    }

    public function listar_compras(Request $request)
    {
        $condicional_tipo_comprobante = !is_null($request->input('comp_compra_tipo_comprobante_id')) ? '=' : '<>';
        $condicional_serie = !is_null($request->input('comp_compra_serie')) ? '=' : '<>';
        $condicional_numero = !is_null($request->input('comp_compra_numero_venta')) ? '=' : '<>';
        if (!is_null($request->input('comp_compra_fecha_desde')) &&
            !is_null($request->input('comp_compra_fecha_hasta'))) {
            $condicional_fecha = [$request->input('comp_compra_fecha_desde'), $request->input('comp_compra_fecha_hasta')];
        } else {
            $condicional_fecha = [date('Y-m-d'), date('Y-m-d')];
        }
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Compra::select(
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
                'comp_compra.comp_compra_fecha_registro',
                'pag_tipo_pago.pago_tipo_pago_nombre')
                ->leftJoin('alm_proveedor', 'comp_compra.comp_compra_preveedor_id', 'alm_proveedor.alm_proveedor_id')
                ->leftJoin('alm_almacen', 'comp_compra.comp_compra_almacen_id', 'alm_almacen.alm_almacen_id')
                ->leftJoin('pag_tipo_pago', 'comp_compra.comp_compra_tipo_venta', 'pag_tipo_pago.pago_tipo_pago_id')
                ->where('comp_compra.comp_compra_tipo', 'C')
                ->where('comp_compra.comp_compra_serie', $condicional_serie, is_null($request->input('comp_compra_serie')) ? null : $request->input('comp_compra_serie'))
                ->where('comp_compra.comp_compra_numero_venta', $condicional_numero, is_null($request->input('comp_compra_numero_venta')) ? null : $request->input('comp_compra_numero_venta'))
                ->where('comp_compra.comp_compra_tipo_comprobante_id', $condicional_tipo_comprobante, is_null($request->input('comp_compra_tipo_comprobante_id')) ? null : TipoComprobante::select('doc_tipo_comprobante_id')->where('doc_tipo_comprobante_codigo', $request->input('comp_compra_tipo_comprobante_id'))->first()->doc_tipo_comprobante_id)
                ->whereBetween('comp_compra.comp_compra_fecha', $condicional_fecha)
                ->orderBy('comp_compra.comp_compra_fecha_registro', 'DESC')
                ->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Registro de compras'], 200);

    }

    public function listar_compra($id)
    {
        $compra = Compra::find($id);
        $compra->proveedor = !is_null(Proveedor::find($compra->comp_compra_preveedor_id)) ? Proveedor::find($compra->comp_compra_preveedor_id)->alm_proveedor_razon_social : null;
        $compra->proveedor_numero = !is_null(Proveedor::find($compra->comp_compra_preveedor_id)) ? Proveedor::find($compra->comp_compra_preveedor_id)->alm_proveedor_ruc : null;

        $compra->tipo_comprobante_codigo = TipoComprobante::find($compra->comp_compra_tipo_comprobante_id)->doc_tipo_comprobante_codigo;
        $compra->almacen = Almacen::find($compra->comp_compra_almacen_id);
        $lista = [];

        foreach (CompraDetalle::where('comp_compra_compra_id', $id)
                     ->orderBy('comp_compra_detalle_item')
                     ->get() as $detalle) {
            $producto = Producto::find($detalle->comp_compra_detalle_producto_id);
            $detalle->producto_codigo = substr($producto->alm_producto_codigo, 0, 10);
            $detalle->detalle_articulo_um_imp = UnidadMedida::find($producto->alm_unidad_medida_id)->alm_unidad_medida_simbolo_impresion;
            array_push($lista, $detalle);
        }
        $compra->detalle = $lista;
        $pago = new  \stdClass();
        $pago->comp_pago_proveedores_tipo_pago = '02';
        $compra->pago_proveedores = !is_null(PagoProveedores::where('comp_pago_proveedores_id_compra', $id)->first()) ? PagoProveedores::where('comp_pago_proveedores_id_compra', $id)->first() : $pago;
        return response()->json(['success' => true,
            'data' => $compra,
            'message' => 'Registro de compras'], 200);
    }


    public function editar_compra(Request $request, $id)
    {
        $compra_data = new Compra();
        $compra_data->comp_compra_total = $request->input('precioTotal');
        $compra_data->comp_compra_igv = $request->input('igvTotal');
        $compra_data->comp_compra_bi = $request->input('baseImpobleTotal');
        $compra_data->comp_compra_fecha = $request->input('fecha');
        $compra_data->comp_compra_tipo_venta = $request->input('tipoPago');
        $compra_data->comp_compra_confirmado = $request->input('comp_compra_confirmado');
        $compra_data->comp_compra_serie = $request->input('serie');
        $compra_data->comp_compra_estado = 'REGISTRADO';
        $compra_data->comp_compra_numero_venta = $request->input('numeroVenta');
        $compra_data->comp_compra_tipo_comprobante_id = TipoComprobante::where('doc_tipo_comprobante_codigo', $request->input('tipoDocumento'))->first()->doc_tipo_comprobante_id;
        $compra_data->comp_compra_almacen_id = $request->input('almacen') == "" ? "PR15469593836888181" : $request->input('almacen');
        $compra_data->comp_compra_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
        $compra_data->comp_compra_preveedor_id = $request->input('proveedor');
        $compra_data->comp_compra_user_id = auth()->user()->id;
        $compra_data->comp_compra_fecha_registro = date('Y-m-d H:i:s', strtotime("now"));
        $compra_data->comp_compra_estado_pago = $request->input('importe') >= $request->input('importe_total') ? true : false;
        $compra_data->comp_compra_tipo = 'C';
        $compra_data->comp_compra_tipo_cambio = $request->input('comp_compra_tipo_cambio');
        $compra_data->comp_compra_flete = $request->input('comp_compra_flete');
        $compra_data->comp_compra_precio_dolar = $request->input('comp_compra_precio_dolar');
        $compra_data->comp_compra_porcentaje_utilidad = $request->input('comp_compra_porcentaje_utilidad');
        Compra::find($id)->update($compra_data->toArray());
        CompraDetalle::where('comp_compra_compra_id', $id)->delete();
        $contador = 1;
        foreach ($request->input('productos') as $item) {
            $compra_detalle = new CompraDetalle($item);
            if ($request->input('comp_compra_confirmado')) {
                $empresa = Empresa::all()->first();
                if (!$empresa->emp_empresa_calculo_total) {
                    $compra_detalle->comp_compra_detalle_precio_unitario = $compra_detalle->comp_compra_detalle_precio_unitario / ((Periodo::select('cont_periodo_igv')->where('cont_periodo_estado', true)->first()->cont_periodo_igv / 100) + 1);
                }
                ListaPrecioDetalle::where('alm_lista_precio_detalle_articulo_id', $compra_detalle->comp_compra_detalle_producto_id)->first();
                if (ListaPrecioDetalle::where('alm_lista_precio_detalle_articulo_id', $compra_detalle->comp_compra_detalle_producto_id)->first()) {
                    ListaPrecioDetalle::where('alm_lista_precio_detalle_articulo_id', $compra_detalle->comp_compra_detalle_producto_id)
                        ->where('alm_lista_precio_detalle_lista_precio_id', ListaPrecio::all()->first()->alm_lista_precio_id)->update(array('alm_lista_precio_detalle_precio' => $compra_detalle->comp_compra_detalle_precio_unitario));
                } else {
                    $listaPrecioDetalle = new  ListaPrecioDetalle();
                    $listaPrecioDetalle->alm_lista_precio_detalle_id = IdGenerador::generaId();
                    $listaPrecioDetalle->alm_lista_precio_detalle_lista_precio_id = ListaPrecio::all()->first()->alm_lista_precio_id;
                    $listaPrecioDetalle->alm_lista_precio_detalle_articulo_id = $compra_detalle->comp_compra_detalle_producto_id;
                    $listaPrecioDetalle->alm_lista_precio_detalle_precio = $compra_detalle->comp_compra_detalle_precio_unitario;
                    $listaPrecioDetalle->save();
                }
            }
            $compra_detalle->comp_compra_compra_id = $id;
            $compra_detalle->comp_compra_detalle_id = IdGenerador::generaId();
            $compra_detalle->comp_compra_detalle_vendido = false;
            $compra_detalle->comp_compra_detalle_item = $contador;
            $compra_detalle->comp_compra_detalle_fecha_registro = date('Y-m-d H:i:s', strtotime("now"));
            $compra_detalle->save();
            $contador = $contador + 1;

        }

        if (!is_null($request->input('importe')) && $request->input('importe') != "" && $request->input('importe') > 0) {
            PagoProveedores::where('comp_pago_proveedores_id_compra', $id)->delete();
            $pago_proveedor = new PagoProveedores();
            $pago_proveedor->comp_pago_proveedores_id = IdGenerador::generaId();
            $pago_proveedor->comp_pago_proveedores_importe = $request->input('importe');
            $pago_proveedor->comp_pago_proveedores_fecha = date('Y-m-d');
            $pago_proveedor->comp_pago_proveedores_serie = 'PP01';
            $pago_proveedor->comp_pago_proveedores_numero_pago = GeneraNumero::genera_numero_pago_proveedor('PP01');
            $pago_proveedor->comp_pago_proveedores_tipo_pago = $request->input('tipoPago');
            $pago_proveedor->comp_pago_proveedores_id_compra = $id;
            $pago_proveedor->comp_pago_proveedores_id_user = auth()->user()->id;
            $pago_proveedor->comp_pago_proveedores_vuelto = $request->input('vuelto');
            $pago_proveedor->comp_pago_proveedores_pago = $request->input('importe');
            $pago_proveedor->comp_pago_proveedores_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
            $pago_proveedor->save();
        }
        $condicional_tipo_comprobante = !is_null($request->input('comp_compra_tipo_comprobante_id')) ? '=' : '<>';
        $condicional_serie = !is_null($request->input('comp_compra_serie')) ? '=' : '<>';
        $condicional_numero = !is_null($request->input('comp_compra_numero_venta')) ? '=' : '<>';
        if (!is_null($request->input('comp_compra_fecha_desde')) &&
            !is_null($request->input('comp_compra_fecha_hasta'))) {
            $condicional_fecha = [$request->input('comp_compra_fecha_desde'), $request->input('comp_compra_fecha_hasta')];
        } else {
            $condicional_fecha = [date('Y-m-d'), date('Y-m-d')];
        }
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Compra::select(
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
                'pag_tipo_pago.pago_tipo_pago_nombre',
                'comp_compra.comp_compra_fecha_registro')
                ->leftJoin('alm_proveedor', 'comp_compra.comp_compra_preveedor_id', 'alm_proveedor.alm_proveedor_id')
                ->leftJoin('pag_tipo_pago', 'comp_compra.comp_compra_tipo_venta', 'pag_tipo_pago.pago_tipo_pago_id')
                ->leftJoin('alm_almacen', 'comp_compra.comp_compra_almacen_id', 'alm_almacen.alm_almacen_id')
                ->where('comp_compra.comp_compra_tipo', 'C')
                ->where('comp_compra.comp_compra_serie', $condicional_serie, is_null($request->input('comp_compra_serie')) ? null : $request->input('comp_compra_serie'))
                ->where('comp_compra.comp_compra_numero_venta', $condicional_numero, is_null($request->input('comp_compra_numero_venta')) ? null : $request->input('comp_compra_numero_venta'))
                ->where('comp_compra.comp_compra_tipo_comprobante_id', $condicional_tipo_comprobante, is_null($request->input('comp_compra_tipo_comprobante_id')) ? null : TipoComprobante::select('doc_tipo_comprobante_id')->where('doc_tipo_comprobante_codigo', $request->input('comp_compra_tipo_comprobante_id'))->first()->doc_tipo_comprobante_id)
                ->whereBetween('comp_compra.comp_compra_fecha', $condicional_fecha)
                ->orderBy('comp_compra.comp_compra_fecha_registro', 'DESC')
                ->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Registro de compras'], 200);
    }

    public function eliminar_compra(Request $request, $id)
    {
        CompraDetalle::where('comp_compra_compra_id', $id)->delete();
        PagoProveedores::where('comp_pago_proveedores_id_compra', $id)->delete();
        Compra::find($id)->delete();
        $condicional_tipo_comprobante = !is_null($request->input('comp_compra_tipo_comprobante_id')) ? '=' : '<>';
        $condicional_serie = !is_null($request->input('comp_compra_serie')) ? '=' : '<>';
        $condicional_numero = !is_null($request->input('comp_compra_numero_venta')) ? '=' : '<>';
        if (!is_null($request->input('comp_compra_fecha_desde')) &&
            !is_null($request->input('comp_compra_fecha_hasta'))) {
            $condicional_fecha = [$request->input('comp_compra_fecha_desde'), $request->input('comp_compra_fecha_hasta')];
        } else {
            $condicional_fecha = [date('Y-m-d'), date('Y-m-d')];
        }
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Compra::select(
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
                'pag_tipo_pago.pago_tipo_pago_nombre')
                ->leftJoin('alm_proveedor', 'comp_compra.comp_compra_preveedor_id', 'alm_proveedor.alm_proveedor_id')
                ->leftJoin('pag_tipo_pago', 'comp_compra.comp_compra_tipo_venta', 'pag_tipo_pago.pago_tipo_pago_id')
                ->leftJoin('alm_almacen', 'comp_compra.comp_compra_almacen_id', 'alm_almacen.alm_almacen_id')
                ->where('comp_compra.comp_compra_tipo', 'C')
                ->where('comp_compra.comp_compra_serie', $condicional_serie, is_null($request->input('comp_compra_serie')) ? null : $request->input('comp_compra_serie'))
                ->where('comp_compra.comp_compra_numero_venta', $condicional_numero, is_null($request->input('comp_compra_numero_venta')) ? null : $request->input('comp_compra_numero_venta'))
                ->where('comp_compra.comp_compra_tipo_comprobante_id', $condicional_tipo_comprobante, is_null($request->input('comp_compra_tipo_comprobante_id')) ? null : TipoComprobante::select('doc_tipo_comprobante_id')->where('doc_tipo_comprobante_codigo', $request->input('comp_compra_tipo_comprobante_id'))->first()->doc_tipo_comprobante_id)
                ->whereBetween('comp_compra.comp_compra_fecha', $condicional_fecha)
                ->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Registro de compras'], 200);

    }


    public function importar_compra(Request $request)
    {
        if ($request->hasFile('importar_archivo')) {
            Excel::load($request->file('importar_archivo')->getRealPath(), function ($reader) {
                $compra = new Compra();
                $comp_compra_id = IdGenerador::generaId();
                $compra->comp_compra_id = $comp_compra_id;
                $compra->comp_compra_estado_pago = false;
                $compra->comp_compra_tipo = "C";
                $compra->comp_compra_confirmado = false;
                $compra->comp_compra_fecha = date('Y-m-d');
                $compra->comp_compra_fecha_registro = date('Y-m-d H:i:s', strtotime("now"));
                $compra->comp_compra_tipo_comprobante_id = TipoComprobante::where('doc_tipo_comprobante_codigo', '03')->first()->doc_tipo_comprobante_id;
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
                        $compra_detalle->comp_compra_detalle_fecha_registro = date('Y-m-d H:i:s', strtotime("now"));
                        $compra_detalle->comp_compra_detalle_vendido = $compra_detalle->comp_compra_detalle_serie != null || $compra_detalle->comp_compra_detalle_serie != "" ? false : true;
                        $compra_detalle->comp_compra_detalle_serie_estado = $compra_detalle->comp_compra_detalle_serie != null || $compra_detalle->comp_compra_detalle_serie != "" ? true : false;
                        $compra_detalle->comp_compra_detalle_item = $contador;
                        $contador = $contador + 1;
                        $compra_detalle->save();
                    }

                }
            });
        }
        $condicional_tipo_comprobante = !is_null($request->input('comp_compra_tipo_comprobante_id')) ? '=' : '<>';
        $condicional_serie = !is_null($request->input('comp_compra_serie')) ? '=' : '<>';
        $condicional_numero = !is_null($request->input('comp_compra_numero_venta')) ? '=' : '<>';
        if (!is_null($request->input('comp_compra_fecha_desde')) &&
            !is_null($request->input('comp_compra_fecha_hasta'))) {
            $condicional_fecha = [$request->input('comp_compra_fecha_desde'), $request->input('comp_compra_fecha_hasta')];
        } else {
            $condicional_fecha = [date('Y-m-d'), date('Y-m-d')];
        }
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Compra::select(
                'alm_proveedor.alm_proveedor_razon_social',
                'comp_compra.comp_compra_total',
                'comp_compra.comp_compra_id',
                'comp_compra.comp_compra_fecha',
                'comp_compra.comp_compra_fecha_registro',
                'comp_compra.comp_compra_estado_pago',
                'comp_compra.comp_compra_serie',
                'comp_compra.comp_compra_numero_venta',
                'pag_tipo_pago.pago_tipo_pago_nombre')
                ->leftJoin('alm_proveedor', 'comp_compra.comp_compra_preveedor_id', 'alm_proveedor.alm_proveedor_id')
                ->leftJoin('pag_tipo_pago', 'comp_compra.comp_compra_tipo_venta', 'pag_tipo_pago.pago_tipo_pago_id')
                ->where('comp_compra.comp_compra_tipo', 'C')
                ->where('comp_compra.comp_compra_serie', $condicional_serie, is_null($request->input('comp_compra_serie')) ? null : $request->input('comp_compra_serie'))
                ->where('comp_compra.comp_compra_numero_venta', $condicional_numero, is_null($request->input('comp_compra_numero_venta')) ? null : $request->input('comp_compra_numero_venta'))
                ->where('comp_compra.comp_compra_tipo_comprobante_id', $condicional_tipo_comprobante, is_null($request->input('comp_compra_tipo_comprobante_id')) ? null : TipoComprobante::select('doc_tipo_comprobante_id')->where('doc_tipo_comprobante_codigo', $request->input('comp_compra_tipo_comprobante_id'))->first()->doc_tipo_comprobante_id)
                ->whereBetween('comp_compra.comp_compra_fecha', $condicional_fecha)
                ->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Registro de compras'], 200);
    }


    public function reporte_compras(Request $request)
    {
        $condicional_tipo_comprobante = !is_null($request->input('comp_compra_tipo_comprobante_id')) ? '=' : '<>';
        $condicional_serie = !is_null($request->input('comp_compra_serie')) ? '=' : '<>';
        $condicional_numero = !is_null($request->input('comp_compra_numero_venta')) ? '=' : '<>';
        if (!is_null($request->input('comp_compra_fecha_desde')) &&
            !is_null($request->input('comp_compra_fecha_hasta'))) {
            $condicional_fecha = [$request->input('comp_compra_fecha_desde'), $request->input('comp_compra_fecha_hasta')];
        } else {
            $condicional_fecha = [date('Y-m-d'), date('Y-m-d')];
        }
        return response()->json(['success' => true,
            'data' => Compra::select(
                'alm_proveedor.alm_proveedor_razon_social',
                'alm_proveedor.alm_porveedor_tipo_doc_ident',
                'alm_proveedor.alm_proveedor_ruc',
                'comp_compra.comp_compra_total',
                'comp_compra.comp_compra_igv',
                'comp_compra.comp_compra_bi',
                'comp_compra.comp_compra_id',
                'comp_compra.comp_compra_fecha',
                'comp_compra.comp_compra_fecha_registro',
                'comp_compra.comp_compra_estado_pago',
                'comp_compra.comp_compra_serie',
                'comp_compra.comp_compra_numero_venta',
                'doc_tipo_comprabante.doc_tipo_comprobante_codigo',
                'pag_tipo_pago.pago_tipo_pago_nombre')
                ->leftJoin('alm_proveedor', 'comp_compra.comp_compra_preveedor_id', 'alm_proveedor.alm_proveedor_id')
                ->leftJoin('pag_tipo_pago', 'comp_compra.comp_compra_tipo_venta', 'pag_tipo_pago.pago_tipo_pago_id')
                ->leftJoin('doc_tipo_comprabante', 'comp_compra.comp_compra_tipo_comprobante_id', 'doc_tipo_comprabante.doc_tipo_comprobante_id')
                ->where('comp_compra.comp_compra_tipo', 'C')
                ->where('comp_compra.comp_compra_serie', $condicional_serie, is_null($request->input('comp_compra_serie')) ? null : $request->input('comp_compra_serie'))
                ->where('comp_compra.comp_compra_numero_venta', $condicional_numero, is_null($request->input('comp_compra_numero_venta')) ? null : $request->input('comp_compra_numero_venta'))
                ->where('comp_compra.comp_compra_tipo_comprobante_id', $condicional_tipo_comprobante, is_null($request->input('comp_compra_tipo_comprobante_id')) ? null : TipoComprobante::select('doc_tipo_comprobante_id')->where('doc_tipo_comprobante_codigo', $request->input('comp_compra_tipo_comprobante_id'))->first()->doc_tipo_comprobante_id)
                ->whereBetween('comp_compra.comp_compra_fecha', $condicional_fecha)
                ->get(),
            'message' => 'Registro de compras'], 200);
    }
}
