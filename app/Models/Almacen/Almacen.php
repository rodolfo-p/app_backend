<?php


namespace App\Models\Almacen;


use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    protected $table = 'alm_almacen';
    public $timestamps = false;
    protected $keyType = 'string';

    /*protected $casts = [
        'cont_periodo_estado' => 'boolean',
    ];*/

    protected $primaryKey = 'alm_almacen_id';
    protected $fillable = ['alm_almacen_id',
        'alm_almacen_nombre',
        'alm_almacen_direccion',
        'alm_almacen_telefono',
        'alm_almacen_email'];
}
