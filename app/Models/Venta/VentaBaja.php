<?php


namespace App\Models\Venta;


use Illuminate\Database\Eloquent\Model;

class VentaBaja extends Model
{
    protected $table = 'vent_venta_baja';
    public $timestamps = false;
    protected $keyType = 'string';

    /*protected $casts = [
        'cont_asiento_estado' => 'boolean',
    ];*/

    protected $primaryKey = 'vent_venta_baja_id';
    protected $fillable = ['vent_venta_baja_id',
        'vent_venta_baja_fecha_referencia',
        'vent_venta_baja_fecha',
        'vent_venta_baja_serie',
        'vent_venta_baja_motivo',
        'vent_venta_baja_venta_serie',
        'vent_venta_baja_venta_numero',
        'vent_venta_baja_secuencia',
        'vent_venta_baja_codigo',
        'vent_venta_baja_venta_id',
        'vent_venta_baja_xml',
        'vent_venta_baja_cdr',
        'vent_venta_baja_hash_cpe',
        'vent_venta_baja_hash_cdr'];

}

