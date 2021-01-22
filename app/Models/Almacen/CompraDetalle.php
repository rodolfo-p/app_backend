<?php


namespace App\Models\Almacen;


use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    protected $table = 'comp_compra_detalle';
    public $timestamps = false;
    protected $keyType = 'string';

    protected $casts = [
        'comp_compra_detalle_serie_estado' => 'boolean',
        'comp_compra_detalle_vendido' => 'boolean',
    ];
    protected $primaryKey = 'comp_compra_detalle_id';
    protected $fillable = ['comp_compra_detalle_id',
        'comp_compra_detalle_precio',
        'comp_compra_detalle_precio_unitario',
        'comp_compra_detalle_cantidad',
        'comp_compra_detalle_igv',
        'comp_compra_detalle_bi',
        'comp_compra_compra_id',
        'comp_compra_detalle_producto_id',
        'comp_compra_detalle_tipo_operacion',
        'comp_compra_detalle_cuenta_compra',
        'comp_compra_detalle_producto',
        'comp_compra_detalle_serie',
        'comp_compra_detalle_vendido',
        'comp_compra_detalle_serie_estado',
        'comp_compra_detalle_item',
        'comp_compra_detalle_fecha_registro',
        'comp_compra_detalle_precio_dolar',
        'comp_compra_detalle_flete',
        'comp_compra_detalle_tipo_cambio',
        'comp_compra_detalle_costo_real_unitario'
    ];
}
