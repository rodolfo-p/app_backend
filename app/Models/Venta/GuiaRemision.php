<?php


namespace App\Models\Venta;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GuiaRemision extends Model
{
    protected $table = 'guia_remision';
    public $timestamps = false;
    protected $keyType = 'string';

    protected $casts = [
        'guia_remision_estado_envio' => 'boolean',
    ];
    protected $primaryKey = 'guia_remision_id';
    protected $fillable = ['guia_remision_id',
        'guia_remision_serie_comprobante',
        'guia_remision_fecha_comprobante',
        'guia_remision_cod_tipo_documento',
        'guia_remision_nota',
        'guia_remision_codmotivo_traslado',
        'guia_remision_motivo_traslado',
        'guia_remision_peso',
        'guia_remision_numero_paquetes',
        'guia_remision_codtipo_trasportista',
        'guia_remision_tipo_documento_transporte',
        'guia_remision_nro_documento_transporte',
        'guia_remision_razon_social_tranporte',
        'guia_remision_ubigeo_destino',
        'guia_remision_dir_destino',
        'guia_remision_ubigeo_partida',
        'guia_remision_dir_partida',
        'guia_remision_cliente_numerodocumento',
        'guia_remision_cliente_nombre',
        'guia_remision_cliente_tipodocuemnto',
        'guia_remision_estado_envio',
        'guia_remision_xml',
        'guia_remision_cdr',
        'guia_remision_numero_comprobante',
        'guia_remision_periodo_id',
        'guia_remision_almacen_id',
        'guia_remision_placa_vehiculo',
        'guia_remision_num_doc_conductor',
        'guia_remision_fecha_partida',
        'guia_remision_unidad_medida',
        'guia_remision_user_id',
        'guia_remision_sunat_codigo',
        'guia_remision_sunat_mensaje',
        'guia_remision_sunat_hash_cdr',
        'guia_remision_sunat_hash_cpe'];


}


