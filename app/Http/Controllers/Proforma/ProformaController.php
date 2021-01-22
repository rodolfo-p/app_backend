<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 25/02/19
 * Time: 03:29 PM
 */

namespace App\Http\Controllers\Proforma;

use App\Http\Controllers\Controller;
use App\Http\Data\Proforma\Proforma;
use App\Http\Data\util\GeneraNumero;
use App\Http\Data\util\IdGenerador;
use App\Http\Data\util\Pagination;
use App\Models\Almacen\Almacen;
use App\Models\Almacen\Distribuidor;
use App\Models\Almacen\ListaPrecioDetalle;
use App\Models\Almacen\Producto;
use App\Models\Configuracion\Empresa;
use App\Models\Configuracion\Periodo;
use App\Models\Configuracion\TipoComprobante;
use App\Models\Venta\Cliente;
use App\Models\Venta\FechaPago;
use App\Models\Venta\Venta;
use App\Models\Venta\VentaDetalle;
use App\Models\Venta\VentaPago;
use Illuminate\Http\Request;

use App\Http\Controllers\Ventas;
use Exception;
use NumeroALetras\NumeroALetras;
use PDF;
use DOMPDF;

class ProformaController extends Controller
{

    public function listar_proformas(Request $request)
    {
        $condicional_tipo_comprobante = !is_null($request->input('vent_venta_tipo_comprobante_id')) ? '=' : '<>';
        $condicional_serie = !is_null($request->input('vent_venta_serie')) ? '=' : '<>';
        $condicional_numero = !is_null($request->input('vent_venta_numero')) ? '=' : '<>';
        $condicional_cliente = !is_null($request->input('cliente_cliente_id')) ? '=' : '<>';
        if (!is_null($request->input('vent_venta_fecha_desde')) &&
            !is_null($request->input('vent_venta_fecha_hasta'))) {
            $condicional_fecha = [$request->input('vent_venta_fecha_desde'), $request->input('vent_venta_fecha_hasta')];
        } else {
            $condicional_fecha = ['2000-01-01', '2060-10-30'];
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
            ->where('doc_tipo_comprabante.doc_tipo_comprobante_id', $condicional_tipo_comprobante, is_null($request->input('vent_venta_tipo_comprobante_id')) ? null : $request->input('vent_venta_tipo_comprobante_id'))
            ->where('vent_venta.vent_venta_serie', $condicional_serie, is_null($request->input('vent_venta_serie')) ? null : $request->input('vent_venta_serie'))
            ->where('vent_venta.vent_venta_numero', $condicional_numero, is_null($request->input('vent_venta_numero')) ? null : $request->input('vent_venta_numero'))
            ->where('vent_venta.vent_venta_cliente_id', $condicional_cliente, is_null($request->input('cliente_cliente_id')) ? null : $request->input('cliente_cliente_id'))
            ->whereIn('vent_venta.vent_venta_tipo_comprobante_id',TipoComprobante::select('doc_tipo_comprobante_id')->whereIn('doc_tipo_comprobante_codigo',['88'])->get())
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
        $venta->almacen=Almacen::find($venta->vent_venta_almacen_id);
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
            $item->vent_venta_detalle_producto = Producto::find($producto->Parent_alm_producto_id)->alm_producto_nombre . ' ' . $producto->alm_producto_nombre . ' ' . $producto->alm_producto_marca . ' ' . $producto->alm_producto_modelo;
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

    public function listar_proforma($id)
    {
        return response()->json(['success' => true,
            'data' => $this->venta_data($id),
            'message' => 'Lista de Almacenes'], 200);
    }


    public function registrar_proforma(Request $request)
    {
        $venta = new Venta($request->all());
        $vent_venta_id = IdGenerador::generaId();
        $venta->vent_venta_id = $vent_venta_id;
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
        $venta->vent_venta_numero = 00000000;
        if ($venta->vent_venta_confirmado) {
            $venta->vent_venta_numero = GeneraNumero::genera_numero_venta($venta->vent_venta_serie);
            $empresa = Empresa::all()->first();
            $venta->vent_venta_qr = $empresa->emp_empresa_ruc .
                '|' . $venta->vent_venta_tipo_comprobante_id .
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
        }

        return response()->json(['success' => true,
            'data' => $this->venta_data($vent_venta_id),
            'message' => 'Lista de Almacenes'], 200);
    }


    public function actualizar_proforma(Request $request, $id)
    {
        $venta = new Venta($request->all());
        $venta->vent_venta_tipo_comprobante_id = TipoComprobante::where('doc_tipo_comprobante_codigo', $venta->vent_venta_tipo_comprobante_id)->first()->doc_tipo_comprobante_id;
        $venta->vent_venta_tipo_venta = $request->input('vent_pago_tipo_pago');
        $venta->vent_venta_estado = true;
        $periodo = Periodo::where('cont_periodo_estado', true)->first();
        $venta->vent_venta_periodo_id = $periodo->cont_periodo_id;
        $venta->vent_venta_user_id = auth()->user()->id;
        $venta->vent_venta_precio_cobrado_letras = NumeroALetras::convertir($venta->vent_venta_total);
        $venta->vent_venta_estado_pago = $request->input('vent_pago_importe') >= $venta->vent_venta_total ? true : false;
        $venta->vent_venta_tipo_venta = $request->input('vent_pago_tipo_pago');
        $venta->vent_venta_numero = 00000000;
        if ($venta->vent_venta_confirmado) {
            $venta->vent_venta_numero = GeneraNumero::genera_numero_venta($venta->vent_venta_serie);
            $empresa = Empresa::all()->first();
            $venta->vent_venta_qr = $empresa->emp_empresa_ruc .
                '|' . $venta->vent_venta_tipo_comprobante_id .
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
        }

        return response()->json(['success' => true,
            'data' => $this->venta_data($id),
            'message' => 'Lista de Almacenes'], 200);
    }

















































    /*public function listar_proforma(Request $request)
    {
        $almacen = $request->input('almacen');
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');
        $proforma_list = Proforma::listar_proforma($almacen, $fecha_inicio, $fecha_fin);
        foreach ($proforma_list as $key => $proforma) {
            $proforma->detalle = Proforma::listar_proforma_detalle($proforma->vent_proforma_id);
        }
        $jResponse['success'] = true;
        $jResponse['data'] = $proforma_list;
        return response()->json($jResponse, 200);
    }*/


    public function insertar_proforma()
    {
        $pedidos_params = json_decode(file_get_contents("php://input"));
        $vent_proforma_total = $pedidos_params->vent_proforma_total;
        $vent_proforma_bi = $pedidos_params->vent_proforma_bi;
        $vent_proforma_igv = $pedidos_params->vent_proforma_igv;
        $vent_proforma_almacen_id = $pedidos_params->vent_proforma_almacen_id;
        $vent_proforma_cliente_numero_documento = $pedidos_params->vent_proforma_cliente_numero_documento;
        $productos = $pedidos_params->productos;
        $proforma = Proforma::registrar_proforma($vent_proforma_total,
            $vent_proforma_bi,
            $vent_proforma_igv,
            auth()->user()->id,
            $vent_proforma_almacen_id,
            $vent_proforma_cliente_numero_documento,
            $productos);
        $empresa = Empresa::empresa();

        if ($empresa->emp_empresa_formato_doc_imp == "03") {
            return $this->generatePdfA4Proforma($proforma);
        } elseif ($empresa->emp_empresa_formato_doc_imp == "01") {
            return $this->generatePdfTicketProforma($proforma);
        }


    }

    public function generatePdfTicketProforma($proforma)
    {
        $paper_size = array(0, 0, 220, 600);
        $data = $proforma;
        $pdf = DOMPDF::loadView('pdf.' . 'ticket_proforma', compact('data'))->setPaper($paper_size);
        return $pdf->stream('proforma' . '.pdf');
    }

    public function generatePdfA4Proforma($proforma)
    {
        $data = $proforma;
        $pdf = DOMPDF::loadView('pdf.' . 'proforma', compact('data'));
        return $pdf->stream('proforma' . '.pdf');
    }

    public function descargar_proforma($id)
    {
        $proforma = Proforma::listar_proforma_por_id($id);
        $empresa = Empresa::empresa();

        if ($empresa->emp_empresa_formato_doc_imp == "03") {
            return $this->generatePdfA4Proforma($proforma);
        } elseif ($empresa->emp_empresa_formato_doc_imp == "01") {
            return $this->generatePdfTicketProforma($proforma);
        }
    }

    public function cambiar_estado_proforma($id)
    {
        $proforma_list = Proforma::cambiar_estado_proforma($id);
        $jResponse['success'] = true;
        $jResponse['data'] = $proforma_list;
        return response()->json($jResponse, 200);

    }


    public function generar_venta()
    {
        $pedido_venta_params = json_decode(file_get_contents("php://input"));
        $precioTotal = $pedido_venta_params->precioTotal;
        $igvTotal = $pedido_venta_params->igvTotal;
        $baseImpobleTotal = $pedido_venta_params->baseImpobleTotal;
        $cliente = $pedido_venta_params->cliente;
        $almacen = $pedido_venta_params->almacen;
        $tipoComprobante = $pedido_venta_params->tipoComprobante;
        $vent_pago_pago = $pedido_venta_params->vent_pago_pago;
        $importe = $pedido_venta_params->importe;
        $modalidad = $pedido_venta_params->modalidad;
        $numeroTranccion = $pedido_venta_params->numeroTranccion;
        $tipoPago = $pedido_venta_params->tipoPago;
        $vuelto = $pedido_venta_params->vuelto;
        $productos = $pedido_venta_params->productos;
        $proforma_id = $pedido_venta_params->proforma_id;
        $venta = Proforma::registrar_proforma_venta($proforma_id, $precioTotal, $igvTotal,
            $baseImpobleTotal, $cliente,
            $almacen, $tipoComprobante,
            $vent_pago_pago, $importe,
            $modalidad, $numeroTranccion, $tipoPago, $vuelto, $productos, auth()->user()->id);
        $empresa = Empresa::empresa();
        if ($empresa->emp_empresa_formato_doc_imp == '01' && $tipoComprobante == '03') {
            return $this->generatePdfTicketBoleta($venta);
        } else if ($empresa->emp_empresa_formato_doc_imp == '01' && $tipoComprobante == '01') {
            return $this->generatePdfTicketFactura($venta);
        } else if ($empresa->emp_empresa_formato_doc_imp == '02' && $tipoComprobante == '01') {
            return $this->generateFacturaA5Pdf($venta);
        } else if ($empresa->emp_empresa_formato_doc_imp == '02' && $tipoComprobante == '03') {
            return $this->generateBoletaA5Pdf($venta);
        } else if ($empresa->emp_empresa_formato_doc_imp == '02' && $tipoComprobante == '99') {
            return $this->generateNotaVentaA5pdf($venta);
        } else if ($empresa->emp_empresa_formato_doc_imp == '03' && $tipoComprobante == '01') {
            return $this->generateFacturaA4Pdf($venta);
        } else if ($empresa->emp_empresa_formato_doc_imp == '03' && $tipoComprobante == '03') {
            return $this->generateBoletaA4Pdf($venta);
        } else if ($empresa->emp_empresa_formato_doc_imp == '03' && $tipoComprobante == '99') {
            return $this->generateNotaVentaA4Pdf($venta);
        } else if ($empresa->emp_empresa_formato_doc_imp == '01' && $tipoComprobante == '99') {
            return $this->generatePdfTicketNotaVenta($venta);
        }
    }

    public function generateBoletaA5Pdf($venta)
    {
        $data = $venta;
        $pdf = DOMPDF::loadView('pdf.' . 'comprobante_boleta_a5', compact('data'))->setPaper('a5');
        return $pdf->stream('comprobante' . '.pdf');
    }

    public function generateFacturaA5Pdf($venta)
    {
        $data = $venta;
        $pdf = DOMPDF::loadView('pdf.' . 'comprobante_factura_a5', compact('data'))->setPaper('a5');
        return $pdf->stream('comprobante' . '.pdf');
    }

    public function generateNotaVentaA5pdf($venta)
    {
        $data = $venta;
        $pdf = DOMPDF::loadView('pdf.' . 'comprobante_nota_venta_a5', compact('data'))->setPaper('a5');
        return $pdf->stream('comprobante' . '.pdf');
    }


    public function generateBoletaA4Pdf($venta)
    {
        $data = $venta;
        $pdf = DOMPDF::loadView('pdf.' . 'comprobante_boleta', compact('data'));
        return $pdf->stream('comprobante' . '.pdf');
    }

    public function generateFacturaA4Pdf($venta)
    {
        $data = $venta;
        $pdf = DOMPDF::loadView('pdf.' . 'comprobante_factura', compact('data'));
        return $pdf->stream('comprobante' . '.pdf');
    }

    public function generateNotaVentaA4Pdf($venta)
    {
        $data = $venta;
        $pdf = DOMPDF::loadView('pdf.' . 'comprobante_nota_venta', compact('data'));
        return $pdf->stream('comprobante' . '.pdf');
    }

    public function generatePdfTicketBoleta($venta)
    {
        $paper_size = array(0, 0, 220, 600);
        $data = $venta;
        $pdf = DOMPDF::loadView('pdf.' . 'ticket_boleta', compact('data'))->setPaper($paper_size);
        return $pdf->stream('comprobante' . '.pdf');
    }

    public function generatePdfTicketNotaVenta($venta)
    {
        $paper_size = array(0, 0, 220, 600);
        $data = $venta;
        $pdf = DOMPDF::loadView('pdf.' . 'ticket_nota_venta', compact('data'))->setPaper($paper_size);
        return $pdf->stream('comprobante' . '.pdf');
    }


    public function generatePdfTicketFactura($venta)
    {
        $paper_size = array(0, 0, 220, 600);
        $data = $venta;
        $pdf = DOMPDF::loadView('pdf.' . 'ticket_factura', compact('data'))->setPaper($paper_size);
        return $pdf->stream('comprobante' . '.pdf');
    }


}
