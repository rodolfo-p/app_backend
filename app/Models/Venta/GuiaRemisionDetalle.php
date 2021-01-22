<?php


namespace App\Models\Venta;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GuiaRemisionDetalle extends Model
{
    protected $table = 'guia_remision_detalle';
    public $timestamps = false;
    protected $keyType = 'string';
    protected $primaryKey = 'guia_remision_detalle_id';
    protected $fillable = ['guia_remision_detalle_id',
        'guia_remision_detalle_item',
        'guia_remision_detalle_descripcion',
        'guia_remision_detalle_cantidad',
        'guia_remision_detalle_guia_remision_id',
        'guia_remision_detalle_codigo_producto',
        'guia_remision_detalle_peso'];


}

