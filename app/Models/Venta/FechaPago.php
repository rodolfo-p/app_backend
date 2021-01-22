<?php


namespace App\Models\Venta;


use Illuminate\Database\Eloquent\Model;

class FechaPago extends Model
{
    protected $table = 'vent_venta_fecha_pago';
    public $timestamps = false;
    protected $keyType = 'string';

    /*protected $casts = [
        'cont_asiento_estado' => 'boolean',
    ];*/

    protected $primaryKey = 'vent_venta_fecha_pago_id';
    protected $fillable = ['vent_venta_fecha_pago_id',
        'vent_venta_fecha_pago_venta_id',
        'vent_venta_fecha_pago_fecha',
        'vent_venta_fecha_pago_importe','vent_venta_fecha_pago_cuota','vent_venta_fecha_pago_comentario'];

}

