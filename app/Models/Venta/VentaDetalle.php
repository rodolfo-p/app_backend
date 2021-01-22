<?php


namespace App\Models\Venta;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VentaDetalle extends Model
{
    protected $table = 'vent_venta_detalle';
    public $timestamps = false;
    protected $keyType = 'string';
    protected $primaryKey = 'vent_venta_detalle_id';
    protected $fillable = ['vent_venta_detalle_id',
        'vent_venta_detalle_precio_unitario',
        'vent_venta_detalle_precio',
        'vent_venta_detalle_descuento',
        'vent_venta_detalle_precio_cobro',
        'vent_venta_detalle_cantidad',
        'vent_venta_detalle_bi',
        'vent_venta_detalle_igv',
        'vent_venta_detalle_venta_id',
        'vent_venta_detalle_producto_id',
        'vent_venta_detalle_tipo_operacion',
        'vent_venta_detalle_costo_almacen',
        'vent_venta_detalle_cuenta_venta',
        'vent_venta_detalle_item',
        'vent_venta_detalle_lista_precio_id',
        'vent_venta_detalle_serie'];

    public static function listar_venta_detalle($venta_id)
    {
        try {
            $sql = "select vent_venta_detalle_producto_id,
       vent_venta_detalle_venta_id,
       vent_venta_detalle_tipo_operacion,
       sum(vent_venta_detalle_cantidad)        as vent_venta_detalle_cantidad,
       sum(vent_venta_detalle_bi)              as vent_venta_detalle_bi,
       sum(vent_venta_detalle_igv)             as vent_venta_detalle_igv,
       ROUND(sum(vent_venta_detalle_precio)/sum(vent_venta_detalle_cantidad),2)  as vent_venta_detalle_precio_unitario,
       ROUND(sum(vent_venta_detalle_precio),2)          as vent_venta_detalle_precio
from vent_venta_detalle
where vent_venta_detalle_venta_id = '$venta_id'
group by vent_venta_detalle_producto_id, vent_venta_detalle_venta_id, vent_venta_detalle_tipo_operacion, vent_venta_detalle_precio_unitario";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }

    public static function listar_venta_detalle_serie($venta_id, $producto_id)
    {
        try {
            $sql = "select vent_venta_detalle_serie
from vent_venta_detalle
where vent_venta_detalle_venta_id = '$venta_id'
  and vent_venta_detalle_producto_id = '$producto_id'";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }

}
