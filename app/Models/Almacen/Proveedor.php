<?php


namespace App\Models\Almacen;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Proveedor extends Model
{
    protected $table = 'alm_proveedor';
    public $timestamps = false;
    protected $keyType = 'string';

    /*protected $casts = [
        'cont_periodo_estado' => 'boolean',
    ];*/

    protected $primaryKey = 'alm_proveedor_id';
    protected $fillable = ['alm_proveedor_id',
        'alm_proveedor_ruc',
        'alm_proveedor_razon_social',
        'alm_proveedor_tipo_contribuyente',
        'alm_proveedor_nombre_comercial',
        'alm_proveedor_fecha_inscripcion',
        'alm_proveedor_fecha_inicio_actividades',
        'alm_proveedor_estado_contribuyente',
        'alm_proveedor_condicion_contribuyente',
        'alm_proveedor_direccion',
        'alm_proveedor_sistema_emicion_comprobante',
        'alm_proveedor_actividad_comercio_exterior',
        'alm_proveedor_sistema_contabilidad',
        'alm_proveedor_telefono',
        'alm_proveedor_correo',
        'alm_proveedor_representante_id','alm_porveedor_tipo_doc_ident'];



}
