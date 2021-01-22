<?php


namespace App\Models\Venta;


use Illuminate\Database\Eloquent\Model;

class VentaPago extends Model
{
    protected $table = 'vent_pago';
    public $timestamps = false;
    protected $keyType = 'string';
    protected $casts = [
        'vent_pago_comision_pagado' => 'boolean',
    ];
    protected $primaryKey = 'vent_pago_id';
    protected $fillable = ['vent_pago_id',
        'vent_pago_importe',
        'vent_pago_pago',
        'vent_pago_vuelto',
        'vent_pago_venta_id',
        'vent_pago_tipo_pago',
        'vent_pago_user_id',
        'vent_pago_fecha',
        'vent_pago_numero_pago',
        'vent_pago_modalidad',
        'vent_pago_numero_transaccion',
        'vent_pago_cliente_documento',
        'vent_pago_almacen_id',
        'vent_pago_periodo_id',
        'vent_pago_serie',
        'vent_pago_comision_id',
        'vent_pago_comision_pagado',
        'vent_pago_comision_importe',
        'vent_pago_nro_cuota',
        ];
}
