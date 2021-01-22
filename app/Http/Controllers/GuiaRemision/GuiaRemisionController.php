<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 04/03/19
 * Time: 07:09 PM
 */

namespace App\Http\Controllers\GuiaRemision;


use App\Http\Controllers\Controller;
use App\Http\Controllers\FacturacionElectronica\Controllers\procesar_data;

use App\Http\Data\util\GeneraNumero;
use App\Http\Data\util\IdGenerador;
use App\Http\Data\util\Pagination;
use App\Models\Configuracion\Periodo;
use App\Models\Venta\GuiaRemision;
use App\Models\Venta\GuiaRemisionDetalle;
use Illuminate\Http\Request;

use App\Http\Data\Setup\Empresa;
use App\Http\Data\Ventas\Ventas;
use PDF;
use DOMPDF;

class GuiaRemisionController extends Controller
{


    public function enviar_guia_remicion(Request $request, $id)
    {
        $guia_remision_data = GuiaRemision::find($id);
        $guia_remision_data->detalle = GuiaRemisionDetalle::where('guia_remision_detalle_guia_remision_id', $id)->get();
        $guia_remision = new \stdClass();
        $guia_remision->serie_comprobante = $guia_remision_data->guia_remision_serie_comprobante;
        $guia_remision->numero_comprobante = $guia_remision_data->guia_remision_numero_comprobante;
        $guia_remision->fecha_comprobante = $guia_remision_data->guia_remision_fecha_comprobante;
        $guia_remision->cod_tipo_documento = $guia_remision_data->guia_remision_cod_tipo_documento;
        $guia_remision->nota = $guia_remision_data->guia_remision_nota;
        $guia_remision->codmotivo_traslado = $guia_remision_data->guia_remision_codmotivo_traslado;
        $guia_remision->motivo_traslado = $guia_remision_data->guia_remision_motivo_traslado;
        $guia_remision->peso = $guia_remision_data->guia_remision_peso;
        $guia_remision->numero_paquetes = $guia_remision_data->guia_remision_numero_paquetes;
        $guia_remision->codtipo_transportista = $guia_remision_data->guia_remision_codtipo_trasportista;
        $guia_remision->tipo_documento_transporte = $guia_remision_data->guia_remision_tipo_documento_transporte;
        $guia_remision->nro_documento_transporte = $guia_remision_data->guia_remision_nro_documento_transporte;
        $guia_remision->razon_social_transporte = $guia_remision_data->guia_remision_razon_social_tranporte;
        $guia_remision->ubigeo_destino = $guia_remision_data->guia_remision_ubigeo_destino;
        $guia_remision->dir_destino = $guia_remision_data->guia_remision_dir_destino;
        $guia_remision->ubigeo_partida = $guia_remision_data->guia_remision_ubigeo_partida;
        $guia_remision->dir_partida = $guia_remision_data->guia_remision_dir_partida;
        $guia_remision->dir_partida_dep = Empresa::listar_nombre_departamento(substr($guia_remision->ubigeo_partida, 0, 2));
        $guia_remision->dir_partida_prov = Empresa::listar_nombre_provincia(substr($guia_remision->ubigeo_partida, 0, 4));
        $guia_remision->dir_partida_distrito = Empresa::listar_nombre_distrito(substr($guia_remision->ubigeo_partida, 0, 6));
        $guia_remision->dir_partida_lugar = Empresa::listar_nombre_distrito(substr($guia_remision->ubigeo_partida, 0, 6));
        $guia_remision->cliente_numerodocumento = $guia_remision_data->guia_remision_cliente_numerodocumento;
        $guia_remision->cliente_nombre = $guia_remision_data->guia_remision_cliente_nombre;
        $guia_remision->cliente_tipodocumento = $guia_remision_data->guia_remision_cliente_tipodocuemnto;
        $guia_remision->guia_remision_placa_vehiculo = $guia_remision_data->guia_remision_placa_vehiculo;
        $guia_remision->guia_remision_num_doc_conductor = $guia_remision_data->guia_remision_num_doc_conductor;
        $emisor = new \stdClass();
        $empresa = Empresa::empresa();
        $emisor->ruc = $empresa->emp_empresa_ruc;
        $emisor->tipo_doc = "6";
        $emisor->nom_comercial = $empresa->emp_empresa_nombre_comercial;
        $emisor->razon_social = $empresa->emp_empresa_razon_social;
        $emisor->codigo_ubigeo = $empresa->emp_empresa_codigo_ubigeo;
        $emisor->direccion = $empresa->emp_empresa_direccion;
        $emisor->direccion_departamento = Empresa::listar_nombre_departamento($empresa->emp_empresa_direccion_departamento);
        $emisor->direccion_provincia = Empresa::listar_nombre_provincia($empresa->emp_empresa_direccion_provincia);
        $emisor->direccion_distrito = Empresa::listar_nombre_distrito($empresa->emp_empresa_direccion_distrito);
        $emisor->direccion_codigopais = $empresa->emp_empresa_codigopais;
        $emisor->emp_empresa_firma_digital_passwd = $empresa->emp_empresa_firma_digital_passwd;
        $emisor->emp_empresa_firma_digital = $empresa->emp_empresa_firma_digital;
        $emisor->usuariosol = $empresa->emp_empresa_usuariosol;
        $emisor->clavesol = $empresa->emp_empresa_clavesol;
        $emisor->tipodeproceso = $empresa->emp_empresa_tipoproceso;
        $emisor->emp_empresa_ose = $empresa->emp_empresa_ose;
        $guia_remision->emisor = $emisor;
        $guia_remision->detalle = $guia_remision_data->detalle;
        $guia = procesar_data::guia_remision($guia_remision);
        if ($guia['respuesta'] == "ok" && $guia['cod_sunat'] == "0") {
            GuiaRemision::find($id)->update(array(
                'guia_remision_xml' => $guia['ruta_xml'] . '.XML',
                'guia_remision_cdr' => $guia['ruta_cdr'] . '.XML',
                'guia_remision_sunat_codigo' => $guia['cod_sunat'],
                'guia_remision_sunat_mensaje' => $guia['msj_sunat'],
                'guia_remision_sunat_hash_cdr' => $guia['hash_cpe'],
                'guia_remision_sunat_hash_cpe' => $guia['hash_cdr'],
                'guia_remision_estado_envio' => true,
            ));

        }
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(GuiaRemision::select(
                'guia_remision_id',
                'guia_remision_serie_comprobante',
                'guia_remision_numero_comprobante',
                'guia_remision_fecha_comprobante',
                'guia_remision_nota',
                'guia_remision_codmotivo_traslado',
                'guia_remision_motivo_traslado',
                'guia_remision_peso',
                'guia_remision_numero_paquetes',
                'guia_remision_cliente_nombre',
                'guia_remision_razon_social_tranporte',
                'guia_remision_placa_vehiculo',
                'guia_remision_num_doc_conductor',
                'guia_remision_estado_envio',
                'guia_remision_sunat_codigo'
            )->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de guia'], 200);

    }

    public function registrar_guia_remision(Request $request)
    {
        $guia_remision = new GuiaRemision($request->all());
        $guia_remision->guia_remision_numero_comprobante = GeneraNumero::genera_numero_guia_remision($guia_remision->guia_remision_serie_comprobante);
        $guia_remision->guia_remision_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
        $guia_remision->guia_remision_fecha_comprobante = date('Y-m-d');
        $guia_remision->guia_remision_user_id = auth()->user()->id;
        $guia_remision->guia_remision_cod_tipo_documento = "09";
        $guia_remision->guia_remision_estado_envio = false;
        $guia_remision_id = IdGenerador::generaId();
        $guia_remision->guia_remision_id = $guia_remision_id;
        $guia_remision->save();
        $contador = 1;
        foreach ($request->input('detalle') as $item) {
            $guia_remision_detalle = new GuiaRemisionDetalle($item);
            $guia_remision_detalle->guia_remision_detalle_id = IdGenerador::generaId();
            $guia_remision_detalle->guia_remision_detalle_guia_remision_id = $guia_remision_id;
            $guia_remision_detalle->guia_remision_detalle_item = $contador;
            $guia_remision_detalle->save();
            $contador = $contador + 1;

        }
        $guia_remision = GuiaRemision::find($guia_remision_id);
        $guia_remision->detalle = GuiaRemisionDetalle::where('guia_remision_detalle_guia_remision_id', $guia_remision_id)
            ->orderBy('guia_remision_detalle_item')
            ->get();
        return response()->json(['success' => true,
            'data' => $guia_remision,
            'message' => 'Lista de guia'], 200);

    }


    public function actualizar_guia_remision(Request $request, $id)
    {
        $guia_remision = new GuiaRemision($request->all());
        unset($guia_remision->guia_remision_serie_comprobante);
        // $guia_remision->guia_remision_numero_comprobante = GeneraNumero::genera_numero_guia_remision($guia_remision->guia_remision_serie_comprobante);
        $guia_remision->guia_remision_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
        $guia_remision->guia_remision_fecha_comprobante = date('Y-m-d');
        $guia_remision->guia_remision_user_id = auth()->user()->id;
        $guia_remision->guia_remision_estado_envio = false;
        $guia_remision->guia_remision_cod_tipo_documento = "09";
        GuiaRemision::find($id)->update($guia_remision->toArray());
        GuiaRemisionDetalle::where('guia_remision_detalle_guia_remision_id', $id)->delete();
        $contador = 1;
        foreach ($request->input('detalle') as $item) {
            $guia_remision_detalle = new GuiaRemisionDetalle($item);
            $guia_remision_detalle->guia_remision_detalle_id = IdGenerador::generaId();
            $guia_remision_detalle->guia_remision_detalle_guia_remision_id = $id;
            $guia_remision_detalle->guia_remision_detalle_item = $contador;
            $guia_remision_detalle->save();
            $contador = $contador + 1;

        }
        $guia_remision = GuiaRemision::find($id);
        $guia_remision->detalle = GuiaRemisionDetalle::where('guia_remision_detalle_guia_remision_id', $id)
            ->orderBy('guia_remision_detalle_item')
            ->get();
        return response()->json(['success' => true,
            'data' => $guia_remision,
            'message' => 'Lista de guia'], 200);

    }


    public function listar_guias_remision(Request $request)
    {
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(GuiaRemision::select(
                'guia_remision_id',
                'guia_remision_serie_comprobante',
                'guia_remision_numero_comprobante',
                'guia_remision_fecha_comprobante',
                'guia_remision_nota',
                'guia_remision_codmotivo_traslado',
                'guia_remision_motivo_traslado',
                'guia_remision_peso',
                'guia_remision_numero_paquetes',
                'guia_remision_cliente_nombre',
                'guia_remision_razon_social_tranporte',
                'guia_remision_placa_vehiculo',
                'guia_remision_num_doc_conductor',
                'guia_remision_estado_envio',
                'guia_remision_sunat_codigo'
            )->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de guia'], 200);

    }

    public function listar_guia_remision($id)
    {
        $guia_remision = GuiaRemision::find($id);
        $guia_remision->detalle = GuiaRemisionDetalle::where('guia_remision_detalle_guia_remision_id', $id)
            ->orderBy('guia_remision_detalle_item')
            ->get();
        return response()->json(['success' => true,
            'data' => $guia_remision,
            'message' => 'Lista de guia'], 200);
    }


    public function descargar_guia_xml($id)
    {
        $url_base_xml = realpath(__DIR__ . '/../../../../..') . '/comprobantes/cpe_xml/';
        $empresa = Empresa::empresa();
        $guias_remision = GuiaRemision::find($id);
        if ($guias_remision->guia_remision_xml != null
            && $empresa->emp_empresa_tipoproceso == '3') {
            $file = $url_base_xml . 'beta/guias/' . $guias_remision->guia_remision_xml;
        } elseif ($guias_remision->guia_remision_xml != null
            && $empresa->emp_empresa_tipoproceso == '1') {
            $file = $url_base_xml . 'produccion/guias/' . $guias_remision->guia_remision_xml;
        }

        return \response()->download($file);
    }

    public function descargar_guia_cdr($id)
    {
        $url_base_xml = realpath(__DIR__ . '/../../../../..') . '/comprobantes/cpe_xml/';
        $empresa = Empresa::empresa();
        $guias_remision = GuiaRemision::find($id);
        if ($guias_remision->guia_remision_xml != null
            && $empresa->emp_empresa_tipoproceso == '3') {
            $file = $url_base_xml . 'beta/guias/' . $guias_remision->guia_remision_cdr;
        } elseif ($guias_remision->guia_remision_xml != null
            && $empresa->emp_empresa_tipoproceso == '1') {
            $file = $url_base_xml . 'produccion/guias/' . $guias_remision->guia_remision_cdr;
        }

        return \response()->download($file);
    }

    public function eliminar_guia_remision(Request $request, $id)
    {

        GuiaRemisionDetalle::where('guia_remision_detalle_guia_remision_id', $id)->delete();
        GuiaRemision::find($id)->delete();
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(GuiaRemision::select(
                'guia_remision_id',
                'guia_remision_serie_comprobante',
                'guia_remision_numero_comprobante',
                'guia_remision_fecha_comprobante',
                'guia_remision_nota',
                'guia_remision_codmotivo_traslado',
                'guia_remision_motivo_traslado',
                'guia_remision_peso',
                'guia_remision_numero_paquetes',
                'guia_remision_cliente_nombre',
                'guia_remision_razon_social_tranporte',
                'guia_remision_placa_vehiculo',
                'guia_remision_num_doc_conductor',
                'guia_remision_estado_envio',
                'guia_remision_sunat_codigo'
            )->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de guia'], 200);
    }

}

