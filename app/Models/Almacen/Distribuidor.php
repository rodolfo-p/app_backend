<?php


namespace App\Models\Almacen;


use Illuminate\Database\Eloquent\Model;

class Distribuidor extends Model
{
    protected $table = 'alm_distribuidor';
    public $timestamps = false;
    protected $keyType = 'string';

    protected $casts = [
        'alm_distribuidor_estado' => 'boolean',
    ];

    protected $primaryKey = 'alm_distribuidor_id';
    protected $fillable = ['alm_distribuidor_id',
        'alm_distribuidor_nombres',
        'alm_distribuidor_apellidos',
        'alm_distribuidor_numero_doc',
        'alm_distribuidor_estado',
        'alm_distribuidor_vehiculo',
        'alm_distribuidor_porcentaje_venta'];
}
