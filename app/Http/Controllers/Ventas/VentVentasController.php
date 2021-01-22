<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 14/11/18
 * Time: 06:10 PM
 */

namespace App\Http\Controllers;

namespace App\Http\Controllers\Ventas;

use App\Exports\VentasExport;


use App\Http\Data\util\GeneraNumero;
use App\Http\Data\util\GeneraSerie;
use App\Http\Data\util\IdGenerador;
use App\Http\Data\util\Pagination;
use App\Models\Almacen\Almacen;
use App\Models\Almacen\CompraDetalle;
use App\Models\Almacen\Distribuidor;
use App\Models\Almacen\ListaPrecioDetalle;
use App\Models\Almacen\Producto;
use App\Models\Configuracion\Empresa;
use App\Models\Configuracion\Periodo;
use App\Models\Configuracion\TipoComprobante;
use App\Models\Venta\CajaMovimiento;
use App\Models\Venta\Cliente;
use App\Models\Venta\FechaPago;
use App\Models\Venta\Venta;
use App\Models\Venta\VentaBaja;
use App\Models\Venta\VentaDetalle;
use App\Models\Venta\VentaPago;
use App\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Http\Data\Ventas\Ventas;

use App\Http\Controllers\FacturacionElectronica\Controllers\procesar_data;
use PDF;
use App\Mail\ComprobanteMail;
use Illuminate\Support\Facades\Mail;
use DOMPDF;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Data\Setup\Persona;
use App\Http\Data\Almacen\Proveedor;
use ZipArchive;

//require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/../../../../vendor/autoload.php'; // Autoload files using Composer autoload
use NumeroALetras\NumeroALetras;

class VentVentasController extends Controller
{


    public function listar_ventas(Request $request)
    {

        $condicional_tipo_comprobante = !is_null($request->input('vent_venta_tipo_comprobante_id')) ? '=' : '<>';
        $condicional_serie = !is_null($request->input('vent_venta_serie')) ? '=' : '<>';
        $condicional_numero = !is_null($request->input('vent_venta_numero')) ? '=' : '<>';
        $condicional_cliente = !is_null($request->input('cliente_cliente_id')) ? '=' : '<>';
        if (!is_null($request->input('vent_venta_fecha_desde')) &&
            !is_null($request->input('vent_venta_fecha_hasta'))) {
            $condicional_fecha = [$request->input('vent_venta_fecha_desde'), $request->input('vent_venta_fecha_hasta')];
        } else {
            $condicional_fecha = [date('Y-m-d'), date('Y-m-d')];
        }
        $ventas = Venta::select(
            'vent_venta.vent_venta_id',
            'vent_venta.vent_venta_serie',
            'vent_venta.vent_venta_numero',
            'vent_venta.vent_venta_estado',
            'vent_venta.vent_venta_total',
            'vent_venta.vent_venta_estado_envio_sunat',
            'vent_venta.vent_venta_cliente_numero_documento',
            'vent_venta.vent_venta_fecha',
            'vent_venta.vent_venta_confirmado',
            'seg_cliente.seg_cliente_razon_social',
            'doc_tipo_comprabante.doc_tipo_comprobante_codigo',
            'vent_venta.vent_venta_fecha_registro', 'doc_tipo_comprabante.doc_tipo_comprobante_nombre')
            ->leftJoin('seg_cliente', 'vent_venta.vent_venta_cliente_id', 'seg_cliente.seg_cliente_id')
            ->leftJoin('doc_tipo_comprabante', 'vent_venta.vent_venta_tipo_comprobante_id', 'doc_tipo_comprabante.doc_tipo_comprobante_id')
            ->where('doc_tipo_comprabante.doc_tipo_comprobante_id', $condicional_tipo_comprobante, is_null($request->input('vent_venta_tipo_comprobante_id')) ? null : TipoComprobante::select('doc_tipo_comprobante_id')->where('doc_tipo_comprobante_codigo', $request->input('vent_venta_tipo_comprobante_id'))->first()->doc_tipo_comprobante_id)
            ->where('vent_venta.vent_venta_serie', $condicional_serie, is_null($request->input('vent_venta_serie')) ? null : $request->input('vent_venta_serie'))
            ->where('vent_venta.vent_venta_numero', $condicional_numero, is_null($request->input('vent_venta_numero')) ? null : $request->input('vent_venta_numero'))
            ->where('vent_venta.vent_venta_cliente_id', $condicional_cliente, is_null($request->input('cliente_cliente_id')) ? null : $request->input('cliente_cliente_id'))
            ->whereIn('vent_venta.vent_venta_tipo_comprobante_id', TipoComprobante::select('doc_tipo_comprobante_id')->whereIn('doc_tipo_comprobante_codigo', array('03', '01', '99', '07'))->get())
            ->orderBy('vent_venta.vent_venta_fecha_registro', 'vent_venta.vent_venta_numero', 'DESC')
            ->whereBetween('vent_venta.vent_venta_fecha', $condicional_fecha)
            ->get();
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru($ventas, $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de Almacenes'], 200);
    }

    public function venta_data($id)
    {
        $venta = Venta::find($id);
        $cliente = Cliente::find($venta->vent_venta_cliente_id);
        $venta->cliente = !is_null($cliente) ? $cliente->seg_cliente_razon_social . ' (' . $cliente->seg_cliente_numero_doc . ')' : '';
        $vent_venta_tipo_doc_codigo = TipoComprobante::find($venta->vent_venta_tipo_comprobante_id)->doc_tipo_comprobante_codigo;
        $venta->vent_venta_tipo_comprobante_id = $vent_venta_tipo_doc_codigo;
        $venta->vent_venta_tipo_doc_codigo = $vent_venta_tipo_doc_codigo;
        $lista_detalle = array();
        foreach (VentaDetalle::where('vent_venta_detalle_venta_id', $id)
                     ->orderBy('vent_venta_detalle_item')
                     ->get() as $item) {
            $producto = Producto::find($item->vent_venta_detalle_producto_id);
            $item->vent_venta_detalle_producto = $producto->alm_producto_serie ?
                Producto::find($producto->Parent_alm_producto_id)->alm_producto_nombre
                . ' ' . $producto->alm_producto_nombre
                . ' ' . $producto->alm_producto_marca
                . ' ' . $producto->alm_producto_modelo
                . 'SERIE: ' . $item->vent_venta_detalle_serie : Producto::find($producto->Parent_alm_producto_id)->alm_producto_nombre
                . ' ' . $producto->alm_producto_nombre
                . ' ' . $producto->alm_producto_marca
                . ' ' . $producto->alm_producto_modelo;
            $item->vent_venta_unidad_medida = Producto::find($item->vent_venta_detalle_producto_id)->alm_unidad_medida_id;
            $item->vent_venta_lista_precios = ListaPrecioDetalle::select('alm_lista_precio.alm_lista_precio_id',
                'alm_lista_precio.alm_lista_precio_nombre', 'alm_lista_precio_detalle.alm_lista_precio_detalle_precio')
                ->where('alm_lista_precio_detalle.alm_lista_precio_detalle_articulo_id', $item->vent_venta_detalle_producto_id)
                ->where('alm_lista_precio.alm_lista_precio_almacen_id', $venta->vent_venta_almacen_id)
                ->where('alm_lista_precio.alm_lista_precio_fecha_vigencia_inicio', '<=', date('Y-m-d'))
                ->where('alm_lista_precio.alm_lista_precio_fecha_vigencia_fin', '>=', date('Y-m-d'))
                ->orderBy('alm_lista_precio_detalle.alm_lista_precio_detalle_precio', 'DESC')
                ->leftJoin('alm_lista_precio', 'alm_lista_precio_detalle.alm_lista_precio_detalle_lista_precio_id', 'alm_lista_precio.alm_lista_precio_id')
                ->get();
            array_push($lista_detalle, $item);
        }
        $venta->detalle = $lista_detalle;
        $venta->pago = VentaPago::where('vent_pago_venta_id', $id)->first();
        $venta->fecha_pagos = FechaPago::where('vent_venta_fecha_pago_venta_id', $id)
            ->orderBy('vent_venta_fecha_pago_cuota')
            ->get();
        return $venta;
    }

    public function data_impresion($id)
    {
        $venta = Venta::find($id);
        $venta->almacen = Almacen::find($venta->vent_venta_almacen_id);
        $cliente = Cliente::find($venta->vent_venta_cliente_id);
        $venta->cliente = !is_null($cliente) ? $cliente->seg_cliente_razon_social . ' (' . $cliente->seg_cliente_numero_doc . ')' : '';
        $venta->cliente_direccion = is_null($cliente->seg_cliente_direccion) ? '' : $cliente->seg_cliente_direccion;
        $vent_venta_tipo_doc_codigo = TipoComprobante::find($venta->vent_venta_tipo_comprobante_id)->doc_tipo_comprobante_codigo;
        $venta->vent_venta_tipo_comprobante_id = $vent_venta_tipo_doc_codigo;
        $venta->vent_venta_tipo_doc_codigo = $vent_venta_tipo_doc_codigo;
        $lista_detalle = array();
        $item_contadot = 1;
        foreach (VentaDetalle::listar_venta_detalle($id) as $item) {
            $series_list = VentaDetalle::listar_venta_detalle_serie($id, $item->vent_venta_detalle_producto_id);
            $series = "";
            foreach ($series_list as $value) {
                $series = $series . $value->vent_venta_detalle_serie . ', ';
            }
            $producto = Producto::find($item->vent_venta_detalle_producto_id);
            $producto->alm_producto_color = !is_null($producto->alm_producto_color) && $producto->alm_producto_color != '' ? ' COLOR:  ' . $producto->alm_producto_color : '';

            $item->alm_producto_codigo = substr($producto->alm_producto_codigo, 0, 10);
            $item->vent_venta_detalle_item = $item_contadot;
            $series = strlen($series) > 2 ? ' SERIE: ' . $series : '';
            $item->vent_venta_detalle_producto = $producto->alm_producto_serie ?
                Producto::find($producto->Parent_alm_producto_id)->alm_producto_nombre
                . ' ' . $producto->alm_producto_nombre
                . ' ' . $producto->alm_producto_marca
                . ' ' . $producto->alm_producto_modelo
                . ' ' . $producto->alm_producto_color
                . ' ' . $series : Producto::find($producto->Parent_alm_producto_id)->alm_producto_nombre
                . ' ' . $producto->alm_producto_nombre
                . ' ' . $producto->alm_producto_marca
                . ' ' . $producto->alm_producto_color
                . ' ' . $producto->alm_producto_modelo;
            $item->vent_venta_unidad_medida = Producto::find($item->vent_venta_detalle_producto_id)->alm_unidad_medida_id;
            $item->vent_venta_lista_precios = ListaPrecioDetalle::select('alm_lista_precio.alm_lista_precio_id',
                'alm_lista_precio.alm_lista_precio_nombre', 'alm_lista_precio_detalle.alm_lista_precio_detalle_precio')
                ->where('alm_lista_precio_detalle.alm_lista_precio_detalle_articulo_id', $item->vent_venta_detalle_producto_id)
                ->where('alm_lista_precio.alm_lista_precio_almacen_id', $venta->vent_venta_almacen_id)
                ->where('alm_lista_precio.alm_lista_precio_fecha_vigencia_inicio', '<=', date('Y-m-d'))
                ->where('alm_lista_precio.alm_lista_precio_fecha_vigencia_fin', '>=', date('Y-m-d'))
                ->orderBy('alm_lista_precio_detalle.alm_lista_precio_detalle_precio', 'DESC')
                ->leftJoin('alm_lista_precio', 'alm_lista_precio_detalle.alm_lista_precio_detalle_lista_precio_id', 'alm_lista_precio.alm_lista_precio_id')
                ->get();
            array_push($lista_detalle, $item);
            $item_contadot = $item_contadot + 1;
        }
        $venta->detalle = $lista_detalle;
        $venta->pago = VentaPago::where('vent_pago_venta_id', $id)->first();
        $venta->fecha_pagos = FechaPago::where('vent_venta_fecha_pago_venta_id', $id)
            ->orderBy('vent_venta_fecha_pago_cuota')
            ->get();
        return $venta;
    }

    public function venta_data_impresion($id)
    {

        return response()->json(['success' => true,
            'data' => $this->data_impresion($id),
            'message' => 'Lista de Almacenes'], 200);
    }

    public function listar_venta($id)
    {
        return response()->json(['success' => true,
            'data' => $this->venta_data($id),
            'message' => 'Lista de Almacenes'], 200);
    }


    public function registrar_venta(Request $request)
    {
        $venta = new Venta($request->all());
        $vent_venta_id = IdGenerador::generaId();
        $venta->vent_venta_id = $vent_venta_id;
        $tipo_comprobante_codigo = $venta->vent_venta_tipo_comprobante_id;
        $venta->vent_venta_tipo_comprobante_id = TipoComprobante::where('doc_tipo_comprobante_codigo', $venta->vent_venta_tipo_comprobante_id)->first()->doc_tipo_comprobante_id;
        $venta->vent_venta_tipo_venta = $request->input('vent_pago_tipo_pago');
        $venta->vent_venta_estado_envio_sunat = false;
        $venta->vent_venta_estado = true;
        $periodo = Periodo::where('cont_periodo_estado', true)->first();
        $venta->vent_venta_periodo_id = $periodo->cont_periodo_id;
        $venta->vent_venta_user_id = auth()->user()->id;
        $venta->vent_venta_precio_cobrado_letras = NumeroALetras::convertir($venta->vent_venta_total);
        $venta->vent_venta_estado_pago = $request->input('vent_pago_importe') >= $venta->vent_venta_total ? true : false;
        $venta->vent_venta_tipo_venta = $request->input('vent_pago_tipo_pago');
        $venta->vent_venta_fecha_registro = date('Y-m-d H:i:s', strtotime("now"));
        $venta->vent_venta_numero = 00000000;
        $venta->vent_venta_fecha = date('Y-m-d');
        $venta_confirmado = $venta->vent_venta_confirmado;
        if ($venta->vent_venta_confirmado) {
            $venta->vent_venta_numero = GeneraNumero::genera_numero_venta($venta->vent_venta_serie);
            $empresa = Empresa::all()->first();
            $venta->vent_venta_qr = $empresa->emp_empresa_ruc .
                '|' . TipoComprobante::find($venta->vent_venta_tipo_comprobante_id)->doc_tipo_comprobante_codigo .
                '|' . $venta->vent_venta_serie .
                '|' . $venta->vent_venta_numero .
                '|' . $venta->vent_venta_igv .
                '|' . $venta->vent_venta_total .
                '|' . $venta->vent_venta_fecha .
                '|' . $venta->vent_venta_cliente_numero_documento .
                '|' . "";
        }
        $venta->save();
        $contador = 1;
        foreach ($request->input('detalle') as $item) {
            $venta_detalle = new VentaDetalle($item);
            $venta_detalle->vent_venta_detalle_id = IdGenerador::generaId();
            $venta_detalle->vent_venta_detalle_venta_id = $vent_venta_id;
            $venta_detalle->vent_venta_detalle_item = $contador;
            $venta_detalle->save();
            $contador = $contador + 1;
            if ($venta_confirmado && $tipo_comprobante_codigo == '07') {
                CompraDetalle::where('comp_compra_detalle_producto_id', $venta_detalle->vent_venta_detalle_producto_id)
                    ->where('comp_compra_detalle_serie', $venta_detalle->vent_venta_detalle_serie)->update(array('comp_compra_detalle_vendido' => false));
            } else {
                CompraDetalle::where('comp_compra_detalle_producto_id', $venta_detalle->vent_venta_detalle_producto_id)
                    ->where('comp_compra_detalle_serie', $venta_detalle->vent_venta_detalle_serie)->update(array('comp_compra_detalle_vendido' => true));
            }
        }
        $importe = $request->input('vent_pago_importe');
        if (floatval($importe) > 0.00) {
            $venta_pago = new VentaPago();
            $venta_pago->vent_pago_id = IdGenerador::generaId();
            $venta_pago->vent_pago_nro_cuota = 1;
            $venta_pago->vent_pago_importe = $request->input('vent_pago_importe');
            $venta_pago->vent_pago_pago = $request->input('vent_pago_pago');
            $venta_pago->vent_pago_vuelto = $request->input('vent_pago_vuelto');
            $venta_pago->vent_pago_venta_id = $vent_venta_id;
            $venta_pago->vent_pago_tipo_pago = $request->input('vent_pago_tipo_pago');
            $venta_pago->vent_pago_user_id = auth()->user()->id;
            $venta_pago->vent_pago_fecha = date('Y-m-d H:i:s', strtotime("now"));
            $venta_pago->vent_pago_serie = 'VP01' . $request->input('vent_num_cajero');
            $venta_pago->vent_pago_numero_pago = GeneraNumero::genera_numero_pago_cliente($venta_pago->vent_pago_serie);
            $venta_pago->vent_pago_modalidad = $request->input('vent_pago_modalidad');
            $venta_pago->vent_pago_numero_transaccion = $request->input('vent_pago_numero_transaccion');
            $venta_pago->vent_pago_cliente_documento = $request->input('vent_venta_cliente_numero_documento');
            $venta_pago->vent_pago_almacen_id = $request->input('vent_venta_almacen_id');
            $venta_pago->vent_pago_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
            if ($request->input('vent_venta_confirmado')) {
                $venta_pago->vent_pago_comision_id = $request->input('vent_venta_distribuidor_id');
                $venta_pago->vent_pago_comision_pagado = false;
                $venta_pago->vent_pago_comision_importe = !is_null($request->input('vent_venta_distribuidor_id')) ? floatval($request->input('vent_pago_importe')) / (floatval($periodo->cont_periodo_igv) / 100 + 1) * (floatval(Distribuidor::find($request->input('vent_venta_distribuidor_id'))->alm_distribuidor_porcentaje_venta) / 100) : 0.00;

            }
            $venta_pago->save();

        }
        foreach ($request->input('vent_venta_fecha_pagos') as $item) {
            $fecha_pago = new FechaPago($item);
            $fecha_pago->vent_venta_fecha_pago_id = IdGenerador::generaId();
            $fecha_pago->vent_venta_fecha_pago_venta_id = $vent_venta_id;
            $fecha_pago->save();
        }
        return response()->json(['success' => true,
            'data' => $this->data_impresion($vent_venta_id),
            'message' => 'Lista de Almacenes'], 200);
    }


    public function actualizar_venta(Request $request, $id)
    {
        $venta = new Venta($request->all());

        $venta->vent_venta_tipo_comprobante_id = TipoComprobante::where('doc_tipo_comprobante_codigo', $venta->vent_venta_tipo_comprobante_id)->first()->doc_tipo_comprobante_id;
        $tipo_comprobante_codigo = $venta->vent_venta_tipo_comprobante_id;
        $venta->vent_venta_tipo_venta = $request->input('vent_pago_tipo_pago');
        $venta->vent_venta_estado = true;
        $periodo = Periodo::where('cont_periodo_estado', true)->first();
        $venta->vent_venta_periodo_id = $periodo->cont_periodo_id;
        $venta->vent_venta_user_id = auth()->user()->id;
        $venta->vent_venta_precio_cobrado_letras = NumeroALetras::convertir($venta->vent_venta_total);
        $venta->vent_venta_estado_pago = $request->input('vent_pago_importe') >= $venta->vent_venta_total ? true : false;
        $venta->vent_venta_tipo_venta = $request->input('vent_pago_tipo_pago');
        $venta->vent_venta_fecha_registro = date('Y-m-d H:i:s', strtotime("now"));

        $venta_confirmado = $venta->vent_venta_confirmado;
        if ($venta->vent_venta_confirmado && $venta->vent_venta_numero != 00000000 || $venta->vent_venta_numero != 0 || $venta->vent_venta_numero != '' || is_null($venta->vent_venta_numero)) {
            $venta->vent_venta_numero = GeneraNumero::genera_numero_venta($venta->vent_venta_serie);
        }
        if ($venta->vent_venta_confirmado) {
            $empresa = Empresa::all()->first();
            $venta->vent_venta_qr = $empresa->emp_empresa_ruc .
                '|' . TipoComprobante::find($venta->vent_venta_tipo_comprobante_id)->doc_tipo_comprobante_codigo .
                '|' . $venta->vent_venta_serie .
                '|' . $venta->vent_venta_numero .
                '|' . $venta->vent_venta_igv .
                '|' . $venta->vent_venta_total .
                '|' . $venta->vent_venta_fecha .
                '|' . $venta->vent_venta_cliente_numero_documento .
                '|' . "";
        }
        Venta::find($id)->update($venta->toArray());
        VentaDetalle::where('vent_venta_detalle_venta_id', $id)->delete();
        $contador = 1;
        foreach ($request->input('detalle') as $item) {
            $venta_detalle = new VentaDetalle($item);
            $venta_detalle->vent_venta_detalle_id = IdGenerador::generaId();
            $venta_detalle->vent_venta_detalle_venta_id = $id;
            $venta_detalle->vent_venta_detalle_item = $contador;
            $venta_detalle->save();
            $contador = $contador + 1;
            if ($venta_confirmado && $tipo_comprobante_codigo == '07') {
                CompraDetalle::where('comp_compra_detalle_producto_id', $venta_detalle->vent_venta_detalle_producto_id)
                    ->where('comp_compra_detalle_serie', $venta_detalle->vent_venta_detalle_serie)->update(array('comp_compra_detalle_vendido' => false));
            } else {
                CompraDetalle::where('comp_compra_detalle_producto_id', $venta_detalle->vent_venta_detalle_producto_id)
                    ->where('comp_compra_detalle_serie', $venta_detalle->vent_venta_detalle_serie)->update(array('comp_compra_detalle_vendido' => true));
            }
        }
        $importe = $request->input('vent_pago_importe');
        VentaPago::where('vent_pago_venta_id', $id)->delete();
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
            $venta_pago->vent_pago_cliente_documento = $request->input('vent_venta_cliente_numero_documento');
            $venta_pago->vent_pago_almacen_id = $request->input('vent_venta_almacen_id');
            $venta_pago->vent_pago_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
            if ($request->input('vent_venta_confirmado')) {
                $venta_pago->vent_pago_comision_id = $request->input('vent_venta_distribuidor_id');
                $venta_pago->vent_pago_comision_pagado = false;
                $venta_pago->vent_pago_comision_importe = !is_null($request->input('vent_venta_distribuidor_id')) ? floatval($request->input('vent_pago_importe')) / (floatval($periodo->cont_periodo_igv) / 100 + 1) * (floatval(Distribuidor::find($request->input('vent_venta_distribuidor_id'))->alm_distribuidor_porcentaje_venta) / 100) : 0.00;

            }
            $venta_pago->save();

        }
        FechaPago::where('vent_venta_fecha_pago_venta_id', $id)->delete();
        foreach ($request->input('vent_venta_fecha_pagos') as $item) {
            $fecha_pago = new FechaPago($item);
            $fecha_pago->vent_venta_fecha_pago_id = IdGenerador::generaId();
            $fecha_pago->vent_venta_fecha_pago_venta_id = $id;
            $fecha_pago->save();
        }
        return response()->json(['success' => true,
            'data' => $this->data_impresion($id),
            'message' => 'Lista de Almacenes'], 200);
    }


    public function venta_enviar_sunat($id)
    {

        $jResponse['success'] = true;
        $jResponse['data'] = 'Documento ya enviado';
        $venta = Venta::find($id);
        $venta->serie_comprobante = $venta->vent_venta_serie;
        $venta->numero_comprobante = $venta->vent_venta_numero;
        $venta->fecha_comprobante = $venta->vent_venta_fecha;
        $venta->codmoneda_comprobante = "PEN";
        $cliente = Cliente::find($venta->vent_venta_cliente_id);
        $venta->cliente_tipodocumento = $cliente->seg_cliente_tipo_documento;
        $venta->cliente_numerodocumento = $cliente->seg_cliente_numero_doc;
        $venta->cliente_nombre = $cliente->seg_cliente_razon_social;
        $venta->cliente_pais = "PE";
        $venta->cliente_ciudad = "";


        $venta->cliente_direccion = $cliente->seg_cliente_direccion;
        $venta->correo_electronico = $cliente->seg_cliente_email;
        $venta->txt_subtotal_comprobante = $venta->vent_venta_bi;
        $venta->txt_igv_comprobante = $venta->vent_venta_igv;
        $venta->txt_total_comprobante = $venta->vent_venta_total;
        $venta->txt_total_letras = $venta->vent_venta_precio_cobrado_letras;
        $tipo_comprobante = TipoComprobante::find($venta->vent_venta_tipo_comprobante_id);
        $venta->tipo_comprobante = $tipo_comprobante->doc_tipo_comprobante_codigo;
        $venta->doc_tipo_comprobante_nombre = strtoupper($tipo_comprobante->doc_tipo_comprobante_nombre);
        $almacen = Almacen::find($venta->vent_venta_almacen_id);
        $venta->alm_almacen_email = $almacen->alm_almacen_email;
        $venta->alm_almacen_telefono = $almacen->alm_almacen_telefono;
        $venta->alm_almacen_direccion = $almacen->alm_almacen_direccion;
        $venta->cliente = $cliente->seg_cliente_razon_social;
        $venta->cliente_direccion = $cliente->seg_cliente_direccion;
        $lista_detalle = array();
        foreach (VentaDetalle::where('vent_venta_detalle_venta_id', $id)
                     ->orderBy('vent_venta_detalle_item')
                     ->get() as $item) {
            $item->ITEM_DET = $item->vent_venta_detalle_item;
            $producto = Producto::find($item->vent_venta_detalle_producto_id);
            $item->UNIDAD_MEDIDA_DET = $producto->alm_unidad_medida_id;
            $item->CANTIDAD_DET = $item->vent_venta_detalle_cantidad;
            // $item->PRECIO_DET = floatval($item->vent_venta_detalle_bi) / floatval($item->vent_venta_detalle_cantidad);
            // $item->SUB_TOTAL_DET = floatval($item->vent_venta_detalle_precio) / floatval($item->vent_venta_detalle_cantidad);

            $item->PRECIO_DET = round(floatval($item->vent_venta_detalle_bi) / floatval($item->vent_venta_detalle_cantidad) * 100) / 100;
            $item->SUB_TOTAL_DET = round(floatval($item->vent_venta_detalle_precio) / floatval($item->vent_venta_detalle_cantidad) * 100) / 100;
            $item->PRECIO_TIPO_CODIGO = "01";
            $item->IGV_DET = $item->vent_venta_detalle_igv;
            $item->ISC_DET = "0";
            $item->IMPORTE_DET = $item->vent_venta_detalle_bi;
            $item->COD_TIPO_OPERACION_DET = $item->vent_venta_detalle_tipo_operacion;
            $item->DESCRIPCION_DET = !is_null($item->vent_venta_detalle_serie) && $item->vent_venta_detalle_serie != "" ? $producto->alm_producto_nombre . ' SN: ' . $item->vent_venta_detalle_serie : $producto->alm_producto_nombre;
            $item->CODIGO_DET = $producto->alm_producto_codigo;
            $item->PRECIO_SIN_IGV_DET = $item->vent_venta_detalle_bi;
            $item->ITEM_DET = $item->vent_venta_detalle_item;
            $item->alm_unidad_medida_id = $producto->alm_unidad_medida_id;
            $item->alm_producto_nombre = $producto->alm_producto_nombre;
            $item->alm_producto_marca = $producto->alm_producto_marca;
            $item->vent_venta_detalle_precio_cobro = $item->PRECIO_DET;
            array_push($lista_detalle, $item);
        }
        $venta->detalle = $lista_detalle;
        $venta_totales = Ventas::listar_totales_comprobante($id);
        $venta->totales = $venta_totales;
        if (substr($venta->serie_comprobante, 0, 1) == 'F'
            || substr($venta->serie_comprobante, 0, 1) == 'B'
            && $venta->vent_venta_estado_envio_sunat == false) {
            $factura_electronica = procesar_data::procesar_data($venta, $venta_totales);
            $empresa = Empresa::all()->first();
            if ($factura_electronica['cod_sunat'] == '0') {
                $data_guardar = array(
                    'vent_venta_ruta_xml' => $factura_electronica['url_xml'],
                    'vent_venta_ruta_cdr' => $factura_electronica['ruta_cdr'],
                    'vent_venta_fecha_envio_sunat' => date('Y-m-d H:i:s'),
                    'vent_venta_hash' => $factura_electronica['hash_cpe'],
                    'vent_venta_cod_sunat' => $factura_electronica['cod_sunat'],
                    'vent_venta_mensaje_sunat' => $factura_electronica['mensaje'],
                    'vent_venta_hash_cdr' => $factura_electronica['hash_cdr'],
                    'vent_venta_estado_envio_sunat' => true,
                    'vent_venta_qr' => $empresa->emp_empresa_ruc .
                        '|' . TipoComprobante::find($venta->vent_venta_tipo_comprobante_id)->doc_tipo_comprobante_codigo .
                        '|' . $venta->vent_venta_serie .
                        '|' . $venta->vent_venta_numero .
                        '|' . $venta->vent_venta_igv .
                        '|' . $venta->vent_venta_total .
                        '|' . $venta->vent_venta_fecha .
                        '|' . $venta->vent_venta_cliente_numero_documento .
                        '|' . $factura_electronica['hash_cpe'],
                );
                Venta::find($id)->update($data_guardar);
                if ($venta->correo_electronico != '-' && $venta->correo_electronico != "" && $venta->correo_electronico != null) {
                    $proceso = "";
                    if ($empresa->emp_empresa_tipoproceso == "1") {
                        $proceso = "produccion";
                    } else if ($empresa->emp_empresa_tipoproceso == "3") {
                        $proceso = "beta";
                    }
                    $data = $venta;

                    $data->empresa = $empresa;
                    $base_url_comprobante = realpath(__DIR__ . '/../../../../../') . '/comprobantes/';
                    if (substr($venta->serie_comprobante, 0, 2) == 'B0') {
                        if ($venta->vent_venta_tipo_venta == "03") {
                            DOMPDF::loadView('pdf.' . 'comprobante_boleta_vehiculo', compact('data'))->save($base_url_comprobante . 'comprobantes_pdf/' . $proceso . '/boletas/' . $venta->serie_comprobante . '-' . $venta->numero_comprobante . '.pdf');

                        } else {
                            DOMPDF::loadView('pdf.' . 'comprobante_boleta', compact('data'))->save($base_url_comprobante . 'comprobantes_pdf/' . $proceso . '/boletas/' . $venta->serie_comprobante . '-' . $venta->numero_comprobante . '.pdf');

                        }
                        $data = [
                            $base_url_comprobante . 'cpe_xml/' . $proceso . '/boletas/' . $factura_electronica['url_xml'],
                            $base_url_comprobante . 'cpe_xml/' . $proceso . '/boletas/' . $factura_electronica['ruta_cdr'],
                            $base_url_comprobante . 'comprobantes_pdf/' . $proceso . '/boletas/' . $venta->serie_comprobante . '-' . $venta->numero_comprobante . '.pdf',
                        ];
                    } else if (substr($venta->serie_comprobante, 0, 2) == 'F0') {
                        if ($venta->vent_venta_tipo_venta == "03") {
                            DOMPDF::loadView('pdf.' . 'comprobante_factura_vehiculo', compact('data'))->save($base_url_comprobante . 'comprobantes_pdf/' . $proceso . '/facturas/' . $venta->serie_comprobante . '-' . $venta->numero_comprobante . '.pdf');

                        } else {
                            DOMPDF::loadView('pdf.' . 'comprobante_factura', compact('data'))->save($base_url_comprobante . 'comprobantes_pdf/' . $proceso . '/facturas/' . $venta->serie_comprobante . '-' . $venta->numero_comprobante . '.pdf');

                        }
                        $data = [
                            $base_url_comprobante . 'cpe_xml/' . $proceso . '/facturas/' . $factura_electronica['url_xml'],
                            $base_url_comprobante . 'cpe_xml/' . $proceso . '/facturas/' . $factura_electronica['ruta_cdr'],
                            $base_url_comprobante . 'comprobantes_pdf/' . $proceso . '/facturas/' . $venta->serie_comprobante . '-' . $venta->numero_comprobante . '.pdf',
                        ];
                    }
                    $ComprobanteEmail = new \stdClass();
                    $ComprobanteEmail->sender = $venta->cliente_nombre;
                    $ComprobanteEmail->archivos = $data;
                    $ComprobanteEmail->empresa_emisor_mail = $empresa->emp_empresa_email;
                    $ComprobanteEmail->empresa = $empresa->emp_empresa_razon_social;
                    Mail::to($venta->correo_electronico)->send(new ComprobanteMail($ComprobanteEmail));
                    if (substr($venta->serie_comprobante, 0, 2) == 'B0') {
                        unlink($base_url_comprobante . 'comprobantes_pdf/' . $proceso . '/boletas/' . $venta->serie_comprobante . '-' . $venta->numero_comprobante . '.pdf');
                    } else if (substr($venta->serie_comprobante, 0, 2) == 'F0') {
                        unlink($base_url_comprobante . 'comprobantes_pdf/' . $proceso . '/facturas/' . $venta->serie_comprobante . '-' . $venta->numero_comprobante . '.pdf');
                    }
                }
                $jResponse['success'] = true;
                $jResponse['data'] = $factura_electronica;
            } else {
                $data_guardar = array(
                    'vent_venta_ruta_xml' => $factura_electronica['url_xml'],
                    'vent_venta_ruta_cdr' => $factura_electronica['ruta_cdr'],
                    'vent_venta_fecha_envio_sunat' => date('Y-m-d H:i:s'),
                    'vent_venta_hash' => $factura_electronica['hash_cpe'],
                    'vent_venta_cod_sunat' => $factura_electronica['cod_sunat'],
                    'vent_venta_mensaje_sunat' => $factura_electronica['mensaje'],
                    'vent_venta_hash_cdr' => $factura_electronica['hash_cdr'],
                    'vent_venta_qr' => $empresa->emp_empresa_ruc .
                        '|' . TipoComprobante::find($venta->vent_venta_tipo_comprobante_id)->doc_tipo_comprobante_codigo .
                        '|' . $venta->vent_venta_serie .
                        '|' . $venta->vent_venta_numero .
                        '|' . $venta->vent_venta_igv .
                        '|' . $venta->vent_venta_total .
                        '|' . $venta->vent_venta_fecha .
                        '|' . $venta->vent_venta_cliente_numero_documento .
                        '|' . $factura_electronica['hash_cpe'],
                );
                Venta::find($id)->update($data_guardar);
                $jResponse['success'] = false;
                $jResponse['data'] = $factura_electronica;
            }


        }
        return response()->json($jResponse, 200);

    }


    public function descargar_xml($id)
    {
        $url_base_xml = realpath(__DIR__ . '/../../../../..') . '/comprobantes/cpe_xml/';
        $empresa = Empresa::all()->first();
        $venta = Venta::find($id);
        if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '3'
            && substr($venta->vent_venta_serie, 0, 2) == 'F0') {
            $file = $url_base_xml . 'beta/facturas/' . $venta->vent_venta_ruta_xml;
            return \response()->download($file);
        } else if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '3'
            && substr($venta->vent_venta_serie, 0, 2) == 'F7') {
            $file = $url_base_xml . 'beta/nota_creditos/' . $venta->vent_venta_ruta_xml;
            return \response()->download($file);
        } else if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '3'
            && substr($venta->vent_venta_serie, 0, 2) == 'B7') {
            $file = $url_base_xml . 'beta/nota_creditos/' . $venta->vent_venta_ruta_xml;
            return \response()->download($file);
        } else if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '1'
            && substr($venta->vent_venta_serie, 0, 2) == 'F7') {
            $file = $url_base_xml . 'produccion/nota_creditos/' . $venta->vent_venta_ruta_xml;
            return \response()->download($file);
        } else if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '1'
            && substr($venta->vent_venta_serie, 0, 2) == 'B7') {
            $file = $url_base_xml . 'produccion/nota_creditos/' . $venta->vent_venta_ruta_xml;
            return \response()->download($file);
        } else if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '1'
            && substr($venta->vent_venta_serie, 0, 2) == 'F0') {
            $file = $url_base_xml . 'produccion/facturas/' . $venta->vent_venta_ruta_xml;
            return \response()->download($file);

        } else if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '3'
            && substr($venta->vent_venta_serie, 0, 2) == 'B0') {
            $file = $url_base_xml . 'beta/boletas/' . $venta->vent_venta_ruta_xml;
            return \response()->download($file);
        } else if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '1'
            && substr($venta->vent_venta_serie, 0, 2) == 'B0') {
            $file = $url_base_xml . 'produccion/boletas/' . $venta->vent_venta_ruta_xml;
            return \response()->download($file);
        } else {
            $jResponse['success'] = false;
            $jResponse['data'] = 'no hay XML';
            return response()->json($jResponse, 200);
        }
    }

    public function descargar_cdr($id)
    {
        $url_base_cdr = realpath(__DIR__ . '/../../../../..') . '/comprobantes/cpe_xml/';
        $empresa = Empresa::all()->first();
        $venta = Venta::find($id);
        if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '3'
            && substr($venta->vent_venta_serie, 0, 2) == 'F0') {
            $file = $url_base_cdr . 'beta/facturas/' . $venta->vent_venta_ruta_cdr;
            return \response()->download($file);
        } else if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '3'
            && substr($venta->vent_venta_serie, 0, 2) == 'F7') {
            $file = $url_base_cdr . 'beta/nota_creditos/' . $venta->vent_venta_ruta_cdr;
            return \response()->download($file);
        } else if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '3'
            && substr($venta->vent_venta_serie, 0, 2) == 'B7') {

            $file = $url_base_cdr . 'beta/nota_creditos/' . $venta->vent_venta_ruta_cdr;
            return \response()->download($file);
        } else if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '1'
            && substr($venta->vent_venta_serie, 0, 2) == 'F7') {

            $file = $url_base_cdr . 'produccion/nota_creditos/' . $venta->vent_venta_ruta_cdr;
            return \response()->download($file);
        } else if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '1'
            && substr($venta->vent_venta_serie, 0, 2) == 'B7') {

            $file = $url_base_cdr . 'produccion/nota_creditos/' . $venta->vent_venta_ruta_cdr;
            return \response()->download($file);
        } else if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '1'
            && substr($venta->vent_venta_serie, 0, 2) == 'F0') {
            $file = $url_base_cdr . 'produccion/facturas/' . $venta->vent_venta_ruta_cdr;
            return \response()->download($file);
        } else if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '3'
            && substr($venta->vent_venta_serie, 0, 2) == 'B0') {
            $file = $url_base_cdr . 'beta/boletas/' . $venta->vent_venta_ruta_cdr;
            return \response()->download($file);
        } else if ($venta->vent_venta_ruta_xml != null
            && $empresa->emp_empresa_tipoproceso == '1'
            && substr($venta->vent_venta_serie, 0, 2) == 'B0') {
            $file = $url_base_cdr . 'produccion/boletas/' . $venta->vent_venta_ruta_cdr;
            return \response()->download($file);
        } else {
            $jResponse['success'] = false;
            $jResponse['data'] = 'no hay XML';
            return response()->json($jResponse, 200);
        }
    }


    public function listar_ventas_por_comision($id)
    {
        $lista = array();
        foreach (Venta::listar_ventas_por_comision($id) as $item) {
            $item->vent_pago_comision_pagado = $item->vent_pago_comision_pagado == 1 ? true : false;
            array_push($lista, $item);
        }
        return response()->json(['success' => true,
            'data' => $lista,
            'message' => 'Lista de Almacenes'], 200);
    }


    public function registrar_ventas_por_comision(Request $request)
    {

        foreach ($request->input('vent_pagos') as $item) {
            $venta_pago_id = $item;
            VentaPago::find($item)->update(array('vent_pago_comision_pagado' => true));
        }

        $caja_movimiento = new CajaMovimiento();
        $caja_movimiento->caja_movimiento_id = IdGenerador::generaId();
        $caja_movimiento->caja_movimiento_monto = $request->input('comision_total');
        $caja_movimiento->caja_movimiento_user = auth()->user()->id;
        $caja_movimiento->caja_movimiento_almacen_id = Venta::find(VentaPago::find($venta_pago_id)->vent_pago_venta_id)->vent_venta_almacen_id;
        $caja_movimiento->caja_movimiento_fecha = date('Y-m-d');
        $caja_movimiento->caja_movimiento_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
        $caja_movimiento->caja_movimiento_tipo = 'SALIDA';
        $caja_movimiento->caja_movimiento_doc_identidad = Distribuidor::find($request->input('distribuidor_id'))->alm_distribuidor_numero_doc;
        $caja_movimiento->caja_movimiento_descripcion = "Pago por comisión";
        $caja_movimiento->save();
        $lista = array();
        foreach (Venta::listar_ventas_por_comision($request->input('distribuidor_id')) as $item) {
            $item->vent_pago_comision_pagado = $item->vent_pago_comision_pagado == 1 ? true : false;
            array_push($lista, $item);
        }
        return response()->json(['success' => true,
            'data' => $lista,
            'message' => 'Lista de Almacenes'], 200);
    }


    /*public function descargar_pdf($id)
    {
        $venta = Ventas::venta($id);
        $empresa = Empresa::empresa();
        if ($venta->vent_venta_tipo_venta == "03" && substr($venta->vent_venta_serie, 0, 2) == 'B0') {
            return $this->genertaeBoletaA4Vehiculopdf($venta);
        } elseif ($venta->vent_venta_tipo_venta == "03" && substr($venta->vent_venta_serie, 0, 2) == 'F0') {
            return $this->genertaeFacturaA4Vehiculopdf($venta);
        } else {
            if ($empresa->emp_empresa_formato_doc_imp == '01' && substr($venta->vent_venta_serie, 0, 2) == 'B0') {
                return $this->generatePdfTicketBoleta($venta);
            } else if ($empresa->emp_empresa_formato_doc_imp == '01' && substr($venta->vent_venta_serie, 0, 2) == 'F0') {
                return $this->generatePdfTicketFactura($venta);
            } else if ($empresa->emp_empresa_formato_doc_imp == '02' && substr($venta->vent_venta_serie, 0, 2) == 'F0') {
                return $this->generateFacturaA5Pdf($venta);
            } else if ($empresa->emp_empresa_formato_doc_imp == '02' && substr($venta->vent_venta_serie, 0, 2) == 'B0') {
                return $this->generateBoletaA5Pdf($venta);
            } else if ($empresa->emp_empresa_formato_doc_imp == '03' && substr($venta->vent_venta_serie, 0, 2) == 'F0') {
                return $this->generateFacturaA4Pdf($venta);
            } else if ($empresa->emp_empresa_formato_doc_imp == '03' && substr($venta->vent_venta_serie, 0, 2) == 'B0') {
                return $this->generateBoletaA4Pdf($venta);
            } else if ($empresa->emp_empresa_formato_doc_imp == '02' && substr($venta->vent_venta_serie, 0, 2) != 'F0'
                && substr($venta->vent_venta_serie, 0, 2) != 'B0') {
                return $this->generateNotaVentaA5pdf($venta);
            } else if ($empresa->emp_empresa_formato_doc_imp == '01' && substr($venta->vent_venta_serie, 0, 2) != 'F0'
                && substr($venta->vent_venta_serie, 0, 2) != 'B0') {
                return $this->generatePdfTicketNotaVenta($venta);
            } else if ($empresa->emp_empresa_formato_doc_imp == '03' && substr($venta->vent_venta_serie, 0, 2) != 'F0'
                && substr($venta->vent_venta_serie, 0, 2) != 'B0') {
                return $this->generateNotaVentaA4Pdf($venta);


            }
        }
    }*/


    public function enviar_por_mail($id)
    {
        $venta = Ventas::venta($id);
        $user = User::all();

        Mail::send('emails.reminder', ['user' => $user[0]], function ($m) use ($user) {
            $m->from('noe_tipo@gamilcom', 'Your Application');

            $m->to($user->email, $user->name)->subject('Your Reminder!');
        });
        return $this->generateBoletaA4Pdf($venta);
    }

    public function eliminar_venta($id)
    {
        $venta = Ventas::buscar_venta_por_id_para_eliminar($id);
        if ($venta->vent_venta_estado_envio_sunat == 0
            && $venta->numero_venta_mayor == $venta->vent_venta_numero) {
            $ventas = Ventas::eliminar_venta($id);
            $jResponse['success'] = true;
            $jResponse['data'] = $ventas;
            return response()->json($jResponse, 200);
        } else {
            $jResponse['mensaje'] = "no es posible elminar";
        }
        $jResponse['data'] = 'ok';
        $jResponse['success'] = true;
        return response()->json($jResponse, 200);

    }

    public function anular_venta(Request $request, $id)
    {
        $empresa = Empresa::all()->first();
        $venta_comunicacion_baja = Venta::find($id);
        foreach (VentaDetalle::where('vent_venta_detalle_venta_id', $venta_comunicacion_baja->vent_venta_id)->get() as $item) {
            CompraDetalle::where('comp_compra_detalle_producto_id', $item->vent_venta_detalle_producto_id)
                ->where('comp_compra_detalle_serie', $item->vent_venta_detalle_serie)->update(array('comp_compra_detalle_vendido' => false));
        }

        $data = new \stdClass();
        $data->codigo = "RA";
        $data->serie = date('Ymd');
        $secuencia = GeneraNumero::genera_numero_comunicacion_baja();
        $data->secuencia = $secuencia;
        $data->fecha_referencia = $venta_comunicacion_baja->vent_venta_fecha;
        $data->fecha_baja = date('Y-m-d');
        $detalle_item = new \stdClass();
        if (substr($venta_comunicacion_baja->vent_venta_serie, 0, 2) == 'F0') {
            $detalle_item->tipo_comprobante = '01';
            $detalle_item->motivo = 'Error en Factura';
            $detalle_item->item = '1';
            $detalle_item->serie = $venta_comunicacion_baja->vent_venta_serie;
            $detalle_item->numero = $venta_comunicacion_baja->vent_venta_numero;
            $detalle = [];
            $emisor = new \stdClass();
            $emisor->ruc = $empresa->emp_empresa_ruc;
            $emisor->tipo_doc = "6";
            $emisor->nom_comercial = $empresa->emp_empresa_nombre_comercial;
            $emisor->razon_social = $empresa->emp_empresa_razon_social;
            $emisor->codigo_ubigeo = $empresa->emp_empresa_codigo_ubigeo;
            $emisor->direccion_departamento = $empresa->emp_empresa_direccion_departamento;
            $emisor->direccion_provincia = $empresa->emp_empresa_direccion_provincia;
            $emisor->direccion_distrito = $empresa->emp_empresa_direccion_distrito;
            $emisor->direccion_codigopais = $empresa->emp_empresa_codigopais;
            $emisor->usuariosol = $empresa->emp_empresa_usuariosol;
            $emisor->clavesol = $empresa->emp_empresa_clavesol;
            $emisor->tipodeproceso = $empresa->emp_empresa_tipoproceso;
            $emisor->emp_empresa_firma_digital_passwd = $empresa->emp_empresa_firma_digital_passwd;
            $emisor->emp_empresa_firma_digital = $empresa->emp_empresa_firma_digital;
            $emisor->emp_empresa_ose = $empresa->emp_empresa_ose ? 1 : 0;
            $data->emisor = $emisor;
            array_push($detalle, $detalle_item);
            $data->detalle = $detalle;
            $factura_electronica = procesar_data::comunicacion_baja($data);
            if ($factura_electronica['respuesta'] == 'ok'
                && $factura_electronica['resp_envio_doc'] == 'ok'
                && $factura_electronica['resp_consulta_ticket'] == 'ok') {
                Venta::find($id)->update(array('vent_venta_estado' => false));
                $comunicacion_baja = new VentaBaja();
                $comunicacion_baja->vent_venta_baja_id = IdGenerador::generaId();
                $comunicacion_baja->vent_venta_baja_fecha_referencia = $venta_comunicacion_baja->vent_venta_fecha;
                $comunicacion_baja->vent_venta_baja_fecha = $data->fecha_baja;
                $comunicacion_baja->vent_venta_baja_serie = $data->serie;
                $comunicacion_baja->vent_venta_baja_motivo = $detalle_item->motivo;
                $comunicacion_baja->vent_venta_baja_venta_serie = $venta_comunicacion_baja->vent_venta_serie;
                $comunicacion_baja->vent_venta_baja_venta_numero = $venta_comunicacion_baja->vent_venta_numero;
                $comunicacion_baja->vent_venta_baja_secuencia = $data->secuencia;
                $comunicacion_baja->vent_venta_baja_codigo = $data->codigo;
                $comunicacion_baja->vent_venta_baja_venta_id = $venta_comunicacion_baja->vent_venta_id;
                $comunicacion_baja->vent_venta_baja_xml = $emisor->ruc . '-' . $data->codigo . '-' . $data->serie . '-' . $data->secuencia;
                $comunicacion_baja->vent_venta_baja_cdr = 'R-' . $emisor->ruc . '-' . $data->codigo . '-' . $data->serie . '-' . $data->secuencia;
                $comunicacion_baja->vent_venta_baja_hash_cpe = $factura_electronica['hash_cpe'];
                $comunicacion_baja->vent_venta_baja_hash_cdr = $factura_electronica['hash_cdr'];
                $comunicacion_baja->save();
            }
        }
        $condicional_tipo_comprobante = !is_null($request->input('vent_venta_tipo_comprobante_id')) ? '=' : '<>';
        $condicional_serie = !is_null($request->input('vent_venta_serie')) ? '=' : '<>';
        $condicional_numero = !is_null($request->input('vent_venta_numero')) ? '=' : '<>';
        $condicional_cliente = !is_null($request->input('cliente_cliente_id')) ? '=' : '<>';
        if (!is_null($request->input('vent_venta_fecha_desde')) &&
            !is_null($request->input('vent_venta_fecha_hasta'))) {
            $condicional_fecha = [$request->input('vent_venta_fecha_desde'), $request->input('vent_venta_fecha_hasta')];
        } else {
            $condicional_fecha = [date('Y-m-d'), date('Y-m-d')];
        }
        $ventas = Venta::select(
            'vent_venta.vent_venta_id',
            'vent_venta.vent_venta_serie',
            'vent_venta.vent_venta_numero',
            'vent_venta.vent_venta_estado',
            'vent_venta.vent_venta_total',
            'vent_venta.vent_venta_estado_envio_sunat',
            'vent_venta.vent_venta_cliente_numero_documento',
            'vent_venta.vent_venta_fecha',
            'vent_venta.vent_venta_confirmado',
            'seg_cliente.seg_cliente_razon_social',
            'vent_venta.vent_venta_fecha_registro', 'doc_tipo_comprabante.doc_tipo_comprobante_nombre')
            ->leftJoin('seg_cliente', 'vent_venta.vent_venta_cliente_id', 'seg_cliente.seg_cliente_id')
            ->leftJoin('doc_tipo_comprabante', 'vent_venta.vent_venta_tipo_comprobante_id', 'doc_tipo_comprabante.doc_tipo_comprobante_id')
            ->where('doc_tipo_comprabante.doc_tipo_comprobante_id', $condicional_tipo_comprobante, is_null($request->input('vent_venta_tipo_comprobante_id')) ? null : TipoComprobante::select('doc_tipo_comprobante_id')->where('doc_tipo_comprobante_codigo', $request->input('vent_venta_tipo_comprobante_id'))->first()->doc_tipo_comprobante_id)
            ->where('vent_venta.vent_venta_serie', $condicional_serie, is_null($request->input('vent_venta_serie')) ? null : $request->input('vent_venta_serie'))
            ->where('vent_venta.vent_venta_numero', $condicional_numero, is_null($request->input('vent_venta_numero')) ? null : $request->input('vent_venta_numero'))
            ->where('vent_venta.vent_venta_cliente_id', $condicional_cliente, is_null($request->input('cliente_cliente_id')) ? null : $request->input('cliente_cliente_id'))
            ->whereIn('vent_venta.vent_venta_tipo_comprobante_id', TipoComprobante::select('doc_tipo_comprobante_id')->whereIn('doc_tipo_comprobante_codigo', array('03', '01', '99', '07'))->get())
            ->orderBy('vent_venta.vent_venta_fecha_registro', 'vent_venta.vent_venta_numero', 'DESC')
            ->whereBetween('vent_venta.vent_venta_fecha', $condicional_fecha)
            ->get();
        return response()->json(['success' => true,
            'data' => $ventas,
            'message' => 'Lista de venta'], 200);
    }


    public function buscar_venta_por_serie_numero(Request $request)
    {

        $venta = Venta::where('vent_venta_serie', $request->input('vent_venta_serie'))->where('vent_venta_numero', $request->input('vent_venta_numero'))->first();
        $cliente = Cliente::find($venta->vent_venta_cliente_id);
        $venta->cliente = !is_null($cliente) ? $cliente->seg_cliente_razon_social . ' (' . $cliente->seg_cliente_numero_doc . ')' : '';
        $vent_venta_tipo_doc_codigo = TipoComprobante::find($venta->vent_venta_tipo_comprobante_id)->doc_tipo_comprobante_codigo;
        $venta->vent_venta_tipo_comprobante_id = $vent_venta_tipo_doc_codigo;
        $venta->vent_venta_tipo_doc_codigo = $vent_venta_tipo_doc_codigo;
        $lista_detalle = array();
        foreach (VentaDetalle::where('vent_venta_detalle_venta_id', $venta->vent_venta_id)
                     ->orderBy('vent_venta_detalle_item')
                     ->get() as $item) {
            $producto = Producto::find($item->vent_venta_detalle_producto_id);
            $item->vent_venta_detalle_producto_codigo = $producto->alm_producto_codigo;
            $item->vent_venta_detalle_producto = $producto->alm_producto_serie ?
                Producto::find($producto->Parent_alm_producto_id)->alm_producto_nombre
                . ' ' . $producto->alm_producto_nombre
                . ' ' . $producto->alm_producto_marca
                . ' ' . $producto->alm_producto_modelo
                . 'SERIE:' . $item->vent_venta_detalle_serie : Producto::find($producto->Parent_alm_producto_id)->alm_producto_nombre
                . ' ' . $producto->alm_producto_nombre
                . ' ' . $producto->alm_producto_marca
                . ' ' . $producto->alm_producto_modelo;
            $item->vent_venta_unidad_medida = Producto::find($item->vent_venta_detalle_producto_id)->alm_unidad_medida_id;

            array_push($lista_detalle, $item);
        }
        $venta->detalle = $lista_detalle;


        return response()->json(['success' => true,
            'data' => $venta,
            'message' => 'Lista de venta'], 200);
    }


    public function avance_ventas_por_dia(Request $request)
    {
        return response()->json(['success' => true,
            'data' => Venta::listar_ventas_avance_por_dia($request->input('alm_almacen_id'), $request->input('fecha_inicio'), $request->input('fecha_fin')),
            'message' => 'Lista de venta'], 200);
    }


    public function listar_ranking_ventas_compras_pedidos(Request $request)
    {
        return response()->json(['success' => true,
            'data' => Venta::listar_ranking_ventas_compras_pedidos($request->input('alm_almacen_id'), $request->input('fecha_inicio'), $request->input('fecha_fin')),
            'message' => 'Lista de venta'], 200);
    }

    public function avance_ventas_por_mes(Request $request)
    {
        $avance_ventas_mes_lista = Venta::listar_ventas_avance_por_mes($request->input('alm_almacen_id'), $request->input('mes_inicio'), $request->input('mes_fin'), $request->input('anio'));
        $avance_ventas_mes = new \stdClass();
        $avance_ventas_mes->credito = 0.00;
        $avance_ventas_mes->contado = 0.00;
        foreach ($avance_ventas_mes_lista as $item) {
            $avance_ventas_mes->credito = $avance_ventas_mes->credito + $item->credito;
            $avance_ventas_mes->contado = $avance_ventas_mes->credito + $item->contado;
        }

        return response()->json(['success' => true,
            'data' => $avance_ventas_mes,
            'message' => 'Lista de venta'], 200);
    }

    public function avance_ventas_por_meses(Request $request)
    {
        $avance_ventas_mes_lista = Venta::listar_ventas_avance_por_mes($request->input('alm_almacen_id'), $request->input('mes_inicio'), $request->input('mes_fin'), $request->input('anio'));
        return response()->json(['success' => true,
            'data' => $avance_ventas_mes_lista,
            'message' => 'Lista de venta'], 200);
    }

    public function listar_ranking_ventas_compras_pedidos_mes(Request $request)
    {
        $avance_ventas_mes_lista = Venta::listar_ranking_ventas_compras_pedidos_mes($request->input('alm_almacen_id'), $request->input('mes_inicio'), $request->input('mes_fin'), $request->input('anio'));
        $avance_ventas_mes = new \stdClass();
        $avance_ventas_mes->venta = 0.00;
        $avance_ventas_mes->compra = 0.00;
        $avance_ventas_mes->pedido = 0.00;
        foreach ($avance_ventas_mes_lista as $item) {
            $avance_ventas_mes->venta = $avance_ventas_mes->venta + $item->venta;
            $avance_ventas_mes->compra = $avance_ventas_mes->compra + $item->compra;
            $avance_ventas_mes->pedido = $avance_ventas_mes->compra + $item->pedido;
        }

        return response()->json(['success' => true,
            'data' => $avance_ventas_mes,
            'message' => 'Lista de venta'], 200);
    }

    public function listar_ranking_ventas_compras_pedidos_meses(Request $request)
    {
        $avance_ventas_mes_lista = Venta::listar_ranking_ventas_compras_pedidos_mes($request->input('alm_almacen_id'), $request->input('mes_inicio'), $request->input('mes_fin'), $request->input('anio'));
        return response()->json(['success' => true,
            'data' => $avance_ventas_mes_lista,
            'message' => 'Lista de venta'], 200);
    }


    /** Reporte de ventas por usuario y por dia */
    public function listar_ventas_usario(Request $request)
    {

        $condicional_tipo_comprobante = !is_null($request->input('vent_venta_tipo_comprobante_id')) ? '=' : '<>';
        $condicional_serie = !is_null($request->input('vent_venta_serie')) ? '=' : '<>';
        $condicional_numero = !is_null($request->input('vent_venta_numero')) ? '=' : '<>';
        $condicional_cliente = !is_null($request->input('cliente_cliente_id')) ? '=' : '<>';
        if (!is_null($request->input('vent_venta_fecha_desde')) &&
            !is_null($request->input('vent_venta_fecha_hasta'))) {
            $condicional_fecha = [$request->input('vent_venta_fecha_desde'), $request->input('vent_venta_fecha_hasta')];
        } else {
            $condicional_fecha = [date('Y-m-d'), date('Y-m-d')];
        }
        $ventas = Venta::select(
            'vent_venta.vent_venta_id',
            'vent_venta.vent_venta_serie',
            'vent_venta.vent_venta_numero',
            'vent_venta.vent_venta_estado',
            'vent_venta.vent_venta_total',
            'vent_venta.vent_venta_estado_envio_sunat',
            'vent_venta.vent_venta_cliente_numero_documento',
            'vent_venta.vent_venta_fecha',
            'vent_venta.vent_venta_confirmado',
            'seg_cliente.seg_cliente_razon_social',
            'doc_tipo_comprabante.doc_tipo_comprobante_codigo',
            'vent_venta.vent_venta_fecha_registro', 'doc_tipo_comprabante.doc_tipo_comprobante_nombre')
            ->leftJoin('seg_cliente', 'vent_venta.vent_venta_cliente_id', 'seg_cliente.seg_cliente_id')
            ->leftJoin('doc_tipo_comprabante', 'vent_venta.vent_venta_tipo_comprobante_id', 'doc_tipo_comprabante.doc_tipo_comprobante_id')
            ->where('doc_tipo_comprabante.doc_tipo_comprobante_id', $condicional_tipo_comprobante, is_null($request->input('vent_venta_tipo_comprobante_id')) ? null : TipoComprobante::select('doc_tipo_comprobante_id')->where('doc_tipo_comprobante_codigo', $request->input('vent_venta_tipo_comprobante_id'))->first()->doc_tipo_comprobante_id)
            ->where('vent_venta.vent_venta_serie', $condicional_serie, is_null($request->input('vent_venta_serie')) ? null : $request->input('vent_venta_serie'))
            ->where('vent_venta.vent_venta_numero', $condicional_numero, is_null($request->input('vent_venta_numero')) ? null : $request->input('vent_venta_numero'))
            ->where('vent_venta.vent_venta_cliente_id', $condicional_cliente, is_null($request->input('cliente_cliente_id')) ? null : $request->input('cliente_cliente_id'))
            ->where('vent_venta_user_id', auth()->user()->id)
            ->whereIn('vent_venta.vent_venta_tipo_comprobante_id', TipoComprobante::select('doc_tipo_comprobante_id')->whereIn('doc_tipo_comprobante_codigo', array('03', '01', '99', '07'))->get())
            ->orderBy('vent_venta.vent_venta_fecha_registro', 'vent_venta.vent_venta_numero', 'DESC')
            ->whereBetween('vent_venta.vent_venta_fecha', $condicional_fecha)
            ->get();
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru($ventas, $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de Almacenes'], 200);

    }


    public function nota_credito()
    {
        $nota_credito_params = json_decode(file_get_contents("php://input"));
        $vent_venta_total = $nota_credito_params->precioTotal;
        $vent_venta_igv = $nota_credito_params->igvTotal;
        $vent_venta_bi = $nota_credito_params->baseImpobleTotal;
        $vent_venta_cliente_numero_documento = $nota_credito_params->cliente;
        $vent_venta_almacen_id = $nota_credito_params->almacen;
        $vent_venta_tipo_comprobante_codigo = $nota_credito_params->tipoComprobante;
        $vent_venta_comprobante_referenciado = $nota_credito_params->venta_comprobante_referenciado;
        $vent_venta_motivo_nota = $nota_credito_params->venta_motivo_nota;
        $vent_venta_nota_codigo = $nota_credito_params->notacredito_motivo_id;
        $productos = $nota_credito_params->productos;
        $vent_pago_pago = $nota_credito_params->importe;
        $vent_venta_tipo_comprobante_referenciado = $nota_credito_params->venta_comprobante_referenciado_codigo;
        Ventas::registrar_nota_credito(auth()->user()->id, $vent_venta_total,
            $vent_venta_igv,
            $vent_venta_bi,
            $vent_venta_cliente_numero_documento,
            $vent_venta_almacen_id,
            $vent_venta_tipo_comprobante_codigo,
            $productos,
            $vent_pago_pago,
            $vent_venta_comprobante_referenciado,
            $vent_venta_motivo_nota, $vent_venta_nota_codigo, $vent_venta_tipo_comprobante_referenciado);
        $jResponse['success'] = true;
        return response()->json($jResponse, 200);


    }


    public function exportar_exel_lista_ventas_usuario(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');
        $tipo_documento = $request->input('tipo_documento');
        $almacen = $request->input('almacen');
        $ventas = Ventas::listar_ventas_usuario_por_dia(auth()->user()->id, $fecha_inicio, $fecha_fin, $almacen, $tipo_documento);
        foreach ($ventas as $venta) {
            $data[] = array(
                "Serie" => $venta->vent_venta_serie,
                "Numero Venta" => $venta->vent_venta_numero,
                "Comprobante" => $venta->doc_tipo_comprobante_nombre,
                "Cliente Numero Documento" => $venta->vent_venta_cliente_numero_documento,
                "Cliente" => $venta->cliente,
                "Cliente Dirección" => $venta->cliente_direccion,
                "Fecha" => $venta->vent_venta_fecha,
                "IGV" => $venta->vent_venta_igv,
                "Base Imponible" => $venta->vent_venta_bi,
                "Total" => $venta->vent_venta_total,
                "Descuento" => $venta->vent_venta_precio_descuento_total,
                "Total Cobrado" => $venta->vent_venta_precio_cobrado,
                "Estado Enviado Sunat" => $venta->vent_venta_estado_envio_sunat,
            );
        }
        return Excel::create('ventas_usuario', function ($excel) use ($data) {
            $excel->sheet('Sheet', function ($sheet) use ($data) {
                $sheet->cells('A1:M1', function ($cells) {
                    $cells->setBackground('#204694');
                    $cells->setFontColor('#ffffff');
                    $cells->setFontFamily('roboto');
                    $cells->setFontSize(12);
                    $cells->setFontWeight('bold');
                });
                $sheet->fromArray($data);
            });
        })->export('xls');
    }

    public function exportar_exel_lista_ventas_general(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');
        $almacen = $request->input('almacen');
        $tipo_documento = $request->input('tipo_documento');
        $distribuidor = $request->input('distribuidor');
        $ventas = Ventas::listar_ventas_general_por_fecha_dia($fecha_inicio, $fecha_fin, $almacen, $tipo_documento, $distribuidor);
        $ventas_reporte = Ventas::listar_resumen_venta_por_dia($fecha_inicio, $fecha_fin, $almacen, $tipo_documento, $distribuidor);

        foreach ($ventas as $key => $venta) {
            $venta->detalle = Ventas::listar_venta_detalle($venta->vent_venta_id);
        }
        $dataVenta = new \stdClass();
        $dataVenta->ventas = $ventas;
        $dataVenta->total = $ventas_reporte;
        Excel::create('Laravel Excel', function ($excel) use ($dataVenta) {
            $excel->sheet('Excel sheet', function ($sheet) use ($dataVenta) {
                $sheet->loadView('exports.ventas')->with('data', $dataVenta);
                $sheet->setOrientation('landscape');
            });

        })->export('xls');
#Usi
        // $userExport = new VentasExport($ventas);
        //return Excel::download($userExport, 'users.xlsx');

        /*foreach ($ventas as $venta) {
            $data[] = array(
                "Serie" => $venta->vent_venta_serie,
                "Numero Venta" => $venta->vent_venta_numero,
                "Comprobante" => $venta->doc_tipo_comprobante_nombre,
                "Cliente Numero Documento" => $venta->vent_venta_cliente_numero_documento,
                "Cliente" => $venta->cliente,
                "Cliente Dirección" => $venta->cliente_direccion,
                "Fecha" => $venta->vent_venta_fecha,
                "IGV" => $venta->vent_venta_igv,
                "Base Imponible" => $venta->vent_venta_bi,
                "Total" => $venta->vent_venta_total,
                "Descuento" => $venta->vent_venta_precio_descuento_total,
                "Total Cobrado" => $venta->vent_venta_precio_cobrado,
                "Estado Enviado Sunat" => $venta->vent_venta_estado_envio_sunat,
                "Cajero" => $venta->email,
                "Desc" => $venta->vent_venta_descripcion,
            );
        }
        return Excel::create('ventas_usuario', function ($excel) use ($data) {
            $excel->sheet('Sheet', function ($sheet) use ($data) {
                $sheet->cells('A1:O1', function ($cells) {
                    $cells->setBackground('#204694');
                    $cells->setFontColor('#ffffff');
                    $cells->setFontFamily('roboto');
                    $cells->setFontSize(12);
                    $cells->setFontWeight('bold');
                });
                $sheet->fromArray($data);
            });
        })->export('xls');*/
    }


    public function exportar_pdf_lista_ventas_general(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');
        $almacen = $request->input('almacen');
        $tipo_documento = $request->input('tipo_documento');
        $distribuidor = $request->input('distribuidor');
        $ventas = Ventas::listar_ventas_general_por_fecha_dia($fecha_inicio, $fecha_fin, $almacen, $tipo_documento, $distribuidor);
        $ventas_reporte = Ventas::listar_resumen_venta_por_dia($fecha_inicio, $fecha_fin, $almacen, $tipo_documento, $distribuidor);
        $dataVenta = new \stdClass();
        $dataVenta->ventas = $ventas;
        $dataVenta->total = $ventas_reporte;
        return $this->generateVentasGeneralPdf($dataVenta);
    }

    public function generateVentasGeneralPdf($venta)
    {
        $data = $venta;
        $pdf = DOMPDF::loadView('pdf.' . 'ventas', compact('data'));
        return $pdf->stream('ventas' . '.pdf');
    }


    public function exportar_pdf_lista_ventas_detallado(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');
        $almacen = $request->input('almacen');
        $tipo_documento = $request->input('tipo_documento');
        $distribuidor = $request->input('distribuidor');
        $ventas = Ventas::listar_ventas_general_por_fecha_dia($fecha_inicio, $fecha_fin, $almacen, $tipo_documento, $distribuidor);
        $ventas_reporte = Ventas::listar_resumen_venta_por_dia($fecha_inicio, $fecha_fin, $almacen, $tipo_documento, $distribuidor);

        foreach ($ventas as $key => $venta) {
            $venta->detalle = Ventas::listar_venta_detalle($venta->vent_venta_id);
        }
        $dataVenta = new \stdClass();
        $dataVenta->ventas = $ventas;
        $dataVenta->total = $ventas_reporte;

        return $this->generateVentasDetalldoPdf($dataVenta);
    }


    public function generateVentasDetalldoPdf($dataVenta)
    {
        $data = $dataVenta;
        $pdf = DOMPDF::loadView('pdf.' . 'ventas_detallado', compact('data'));
        return $pdf->stream('ventas_detallado' . '.pdf');
    }

    /** Reporte de ventas general por dia */
    public function listar_ventas_general_por_dia(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');
        $almacen = $request->input('almacen');
        $tipo_documento = $request->input('tipo_documento');
        $distribuidor = $request->input('distribuidor');
        $ventas_reporte = Ventas::listar_resumen_venta_por_dia($fecha_inicio, $fecha_fin, $almacen, $tipo_documento, $distribuidor);
        $ventas = Ventas::listar_ventas_general_por_fecha_dia($fecha_inicio, $fecha_fin, $almacen, $tipo_documento, $distribuidor);
        foreach ($ventas as $key => $venta) {
            $venta->detalle = Ventas::listar_venta_detalle($venta->vent_venta_id);
        }
        $jResponse['success'] = true;
        $jResponse['data'] = ['data' => $ventas, 'total' => $ventas_reporte];
        $jResponse['success'] = true;
        return response()->json($jResponse, 200);
    }

    public function reporte_ventas_por_mes_usuario(Request $request)
    {
        $mes = $request->input('mes');
        $anio = $request->input('anio');
        $almacen = $request->input('almacen');
        $ventas_mes_usuario = Ventas::reporte_ventas_por_mes_usuario($almacen, auth()->user()->id, $mes, $anio);
        $total_ventas_mes_usuario = Ventas::total_reporte_ventas_por_mes_usuario($almacen, auth()->user()->id, $mes, $anio);
        $jResponse['success'] = true;
        $jResponse['data'] = ['data' => $ventas_mes_usuario, 'total' => $total_ventas_mes_usuario[0]];

        return response()->json($jResponse, 200);
    }


    public function reporte_ventas_por_mes(Request $request)
    {
        $mes = $request->input('mes');
        $anio = $request->input('anio');
        $almacen = $request->input('almacen');
        $ventas_mes_usuario = Ventas::reporte_ventas_por_mes($almacen, $mes, $anio);
        $total_ventas_mes_usuario = Ventas::total_reporte_ventas_por_mes($almacen, $mes, $anio);
        $jResponse['success'] = true;
        $jResponse['data'] = ['data' => $ventas_mes_usuario, 'total' => $total_ventas_mes_usuario[0]];

        return response()->json($jResponse, 200);
    }


    public function comprobante(Request $request)
    {
        $serie = $request->input('serie');
        $numero_venta = $request->input('numero_venta');
        $venta = Ventas::comprobante($serie, $numero_venta);
        $venta->detalle = Ventas::comprobante_detalle($venta->vent_venta_id);
        $jResponse['success'] = true;
        $jResponse['data'] = $venta;
        return response()->json($jResponse, 200);
    }

    public function listar_ventas_con_fise_por_fecha_dia(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');
        $almacen = $request->input('almacen');
        $tipo_documento = $request->input('tipo_documento');
        $distribuidor = $request->input('distribuidor');
        $ventas_fise = Ventas::listar_ventas_con_fise_por_fecha_dia($fecha_inicio, $fecha_fin, $almacen,
            $tipo_documento, $distribuidor);
        $detalle = Ventas::listar_resumen_venta_fise_por_dia($fecha_inicio, $fecha_fin, $almacen,
            $tipo_documento, $distribuidor);
        $jResponse['success'] = true;
        $jResponse['data'] = ['data' => $ventas_fise, 'total' => $detalle];
        return response()->json($jResponse, 200);
    }

    public function reporte_ventas(Request $request)
    {
        $condicional_tipo_comprobante = !is_null($request->input('vent_venta_tipo_comprobante_id')) ? '=' : '<>';
        $condicional_serie = !is_null($request->input('vent_venta_serie')) ? '=' : '<>';
        $condicional_numero = !is_null($request->input('vent_venta_numero')) ? '=' : '<>';
        $condicional_cliente = !is_null($request->input('cliente_cliente_id')) ? '=' : '<>';
        if (!is_null($request->input('vent_venta_fecha_desde')) &&
            !is_null($request->input('vent_venta_fecha_hasta'))) {
            $condicional_fecha = [$request->input('vent_venta_fecha_desde'), $request->input('vent_venta_fecha_hasta')];
        } else {
            $condicional_fecha = [date('Y-m-d'), date('Y-m-d')];
        }
        $ventas = Venta::select(
            'vent_venta.vent_venta_id',
            'vent_venta.vent_venta_serie',
            'vent_venta.vent_venta_numero',
            'vent_venta.vent_venta_estado',
            'vent_venta.vent_venta_total',
            'vent_venta.vent_venta_bi',
            'vent_venta.vent_venta_igv',
            'vent_venta.vent_venta_estado_envio_sunat',
            'vent_venta.vent_venta_cliente_numero_documento',
            'vent_venta.vent_venta_fecha',
            'vent_venta.vent_venta_confirmado',
            'doc_tipo_comprabante.doc_tipo_comprobante_codigo',
            'seg_cliente.seg_cliente_tipo_documento',
            'seg_cliente.seg_cliente_razon_social',
            'vent_venta.vent_venta_fecha_registro', 'doc_tipo_comprabante.doc_tipo_comprobante_nombre')
            ->leftJoin('seg_cliente', 'vent_venta.vent_venta_cliente_id', 'seg_cliente.seg_cliente_id')
            ->leftJoin('doc_tipo_comprabante', 'vent_venta.vent_venta_tipo_comprobante_id', 'doc_tipo_comprabante.doc_tipo_comprobante_id')
            ->where('doc_tipo_comprabante.doc_tipo_comprobante_id', $condicional_tipo_comprobante, is_null($request->input('vent_venta_tipo_comprobante_id')) ? null : TipoComprobante::select('doc_tipo_comprobante_id')->where('doc_tipo_comprobante_codigo', $request->input('vent_venta_tipo_comprobante_id'))->first()->doc_tipo_comprobante_id)
            ->where('vent_venta.vent_venta_serie', $condicional_serie, is_null($request->input('vent_venta_serie')) ? null : $request->input('vent_venta_serie'))
            ->where('vent_venta.vent_venta_numero', $condicional_numero, is_null($request->input('vent_venta_numero')) ? null : $request->input('vent_venta_numero'))
            ->where('vent_venta.vent_venta_cliente_id', $condicional_cliente, is_null($request->input('cliente_cliente_id')) ? null : $request->input('cliente_cliente_id'))
            ->whereIn('vent_venta.vent_venta_tipo_comprobante_id', TipoComprobante::select('doc_tipo_comprobante_id')->whereIn('doc_tipo_comprobante_codigo', array('03', '01', '99', '07'))->get())
            ->orderBy('vent_venta.vent_venta_fecha', 'vent_venta.vent_venta_numero', 'DESC')
            ->whereBetween('vent_venta.vent_venta_fecha', $condicional_fecha)
            ->get();
        return response()->json(['success' => true,
            'data' => $ventas,
            'message' => 'Lista de Almacenes'], 200);
    }

    public function consulta_web(Request $request)
    {
        $venta = Venta::where('vent_venta_serie', $request->input('vent_venta_serie'))
            ->where('vent_venta_numero', $request->input('vent_venta_numero_documento'))
            ->where('vent_venta_total', $request->input('vent_venta_importe_bruto'))
            ->where('vent_venta_cliente_numero_documento', $request->input('vent_venta_cliente_numero_documento'))
            ->where('vent_venta_tipo_comprobante_id', TipoComprobante::select('doc_tipo_comprobante_id')->where('doc_tipo_comprobante_codigo', $request->input('vent_venta_tipo_doc_codigo'))->first()->doc_tipo_comprobante_id)
            ->first();


        $venta->serie_comprobante = $venta->vent_venta_serie;
        $venta->numero_comprobante = $venta->vent_venta_numero;
        $venta->fecha_comprobante = $venta->vent_venta_fecha;
        $venta->codmoneda_comprobante = "PEN";
        $cliente = Cliente::find($venta->vent_venta_cliente_id);
        $venta->cliente_tipodocumento = $cliente->seg_cliente_tipo_documento;
        $venta->cliente_numerodocumento = $cliente->seg_cliente_numero_doc;
        $venta->cliente_nombre = $cliente->seg_cliente_razon_social;
        $venta->cliente_pais = "PE";
        $venta->cliente_ciudad = "";


        $venta->cliente_direccion = $cliente->seg_cliente_direccion;
        $venta->correo_electronico = $cliente->seg_cliente_email;
        $venta->txt_subtotal_comprobante = $venta->vent_venta_bi;
        $venta->txt_igv_comprobante = $venta->vent_venta_igv;
        $venta->txt_total_comprobante = $venta->vent_venta_total;
        $venta->txt_total_letras = $venta->vent_venta_precio_cobrado_letras;
        $tipo_comprobante = TipoComprobante::find($venta->vent_venta_tipo_comprobante_id);
        $venta->tipo_comprobante = $tipo_comprobante->doc_tipo_comprobante_codigo;
        $venta->doc_tipo_comprobante_nombre = strtoupper($tipo_comprobante->doc_tipo_comprobante_nombre);
        $almacen = Almacen::find($venta->vent_venta_almacen_id);
        $venta->alm_almacen_email = $almacen->alm_almacen_email;
        $venta->alm_almacen_telefono = $almacen->alm_almacen_telefono;
        $venta->alm_almacen_direccion = $almacen->alm_almacen_direccion;
        $venta->cliente = $cliente->seg_cliente_razon_social;
        $venta->cliente_direccion = $cliente->seg_cliente_direccion;
        $lista_detalle = array();
        foreach (VentaDetalle::where('vent_venta_detalle_venta_id', $venta->vent_venta_id)
                     ->orderBy('vent_venta_detalle_item')
                     ->get() as $item) {
            $item->ITEM_DET = $item->vent_venta_detalle_item;
            $producto = Producto::find($item->vent_venta_detalle_producto_id);
            $item->UNIDAD_MEDIDA_DET = $producto->alm_unidad_medida_id;
            $item->CANTIDAD_DET = $item->vent_venta_detalle_cantidad;
            // $item->PRECIO_DET = floatval($item->vent_venta_detalle_bi) / floatval($item->vent_venta_detalle_cantidad);
            // $item->SUB_TOTAL_DET = floatval($item->vent_venta_detalle_precio) / floatval($item->vent_venta_detalle_cantidad);

            $item->PRECIO_DET = round(floatval($item->vent_venta_detalle_bi) / floatval($item->vent_venta_detalle_cantidad) * 100) / 100;
            $item->SUB_TOTAL_DET = round(floatval($item->vent_venta_detalle_precio) / floatval($item->vent_venta_detalle_cantidad) * 100) / 100;
            $item->PRECIO_TIPO_CODIGO = "01";
            $item->IGV_DET = $item->vent_venta_detalle_igv;
            $item->ISC_DET = "0";
            $item->IMPORTE_DET = $item->vent_venta_detalle_bi;
            $item->COD_TIPO_OPERACION_DET = $item->vent_venta_detalle_tipo_operacion;
            $item->DESCRIPCION_DET = !is_null($item->vent_venta_detalle_serie) && $item->vent_venta_detalle_serie != "" ? $producto->alm_producto_nombre . ' SN: ' . $item->vent_venta_detalle_serie : $producto->alm_producto_nombre;
            $item->CODIGO_DET = $producto->alm_producto_codigo;
            $item->PRECIO_SIN_IGV_DET = $item->vent_venta_detalle_bi;
            $item->ITEM_DET = $item->vent_venta_detalle_item;
            $item->alm_unidad_medida_id = $producto->alm_unidad_medida_id;
            $item->alm_producto_nombre = $producto->alm_producto_nombre;
            $item->alm_producto_marca = $producto->alm_producto_marca;
            $item->vent_venta_detalle_precio_cobro = $item->PRECIO_DET;
            array_push($lista_detalle, $item);
        }
        $venta->detalle = $lista_detalle;
        $venta_totales = Ventas::listar_totales_comprobante($venta->vent_venta_id);
        $venta->totales = $venta_totales;

        $data = $venta;
        $empresa = Empresa::all()->first();
        $proceso = "";
        if ($empresa->emp_empresa_tipoproceso == "1") {
            $proceso = "produccion";
        } else if ($empresa->emp_empresa_tipoproceso == "3") {
            $proceso = "beta";
        }
        $data->empresa = $empresa;
        $base_url_comprobante = realpath(__DIR__ . '/../../../../../') . '/comprobantes/';
        if (substr($venta->serie_comprobante, 0, 2) == 'B0') {
            if ($venta->vent_venta_tipo_venta == "03") {
                DOMPDF::loadView('pdf.' . 'comprobante_boleta_vehiculo', compact('data'))->save($base_url_comprobante . 'comprobantes_pdf/' . $proceso . '/boletas/' . $venta->serie_comprobante . '-' . $venta->numero_comprobante . '.pdf');

            } else {
                DOMPDF::loadView('pdf.' . 'comprobante_boleta', compact('data'))->save($base_url_comprobante . 'comprobantes_pdf/' . $proceso . '/boletas/' . $venta->serie_comprobante . '-' . $venta->numero_comprobante . '.pdf');

            }
            $data = [
                $base_url_comprobante . 'cpe_xml/' . $proceso . '/boletas/' . $venta->vent_venta_ruta_xml,
                $base_url_comprobante . 'cpe_xml/' . $proceso . '/boletas/' . $venta->vent_venta_ruta_cdr,
                $base_url_comprobante . 'comprobantes_pdf/' . $proceso . '/boletas/' . $venta->serie_comprobante . '-' . $venta->numero_comprobante . '.pdf',
            ];
        } else if (substr($venta->serie_comprobante, 0, 2) == 'F0') {
            if ($venta->vent_venta_tipo_venta == "03") {
                DOMPDF::loadView('pdf.' . 'comprobante_factura_vehiculo', compact('data'))->save($base_url_comprobante . 'comprobantes_pdf/' . $proceso . '/facturas/' . $venta->serie_comprobante . '-' . $venta->numero_comprobante . '.pdf');

            } else {
                DOMPDF::loadView('pdf.' . 'comprobante_factura', compact('data'))->save($base_url_comprobante . 'comprobantes_pdf/' . $proceso . '/facturas/' . $venta->serie_comprobante . '-' . $venta->numero_comprobante . '.pdf');

            }
            $data = [
                $base_url_comprobante . 'cpe_xml/' . $proceso . '/facturas/' . $venta->vent_venta_ruta_xml,
                $base_url_comprobante . 'cpe_xml/' . $proceso . '/facturas/' . $venta->vent_venta_ruta_cdr,
                $base_url_comprobante . 'comprobantes_pdf/' . $proceso . '/facturas/' . $venta->serie_comprobante . '-' . $venta->numero_comprobante . '.pdf',
            ];
        }

        $zip = new ZipArchive();
        $filename = $base_url_comprobante . $venta->vent_venta_serie . '-' . $venta->vent_venta_numero . '.zip';
        if ($venta->vent_venta_estado_envio_sunat && $zip->open($filename, ZIPARCHIVE::CREATE) === true) {
            $zip->addFile($data[0], $venta->vent_venta_ruta_xml);
            $zip->addFile($data[1], $venta->vent_venta_ruta_cdr);
            $zip->addFile($data[2], $venta->vent_venta_serie . '-' . $venta->vent_venta_numero . '.pdf'); //ORIGEN, DESTINO
            $zip->close();
        } elseif ($zip->open($filename, ZIPARCHIVE::CREATE) === true) {
            $zip->addFile($data[2], $venta->vent_venta_serie . '-' . $venta->vent_venta_numero . '.pdf'); //ORIGEN, DESTINO
            $zip->close();
        }
        return \response()->download($filename);
    }

    public function borrar_zip(Request $request)
    {
        $venta = Venta::where('vent_venta_serie', $request->input('vent_venta_serie'))
            ->where('vent_venta_numero', $request->input('vent_venta_numero_documento'))
            ->where('vent_venta_total', $request->input('vent_venta_importe_bruto'))
            ->where('vent_venta_cliente_numero_documento', $request->input('vent_venta_cliente_numero_documento'))
            ->where('vent_venta_tipo_comprobante_id', TipoComprobante::select('doc_tipo_comprobante_id')->where('doc_tipo_comprobante_codigo', $request->input('vent_venta_tipo_doc_codigo'))->first()->doc_tipo_comprobante_id)
            ->first();
        $base_url_comprobante = realpath(__DIR__ . '/../../../../../') . '/comprobantes/';
        $filename = $base_url_comprobante . $venta->vent_venta_serie . '-' . $venta->vent_venta_numero . '.zip';
        unlink($filename);
        return response()->json(['success' => true,
            'data' => 'Consulta Correcta',
            'message' => 'Zip Borrado'], 200);


    }

    public function listar_ventas_avance(Request $request)
    {
        $vent_venta_fecha_desde = $request->input('vent_venta_fecha_desde');
        $vent_venta_fecha_hasta = $request->input('vent_venta_fecha_hasta');
        if (!is_null($vent_venta_fecha_desde) && !is_null($vent_venta_fecha_hasta)) {
            $vent_venta_fecha_desde = $request->input('vent_venta_fecha_desde');
            $vent_venta_fecha_hasta = $request->input('vent_venta_fecha_hasta');
        } else {
            $vent_venta_fecha_desde = date('Y-m-d');
            $vent_venta_fecha_hasta = date('Y-m-d');
        }
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Venta::listar_ventas_avance($request->input('categoria_id'), $vent_venta_fecha_desde, $vent_venta_fecha_hasta, $request->input('almacen_id')), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista Avance de ventas'], 200);
    }

    public function listar_venta_utilidad(Request $request)
    {
        $vent_venta_fecha_desde = $request->input('vent_venta_fecha_desde');
        $vent_venta_fecha_hasta = $request->input('vent_venta_fecha_hasta');
        if (!is_null($vent_venta_fecha_desde) && !is_null($vent_venta_fecha_hasta)) {
            $vent_venta_fecha_desde = $request->input('vent_venta_fecha_desde');
            $vent_venta_fecha_hasta = $request->input('vent_venta_fecha_hasta');
        } else {
            $vent_venta_fecha_desde = date('Y-m-d');
            $vent_venta_fecha_hasta = date('Y-m-d');
        }
        $totales = new \stdClass();
        $totales->cantidad = 0.00;
        $totales->compra = 0.00;
        $totales->venta = 0.00;
        $totales->diferencia = 0.00;

        $lista = [];
        foreach (Venta::listar_venta_utilidad($vent_venta_fecha_desde, $vent_venta_fecha_hasta, $request->input('data'), $request->input('categoria_id'), $request->input('almacen_id')) as $item) {
            $totales->cantidad = $totales->cantidad + $item->vent_venta_detalle_cantidad;
            $item->diferencia_precios = round( ($item->vent_venta_detalle_precio_unitario - $item->comp_compra_detalle_precio_unitario) * 100) / 100;
            $totales->compra= $totales->compra+$item->comp_compra_detalle_precio_unitario;
            $totales->venta= $totales->venta+$item->vent_venta_detalle_precio_unitario;
            $totales->diferencia= round(($totales->diferencia+$item->diferencia_precios) * 100) / 100;
            array_push($lista, $item);
        };

        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru($lista, $request->input('ver_por_pagina'), $request),
            'dataTotal' => $totales,
            'message' => 'Lista Avance de ventas'], 200);
    }
}

