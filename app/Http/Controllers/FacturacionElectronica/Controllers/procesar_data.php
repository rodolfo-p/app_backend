<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 13/12/18
 * Time: 09:14 AM
 */

namespace App\Http\Controllers\FacturacionElectronica\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacturacionElectronica\Controllers\validaciondedatos;
use App\Http\Controllers\FacturacionElectronica\Controllers\procesarcomprobante;
use App\Models\Configuracion\Periodo;
use Illuminate\Support\Facades\DB;
use \App\Http\Data\Setup\Empresa;

class procesar_data extends Controller
{

    public static $ruta_firma = "";
    public static $pass_firma = "";
    public static $ruta_ws = "";

    /** produccion */
    public static $url_base_produccion_factura = "";
    public static $url_base_produccion_nota_credito = "";
    public static $url_base_produccion_factura_comunicacion_baja = "";
    public static $url_base_produccion_boleta = "";
    public static $url_base_produccion_guia_remision = "";
    public static $url_base_produccion_certificados = "";
    public static $url_web_service_sunat_produccion_comprobante_factura_boleta_notas = "https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService";


    public static $url_web_service_sunat_produccion_comprobante_guia_remision = "https://e-guiaremision.sunat.gob.pe/ol-ti-itemision-guia-gem/billService";

    /** pruebas  */
    public static $url_base_prueba_factura = "";
    public static $url_base_prueba_nota_credito = "";
    public static $url_base_prueba_boleta = "";
    public static $url_base_prueba_guia_remision = "";
    public static $url_base_prueba_certificados = "";
    public static $url_base_prueba_factura_comunicacion_baja = "";
    public static $url_base_prueba_certificados_ose = "";
    public static $url_web_service_sunat_prueba_comprobante_factura_boleta_notas = "https://e-beta.sunat.gob.pe:443/ol-ti-itcpfegem-beta/billService";
    //public static $url_web_service_prueba_ose = "https://demo-ose.nubefact.com/ol-ti-itcpe/billService?wsdl";
    public static $url_web_service_prueba_ose = "https://test.conose.pe/ol-ti-itcpe/billService.svc";


    //public static $url_web_service_produccion_ose = "https://ose.nubefact.com/ol-ti-itcpe/billService?wsdl";
    public static $url_web_service_produccion_ose = "https://prod.conose.pe/ol-ti-itcpe/billService.svc";

    public static $url_web_service_sunat_beta_comprobante_guia_remision = "https://e-beta.sunat.gob.pe/ol-ti-itemision-guia-gem-beta/billService";


    public static function procesar_data($data_params, $venta_totales)
    {
        /** variables de produccion*/

        self::$url_base_produccion_factura = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/cpe_xml/produccion/facturas/';
        self::$url_base_produccion_boleta = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/cpe_xml/produccion/boletas/';
        self::$url_base_produccion_nota_credito = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/cpe_xml/produccion/nota_creditos/';
        self::$url_base_produccion_certificados = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/certificados/produccion/';

        /** variables de prueba*/

        self::$url_base_prueba_factura = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/cpe_xml/beta/facturas/';
        self::$url_base_prueba_boleta = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/cpe_xml/beta/boletas/';
        self::$url_base_prueba_nota_credito = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/cpe_xml/beta/nota_creditos/';
        self::$url_base_prueba_certificados_ose = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/certificados/beta/C1903077048.pfx';
        self::$url_base_prueba_certificados = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/certificados/beta/firmabeta.pfx';

        $data = $data_params;
        $data->total_grabados = $venta_totales->gravado;
        $data->total_exonerados = $venta_totales->exonerado;
        $data->total_inafectos = $venta_totales->inafecto;

        $items_detalle = $data_params->detalle;
        $empresa = Empresa::empresa();
        $ruc_emisor = $empresa->emp_empresa_ruc;
        $emisor['ruc'] = $empresa->emp_empresa_ruc;
        $emisor['tipo_doc'] = "6";
        $emisor['nom_comercial'] = $empresa->emp_empresa_nombre_comercial;
        $emisor['razon_social'] = $empresa->emp_empresa_razon_social;
        $emisor['codigo_ubigeo'] = $empresa->emp_empresa_codigo_ubigeo;
        $emisor['direccion'] = $empresa->emp_empresa_direccion;
        $emisor['direccion_departamento'] = Empresa::listar_nombre_departamento($empresa->emp_empresa_direccion_departamento);
        $emisor['direccion_provincia'] = Empresa::listar_nombre_provincia($empresa->emp_empresa_direccion_provincia);
        $emisor['direccion_distrito'] = Empresa::listar_nombre_distrito($empresa->emp_empresa_direccion_distrito);
        $emisor['direccion_codigopais'] = $empresa->emp_empresa_codigopais;
        $emisor['usuariosol'] =$empresa->emp_empresa_tipoproceso==1? $empresa->emp_empresa_usuariosol: 'MODDATOS';
        $emisor['clavesol'] =$empresa->emp_empresa_tipoproceso==1? $empresa->emp_empresa_clavesol: 'MODDATOS';
        $emisor['ubigeo'] = $empresa->emp_empresa_codigo_ubigeo;
        $tipodeproceso = $empresa->emp_empresa_tipoproceso; //3=beta,2=homologacion,1=produccion
        $emisor['tipoproceso'] = $empresa->emp_empresa_tipoproceso; //3=beta,2=homologacion,1=produccion
        $archivo = $ruc_emisor . '-' . $data->tipo_comprobante . '-' . $data->serie_comprobante . '-' . $data->numero_comprobante;
        if ($tipodeproceso == '1') {
            /** ruta xml factura*/
            $ruta_factura = self::$url_base_produccion_factura . $archivo;
            $ruta_cdr_factura = self::$url_base_produccion_factura;

            /** ruta xml boleta*/
            $ruta_boleta = self::$url_base_produccion_boleta . $archivo;
            $ruta_cdr_boleta = self::$url_base_produccion_boleta;

            /** ruta xml nota de credito*/

            $ruta_nota_credito = self::$url_base_produccion_nota_credito . $archivo;
            $ruta_cdr_nota_credito = self::$url_base_produccion_nota_credito;
            self::$ruta_firma = self::$url_base_produccion_certificados . $empresa->emp_empresa_firma_digital;
            self::$ruta_ws = $empresa->emp_empresa_ose == 1 ? self::$url_web_service_produccion_ose : self::$url_web_service_sunat_produccion_comprobante_factura_boleta_notas;
            self::$pass_firma = $empresa->emp_empresa_firma_digital_passwd;
        } else if ($tipodeproceso == '3') {
            /** ruta xml factura*/
            $ruta_factura = self::$url_base_prueba_factura . $archivo;
            $ruta_cdr_factura = self::$url_base_prueba_factura;


            /** ruta xml boleta*/
            $ruta_boleta = self::$url_base_prueba_boleta . $archivo;
            $ruta_cdr_boleta = self::$url_base_prueba_boleta;

            /** ruta xml nota de credito*/

            $ruta_nota_credito = self::$url_base_prueba_nota_credito . $archivo;
            $ruta_cdr_nota_credito = self::$url_base_prueba_nota_credito;

            self::$ruta_firma = $empresa->emp_empresa_ose == 1 ? self::$url_base_prueba_certificados_ose : self::$url_base_prueba_certificados;

            self::$pass_firma = $empresa->emp_empresa_ose == 1 ? 'mSjPiGv9E9f2bLm' : '123456';
            self::$ruta_ws = $empresa->emp_empresa_ose == 1 ? self::$url_web_service_prueba_ose : self::$url_web_service_sunat_prueba_comprobante_factura_boleta_notas;
        }

        $rutas = array();
        $rutas['nombre_archivo'] = $archivo;
        $rutas['ruta_firma'] = self::$ruta_firma;
        $rutas['pass_firma'] = self::$pass_firma;
        $rutas['ruta_ws'] = self::$ruta_ws;
        $data_comprobante = self::crear_cabecera($emisor, $data);
        $procesarcomprobante = new procesarcomprobante();
        $tipo_comprobante = $data->tipo_comprobante;
        if ($tipo_comprobante == "01") {
            $rutas['ruta_xml'] = $ruta_factura;
            $rutas['ruta_cdr'] = $ruta_cdr_factura;
            $resp = $procesarcomprobante->procesar_boleta_factura_nuevo($data_comprobante, $items_detalle, $rutas);
            $resp['url_xml'] = $archivo . '.XML';
            return $resp;
        }

        if ($tipo_comprobante == "03") {
            $rutas['ruta_xml'] = $ruta_boleta;
            $rutas['ruta_cdr'] = $ruta_cdr_boleta;

            $resp = $procesarcomprobante->procesar_boleta_factura_nuevo($data_comprobante, $items_detalle, $rutas);
            $resp['url_xml'] = $archivo . '.XML';
            return $resp;
        }

        if ($tipo_comprobante == "07") {
            $rutas['ruta_xml'] = $ruta_nota_credito;
            $rutas['ruta_cdr'] = $ruta_cdr_nota_credito;
            $resp = $procesarcomprobante->procesar_nota_de_credito($data_comprobante, $items_detalle, $rutas);
            $resp['url_xml'] = $archivo . '.XML';
            return $resp;
        }

        if ($tipo_comprobante == "08") {
            $resp = $procesarcomprobante->procesar_nota_de_debito($data_comprobante, $items_detalle, $rutas);
            //$resp['ruta_xml'] = 'archivos_xml_sunat/cpe_xml/beta/20138122256/'.$archivo.'.XML';
            $resp['ruta_cdr'] = 'R-' . $archivo . '.xml';
            $resp['ruta_pdf'] = 'controllers/prueba.php?tipo=notadebito&id=0';
            $resp['url_xml'] = $archivo . '.XML';
            return $resp;
        }


    }

    public static function comunicacion_baja($data)
    {
        self::$url_base_produccion_certificados = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/certificados/produccion/';
        self::$url_base_prueba_certificados = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/certificados/beta/firmabeta.pfx';
        self::$url_base_prueba_certificados_ose = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/certificados/beta/C1903077048.pfx';
        self::$url_base_produccion_factura_comunicacion_baja = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/cpe_xml/produccion/comunicaciones_baja/';
        self::$url_base_prueba_factura_comunicacion_baja = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/cpe_xml/beta/comunicaciones_baja/';
        //$empresa = Empresa::empresa();
        $procesarcomprobante = new procesarcomprobante();
        $archivo = $data->emisor->ruc . '-' . $data->codigo . '-' . $data->serie . '-' . $data->secuencia;
        if ($data->emisor->tipodeproceso == '1') {
            $ruta = self::$url_base_produccion_factura_comunicacion_baja . $archivo;
            $ruta_cdr = self::$url_base_produccion_factura_comunicacion_baja;
            $ruta_firma = self::$url_base_produccion_certificados . $data->emisor->emp_empresa_firma_digital;
            $ruta_ws = $data->emisor->emp_empresa_ose == 1 ? self::$url_web_service_produccion_ose : self::$url_web_service_sunat_produccion_comprobante_factura_boleta_notas;
            $pass_firma = $data->emisor->emp_empresa_firma_digital_passwd;
        }
        if ($data->emisor->tipodeproceso == '3') {
            $ruta = self::$url_base_prueba_factura_comunicacion_baja . $archivo;
            $ruta_cdr = self::$url_base_prueba_factura_comunicacion_baja;
            $ruta_firma = $data->emisor->emp_empresa_ose == 1 ? self::$url_base_prueba_certificados_ose : self::$url_base_prueba_certificados;
            $pass_firma = $data->emisor->emp_empresa_ose == 1 ? 'mSjPiGv9E9f2bLm' : '123456';
            $ruta_ws = $data->emisor->emp_empresa_ose == 1 ? self::$url_web_service_prueba_ose : self::$url_web_service_sunat_prueba_comprobante_factura_boleta_notas;
        }
        $rutas = array();
        $rutas['nombre_archivo'] = $archivo;
        $rutas['ruta_xml'] = $ruta;
        $rutas['ruta_cdr'] = $ruta_cdr;
        $rutas['ruta_firma'] = $ruta_firma;
        $rutas['pass_firma'] = $pass_firma;
        $rutas['ruta_ws'] = $ruta_ws;
        $resp = $procesarcomprobante->procesar_baja_sunat($data, $data->detalle, $rutas);
        return $resp;
    }

    public static function guia_remision($data)
    {
        self::$url_base_produccion_certificados = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/certificados/produccion/';
        self::$url_base_prueba_certificados = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/certificados/beta/firmabeta.pfx';
        self::$url_base_prueba_certificados_ose = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/certificados/beta/C1903077048.pfx';
        self::$url_base_produccion_guia_remision = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/cpe_xml/produccion/guias/';
        self::$url_base_prueba_guia_remision = realpath(__DIR__ . '/../../../../../../') . '/comprobantes/cpe_xml/beta/guias/';
        $nombre_archivo = $data->emisor->ruc . '-' . $data->cod_tipo_documento . '-' . $data->serie_comprobante . '-' . $data->numero_comprobante;
        if ($data->emisor->tipodeproceso == '1') {
            $ruta = self::$url_base_produccion_guia_remision . $nombre_archivo;
            $ruta_cdr = self::$url_base_produccion_guia_remision;
            $ruta_firma = self::$url_base_produccion_certificados . $data->emisor->emp_empresa_firma_digital;
            $ruta_ws = $data->emisor->emp_empresa_ose == 1 ? self::$url_web_service_produccion_ose : self::$url_web_service_sunat_produccion_comprobante_guia_remision;
            $pass_firma = $data->emisor->emp_empresa_firma_digital_passwd;
        }
        if ($data->emisor->tipodeproceso == '3') {
            $ruta = self::$url_base_prueba_guia_remision . $nombre_archivo;
            $ruta_cdr = self::$url_base_prueba_guia_remision;
            $ruta_firma = $data->emisor->emp_empresa_ose === 1 ? self::$url_base_prueba_certificados_ose : self::$url_base_prueba_certificados;
            $pass_firma = $data->emisor->emp_empresa_ose === 1 ? 'mSjPiGv9E9f2bLm' : '123456';
            $ruta_ws = $data->emisor->emp_empresa_ose === 1 ? self::$url_web_service_prueba_ose : self::$url_web_service_sunat_beta_comprobante_guia_remision;


        }
        $rutas = array();
        $rutas['nombre_archivo'] = $nombre_archivo;
        $rutas['ruta_xml'] = $ruta;
        $rutas['ruta_cdr'] = $ruta_cdr;
        $rutas['ruta_firma'] = $ruta_firma;
        $rutas['pass_firma'] = $pass_firma;
        $rutas['ruta_ws'] = $ruta_ws;
        $procesarcomprobante = new Procesarcomprobante();
        $resp = $procesarcomprobante->procesar_guia_de_remision($data, $data->detalle, $rutas);
        $resp['ruta_xml'] = 'archivos_xml_sunat/cpe_xml/beta/' . $nombre_archivo . '.XML';
        $resp['ruta_cdr'] = 'archivos_xml_sunat/cpe_xml/beta/R-' . $nombre_archivo . '.XML';
        $resp['ruta_pdf'] = 'controllers/prueba.php?tipo=factura&id=0';
        $resp['ruta_xml'] = $nombre_archivo;
        $resp['url_xml'] = $nombre_archivo;
        $resp['ruta_cdr'] = 'R-' . $nombre_archivo;
        return $resp;
    }


    public static function crear_cabecera($emisor, $data)
    {

        $notadebito_descripcion['01'] = 'INTERES POR MORA';
        $notadebito_descripcion['02'] = 'AUMENTO EN EL VALOR';
        $notadebito_descripcion['03'] = 'PENALIDADES';

        $notacredito_descripcion['01'] = 'ANULACION DE LA OPERACION';
        $notacredito_descripcion['02'] = 'ANULACION POR ERROR EN EL RUC';
        $notacredito_descripcion['03'] = 'CORRECION POR ERROR EN LA DESCRIPCION';
        $notacredito_descripcion['04'] = 'DESCUENTO GLOBAL';
        $notacredito_descripcion['05'] = 'DESCUENTO POR ITEM';
        $notacredito_descripcion['06'] = 'DEVOLUCION TOTAL';
        $notacredito_descripcion['07'] = 'DEVOLUCION POR ITEM';
        $notacredito_descripcion['08'] = 'BONIFICACION';
        $notacredito_descripcion['09'] = 'DISMINUCION EN EL VALOR';


        if (isset($data->tipo_comprobante)) {
            if ($data->tipo_comprobante == '07') { //Nota de Crédito
                $codigo_motivo_modifica = $data->vent_venta_nota_codigo;
                $descripcion_motivo_modifica = $notacredito_descripcion[$data->vent_venta_nota_codigo];
            } else if ($data->tipo_comprobante == '08') { //Nota de Débito
                $codigo_motivo_modifica = $data->vent_venta_nota_codigo;
                $descripcion_motivo_modifica = $notadebito_descripcion[$data->vent_venta_nota_codigo];
            } else {
                $codigo_motivo_modifica = "";
                $descripcion_motivo_modifica = "";
            }
        }

        //********** CAMPOS NUEVOS PARA UBL 2.1 */
        //http://cpe.sunat.gob.pe/sites/default/files/inline-images/Guia%2BXML%2BFactura%2Bversion%202-1%2B1%2B0%20%282%29.pdf
        /*
        'TIPO_OPERACION' => '0101', //pag. 28
        'FECHA_VTO' => $data['fecha_comprobante'], //pag. 31 //fecha de vencimiento
        'POR_IGV' => '18.00', //Porcentaje del impuesto
        'CONTACTO_EMPRESA' => "",
        'TOTAL_EXPORTACION' => $data['TOTAL_EXPORTACION']
        'COD_UBIGEO_CLIENTE' => (isset($data['cliente_codigoubigeo'])) ? $data['cliente_codigoubigeo'] : "",
        'DEPARTAMENTO_CLIENTE' => (isset($data['cliente_departamento'])) ? $data['cliente_departamento'] : "",
        'PROVINCIA_CLIENTE' => (isset($data['cliente_provincia'])) ? $data['cliente_provincia'] : "",
        'DISTRITO_CLIENTE' => (isset($data['cliente_distrito'])) ? $data['cliente_distrito'] : "",
        */

        /*$data['txt_subtotal_comprobante'] = '100';
        $data['txt_total_comprobante'] = '100';
        $data['txt_igv_porcentaje'] = '18.00';
        $data['txt_igv_comprobante'] = '0';
        $data['txt_total_exoneradas'] = '100';
        $data['txt_total_letras'] = 'CIEN';*/

        //$data['txt_total_exoneradas'] = 13000;
        $cabecera = array(
            'TIPO_OPERACION' => '0101', //pag. 28
            //'TOTAL_GRAVADAS' => (isset($data->txt_subtotal_comprobante)) ? $data->txt_subtotal_comprobante : "0",

            'TOTAL_GRAVADAS' =>   $data->vent_venta_bi, // falta corregir para exonerados e inafectos


            'TOTAL_INAFECTA' => $data->total_inafectos,
            //'TOTAL_INAFECTA' => "354.00",


            //'TOTAL_EXONERADAS' => (isset($data->txt_total_exoneradas)) ? $data->txt_total_exoneradas : "0",
            'TOTAL_EXONERADAS' => $data->total_exonerados,
            //'TOTAL_EXONERADAS' => "0.00",
            'TOTAL_PERCEPCIONES' => "0",
            'TOTAL_RETENCIONES' => "0",
            'TOTAL_DETRACCIONES' => "0",
            'TOTAL_BONIFICACIONES' => "0",
            'TOTAL_EXPORTACION' => "0",
            'TOTAL_DESCUENTO' => $data->vent_venta_precio_descuento_total,
            'TOTAL_SIN_DESCUENTO' => $data->vent_venta_bi_real,
            'PROCENTAJE_DESCUENTO' => floatval($data->vent_venta_porcentaje_descuento) / 100,
            'SUB_TOTAL' => (isset($data->txt_subtotal_comprobante)) ? $data->txt_subtotal_comprobante : "0",
            'POR_IGV' => (isset($data->txt_igv_porcentaje)) ? $data->txt_igv_porcentaje : "18.00", //Porcentaje del impuesto
            'TOTAL_IGV' => (isset($data->txt_igv_comprobante)) ? $data->txt_igv_comprobante : "0",

            'TOTAL_EXONERADOS' => "40.00",
            'TOTAL_INAFECTOS' => "10.00",

            'TOTAL_ISC' => "0",
            'TOTAL_OTR_IMP' => "0",
            'TOTAL' => (isset($data->txt_total_comprobante)) ? $data->txt_total_comprobante : "0",
            'TOTAL_LETRAS' => $data->txt_total_letras,
            //==============================================
            'NRO_GUIA_REMISION' => "",
            'COD_GUIA_REMISION' => "",
            'NRO_OTR_COMPROBANTE' => "",
            'COD_OTR_COMPROBANTE' => "",
            //==============================================
            'TIPO_COMPROBANTE_MODIFICA' => (isset($data->vent_venta_tipo_comprobante_referenciado)) ? $data->vent_venta_tipo_comprobante_referenciado : "",
            'NRO_DOCUMENTO_MODIFICA' => (isset($data->vent_venta_comprobante_referenciado)) ? $data->vent_venta_comprobante_referenciado : "",
            'COD_TIPO_MOTIVO' => $codigo_motivo_modifica,
            'DESCRIPCION_MOTIVO' => $descripcion_motivo_modifica,
            //===============================================
            'NRO_COMPROBANTE' => $data->serie_comprobante . '-' . $data->numero_comprobante,
            'FECHA_DOCUMENTO' => $data->fecha_comprobante,
            'FECHA_VTO' => $data->fecha_comprobante, //pag. 31 //fecha de vencimiento
            'COD_TIPO_DOCUMENTO' => $data->tipo_comprobante,
            'COD_MONEDA' => $data->codmoneda_comprobante,
            //==================================================
            'NRO_DOCUMENTO_CLIENTE' => $data->cliente_numerodocumento,
            'RAZON_SOCIAL_CLIENTE' => $data->cliente_nombre,
            'TIPO_DOCUMENTO_CLIENTE' => $data->cliente_tipodocumento, //RUC
            'DIRECCION_CLIENTE' => $data->cliente_direccion,

            'COD_UBIGEO_CLIENTE' => (isset($data->cliente_codigoubigeo)) ? $data->cliente_codigoubigeo : "",
            'DEPARTAMENTO_CLIENTE' => (isset($data->cliente_departamento)) ? $data->cliente_departamento : "",
            'PROVINCIA_CLIENTE' => (isset($data->cliente_provincia)) ? $data->cliente_provincia : "",
            'DISTRITO_CLIENTE' => (isset($data->cliente_distrito)) ? $data->cliente_distrito : "",

            'CIUDAD_CLIENTE' => $data->cliente_ciudad,
            'COD_PAIS_CLIENTE' => $data->cliente_pais,
            //===============================================
            'NRO_DOCUMENTO_EMPRESA' => $emisor['ruc'],
            'TIPO_DOCUMENTO_EMPRESA' => $emisor['tipo_doc'], //RUC
            'NOMBRE_COMERCIAL_EMPRESA' => $emisor['nom_comercial'],
            'CODIGO_UBIGEO_EMPRESA' => $emisor['codigo_ubigeo'],
            'DIRECCION_EMPRESA' => $emisor['direccion'],
            'DEPARTAMENTO_EMPRESA' => $emisor['direccion_departamento'],
            'PROVINCIA_EMPRESA' => $emisor['direccion_provincia'],
            'DISTRITO_EMPRESA' => $emisor['direccion_distrito'],
            'CODIGO_PAIS_EMPRESA' => $emisor['direccion_codigopais'],
            'RAZON_SOCIAL_EMPRESA' => $emisor['razon_social'],
            'CODIGO_UBIGEO' => $emisor['ubigeo'],
            'CONTACTO_EMPRESA' => "",
            //====================INFORMACION PARA ANTICIPO=====================//
            'FLG_ANTICIPO' => "0",
            //====================REGULAR ANTICIPO=====================//
            'FLG_REGU_ANTICIPO' => "0",
            'NRO_COMPROBANTE_REF_ANT' => "",
            'MONEDA_REGU_ANTICIPO' => "",
            'MONTO_REGU_ANTICIPO' => "0",
            'TIPO_DOCUMENTO_EMP_REGU_ANT' => "",
            'NRO_DOCUMENTO_EMP_REGU_ANT' => "",
            //===================CLAVES SOL EMISOR====================//
            'EMISOR_RUC' => $emisor['ruc'],
            'EMISOR_USUARIO_SOL' => $emisor['usuariosol'],
            'EMISOR_PASS_SOL' => $emisor['clavesol']
        );

        return $cabecera;
    }


}
