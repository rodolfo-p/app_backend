<?php


namespace App\Models\Venta;


use Illuminate\Database\Eloquent\Model;

class PedidoDetalle extends Model
{
    protected $table = 'vent_pedido_detalle';
    public $timestamps = false;
    protected $keyType = 'string';
    protected $primaryKey = 'vent_pedido_detalle_id';
    protected $fillable = ['ent_pedido_detalle_id',
        'vent_pedido_detalle_cantidad',
        'vent_pedido_detalle_descripcion',
        'vent_pedido_detalle_precio_initario',
        'vent_pedido_detalle_precio',
        'vent_pedido_detalle_pedido_id'];
}

