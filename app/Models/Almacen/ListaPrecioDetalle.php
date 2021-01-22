<?php


namespace App\Models\Almacen;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ListaPrecioDetalle extends Model
{
    protected $table = 'alm_lista_precio_detalle';
    public $timestamps = false;
    protected $keyType = 'string';

    /*protected $casts = [
        'cont_periodo_estado' => 'boolean',
    ];*/

    protected $primaryKey = 'alm_lista_precio_detalle_id';
    protected $fillable = ['alm_lista_precio_detalle_id',
        'alm_lista_precio_detalle_lista_precio_id',
        'alm_lista_precio_detalle_articulo_id',
        'alm_lista_precio_detalle_precio'];


    public static function lista_precios_por_articulo($categoria="", $lista_precio="", $articulo="")
    {

        $query_data = "";
        if (!is_null($categoria) && $categoria != "") {
            $query_data = " and ap.Parent_alm_producto_id = '$categoria'";
        }
        $query_data_articulo = "";
        if (!is_null($articulo) && $articulo != "") {
            $query_data_articulo = "and  concat(ap.alm_producto_codigo,' ',(select alm_producto_nombre from alm_producto where alm_producto_id=ap.Parent_alm_producto_id),' ',ap.alm_producto_nombre ) like '%$articulo%'";
        }
        try {
            $sql = "select ap.alm_producto_id,
    substring(ap.alm_producto_codigo,1,10) as alm_producto_codigo,
       ap.alm_producto_marca,

       concat((select alm_producto_nombre from alm_producto where alm_producto_id=ap.Parent_alm_producto_id),' ',
       ap.alm_producto_nombre) as alm_producto_nombre,
       ap.alm_producto_modelo,
       ap.alm_producto_descripcion,
              round(ifnull((select comp_compra_detalle_costo_real_unitario
                     from comp_compra_detalle
                     where comp_compra_detalle_id =
                           (select comp_compra_detalle_id
                            from comp_compra_detalle
                            where comp_compra_detalle_precio_unitario > 0.00
                              and comp_compra_detalle_producto_id = ap.alm_producto_id
                            order by comp_compra_detalle_fecha_registro desc limit 1
                           )
                       and comp_compra_detalle_producto_id = ap.alm_producto_id
                       and comp_compra_detalle_precio_unitario > 0.00


                     limit 1),
                    0.00) * 1, 2) as comp_compra_detalle_costo_real_unitario,
       ifnull((select alm_lista_precio_detalle_precio
               from alm_lista_precio_detalle
               where alm_lista_precio_detalle_articulo_id = ap.alm_producto_id
                 and alm_lista_precio_detalle_lista_precio_id = '$lista_precio'),
              0.00) as alm_lista_precio_detalle_precio,

     case
           when (select emp_empresa_calculo_total from emp_empresa limit 1) = 1
               then
               round(ifnull((select comp_compra_detalle_precio_unitario
                             from comp_compra_detalle
                             where comp_compra_detalle_id =
                                   (select comp_compra_detalle_id
                                    from comp_compra_detalle
                                    where comp_compra_detalle_precio_unitario > 0.00
                                      and comp_compra_detalle_producto_id = ap.alm_producto_id
                                    order by comp_compra_detalle_fecha_registro desc limit 1
                                   )
                               and comp_compra_detalle_producto_id = ap.alm_producto_id
                               and comp_compra_detalle_precio_unitario > 0.00


                            limit 1),
                            0.00) * 1, 2)
           else
               round(ifnull((select comp_compra_detalle_precio_unitario
                             from comp_compra_detalle
                             where comp_compra_detalle_id =
                                   (select comp_compra_detalle_id
                                    from comp_compra_detalle
                                    where comp_compra_detalle_precio_unitario > 0.00
                                      and comp_compra_detalle_producto_id =  ap.alm_producto_id
                                    order by comp_compra_detalle_fecha_registro desc limit 1
                                   )
                               and comp_compra_detalle_producto_id =  ap.alm_producto_id
                               and comp_compra_detalle_precio_unitario > 0.00
                             limit 1),
                            0.00) /
                     (select (cont_periodo_igv / 100) + 1 from cont_periodo where cont_periodo_estado = 1), 2)
           end      as precio_anterior
from alm_producto as ap
where ap.alm_producto_nivel = '2'
  and ap.alm_producto_estado = 1 $query_data $query_data_articulo";
            $lista_precio_select = DB::select($sql);
        } catch
        (\Exception $e) {
            dd($e);
        }
        return $lista_precio_select;
    }
}
