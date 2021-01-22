<?php


namespace App\Models\Venta;


use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'seg_cliente';
    public $timestamps = false;
    protected $keyType = 'string';

    protected $casts = [
        'seg_cliente_estado' => 'boolean',
    ];
    protected $primaryKey = 'seg_cliente_id';
    protected $fillable = ['seg_cliente_id',
        'seg_cliente_estado',
        'seg_cliente_nombres',
        'seg_cliente_apellido_paterno',
        'seg_cliente_apellido_materno',
        'seg_cliente_numero_doc',
        'seg_cliente_telefono',
        'seg_cliente_direccion',
        'seg_cliente_email',
        'seg_cliente_razon_social',
        'seg_cliente_tipo_documento'];
}
