<?php


namespace App\Models\Almacen;


use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{
    protected $table = 'alm_catalogo';
    public $timestamps = false;
    protected $keyType = 'string';

    /*protected $casts = [
        'cont_periodo_estado' => 'boolean',
    ];*/

    protected $primaryKey = 'alm_catalogo_id';
    protected $fillable = ['alm_catalogo_id',
        'alm_catalogo_precio_venta',
        'alm_catalogo_precio_venta_oferta',
        'alm_catalogo_estado',
        'alm_producto_id',
        'alm_catalogo_fecha_actualizacion',
        'alm_catalogo_porcentaje_utilidad',
        'alm_catalogo_ganancia',
        'alm_catalogo_precio_compra'];
}
