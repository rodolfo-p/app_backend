<?php


namespace App\Models\Venta;


use Illuminate\Database\Eloquent\Model;

class UsuarioSerie extends Model
{
    protected $table = 'vent_usuario_serie';
    public $timestamps = false;
    protected $keyType = 'string';

    /*protected $casts = [
        'cont_asiento_estado' => 'boolean',
    ];*/

    protected $primaryKey = 'vent_usuario_serie_id';
    protected $fillable = ['vent_usuario_serie_id',
        'vent_usuario_user_id',
        'vent_num_cajero',
        'vent_almacen_id'];
}
