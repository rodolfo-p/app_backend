<?php


namespace App\Models\Almacen;


use Illuminate\Database\Eloquent\Model;

class ListaPrecio extends Model
{
    protected $table = 'alm_lista_precio';
    public $timestamps = false;
    protected $keyType = 'string';

    /*protected $casts = [
        'cont_periodo_estado' => 'boolean',
    ];*/

    protected $primaryKey = 'alm_lista_precio_id';
    protected $fillable = ['alm_lista_precio_id',
        'alm_lista_precio_almacen_id',
        'alm_lista_precio_nombre',
        'alm_lista_precio_descripcion',
        'alm_lista_precio_fecha_vigencia_inicio',
        'alm_lista_precio_fecha_vigencia_fin'];
}
