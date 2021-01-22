<?php


namespace App\Models\Almacen;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Compra extends Model
{
    protected $table = 'comp_compra';
    public $timestamps = false;
    protected $keyType = 'string';
    protected $casts = [
        'comp_compra_estado_pago' => 'boolean',
        'comp_compra_confirmado' => 'boolean',
        'comp_compra_validacion_1' => 'boolean',
        'comp_compra_validacion_2' => 'boolean'
    ];
    protected $primaryKey = 'comp_compra_id';
    protected $fillable = ['comp_compra_id',
        'comp_compra_total',
        'comp_compra_igv',
        'comp_compra_bi',
        'comp_compra_fecha',
        'comp_compra_tipo_venta',
        'comp_compra_serie',
        'comp_compra_estado',
        'comp_compra_numero_venta',
        'comp_compra_tipo_comprobante_id',
        'comp_compra_almacen_id',
        'comp_compra_periodo_id',
        'comp_compra_preveedor_id',
        'comp_compra_user_id',
        'comp_compra_fecha_registro',
        'comp_compra_retenciones',
        'comp_compra_estado_pago',
        'comp_compra_tipo',
        'comp_compra_almacen_destino',
        'comp_compra_confirmado',
        'comp_compra_desc',
        'comp_compra_serie_ref',
        'comp_compra_numero_venta_ref',
        'comp_compra_validacion_1',
        'comp_compra_validacion_2',
        'comp_compra_tipo_cambio',
        'comp_compra_flete',
        'comp_compra_precio_dolar',
        'comp_compra_porcentaje_utilidad',
        'comp_compra_receta_nombre',
        'comp_compra_receta_rendimiento'

    ];


    public static function listar_compras_por_pagar()
    {
        try {
            $sql = "select comp_compra_id,
       comp_compra_total,
       comp_compra_fecha,
       comp_compra_serie,
       comp_compra_estado,
       comp_compra_numero_venta,
       (select doc_tipo_comprobante_nombre
        from doc_tipo_comprabante
        where doc_tipo_comprobante_id = comp_compra_tipo_comprobante_id)      as tipo_comprobante,
       (select alm_proveedor_razon_social
        from alm_proveedor
        where alm_proveedor_id = comp_compra_preveedor_id)                    as proveedor,
       comp_compra_fecha_registro,
       comp_compra_estado_pago,
       ifnull((select sum(comp_pago_proveedores_importe)
               from comp_pago_proveedores
               where comp_pago_proveedores_id_compra = comp_compra_id), 0.00) as acuenta,

       comp_compra_total-ifnull((select sum(comp_pago_proveedores_importe)
               from comp_pago_proveedores
               where comp_pago_proveedores_id_compra = comp_compra_id), 0.00) as saldo
from comp_compra
where comp_compra_estado = 'REGISTRADO'
  and comp_compra_tipo = 'C'
  and comp_compra_confirmado = 1
  and comp_compra_estado_pago = 0";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }


    public static function listar_compra_por_pagar($id)
    {
        try {
            $sql = "select comp_compra_id,
       comp_compra_total,
       comp_compra_fecha,
       comp_compra_serie,
       comp_compra_estado,
       comp_compra_numero_venta,
       (select doc_tipo_comprobante_nombre
        from doc_tipo_comprabante
        where doc_tipo_comprobante_id = comp_compra_tipo_comprobante_id)      as tipo_comprobante,
       (select alm_proveedor_razon_social
        from alm_proveedor
        where alm_proveedor_id = comp_compra_preveedor_id)                    as proveedor,
       comp_compra_fecha_registro,
       comp_compra_estado_pago,
       ifnull((select sum(comp_pago_proveedores_importe)
               from comp_pago_proveedores
               where comp_pago_proveedores_id_compra = comp_compra_id), 0.00) as acuenta,

       comp_compra_total-ifnull((select sum(comp_pago_proveedores_importe)
               from comp_pago_proveedores
               where comp_pago_proveedores_id_compra = comp_compra_id), 0.00) as saldo
from comp_compra
where comp_compra_estado = 'REGISTRADO'
  and comp_compra_tipo = 'C'
  and comp_compra_confirmado = 1
  and comp_compra_estado_pago = 0
  and comp_compra_id='$id'
  ";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }



    public static function listar_expediente_tecnico_avance($almacen_id)
    {
        try {
            $sql = "select substr((select alm_producto_codigo
               from alm_producto
               where alm_producto_id = ccd.comp_compra_detalle_producto_id), 1, 10) as alm_producto_codigo,
       concat((select alm_producto_nombre
               from alm_producto
               where alm_producto_id = (select Parent_alm_producto_id
                                        from alm_producto
                                        where alm_producto_id = ccd.comp_compra_detalle_producto_id))
           , ' ', (select alm_producto_nombre
                   from alm_producto
                   where alm_producto_id = ccd.comp_compra_detalle_producto_id))    as alm_producto_nombre,
       ccd.comp_compra_compra_id,
       ccd.comp_compra_detalle_producto_id,
       ccd.comp_compra_detalle_id,
       ccd.comp_compra_detalle_precio_unitario                                      as et_precio_unitario,
       ccd.comp_compra_detalle_cantidad                                             as et_cantidad,
       ccd.comp_compra_detalle_igv                                                  as et_igv,
       ccd.comp_compra_detalle_bi                                                   as et_bi,
       ccd.comp_compra_detalle_precio                                               as et_precio,
       ifnull(round((select sum(comp_compra_detalle_cantidad)
        from comp_compra_detalle
        where comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
          and comp_compra_compra_id in (select comp_compra_id
                                        from comp_compra
                                        where comp_compra_tipo = 'C'
                                          and comp_compra_almacen_id = '$almacen_id'
                                          and comp_compra_confirmado = 1
        )),0),0)                                                                          as comp_cantidad,
      ifnull( round((select avg(comp_compra_detalle_precio_unitario)
        from comp_compra_detalle
        where ccd.comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
          and comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
          and comp_compra_compra_id in (select comp_compra_id
                                        from comp_compra
                                        where comp_compra_tipo = 'C'
                                          and comp_compra_almacen_id = '$almacen_id'
                                          and comp_compra_confirmado = 1/*
                                         and comp_compra_detalle_fecha_registro =
                                             (select max(comp_compra_fecha_registro)
                                              from comp_compra
                                              where comp_compra_almacen_id = '159396651674028042'
                                                and comp_compra_tipo = 'C'
                                                and comp_compra_confirmado = 1)*/

        )),2)  ,0)                                                                        as comp_precio_unitario,
    round(  ifnull ((select sum(comp_compra_detalle_cantidad)
        from comp_compra_detalle
        where comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
          and comp_compra_compra_id in (select comp_compra_id
                                        from comp_compra
                                        where comp_compra_tipo = 'C'
                                          and comp_compra_almacen_id = '$almacen_id'
                                          and comp_compra_confirmado = 1
        )),0) * ifnull((select avg(comp_compra_detalle_precio_unitario)
              from comp_compra_detalle
              where comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
                and comp_compra_compra_id in (select comp_compra_id
                                              from comp_compra
                                              where comp_compra_tipo = 'C'
                                                and comp_compra_almacen_id = '$almacen_id'
                                                and comp_compra_confirmado = 1/*
                                         and comp_compra_detalle_fecha_registro =
                                             (select max(comp_compra_fecha_registro)
                                              from comp_compra
                                              where comp_compra_almacen_id = '159396651674028042'
                                                and comp_compra_tipo = 'C'
                                                and comp_compra_confirmado = 1)*/

              )),0),2)                                                                    as comp_precio,

      round( ifnull((select sum(comp_compra_detalle_cantidad)
        from comp_compra_detalle
        where comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
          and comp_compra_compra_id in (select comp_compra_id
                                        from comp_compra
                                        where comp_compra_tipo = 'S'
                                          and comp_compra_almacen_id = '$almacen_id'
                                          and comp_compra_confirmado = 1
        )),0),0)                                                                          as sal_cantidad,

       round(ifnull((select sum(comp_compra_detalle_cantidad)
        from comp_compra_detalle
        where comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
          and comp_compra_compra_id in (select comp_compra_id
                                        from comp_compra
                                        where comp_compra_tipo = 'S'
                                          and comp_compra_almacen_id = '$almacen_id'
                                          and comp_compra_confirmado = 1)),0) *
       ifnull((select avg(comp_compra_detalle_precio_unitario)
        from comp_compra_detalle
        where comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
          and comp_compra_compra_id in (select comp_compra_id
                                        from comp_compra
                                        where comp_compra_tipo = 'c'
                                          and comp_compra_almacen_id = '$almacen_id'
                                          and comp_compra_confirmado = 1)),0) ,2)         as sal_precio

from comp_compra_detalle as ccd
where ccd.comp_compra_compra_id in
      (select comp_compra_id
       from comp_compra
       where comp_compra_almacen_id = '$almacen_id'
         and comp_compra_tipo = 'E'
         and comp_compra_confirmado = 1
         and comp_compra_detalle_fecha_registro = (select max(comp_compra_fecha_registro)
                                                   from comp_compra
                                                   where comp_compra_almacen_id = '$almacen_id'
                                                     and comp_compra_tipo = 'E'
                                                     and comp_compra_confirmado = 1))";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }


}
