<?php


namespace App\Models\Almacen;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Producto extends Model
{
    protected $table = 'alm_producto';
    public $timestamps = false;
    protected $keyType = 'string';

    protected $casts = [
        'alm_producto_estado' => 'boolean',
        'alm_producto_controla_stock' => 'boolean',
        'alm_producto_vehiculo' => 'boolean',
        'alm_producto_serie' => 'boolean'
    ];
    protected $primaryKey = 'alm_producto_id';
    protected $fillable = ['alm_producto_id',
        'alm_producto_nombre',
        'alm_producto_nivel',
        'alm_producto_codigo',
        'alm_producto_estado',
        'Parent_alm_producto_id',
        'alm_unidad_medida_id',
        'alm_producto_tipo_operacion',
        'alm_producto_marca',
        'alm_producto_controla_stock',
        'alm_producto_cuenta_compra',
        'alm_producto_cuenta_venta',
        'alm_producto_descripcion',
        'alm_producto_foto',
        'alm_producto_modelo',
        'alm_producto_color',
        'alm_producto_motor',
        'alm_producto_chasis',
        'alm_producto_dua',
        'alm_producto_item',
        'alm_producto_vehiculo',
        'alm_producto_serie'];


    public static function producto_autocomplete_codigo($data, $alm_almacen_id)
    {
        try {
            $sql = "select p.alm_producto_id                                           as alm_producto_id,
       case
           when p.alm_producto_vehiculo = 1 then (
               concat('DESC:', p.alm_producto_nombre,
                      ',  MARCA: ', p.alm_producto_marca,
                      ',  MODELO:', p.alm_producto_modelo,
                      ',  MOTOR:', p.alm_producto_motor,
                      ',  CHASIS:', p.alm_producto_chasis,
                      ',  DUA:', p.alm_producto_dua)
               )
           else concat(ifnull(pp.alm_producto_nombre, ''), ' ', ifnull(p.alm_producto_nombre, ''), ' ',
                       ifnull(p.alm_producto_marca, '')) end       as producto,
       p.alm_producto_tipo_operacion,
       p.alm_producto_controla_stock,
       p.alm_producto_foto,
       p.alm_producto_codigo,
       p.alm_producto_serie,
       p.alm_producto_modelo,
       p.alm_producto_marca,
       p.alm_producto_descripcion,
       pp.alm_producto_nombre                                      as categoria,
       um.alm_unidad_medida_nombre                                 as alm_unidad_medida_nombre,
       um.alm_unidad_medida_simbolo                                as alm_unidad_medida_simbolo,
       case
           when p.alm_producto_vehiculo = 1 then (
               concat('DESC:', p.alm_producto_nombre,
                      ',  MARCA:', p.alm_producto_marca,
                      ',  MODELO:', p.alm_producto_modelo,
                      ',  COLOR:', p.alm_producto_color,
                      ',  MOTOR:', p.alm_producto_motor,
                      ',  CHASIS:', p.alm_producto_chasis,
                      ',  DUA:', p.alm_producto_dua)
               )

           else concat(ifnull(pp.alm_producto_nombre, ''), ' ', ifnull(p.alm_producto_nombre, ''), ' ',
                       ifnull(p.alm_producto_marca, ''), ' ',
                       ifnull(p.alm_producto_descripcion, '')) end as producto_nombre,
       (ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = p.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_destino = '$alm_almacen_id'

                                                  and comp_compra_tipo in ('T'))
               ), 0.00) +
        ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = p.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id'

                                                  and comp_compra_tipo in ('C', 'I'))
               ), 0.00) +
        ifnull((select sum(vent_venta_detalle_cantidad)
                from vent_venta_detalle
                where vent_venta_detalle_producto_id = p.alm_producto_id
                  and vent_venta_detalle_venta_id in
                      (select vent_venta_id
                       from vent_venta
                       where vent_venta_confirmado = 1
                         and vent_venta_estado = 1
                         and vent_venta_almacen_id = '$alm_almacen_id'

                         and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                from doc_tipo_comprabante
                                                                where doc_tipo_comprobante_codigo in ('07')))),
               0.00)) - (    ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = p.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id'

                                                  and comp_compra_tipo in ('S'))
               ), 0.00)+

               ifnull((select sum(vent_venta_detalle_cantidad)
                       from vent_venta_detalle
                       where vent_venta_detalle_producto_id = p.alm_producto_id
                         and vent_venta_detalle_venta_id in
                             (select vent_venta_id
                              from vent_venta
                              where vent_venta_confirmado = 1
                                and vent_venta_estado = 1
                                and vent_venta_almacen_id = '$alm_almacen_id'

                                and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                       from doc_tipo_comprabante
                                                                       where doc_tipo_comprobante_codigo in ('03', '01', '99')))),
                      0.00) +
               ifnull((select sum(comp_compra_detalle_cantidad)
                       from comp_compra_detalle
                       where comp_compra_detalle_producto_id = p.alm_producto_id
                         and comp_compra_compra_id in (select comp_compra_id
                                                       from comp_compra
                                                       where comp_compra_confirmado = 1
                                                         and comp_compra_almacen_id = '$alm_almacen_id'

                                                         and comp_compra_tipo in ('T'))
                      ), 0.00))                                    as stock
from alm_producto as p,
     alm_producto as pp,
     alm_unidad_medida as um
where p.Parent_alm_producto_id = pp.alm_producto_id
  and p.alm_unidad_medida_id = um.alm_unidad_medida_id
  and p.alm_producto_estado = '1'
  and p.alm_producto_nivel = '2'
  and p.alm_producto_codigo = '$data'";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }


    public static function producto_autocomplete($data, $alm_almacen_id)
    {
        try {
            $sql = "select p.alm_producto_id                                           as alm_producto_id,
       case
           when p.alm_producto_vehiculo = 1 then (
               concat('DESC:', p.alm_producto_nombre,
                      ',  MARCA: ', p.alm_producto_marca,
                      ',  MODELO:', p.alm_producto_modelo,
                      ',  MOTOR:', p.alm_producto_motor,
                      ',  CHASIS:', p.alm_producto_chasis,
                      ',  DUA:', p.alm_producto_dua)
               )
           else concat(ifnull(pp.alm_producto_nombre, ''), ' ', ifnull(p.alm_producto_nombre, ''), ' ',
                       ifnull(p.alm_producto_marca, '')) end       as producto,
       p.alm_producto_tipo_operacion,
       p.alm_producto_controla_stock,
       p.alm_producto_foto,
       p.alm_producto_codigo,
       p.alm_producto_serie,
       p.alm_producto_modelo,
       p.alm_producto_marca,
       p.alm_producto_descripcion,
       pp.alm_producto_nombre                                      as categoria,
       um.alm_unidad_medida_nombre                                 as alm_unidad_medida_nombre,
       um.alm_unidad_medida_simbolo                                as alm_unidad_medida_simbolo,
       case
           when p.alm_producto_vehiculo = 1 then (
               concat('DESC:', p.alm_producto_nombre,
                      ',  MARCA:', p.alm_producto_marca,
                      ',  MODELO:', p.alm_producto_modelo,
                      ',  COLOR:', p.alm_producto_color,
                      ',  MOTOR:', p.alm_producto_motor,
                      ',  CHASIS:', p.alm_producto_chasis,
                      ',  DUA:', p.alm_producto_dua)
               )

           else concat(ifnull(pp.alm_producto_nombre, ''), ' ', ifnull(p.alm_producto_nombre, ''), ' ',
                       ifnull(p.alm_producto_marca, ''), ' ',
                       ifnull(p.alm_producto_descripcion, '')) end as producto_nombre,
       (ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = p.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_destino = '$alm_almacen_id'

                                                  and comp_compra_tipo in ('T'))
               ), 0.00) +
        ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = p.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id'

                                                  and comp_compra_tipo in ('C', 'I'))
               ), 0.00) +
        ifnull((select sum(vent_venta_detalle_cantidad)
                from vent_venta_detalle
                where vent_venta_detalle_producto_id = p.alm_producto_id
                  and vent_venta_detalle_venta_id in
                      (select vent_venta_id
                       from vent_venta
                       where vent_venta_confirmado = 1
                         and vent_venta_estado = 1
                         and vent_venta_almacen_id = '$alm_almacen_id'

                         and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                from doc_tipo_comprabante
                                                                where doc_tipo_comprobante_codigo in ('07')))),
               0.00)) - (    ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = p.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id'

                                                  and comp_compra_tipo in ('S'))
               ), 0.00)+

               ifnull((select sum(vent_venta_detalle_cantidad)
                       from vent_venta_detalle
                       where vent_venta_detalle_producto_id = p.alm_producto_id
                         and vent_venta_detalle_venta_id in
                             (select vent_venta_id
                              from vent_venta
                              where vent_venta_confirmado = 1
                                and vent_venta_estado = 1
                                and vent_venta_almacen_id = '$alm_almacen_id'

                                and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                       from doc_tipo_comprabante
                                                                       where doc_tipo_comprobante_codigo in ('03', '01', '99')))),
                      0.00) +
               ifnull((select sum(comp_compra_detalle_cantidad)
                       from comp_compra_detalle
                       where comp_compra_detalle_producto_id = p.alm_producto_id
                         and comp_compra_compra_id in (select comp_compra_id
                                                       from comp_compra
                                                       where comp_compra_confirmado = 1
                                                         and comp_compra_almacen_id = '$alm_almacen_id'

                                                         and comp_compra_tipo in ('T'))
                      ), 0.00))                                    as stock
from alm_producto as p,
     alm_producto as pp,
     alm_unidad_medida as um
where p.Parent_alm_producto_id = pp.alm_producto_id
  and p.alm_unidad_medida_id = um.alm_unidad_medida_id
  and p.alm_producto_estado = '1'
  and p.alm_producto_nivel = '2'
  and concat(ifnull(pp.alm_producto_nombre, ''), ' ', ifnull(p.alm_producto_nombre, ''), ' ',
             ifnull(p.alm_producto_marca, ''), ' ', ifnull(p.alm_producto_modelo, ''), ' ',
             ifnull(p.alm_producto_motor, ''), ' ', ifnull(p.alm_producto_chasis, ''), ' ',
             ifnull(p.alm_producto_codigo, ''), ' ', ifnull(p.alm_producto_dua, '')
          ) LIKE '%$data%'";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
        }
        return $Query;
    }


    public static function producto_autocomplete_venta($data, $alm_almacen_id)
    {
        try {
            $sql = "select p.alm_producto_id                                           as alm_producto_id,
       case
           when p.alm_producto_vehiculo = 1 then (
               concat('DESC:', p.alm_producto_nombre,
                      ',  MARCA: ', p.alm_producto_marca,
                      ',  MODELO:', p.alm_producto_modelo,
                      ',  MOTOR:', p.alm_producto_motor,
                      ',  CHASIS:', p.alm_producto_chasis,
                      ',  DUA:', p.alm_producto_dua)
               )
           else concat(ifnull(pp.alm_producto_nombre, ''), ' ', ifnull(p.alm_producto_nombre, ''), ' ',
                       ifnull(p.alm_producto_marca, '')) end       as producto,
       p.alm_producto_tipo_operacion,
       p.alm_producto_controla_stock,
       p.alm_producto_foto,
       p.alm_producto_codigo,
       p.alm_producto_serie,
       p.alm_producto_modelo,
       p.alm_producto_marca,
       p.alm_producto_descripcion,
       pp.alm_producto_nombre                                      as categoria,
       um.alm_unidad_medida_nombre                                 as alm_unidad_medida_nombre,
       um.alm_unidad_medida_simbolo                                as alm_unidad_medida_simbolo,
       case
           when p.alm_producto_vehiculo = 1 then (
               concat('DESC:', p.alm_producto_nombre,
                      ',  MARCA:', p.alm_producto_marca,
                      ',  MODELO:', p.alm_producto_modelo,
                      ',  COLOR:', p.alm_producto_color,
                      ',  MOTOR:', p.alm_producto_motor,
                      ',  CHASIS:', p.alm_producto_chasis,
                      ',  DUA:', p.alm_producto_dua)
               )

           else concat(ifnull(pp.alm_producto_nombre, ''), ' ', ifnull(p.alm_producto_nombre, ''), ' ',
                       ifnull(p.alm_producto_marca, ''), ' ',
                       ifnull(p.alm_producto_descripcion, '')) end as producto_nombre,
       --  case
       --   when p.alm_producto_controla_stock = '1' then
       (ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = p.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_destino = '$alm_almacen_id'

                                                  and comp_compra_tipo in ('T'))
               ), 0.00) +
        ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = p.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id'

                                                  and comp_compra_tipo in ('C', 'I'))
               ), 0.00) +
        ifnull((select sum(vent_venta_detalle_cantidad)
                from vent_venta_detalle
                where vent_venta_detalle_producto_id = p.alm_producto_id
                  and vent_venta_detalle_venta_id in
                      (select vent_venta_id
                       from vent_venta
                       where vent_venta_confirmado = 1
                         and vent_venta_estado = 1
                         and vent_venta_almacen_id = '$alm_almacen_id'

                         and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                from doc_tipo_comprabante
                                                                where doc_tipo_comprobante_codigo in ('07')))),
               0.00)) - (      ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = p.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id'

                                                  and comp_compra_tipo in ('S'))
               ), 0.00) +
               ifnull((select sum(vent_venta_detalle_cantidad)
                       from vent_venta_detalle
                       where vent_venta_detalle_producto_id = p.alm_producto_id
                         and vent_venta_detalle_venta_id in
                             (select vent_venta_id
                              from vent_venta
                              where vent_venta_confirmado = 1
                                and vent_venta_estado = 1
                                and vent_venta_almacen_id = '$alm_almacen_id'

                                and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                       from doc_tipo_comprabante
                                                                       where doc_tipo_comprobante_codigo in ('03', '01', '99')))),
                      0.00) +
               ifnull((select sum(comp_compra_detalle_cantidad)
                       from comp_compra_detalle
                       where comp_compra_detalle_producto_id = p.alm_producto_id
                         and comp_compra_compra_id in (select comp_compra_id
                                                       from comp_compra
                                                       where comp_compra_confirmado = 1
                                                         and comp_compra_almacen_id = '$alm_almacen_id'

                                                         and comp_compra_tipo in ('T'))
                      ), 0.00))                                    as stock,
       ''                                                          as serie

from alm_producto as p,
     alm_producto as pp,
     alm_unidad_medida as um
where p.Parent_alm_producto_id = pp.alm_producto_id
  and p.alm_unidad_medida_id = um.alm_unidad_medida_id
  and p.alm_producto_estado = '1'
  and p.alm_producto_nivel = '2'
  and concat(ifnull(pp.alm_producto_nombre, ''), ' ', ifnull(p.alm_producto_nombre, ''), ' ',
             ifnull(p.alm_producto_marca, ''), ' ', ifnull(p.alm_producto_modelo, ''), ' ',
             ifnull(p.alm_producto_motor, ''), ' ', ifnull(p.alm_producto_chasis, ''), ' ',
             ifnull(p.alm_producto_codigo, ''), ' ', ifnull(p.alm_producto_dua, '')
          ) LIKE '%$data%'
union ALL

select comp_compra_detalle_producto_id                                                                 as alm_producto_id,
       case
           when ifnull((select alm_producto_vehiculo
                        from alm_producto
                        where alm_producto_id = comp_compra_detalle_producto_id), 0) = 1 then (
               concat('DESC:', (select alm_producto_nombre
                                from alm_producto
                                where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MARCA: ', (select alm_producto_marca
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MODELO:', (select alm_producto_modelo
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MOTOR:', (select alm_producto_motor
                                    from alm_producto
                                    where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  CHASIS:', (select alm_producto_chasis
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  DUA:', (select alm_producto_dua
                                  from alm_producto
                                  where alm_producto_id = comp_compra_detalle_producto_id))
               )

           else concat(
                   (select alm_producto_nombre
                    from alm_producto
                    where alm_producto_id = ifnull((select Parent_alm_producto_id
                                                    from alm_producto
                                                    where alm_producto_id = comp_compra_detalle_producto_id), ''))
               , ' ', ifnull((select alm_producto_nombre
                              from alm_producto
                              where alm_producto_id = comp_compra_detalle_producto_id), ''), ' ',
                   ifnull((select alm_producto_marca
                           from alm_producto
                           where alm_producto_id = comp_compra_detalle_producto_id), '')) end          as producto,
       (select alm_producto_tipo_operacion
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_tipo_operacion,
       (select alm_producto_controla_stock
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_controla_stock,
       (select alm_producto_foto
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_foto,
       (select alm_producto_codigo
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_codigo,
       (select alm_producto_serie
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_serie,
       (select alm_producto_modelo
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_modelo,
       (select alm_producto_marca
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_marca,
       (select alm_producto_descripcion
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_descripcion,

       (select alm_producto_nombre
        from alm_producto
        where alm_producto_id = ifnull((select Parent_alm_producto_id
                                        from alm_producto
                                        where alm_producto_id = comp_compra_detalle_producto_id), '')) as categoria,


       (select alm_unidad_medida_nombre
        from alm_unidad_medida
        where alm_unidad_medida_id = (select alm_producto.alm_unidad_medida_id
                                      from alm_producto
                                      where alm_producto_id = comp_compra_detalle_producto_id))        as alm_unidad_medida_nombre,

       (select alm_unidad_medida_simbolo
        from alm_unidad_medida
        where alm_unidad_medida_id = (select alm_producto.alm_unidad_medida_id
                                      from alm_producto
                                      where alm_producto_id = comp_compra_detalle_producto_id))        as alm_unidad_medida_simbolo,
       case
           when ifnull((select alm_producto_vehiculo
                        from alm_producto
                        where alm_producto_id = comp_compra_detalle_producto_id), 0) = 1 then (
               concat('DESC:', (select alm_producto_nombre
                                from alm_producto
                                where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MARCA: ', (select alm_producto_marca
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MODELO:', (select alm_producto_modelo
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MOTOR:', (select alm_producto_motor
                                    from alm_producto
                                    where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  CHASIS:', (select alm_producto_chasis
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  DUA:', (select alm_producto_dua
                                  from alm_producto
                                  where alm_producto_id = comp_compra_detalle_producto_id))
               )

           else concat(
                   (select alm_producto_nombre
                    from alm_producto
                    where alm_producto_id = ifnull((select Parent_alm_producto_id
                                                    from alm_producto
                                                    where alm_producto_id = comp_compra_detalle_producto_id), ''))
               , ' ', ifnull((select alm_producto_nombre
                              from alm_producto
                              where alm_producto_id = comp_compra_detalle_producto_id), ''), ' ',
                   ifnull((select alm_producto_marca
                           from alm_producto
                           where alm_producto_id = comp_compra_detalle_producto_id), '')
               , ' ',
                   ifnull((select alm_producto_descripcion
                           from alm_producto
                           where alm_producto_id = comp_compra_detalle_producto_id), '')
               ) end                                                                                   as producto_nombre,
       1                                                                                               as stock,
       comp_compra_detalle_serie                                                                       as serie

from comp_compra_detalle
where comp_compra_detalle_vendido = 0
  and comp_compra_compra_id in
      (select comp_compra_id
       from comp_compra
       where comp_compra_almacen_id = '$alm_almacen_id'
         and comp_compra_confirmado = 1)

  and concat(
              (select alm_producto_nombre
               from alm_producto
               where alm_producto_id = ifnull((select Parent_alm_producto_id
                                               from alm_producto
                                               where alm_producto_id = comp_compra_detalle_producto_id), ''))
          , ' ', ifnull((select alm_producto_nombre
                         from alm_producto
                         where alm_producto_id = comp_compra_detalle_producto_id), ''), ' ',
              ifnull((select alm_producto_marca
                      from alm_producto
                      where alm_producto_id = comp_compra_detalle_producto_id), '')
          , ' ',
              ifnull((select alm_producto_descripcion
                      from alm_producto
                      where alm_producto_id = comp_compra_detalle_producto_id), ''), '', comp_compra_detalle_serie
          ) LIKE '%$data%'


union ALL

select comp_compra_detalle_producto_id                                                                 as alm_producto_id,
       case
           when ifnull((select alm_producto_vehiculo
                        from alm_producto
                        where alm_producto_id = comp_compra_detalle_producto_id), 0) = 1 then (
               concat('DESC:', (select alm_producto_nombre
                                from alm_producto
                                where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MARCA: ', (select alm_producto_marca
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MODELO:', (select alm_producto_modelo
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MOTOR:', (select alm_producto_motor
                                    from alm_producto
                                    where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  CHASIS:', (select alm_producto_chasis
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  DUA:', (select alm_producto_dua
                                  from alm_producto
                                  where alm_producto_id = comp_compra_detalle_producto_id))
               )

           else concat(
                   (select alm_producto_nombre
                    from alm_producto
                    where alm_producto_id = ifnull((select Parent_alm_producto_id
                                                    from alm_producto
                                                    where alm_producto_id = comp_compra_detalle_producto_id), ''))
               , ' ', ifnull((select alm_producto_nombre
                              from alm_producto
                              where alm_producto_id = comp_compra_detalle_producto_id), ''), ' ',
                   ifnull((select alm_producto_marca
                           from alm_producto
                           where alm_producto_id = comp_compra_detalle_producto_id), '')) end          as producto,
       (select alm_producto_tipo_operacion
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_tipo_operacion,
       (select alm_producto_controla_stock
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_controla_stock,
       (select alm_producto_foto
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_foto,
       (select alm_producto_codigo
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_codigo,
       (select alm_producto_serie
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_serie,
       (select alm_producto_modelo
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_modelo,
       (select alm_producto_marca
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_marca,
       (select alm_producto_descripcion
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_descripcion,

       (select alm_producto_nombre
        from alm_producto
        where alm_producto_id = ifnull((select Parent_alm_producto_id
                                        from alm_producto
                                        where alm_producto_id = comp_compra_detalle_producto_id), '')) as categoria,


       (select alm_unidad_medida_nombre
        from alm_unidad_medida
        where alm_unidad_medida_id = (select alm_producto.alm_unidad_medida_id
                                      from alm_producto
                                      where alm_producto_id = comp_compra_detalle_producto_id))        as alm_unidad_medida_nombre,

       (select alm_unidad_medida_simbolo
        from alm_unidad_medida
        where alm_unidad_medida_id = (select alm_producto.alm_unidad_medida_id
                                      from alm_producto
                                      where alm_producto_id = comp_compra_detalle_producto_id))        as alm_unidad_medida_simbolo,
       case
           when ifnull((select alm_producto_vehiculo
                        from alm_producto
                        where alm_producto_id = comp_compra_detalle_producto_id), 0) = 1 then (
               concat('DESC:', (select alm_producto_nombre
                                from alm_producto
                                where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MARCA: ', (select alm_producto_marca
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MODELO:', (select alm_producto_modelo
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MOTOR:', (select alm_producto_motor
                                    from alm_producto
                                    where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  CHASIS:', (select alm_producto_chasis
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  DUA:', (select alm_producto_dua
                                  from alm_producto
                                  where alm_producto_id = comp_compra_detalle_producto_id))
               )

           else concat(
                   (select alm_producto_nombre
                    from alm_producto
                    where alm_producto_id = ifnull((select Parent_alm_producto_id
                                                    from alm_producto
                                                    where alm_producto_id = comp_compra_detalle_producto_id), ''))
               , ' ', ifnull((select alm_producto_nombre
                              from alm_producto
                              where alm_producto_id = comp_compra_detalle_producto_id), ''), ' ',
                   ifnull((select alm_producto_marca
                           from alm_producto
                           where alm_producto_id = comp_compra_detalle_producto_id), '')
               , ' ',
                   ifnull((select alm_producto_descripcion
                           from alm_producto
                           where alm_producto_id = comp_compra_detalle_producto_id), '')
               ) end                                                                                   as producto_nombre,
       1                                                                                               as stock,
       comp_compra_detalle_serie                                                                       as serie

from comp_compra_detalle
where comp_compra_detalle_vendido = 0
  and comp_compra_compra_id in
      (select comp_compra_id
       from comp_compra
       where comp_compra_almacen_destino = '$alm_almacen_id'
         and comp_compra_confirmado = 1)

  and concat(
              (select alm_producto_nombre
               from alm_producto
               where alm_producto_id = ifnull((select Parent_alm_producto_id
                                               from alm_producto
                                               where alm_producto_id = comp_compra_detalle_producto_id), ''))
          , ' ', ifnull((select alm_producto_nombre
                         from alm_producto
                         where alm_producto_id = comp_compra_detalle_producto_id), ''), ' ',
              ifnull((select alm_producto_marca
                      from alm_producto
                      where alm_producto_id = comp_compra_detalle_producto_id), '')
          , ' ',
              ifnull((select alm_producto_descripcion
                      from alm_producto
                      where alm_producto_id = comp_compra_detalle_producto_id), ''), '', comp_compra_detalle_serie
          ) LIKE '%$data%'";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }


    public static function producto_autocomplete_venta_serie_codigo($data, $alm_almacen_id)
    {
        try {
            $sql = "select p.alm_producto_id                                           as alm_producto_id,
       case
           when p.alm_producto_vehiculo = 1 then (
               concat('DESC:', p.alm_producto_nombre,
                      ',  MARCA: ', p.alm_producto_marca,
                      ',  MODELO:', p.alm_producto_modelo,
                      ',  MOTOR:', p.alm_producto_motor,
                      ',  CHASIS:', p.alm_producto_chasis,
                      ',  DUA:', p.alm_producto_dua)
               )
           else concat(ifnull(pp.alm_producto_nombre, ''), ' ', ifnull(p.alm_producto_nombre, ''), ' ',
                       ifnull(p.alm_producto_marca, '')) end       as producto,
       p.alm_producto_tipo_operacion,
       p.alm_producto_controla_stock,
       p.alm_producto_foto,
       p.alm_producto_codigo,
       p.alm_producto_serie,
       p.alm_producto_modelo,
       p.alm_producto_marca,
       p.alm_producto_descripcion,
       pp.alm_producto_nombre                                      as categoria,
       um.alm_unidad_medida_nombre                                 as alm_unidad_medida_nombre,
       um.alm_unidad_medida_simbolo                                as alm_unidad_medida_simbolo,
       case
           when p.alm_producto_vehiculo = 1 then (
               concat('DESC:', p.alm_producto_nombre,
                      ',  MARCA:', p.alm_producto_marca,
                      ',  MODELO:', p.alm_producto_modelo,
                      ',  COLOR:', p.alm_producto_color,
                      ',  MOTOR:', p.alm_producto_motor,
                      ',  CHASIS:', p.alm_producto_chasis,
                      ',  DUA:', p.alm_producto_dua)
               )

           else concat(ifnull(pp.alm_producto_nombre, ''), ' ', ifnull(p.alm_producto_nombre, ''), ' ',
                       ifnull(p.alm_producto_marca, ''), ' ',
                       ifnull(p.alm_producto_descripcion, '')) end as producto_nombre,
       case
           when p.alm_producto_controla_stock = '1' then
               (ifnull(
                        (select sum(b.comp_compra_detalle_cantidad)
                         from comp_compra a,
                              comp_compra_detalle b
                         where a.comp_compra_id = b.comp_compra_compra_id
                           and a.comp_compra_estado = '1'
                           and a.comp_compra_periodo_id in (select cont_periodo_id
                                                            from cont_periodo
                                                            where cont_periodo_anio in (select cont_periodo_anio
                                                                                        from cont_periodo
                                                                                        where cont_periodo_estado = '1'))
                           and b.comp_compra_detalle_producto_id = p.alm_producto_id
                           and a.comp_compra_almacen_id = '$alm_almacen_id'
                        ), 0)
                    - (ifnull((
                                  select sum(b.vent_venta_detalle_cantidad)
                                  from vent_venta a,
                                       vent_venta_detalle b
                                  where a.vent_venta_id = b.vent_venta_detalle_venta_id
                                    and a.vent_venta_estado = '1'
                                    and b.vent_venta_detalle_producto_id = p.alm_producto_id
                                    and a.vent_venta_almacen_id = '$alm_almacen_id'
                                    and a.vent_venta_tipo_comprobante_id in
                                        ((select doc_tipo_comprobante_id
                                          from doc_tipo_comprabante
                                          where doc_tipo_comprobante_codigo != '07'))
                              ), 0))
                   + (ifnull((
                                 select sum(b.vent_venta_detalle_cantidad)
                                 from vent_venta a,
                                      vent_venta_detalle b
                                 where a.vent_venta_id = b.vent_venta_detalle_venta_id
                                   and b.vent_venta_detalle_producto_id = p.alm_producto_id
                                   and a.vent_venta_estado = '1'
                                   and a.vent_venta_almacen_id = '$alm_almacen_id'
                                   and a.vent_venta_tipo_comprobante_id in
                                       ((select doc_tipo_comprobante_id
                                         from doc_tipo_comprabante
                                         where doc_tipo_comprobante_codigo = '07'))
                             ), 0))
                   )
           else 0 end                                              as stock,
       ''                                                          as serie
from alm_producto as p,
     alm_producto as pp,
     alm_unidad_medida as um
where p.Parent_alm_producto_id = pp.alm_producto_id
  and p.alm_unidad_medida_id = um.alm_unidad_medida_id
  and p.alm_producto_estado = '1'
  and p.alm_producto_nivel = '2'
  and p.alm_producto_codigo='$data'
union ALL

select comp_compra_detalle_producto_id                                                                 as alm_producto_id,
       case
           when ifnull((select alm_producto_vehiculo
                        from alm_producto
                        where alm_producto_id = comp_compra_detalle_producto_id), 0) = 1 then (
               concat('DESC:', (select alm_producto_nombre
                                from alm_producto
                                where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MARCA: ', (select alm_producto_marca
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MODELO:', (select alm_producto_modelo
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MOTOR:', (select alm_producto_motor
                                    from alm_producto
                                    where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  CHASIS:', (select alm_producto_chasis
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  DUA:', (select alm_producto_dua
                                  from alm_producto
                                  where alm_producto_id = comp_compra_detalle_producto_id))
               )

           else concat(
                   (select alm_producto_nombre
                    from alm_producto
                    where alm_producto_id = ifnull((select Parent_alm_producto_id
                                                    from alm_producto
                                                    where alm_producto_id = comp_compra_detalle_producto_id), ''))
               , ' ', ifnull((select alm_producto_nombre
                              from alm_producto
                              where alm_producto_id = comp_compra_detalle_producto_id), ''), ' ',
                   ifnull((select alm_producto_marca
                           from alm_producto
                           where alm_producto_id = comp_compra_detalle_producto_id), '')) end          as producto,
       (select alm_producto_tipo_operacion
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_tipo_operacion,
       (select alm_producto_controla_stock
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_controla_stock,
       (select alm_producto_foto
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_foto,
       (select alm_producto_codigo
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_codigo,
       (select alm_producto_serie
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_serie,
       (select alm_producto_modelo
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_modelo,
       (select alm_producto_marca
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_marca,
       (select alm_producto_descripcion
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_descripcion,

       (select alm_producto_nombre
        from alm_producto
        where alm_producto_id = ifnull((select Parent_alm_producto_id
                                        from alm_producto
                                        where alm_producto_id = comp_compra_detalle_producto_id), '')) as categoria,


       (select alm_unidad_medida_nombre
        from alm_unidad_medida
        where alm_unidad_medida_id = (select alm_producto.alm_unidad_medida_id
                                      from alm_producto
                                      where alm_producto_id = comp_compra_detalle_producto_id))        as alm_unidad_medida_nombre,

       (select alm_unidad_medida_simbolo
        from alm_unidad_medida
        where alm_unidad_medida_id = (select alm_producto.alm_unidad_medida_id
                                      from alm_producto
                                      where alm_producto_id = comp_compra_detalle_producto_id))        as alm_unidad_medida_simbolo,
       case
           when ifnull((select alm_producto_vehiculo
                        from alm_producto
                        where alm_producto_id = comp_compra_detalle_producto_id), 0) = 1 then (
               concat('DESC:', (select alm_producto_nombre
                                from alm_producto
                                where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MARCA: ', (select alm_producto_marca
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MODELO:', (select alm_producto_modelo
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MOTOR:', (select alm_producto_motor
                                    from alm_producto
                                    where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  CHASIS:', (select alm_producto_chasis
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  DUA:', (select alm_producto_dua
                                  from alm_producto
                                  where alm_producto_id = comp_compra_detalle_producto_id))
               )

           else concat(
                   (select alm_producto_nombre
                    from alm_producto
                    where alm_producto_id = ifnull((select Parent_alm_producto_id
                                                    from alm_producto
                                                    where alm_producto_id = comp_compra_detalle_producto_id), ''))
               , ' ', ifnull((select alm_producto_nombre
                              from alm_producto
                              where alm_producto_id = comp_compra_detalle_producto_id), ''), ' ',
                   ifnull((select alm_producto_marca
                           from alm_producto
                           where alm_producto_id = comp_compra_detalle_producto_id), '')
               , ' ',
                   ifnull((select alm_producto_descripcion
                           from alm_producto
                           where alm_producto_id = comp_compra_detalle_producto_id), '')
               ) end                                                                                   as producto_nombre,
       1                                                                                               as stock,
       comp_compra_detalle_serie as serie

from comp_compra_detalle
where comp_compra_detalle_vendido = 0
  and comp_compra_compra_id in
      (select comp_compra_id
       from comp_compra
       where comp_compra_almacen_id = '$alm_almacen_id' and comp_compra_confirmado = 1)

  and comp_compra_detalle_serie='$data'



union ALL

select comp_compra_detalle_producto_id                                                                 as alm_producto_id,
       case
           when ifnull((select alm_producto_vehiculo
                        from alm_producto
                        where alm_producto_id = comp_compra_detalle_producto_id), 0) = 1 then (
               concat('DESC:', (select alm_producto_nombre
                                from alm_producto
                                where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MARCA: ', (select alm_producto_marca
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MODELO:', (select alm_producto_modelo
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MOTOR:', (select alm_producto_motor
                                    from alm_producto
                                    where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  CHASIS:', (select alm_producto_chasis
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  DUA:', (select alm_producto_dua
                                  from alm_producto
                                  where alm_producto_id = comp_compra_detalle_producto_id))
               )

           else concat(
                   (select alm_producto_nombre
                    from alm_producto
                    where alm_producto_id = ifnull((select Parent_alm_producto_id
                                                    from alm_producto
                                                    where alm_producto_id = comp_compra_detalle_producto_id), ''))
               , ' ', ifnull((select alm_producto_nombre
                              from alm_producto
                              where alm_producto_id = comp_compra_detalle_producto_id), ''), ' ',
                   ifnull((select alm_producto_marca
                           from alm_producto
                           where alm_producto_id = comp_compra_detalle_producto_id), '')) end          as producto,
       (select alm_producto_tipo_operacion
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_tipo_operacion,
       (select alm_producto_controla_stock
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_controla_stock,
       (select alm_producto_foto
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_foto,
       (select alm_producto_codigo
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_codigo,
       (select alm_producto_serie
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_serie,
       (select alm_producto_modelo
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_modelo,
       (select alm_producto_marca
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_marca,
       (select alm_producto_descripcion
        from alm_producto
        where alm_producto_id = comp_compra_detalle_producto_id)                                       as alm_producto_descripcion,

       (select alm_producto_nombre
        from alm_producto
        where alm_producto_id = ifnull((select Parent_alm_producto_id
                                        from alm_producto
                                        where alm_producto_id = comp_compra_detalle_producto_id), '')) as categoria,


       (select alm_unidad_medida_nombre
        from alm_unidad_medida
        where alm_unidad_medida_id = (select alm_producto.alm_unidad_medida_id
                                      from alm_producto
                                      where alm_producto_id = comp_compra_detalle_producto_id))        as alm_unidad_medida_nombre,

       (select alm_unidad_medida_simbolo
        from alm_unidad_medida
        where alm_unidad_medida_id = (select alm_producto.alm_unidad_medida_id
                                      from alm_producto
                                      where alm_producto_id = comp_compra_detalle_producto_id))        as alm_unidad_medida_simbolo,
       case
           when ifnull((select alm_producto_vehiculo
                        from alm_producto
                        where alm_producto_id = comp_compra_detalle_producto_id), 0) = 1 then (
               concat('DESC:', (select alm_producto_nombre
                                from alm_producto
                                where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MARCA: ', (select alm_producto_marca
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MODELO:', (select alm_producto_modelo
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  MOTOR:', (select alm_producto_motor
                                    from alm_producto
                                    where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  CHASIS:', (select alm_producto_chasis
                                     from alm_producto
                                     where alm_producto_id = comp_compra_detalle_producto_id),
                      ',  DUA:', (select alm_producto_dua
                                  from alm_producto
                                  where alm_producto_id = comp_compra_detalle_producto_id))
               )

           else concat(
                   (select alm_producto_nombre
                    from alm_producto
                    where alm_producto_id = ifnull((select Parent_alm_producto_id
                                                    from alm_producto
                                                    where alm_producto_id = comp_compra_detalle_producto_id), ''))
               , ' ', ifnull((select alm_producto_nombre
                              from alm_producto
                              where alm_producto_id = comp_compra_detalle_producto_id), ''), ' ',
                   ifnull((select alm_producto_marca
                           from alm_producto
                           where alm_producto_id = comp_compra_detalle_producto_id), '')
               , ' ',
                   ifnull((select alm_producto_descripcion
                           from alm_producto
                           where alm_producto_id = comp_compra_detalle_producto_id), '')
               ) end                                                                                   as producto_nombre,
       1                                                                                               as stock,
       comp_compra_detalle_serie as serie

from comp_compra_detalle
where comp_compra_detalle_vendido = 0
  and comp_compra_compra_id in
      (select comp_compra_id
       from comp_compra
       where comp_compra_almacen_destino = '$alm_almacen_id' and comp_compra_confirmado = 1)

  and comp_compra_detalle_serie='$data' limit 1";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }

    public static function producto_stock($data, $alm_almacen_id, $fecha, $categoria_id, $marca)
    {
        $query_data = "";
        if (!is_null($data) && $data != "") {
            $query_data = "and concat(ifnull(cat.alm_producto_nombre,''), ' ', ifnull(ap.alm_producto_nombre,''), ' ',
                ifnull(ap.alm_producto_codigo,''), ' ', ifnull(ap.alm_producto_descripcion,'')) LIKE '%" . $data . "%'";

        }
        $query_categoria = "";
        if (!is_null($categoria_id) && $categoria_id != "") {
            $query_categoria = "and cat.alm_producto_id = '$categoria_id'";
        }
        $query_marca = "";
        if (!is_null($marca) && $marca != "") {
            $query_marca = "and ap.alm_producto_marca = '$marca'";
        }


        try {
            $sql = "select ap.alm_producto_id, cat.alm_producto_nombre as categoria,
       substring(ap.alm_producto_codigo,1,10) as alm_producto_codigo,
       ap.alm_producto_serie,
concat(cat.alm_producto_nombre, ' ', ap.alm_producto_nombre) as alm_producto_nombre,
       ap.alm_producto_marca,
       ap.alm_producto_modelo,
       ap.alm_producto_descripcion,
       ap.alm_producto_controla_stock,
ifnull((select comp_compra_detalle_precio_unitario
        from comp_compra_detalle
        where comp_compra_detalle_producto_id = ap.alm_producto_id
        limit 1
       ),0.00) as costo,
       (ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_destino = '$alm_almacen_id'
                                                  and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                  and comp_compra_tipo in ('T'))
               ), 0.00) +
        ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id'
                                                  and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                  and comp_compra_tipo in ('C', 'I'))
               ), 0.00) +
        ifnull((select sum(vent_venta_detalle_cantidad)
                from vent_venta_detalle
                where vent_venta_detalle_producto_id = ap.alm_producto_id
                  and vent_venta_detalle_venta_id in
                      (select vent_venta_id
                       from vent_venta
                       where vent_venta_confirmado = 1
                         and vent_venta_estado = 1
                         and vent_venta_almacen_id = '$alm_almacen_id'
                         and DATE(vent_venta_fecha) <= DATE('$fecha')
                         and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                from doc_tipo_comprabante
                                                                where doc_tipo_comprobante_codigo in ('07')))),
               0.00)) - (
        ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id'
                                                  and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                  and comp_compra_tipo in ('S'))
               ), 0.00)+

               ifnull((select sum(vent_venta_detalle_cantidad)
                       from vent_venta_detalle
                       where vent_venta_detalle_producto_id = ap.alm_producto_id
                         and vent_venta_detalle_venta_id in
                             (select vent_venta_id
                              from vent_venta
                              where vent_venta_confirmado = 1
                                and vent_venta_estado = 1
                                and vent_venta_almacen_id = '$alm_almacen_id'
                                and DATE(vent_venta_fecha) <= DATE('$fecha')
                                and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                       from doc_tipo_comprabante
                                                                       where doc_tipo_comprobante_codigo in ('03', '01', '99')))),
                      0.00) +
               ifnull((select sum(comp_compra_detalle_cantidad)
                       from comp_compra_detalle
                       where comp_compra_detalle_producto_id = ap.alm_producto_id
                         and comp_compra_compra_id in (select comp_compra_id
                                                       from comp_compra
                                                       where comp_compra_confirmado = 1
                                                         and comp_compra_almacen_id = '$alm_almacen_id'
                                                         and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                         and comp_compra_tipo in ('T'))
                      ), 0.00)) as stock
from alm_producto as ap,
     alm_producto as cat
where ap.Parent_alm_producto_id = cat.alm_producto_id
  and   ap.alm_producto_estado='1'
  and ap.alm_producto_nivel=2 $query_data $query_categoria $query_marca";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }


    public static function producto_kardex($alm_almacen_id, $fecha_inicio, $fecha_fin, $producto_id)
    {


        try {
            $sql = "select 'INGRESO'                                                   as tipo,
       concat(cat.alm_producto_nombre, ' ', ap.alm_producto_nombre ,' ',ap.alm_producto_marca ) as producto,
       DATE_SUB(DATE('$fecha_inicio'), INTERVAL 1 DAY)             as fecha,
       'SALDO ANTERIOR'                                            as descripcion,
       ''                                                          as serie,
       ''                                                          as numero,
       ''                                                          as estado,
       ap.alm_unidad_medida_id                                     as unidad_medida_ingreso,

       (ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_destino = '$alm_almacen_id'
                                                  and DATE(comp_compra_fecha_registro) <=
                                                      DATE_SUB(DATE('$fecha_inicio'), INTERVAL 1 DAY)
                                                  and comp_compra_tipo in ('T'))
               ), 0.00) +
        ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id'
                                                  and DATE(comp_compra_fecha_registro) <=
                                                      DATE_SUB(DATE('$fecha_inicio'), INTERVAL 1 DAY)
                                                  and comp_compra_tipo in ('C', 'I'))
               ), 0.00) +
        ifnull((select sum(vent_venta_detalle_cantidad)
                from vent_venta_detalle
                where vent_venta_detalle_producto_id = ap.alm_producto_id
                  and vent_venta_detalle_venta_id in
                      (select vent_venta_id
                       from vent_venta
                       where vent_venta_confirmado = 1
                         and vent_venta_estado = 1
                         and vent_venta_almacen_id = '$alm_almacen_id'

                         and DATE(vent_venta_fecha_registro) <= DATE_SUB(DATE('$fecha_inicio'), INTERVAL 1 DAY)
                         and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                from doc_tipo_comprabante
                                                                where doc_tipo_comprobante_codigo in ('07')))),
               0.00)) - (
               ifnull((select sum(vent_venta_detalle_cantidad)
                       from vent_venta_detalle
                       where vent_venta_detalle_producto_id = ap.alm_producto_id
                         and vent_venta_detalle_venta_id in
                             (select vent_venta_id
                              from vent_venta
                              where vent_venta_confirmado = 1
                                and vent_venta_estado = 1
                                and vent_venta_almacen_id = '$alm_almacen_id'
                                and DATE(vent_venta_fecha_registro) <= DATE_SUB(DATE('$fecha_inicio'), INTERVAL 1 DAY)
                                and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                       from doc_tipo_comprabante
                                                                       where doc_tipo_comprobante_codigo in ('03', '01', '99')))),
                      0.00) +
               ifnull((select sum(comp_compra_detalle_cantidad)
                       from comp_compra_detalle
                       where comp_compra_detalle_producto_id = ap.alm_producto_id
                         and comp_compra_compra_id in (select comp_compra_id
                                                       from comp_compra
                                                       where comp_compra_confirmado = 1
                                                         and comp_compra_almacen_id = '$alm_almacen_id'

                                                         and DATE(comp_compra_fecha_registro) <=
                                                             DATE_SUB(DATE('$fecha_inicio'), INTERVAL 1 DAY)
                                                         and comp_compra_tipo in ('T'))
                      ), 0.00))                                    as ingresos,
       ''                                                          as unidad_medida_salida,
       ''                                                          as salidas,
       (ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_destino = '$alm_almacen_id'
                                                  and DATE(comp_compra_fecha_registro) <=
                                                      DATE_SUB(DATE('$fecha_inicio'), INTERVAL 1 DAY)
                                                  and comp_compra_tipo in ('T'))
               ), 0.00) +
        ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id'
                                                  and DATE(comp_compra_fecha_registro) <=
                                                      DATE_SUB(DATE('$fecha_inicio'), INTERVAL 1 DAY)
                                                  and comp_compra_tipo in ('C', 'I'))
               ), 0.00) +
        ifnull((select sum(vent_venta_detalle_cantidad)
                from vent_venta_detalle
                where vent_venta_detalle_producto_id = ap.alm_producto_id
                  and vent_venta_detalle_venta_id in
                      (select vent_venta_id
                       from vent_venta
                       where vent_venta_confirmado = 1
                         and vent_venta_estado = 1
                         and vent_venta_almacen_id = '$alm_almacen_id'

                         and DATE(vent_venta_fecha_registro) <= DATE_SUB(DATE('$fecha_inicio'), INTERVAL 1 DAY)
                         and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                from doc_tipo_comprabante
                                                                where doc_tipo_comprobante_codigo in ('07')))),
               0.00)) - ( ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id'
                                                  and DATE(comp_compra_fecha_registro) <=
                                                      DATE_SUB(DATE('$fecha_inicio'), INTERVAL 1 DAY)
                                                  and comp_compra_tipo in ('S'))
               ), 0.00) +
               ifnull((select sum(vent_venta_detalle_cantidad)
                       from vent_venta_detalle
                       where vent_venta_detalle_producto_id = ap.alm_producto_id
                         and vent_venta_detalle_venta_id in
                             (select vent_venta_id
                              from vent_venta
                              where vent_venta_confirmado = 1
                                and vent_venta_estado = 1
                                and vent_venta_almacen_id = '$alm_almacen_id'
                                and DATE(vent_venta_fecha_registro) <= DATE_SUB(DATE('$fecha_inicio'), INTERVAL 1 DAY)
                                and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                       from doc_tipo_comprabante
                                                                       where doc_tipo_comprobante_codigo in ('03', '01', '99')))),
                      0.00) +
               ifnull((select sum(comp_compra_detalle_cantidad)
                       from comp_compra_detalle
                       where comp_compra_detalle_producto_id = ap.alm_producto_id
                         and comp_compra_compra_id in (select comp_compra_id
                                                       from comp_compra
                                                       where comp_compra_confirmado = 1
                                                         and comp_compra_almacen_id = '$alm_almacen_id'

                                                         and DATE(comp_compra_fecha_registro) <=
                                                             DATE_SUB(DATE('$fecha_inicio'), INTERVAL 1 DAY)
                                                         and comp_compra_tipo in ('T'))
                      ), 0.00))                                    as cantidad
from alm_producto as ap,
     alm_producto as cat
where ap.Parent_alm_producto_id = cat.alm_producto_id
  and ap.alm_producto_nivel = 2
  and ap.alm_producto_id = '$producto_id'
union all


select 'INGRESO'                                                     as tipo,
       ccd.comp_compra_detalle_producto                              as producto,
       cc.comp_compra_fecha_registro                                 as fecha,
       'INGRESO POR COMPRA'                                          as descripcion,
       cc.comp_compra_serie                                          as serie,
       cc.comp_compra_numero_venta                                   as numero,
       cc.comp_compra_estado                                         as estado,
       (select alm_unidad_medida_id
        from alm_producto
        where alm_producto_id = ccd.comp_compra_detalle_producto_id) as unidad_medida_ingreso,
       sum(ccd.comp_compra_detalle_cantidad)                         as ingresos,
       ''                                                            as unidad_medida_salida,
       ''                                                            as salidas,
       sum(ccd.comp_compra_detalle_cantidad)                         as catidad
from comp_compra_detalle as ccd,
     comp_compra as cc
where ccd.comp_compra_compra_id = cc.comp_compra_id
  and cc.comp_compra_tipo = 'C'
  and cc.comp_compra_confirmado = 1
  and cc.comp_compra_almacen_id = '$alm_almacen_id'
  and cc.comp_compra_estado = 'REGISTRADO'
  and ccd.comp_compra_detalle_producto_id = '$producto_id'
  and DATE(cc.comp_compra_fecha_registro) BETWEEN DATE('$fecha_inicio') and DATE('$fecha_fin')
group by 'INGRESO', ccd.comp_compra_detalle_producto, cc.comp_compra_fecha_registro, 'INGRESO POR COMPRA',
         cc.comp_compra_serie, cc.comp_compra_numero_venta, cc.comp_compra_estado,
         (select alm_unidad_medida_id
          from alm_producto
          where alm_producto_id = ccd.comp_compra_detalle_producto_id), '', '', ccd.comp_compra_detalle_cantidad
union all
select 'INGRESO'                                                     as tipo,
       ccd.comp_compra_detalle_producto                              as producto,
       cc.comp_compra_fecha_registro                                 as fecha,
       'AJUSTE POR INGRESO'                                          as descripcion,
       cc.comp_compra_serie                                          as serie,
       cc.comp_compra_numero_venta                                   as numero,
       cc.comp_compra_estado                                         as estado,
       (select alm_unidad_medida_id
        from alm_producto
        where alm_producto_id = ccd.comp_compra_detalle_producto_id) as unidad_medida_ingreso,
       sum(ccd.comp_compra_detalle_cantidad)                         as ingresos,
       ''                                                            as unidad_medida_salida,
       ''                                                            as salidas,
       sum(ccd.comp_compra_detalle_cantidad)                         as catidad
from comp_compra_detalle as ccd,
     comp_compra as cc
where ccd.comp_compra_compra_id = cc.comp_compra_id
  and cc.comp_compra_tipo = 'I'
  and cc.comp_compra_confirmado = 1
  and cc.comp_compra_almacen_id = '$alm_almacen_id'
  and cc.comp_compra_estado = 'REGISTRADO'
  and ccd.comp_compra_detalle_producto_id = '$producto_id'
  and DATE(cc.comp_compra_fecha_registro) BETWEEN DATE('$fecha_inicio') and DATE('$fecha_fin')
group by 'INGRESO', ccd.comp_compra_detalle_producto, cc.comp_compra_fecha_registro, 'INGRESO POR COMPRA',
         cc.comp_compra_serie, cc.comp_compra_numero_venta, cc.comp_compra_estado,
         (select alm_unidad_medida_id
          from alm_producto
          where alm_producto_id = ccd.comp_compra_detalle_producto_id), '', '', ccd.comp_compra_detalle_cantidad
union all
select 'INGRESO'                                                     as tipo,
       ccd.comp_compra_detalle_producto                              as producto,
       cc.comp_compra_fecha_registro                                 as fecha,
       'INGRESO POR TRANFERENCIA DE ALMACEN'                         as descripcion,
       cc.comp_compra_serie                                          as serie,
       cc.comp_compra_numero_venta                                   as numero,
       cc.comp_compra_estado                                         as estado,
       (select alm_unidad_medida_id
        from alm_producto
        where alm_producto_id = ccd.comp_compra_detalle_producto_id) as unidad_medida_ingreso,
       sum(ccd.comp_compra_detalle_cantidad)                              as ingresos,
       ''                                                            as unidad_medida_salida,
       ''                                                            as salidas,
       sum(ccd.comp_compra_detalle_cantidad)                              as catidad
from comp_compra_detalle as ccd,
     comp_compra as cc
where ccd.comp_compra_compra_id = cc.comp_compra_id
  and cc.comp_compra_tipo = 'T'
  and cc.comp_compra_confirmado = 1
  and cc.comp_compra_almacen_destino = '$alm_almacen_id'
  and cc.comp_compra_estado = 'REGISTRADO'
  and ccd.comp_compra_detalle_producto_id = '$producto_id'
  and DATE(cc.comp_compra_fecha_registro) BETWEEN DATE('$fecha_inicio') and DATE('$fecha_fin')
group by 'INGRESO', ccd.comp_compra_detalle_producto, cc.comp_compra_fecha_registro, 'INGRESO POR COMPRA',
         cc.comp_compra_serie, cc.comp_compra_numero_venta, cc.comp_compra_estado,
         (select alm_unidad_medida_id
          from alm_producto
          where alm_producto_id = ccd.comp_compra_detalle_producto_id), '', '', ccd.comp_compra_detalle_cantidad
union all
-- SALIDA TRANSFERENCIAAAA

select 'SALIDA'                                                      as tipo,
       ccd.comp_compra_detalle_producto                              as producto,
       cc.comp_compra_fecha_registro                                 as fecha,
       'SALIDA POR TRANFERENCIA DE ALMACEN'                          as descripcion,
       cc.comp_compra_serie                                          as serie,
       cc.comp_compra_numero_venta                                   as numero,
       cc.comp_compra_estado                                         as estado,
       ''                                                            as unidad_medida_ingreso,
       ''                                                             as ingresos,
       (select alm_unidad_medida_id
        from alm_producto
        where alm_producto_id = ccd.comp_compra_detalle_producto_id)    as unidad_medida_salida,
        sum(ccd.comp_compra_detalle_cantidad)                       as salidas,
       sum(ccd.comp_compra_detalle_cantidad)                              as catidad
from comp_compra_detalle as ccd,
     comp_compra as cc
where ccd.comp_compra_compra_id = cc.comp_compra_id
  and cc.comp_compra_tipo = 'T'
  and cc.comp_compra_confirmado = 1
  and cc.comp_compra_almacen_id = '$alm_almacen_id'
  -- and cc.comp_compra_almacen_destino  '$alm_almacen_id'
  and cc.comp_compra_estado = 'REGISTRADO'
  and ccd.comp_compra_detalle_producto_id = '$producto_id'
  and DATE(cc.comp_compra_fecha_registro) BETWEEN DATE('$fecha_inicio') and DATE('$fecha_fin')
group by 'INGRESO', ccd.comp_compra_detalle_producto, cc.comp_compra_fecha_registro, 'INGRESO POR COMPRA',
         cc.comp_compra_serie, cc.comp_compra_numero_venta, cc.comp_compra_estado,
         (select alm_unidad_medida_id
          from alm_producto
          where alm_producto_id = ccd.comp_compra_detalle_producto_id), '', '', ccd.comp_compra_detalle_cantidad
union all



select 'SALIDA'                                                      as tipo,
       ccd.comp_compra_detalle_producto                              as producto,
       cc.comp_compra_fecha_registro                                 as fecha,
       'INGRESO POR TRANFERENCIA DE ALMACEN'                         as descripcion,
       cc.comp_compra_serie                                          as serie,
       cc.comp_compra_numero_venta                                   as numero,
       cc.comp_compra_estado                                         as estado,
       ''                                                            as unidad_medida_ingreso,
       ''                                                            as ingresos,
       (select alm_unidad_medida_id
        from alm_producto
        where alm_producto_id = ccd.comp_compra_detalle_producto_id) as unidad_medida_salida,
       sum(ccd.comp_compra_detalle_cantidad)                              as salidas,
       sum(ccd.comp_compra_detalle_cantidad)                              as catidad
from comp_compra_detalle as ccd,
     comp_compra as cc
where ccd.comp_compra_compra_id = cc.comp_compra_id
  and cc.comp_compra_tipo = 'S'
  and cc.comp_compra_confirmado = 1
  and cc.comp_compra_almacen_id = '$alm_almacen_id'
  and cc.comp_compra_estado = 'REGISTRADO'
  and ccd.comp_compra_detalle_producto_id = '$producto_id'
  and DATE(cc.comp_compra_fecha_registro) BETWEEN DATE('$fecha_inicio') and DATE('$fecha_fin')
group by 'SALIDA', ccd.comp_compra_detalle_producto, cc.comp_compra_fecha_registro, 'INGRESO POR TRANFERENCIA DE ALMACEN', cc.comp_compra_serie, cc.comp_compra_numero_venta, cc.comp_compra_estado, '', '', (select alm_unidad_medida_id
        from alm_producto
        where alm_producto_id = ccd.comp_compra_detalle_producto_id)
union all
select 'SALIDA'                                                        as tipo,
       (select concat(pp.alm_producto_nombre, ' ', ap.alm_producto_nombre,' ',ap.alm_producto_marca )
        from alm_producto as ap,
             alm_producto as pp
        where ap.Parent_alm_producto_id = pp.alm_producto_id
          and ap.alm_producto_id = vvd.vent_venta_detalle_producto_id) as producto,
       vv.vent_venta_fecha_registro                                    as fecha,
       'SALIDA POR VENTA'                                              as descripcion,
       vv.vent_venta_serie                                             as serie,
       vv.vent_venta_numero                                            as numero,
       CASE
           WHEN vv.vent_venta_estado = 1 THEN 'REGISTRADO'
           ELSE 'ANULADO'
           END                                                         as estado,
       ''                                                              as unidad_medida_ingreso,
       ''                                                              as ingresos,
       (select alm_unidad_medida_id
        from alm_producto
        where alm_producto_id = vvd.vent_venta_detalle_producto_id)    as unidad_medida_salida,
       sum(vvd.vent_venta_detalle_cantidad)                                 as salidas,
       sum(vvd.vent_venta_detalle_cantidad)                                 as cantidad
from vent_venta_detalle as vvd,
     vent_venta as vv
where vvd.vent_venta_detalle_venta_id = vv.vent_venta_id
  and vv.vent_venta_tipo_comprobante_id in
      (select doc_tipo_comprobante_id from doc_tipo_comprabante where doc_tipo_comprobante_codigo in ('01', '03', '99'))
  and vv.vent_venta_confirmado = 1
    and vv.vent_venta_estado = 1
  and vv.vent_venta_almacen_id = '$alm_almacen_id'
  and vvd.vent_venta_detalle_producto_id = '$producto_id'
  and DATE(vv.vent_venta_fecha_registro) BETWEEN DATE('$fecha_inicio') and DATE('$fecha_fin')
group by 'SALIDA', (select concat(pp.alm_producto_nombre, ' ', ap.alm_producto_nombre)
        from alm_producto as ap,
             alm_producto as pp
        where ap.Parent_alm_producto_id = pp.alm_producto_id
          and ap.alm_producto_id = vvd.vent_venta_detalle_producto_id), vv.vent_venta_fecha_registro, 'SALIDA POR VENTA', vv.vent_venta_serie, vv.vent_venta_numero, CASE
           WHEN vv.vent_venta_estado = 1 THEN 'REGISTRADO'
           ELSE 'ANULADO'
           END, '', '', (select alm_unidad_medida_id
        from alm_producto
        where alm_producto_id = vvd.vent_venta_detalle_producto_id), vvd.vent_venta_detalle_cantidad, vvd.vent_venta_detalle_cantidad
union all
select 'INGRESO'                                                       as tipo,
       (select concat(pp.alm_producto_nombre, ' ', ap.alm_producto_nombre)
        from alm_producto as ap,
             alm_producto as pp
        where ap.Parent_alm_producto_id = pp.alm_producto_id
          and ap.alm_producto_id = vvd.vent_venta_detalle_producto_id) as producto,
       vv.vent_venta_fecha_registro                                    as fecha,
       'INGRESO POR DEVOLUCION'                                        as descripcion,
       vv.vent_venta_serie                                             as serie,
       vv.vent_venta_numero                                            as numero,
       CASE
           WHEN vv.vent_venta_estado = 1 THEN 'REGISTRADO'
           ELSE 'ANULADO'
           END                                                         as estado,
       (select alm_unidad_medida_id
        from alm_producto
        where alm_producto_id = vvd.vent_venta_detalle_producto_id)       as unidad_medida_ingreso,
       sum(vvd.vent_venta_detalle_cantidad)                               as ingresos,
 ''   as unidad_medida_salida,
       ''                                as salidas,
       sum(vvd.vent_venta_detalle_cantidad)                                 as cantidad
from vent_venta_detalle as vvd,
     vent_venta as vv
where vvd.vent_venta_detalle_venta_id = vv.vent_venta_id
  and vv.vent_venta_tipo_comprobante_id in
      (select doc_tipo_comprobante_id from doc_tipo_comprabante where doc_tipo_comprobante_codigo in ('07'))
  and vv.vent_venta_confirmado = 1
  and vv.vent_venta_almacen_id = '$alm_almacen_id'
  and vvd.vent_venta_detalle_producto_id = '$producto_id'
  and DATE(vv.vent_venta_fecha_registro) BETWEEN DATE('$fecha_inicio') and DATE('$fecha_fin')
group by 'INGRESO', (select concat(pp.alm_producto_nombre, ' ', ap.alm_producto_nombre)
        from alm_producto as ap,
             alm_producto as pp
        where ap.Parent_alm_producto_id = pp.alm_producto_id
          and ap.alm_producto_id = vvd.vent_venta_detalle_producto_id), vv.vent_venta_fecha_registro, 'INGRESO POR DEVOLUCION', vv.vent_venta_serie, vv.vent_venta_numero, CASE
           WHEN vv.vent_venta_estado = 1 THEN 'REGISTRADO'
           ELSE 'ANULADO'
           END, '', '', (select alm_unidad_medida_id
        from alm_producto
        where alm_producto_id = vvd.vent_venta_detalle_producto_id), vvd.vent_venta_detalle_cantidad, vvd.vent_venta_detalle_cantidad

order by fecha";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }


    public static function producto_imprimir_codigo_barras($producto_id)
    {
        try {
            $sql = "select concat(ap2.alm_producto_nombre, ' ', ap.alm_producto_nombre) as producto,
       ap.alm_producto_codigo,
       (select max(alm_lista_precio_detalle_precio)
        from alm_lista_precio_detalle
        where alm_lista_precio_detalle_articulo_id = ap.alm_producto_id
          and alm_lista_precio_detalle_lista_precio_id in
              (select alm_lista_precio_id
               from alm_lista_precio
               where date(alm_lista_precio_fecha_vigencia_inicio) <= date(curdate())
                 and date(alm_lista_precio_fecha_vigencia_fin) >= date(curdate()))
       )                                                            as precio_venta


from alm_producto as ap,
     alm_producto as ap2
where ap.Parent_alm_producto_id = ap2.alm_producto_id
and ap.alm_producto_id='$producto_id'";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }


    public static function producto_series_almacen($producto_id, $almacen_id)
    {
        try {
            $sql = "select comp_compra_detalle_serie, comp_compra_detalle_producto
from comp_compra_detalle
where comp_compra_compra_id in
      (select comp_compra_id
       from comp_compra
       where comp_compra_almacen_id = '$almacen_id'
         and comp_compra_tipo in ('C', 'I'))
  and comp_compra_detalle_serie_estado = 1
  and comp_compra_detalle_vendido = 0
  and comp_compra_detalle_producto_id = '$producto_id'
  and comp_compra_detalle_serie is not null
union all
select comp_compra_detalle_serie , comp_compra_detalle_producto
from comp_compra_detalle
where comp_compra_compra_id in
      (select comp_compra_id
       from comp_compra
       where comp_compra_almacen_destino = '$almacen_id'
         and comp_compra_tipo in ('T'))
  and comp_compra_detalle_serie_estado = 1
  and comp_compra_detalle_vendido = 0
  and comp_compra_detalle_producto_id = '$producto_id'
  and comp_compra_detalle_serie is not null";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }


    public static function producto_marcas()
    {
        try {
            $sql = "select alm_producto_marca from alm_producto where alm_producto_nivel='2'
                                              and alm_producto_marca is not null and alm_producto_marca<>''  group by alm_producto_marca";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }


    public static function producto_consulta_garantia_por_serie($serie)
    {
        try {
            $sql = "select aa.alm_almacen_nombre,
       cc.comp_compra_fecha,
       cc.comp_compra_fecha_registro,
       ap.alm_proveedor_razon_social,
       ap.alm_proveedor_ruc,
       cc.comp_compra_serie,
       cc.comp_compra_numero_venta,
       ccd.comp_compra_detalle_producto,
       ccd.comp_compra_detalle_serie,
       ccd.comp_compra_detalle_vendido,
       (select comp_compra_detalle_precio_unitario
        from comp_compra_detalle
        where comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
          and comp_compra_compra_id = cc.comp_compra_id
          and comp_compra_detalle_precio_unitario > 0
        limit 1)         as comp_compra_detalle_precio_unitario,
       CASE
           WHEN ccd.comp_compra_detalle_vendido = 1 THEN
               (select vv.vent_venta_cliente_numero_documento
                from vent_venta_detalle
                         join vent_venta vv on vv.vent_venta_id = vent_venta_detalle.vent_venta_detalle_venta_id
                where vent_venta_detalle_serie = ccd.comp_compra_detalle_serie
                  and comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
                  and vv.vent_venta_confirmado = 1
                  and vv.vent_venta_estado = 1
                  and vv.vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                            from doc_tipo_comprabante
                                                            where doc_tipo_comprobante_codigo in ('01', '03', '99'))
                limit 1)
           else null end as vent_venta_cliente_numero_documento,

       CASE
           WHEN ccd.comp_compra_detalle_vendido = 1 THEN
               (select (select seg_cliente_razon_social
                        from seg_cliente
                        where seg_cliente_numero_doc = vv.vent_venta_cliente_numero_documento
                          and vv.vent_venta_confirmado = 1
                        limit 1
                       )
                from vent_venta_detalle
                         join vent_venta vv on vv.vent_venta_id = vent_venta_detalle.vent_venta_detalle_venta_id
                where vent_venta_detalle_serie = ccd.comp_compra_detalle_serie
                  and comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
                  and vv.vent_venta_confirmado = 1
                  and vv.vent_venta_estado = 1
                  and vv.vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                            from doc_tipo_comprabante
                                                            where doc_tipo_comprobante_codigo in ('01', '03', '99'))
                limit 1)
           else null end as seg_cliente_razon_social,
       CASE
           WHEN ccd.comp_compra_detalle_vendido = 1 THEN
               (select vent_venta_detalle_precio_unitario
                from vent_venta_detalle
                         join vent_venta vv on vv.vent_venta_id = vent_venta_detalle.vent_venta_detalle_venta_id
                where vent_venta_detalle_serie = ccd.comp_compra_detalle_serie
                  and comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
                  and vv.vent_venta_confirmado = 1
                  and vv.vent_venta_estado = 1
                  and vv.vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                            from doc_tipo_comprabante
                                                            where doc_tipo_comprobante_codigo in ('01', '03', '99'))
                limit 1)
           else null end as vent_venta_detalle_precio_unitario,
       CASE
           WHEN ccd.comp_compra_detalle_vendido = 1 THEN (select vv.vent_venta_fecha
                                                          from vent_venta_detalle
                                                                   join vent_venta vv
                                                                        on vv.vent_venta_id = vent_venta_detalle.vent_venta_detalle_venta_id
                                                          where vent_venta_detalle_serie = ccd.comp_compra_detalle_serie
                                                            and comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
                                                            and vv.vent_venta_confirmado = 1
                                                            and vv.vent_venta_estado = 1
                                                            and vv.vent_venta_tipo_comprobante_id in
                                                                (select doc_tipo_comprobante_id
                                                                 from doc_tipo_comprabante
                                                                 where doc_tipo_comprobante_codigo in ('01', '03', '99'))
                                                          limit 1)
           else null end as vent_venta_fecha,
       CASE
           WHEN ccd.comp_compra_detalle_vendido = 1 THEN (select vv.vent_venta_serie
                                                          from vent_venta_detalle
                                                                   join vent_venta vv
                                                                        on vv.vent_venta_id = vent_venta_detalle.vent_venta_detalle_venta_id
                                                          where vent_venta_detalle_serie = ccd.comp_compra_detalle_serie
                                                            and comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
                                                            and vv.vent_venta_confirmado = 1
                                                            and vv.vent_venta_estado = 1
                                                            and vv.vent_venta_tipo_comprobante_id in
                                                                (select doc_tipo_comprobante_id
                                                                 from doc_tipo_comprabante
                                                                 where doc_tipo_comprobante_codigo in ('01', '03', '99'))
                                                          limit 1)
           else null end as vent_venta_serie,
       CASE
           WHEN ccd.comp_compra_detalle_vendido = 1 THEN (select vv.vent_venta_numero
                                                          from vent_venta_detalle
                                                                   join vent_venta vv
                                                                        on vv.vent_venta_id = vent_venta_detalle.vent_venta_detalle_venta_id
                                                          where vent_venta_detalle_serie = ccd.comp_compra_detalle_serie
                                                            and comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
                                                            and vv.vent_venta_confirmado = 1
                                                            and vv.vent_venta_estado = 1
                                                            and vv.vent_venta_tipo_comprobante_id in
                                                                (select doc_tipo_comprobante_id
                                                                 from doc_tipo_comprabante
                                                                 where doc_tipo_comprobante_codigo in ('01', '03', '99'))
                                                          limit 1)
           else null end as vent_venta_numero,
       CASE
           WHEN ccd.comp_compra_detalle_vendido = 1 THEN
               (select vv.vent_venta_fecha_registro
                from vent_venta_detalle
                         join vent_venta vv on vv.vent_venta_id = vent_venta_detalle.vent_venta_detalle_venta_id
                where vent_venta_detalle_serie = ccd.comp_compra_detalle_serie
                  and comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
                  and vv.vent_venta_confirmado = 1
                  and vv.vent_venta_estado = 1
                  and vv.vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                            from doc_tipo_comprabante
                                                            where doc_tipo_comprobante_codigo in ('01', '03', '99'))
                limit 1)
           else null end as vent_venta_fecha_registro,

       CASE
           WHEN ccd.comp_compra_detalle_vendido = 1 THEN
               ifnull(datediff(curdate(), (select vv.vent_venta_fecha_registro
                                           from vent_venta_detalle
                                                    join vent_venta vv
                                                         on vv.vent_venta_id = vent_venta_detalle.vent_venta_detalle_venta_id
                                           where vent_venta_detalle_serie = ccd.comp_compra_detalle_serie
                                             and comp_compra_detalle_producto_id = ccd.comp_compra_detalle_producto_id
                                             and vv.vent_venta_confirmado = 1
                                             and vv.vent_venta_estado = 1
                                             and vv.vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                                       from doc_tipo_comprabante
                                                                                       where doc_tipo_comprobante_codigo in ('01', '03', '99'))
                                           limit 1)),
                      0)
           else null end as dias_transcurridos_desde_venta
from comp_compra_detalle as ccd
         join comp_compra as cc on ccd.comp_compra_compra_id = cc.comp_compra_id
         join alm_proveedor ap on ap.alm_proveedor_id = cc.comp_compra_preveedor_id
         join alm_almacen aa on aa.alm_almacen_id = cc.comp_compra_almacen_id
where ccd.comp_compra_detalle_serie = '$serie'
  and cc.comp_compra_confirmado = 1
  and cc.comp_compra_estado = 'REGISTRADO'";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }


    public static function producto_stock_por_almacenes($data, $alm_almacen_id1, $alm_almacen_id2, $alm_almacen_id3, $fecha, $categoria_id)
    {
        $query_data = "";
        if (!is_null($data) && $data != "") {
            $query_data = "and concat(ifnull(cat.alm_producto_nombre,''), ' ', ifnull(ap.alm_producto_nombre,''), ' ',
                ifnull(ap.alm_producto_codigo,''), ' ', ifnull(ap.alm_producto_descripcion,'')) LIKE '%" . $data . "%'";

        }
        $query_categoria = "";
        if (!is_null($categoria_id) && $categoria_id != "") {
            $query_categoria = "and cat.alm_producto_id = '$categoria_id'";
        }

        try {
            $sql = "select ap.alm_producto_id, cat.alm_producto_nombre as categoria,
       substring(ap.alm_producto_codigo,1,10) as alm_producto_codigo,
       ap.alm_producto_serie,
concat(cat.alm_producto_nombre, ' ', ap.alm_producto_nombre) as alm_producto_nombre,
       ap.alm_producto_marca,
       ap.alm_producto_modelo,
       ap.alm_producto_descripcion,
       ap.alm_producto_controla_stock,
       (ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_destino = '$alm_almacen_id1'
                                                  and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                  and comp_compra_tipo in ('T'))
               ), 0.00) +
        ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id1'
                                                  and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                  and comp_compra_tipo in ('C', 'I'))
               ), 0.00) +
        ifnull((select sum(vent_venta_detalle_cantidad)
                from vent_venta_detalle
                where vent_venta_detalle_producto_id = ap.alm_producto_id
                  and vent_venta_detalle_venta_id in
                      (select vent_venta_id
                       from vent_venta
                       where vent_venta_confirmado = 1
                         and vent_venta_estado = 1
                         and vent_venta_almacen_id = '$alm_almacen_id1'
                         and DATE(vent_venta_fecha) <= DATE('$fecha')
                         and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                from doc_tipo_comprabante
                                                                where doc_tipo_comprobante_codigo in ('07')))),
               0.00)) - (
        ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id1'
                                                  and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                  and comp_compra_tipo in ('S'))
               ), 0.00)+

               ifnull((select sum(vent_venta_detalle_cantidad)
                       from vent_venta_detalle
                       where vent_venta_detalle_producto_id = ap.alm_producto_id
                         and vent_venta_detalle_venta_id in
                             (select vent_venta_id
                              from vent_venta
                              where vent_venta_confirmado = 1
                                and vent_venta_estado = 1
                                and vent_venta_almacen_id = '$alm_almacen_id1'
                                and DATE(vent_venta_fecha) <= DATE('$fecha')
                                and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                       from doc_tipo_comprabante
                                                                       where doc_tipo_comprobante_codigo in ('03', '01', '99')))),
                      0.00) +
               ifnull((select sum(comp_compra_detalle_cantidad)
                       from comp_compra_detalle
                       where comp_compra_detalle_producto_id = ap.alm_producto_id
                         and comp_compra_compra_id in (select comp_compra_id
                                                       from comp_compra
                                                       where comp_compra_confirmado = 1
                                                         and comp_compra_almacen_id = '$alm_almacen_id1'
                                                         and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                         and comp_compra_tipo in ('T'))
                      ), 0.00)) as stock_almacen1,


                      (ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_destino = '$alm_almacen_id2'
                                                  and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                  and comp_compra_tipo in ('T'))
               ), 0.00) +
        ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id2'
                                                  and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                  and comp_compra_tipo in ('C', 'I'))
               ), 0.00) +
        ifnull((select sum(vent_venta_detalle_cantidad)
                from vent_venta_detalle
                where vent_venta_detalle_producto_id = ap.alm_producto_id
                  and vent_venta_detalle_venta_id in
                      (select vent_venta_id
                       from vent_venta
                       where vent_venta_confirmado = 1
                         and vent_venta_estado = 1
                         and vent_venta_almacen_id = '$alm_almacen_id2'
                         and DATE(vent_venta_fecha) <= DATE('$fecha')
                         and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                from doc_tipo_comprabante
                                                                where doc_tipo_comprobante_codigo in ('07')))),
               0.00)) - (
        ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id2'
                                                  and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                  and comp_compra_tipo in ('S'))
               ), 0.00)+

               ifnull((select sum(vent_venta_detalle_cantidad)
                       from vent_venta_detalle
                       where vent_venta_detalle_producto_id = ap.alm_producto_id
                         and vent_venta_detalle_venta_id in
                             (select vent_venta_id
                              from vent_venta
                              where vent_venta_confirmado = 1
                                and vent_venta_estado = 1
                                and vent_venta_almacen_id = '$alm_almacen_id2'
                                and DATE(vent_venta_fecha) <= DATE('$fecha')
                                and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                       from doc_tipo_comprabante
                                                                       where doc_tipo_comprobante_codigo in ('03', '01', '99')))),
                      0.00) +
               ifnull((select sum(comp_compra_detalle_cantidad)
                       from comp_compra_detalle
                       where comp_compra_detalle_producto_id = ap.alm_producto_id
                         and comp_compra_compra_id in (select comp_compra_id
                                                       from comp_compra
                                                       where comp_compra_confirmado = 1
                                                         and comp_compra_almacen_id = '$alm_almacen_id2'
                                                         and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                         and comp_compra_tipo in ('T'))
                      ), 0.00)) as stock_almacen2,

                      (ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_destino = '$alm_almacen_id3'
                                                  and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                  and comp_compra_tipo in ('T'))
               ), 0.00) +
        ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id3'
                                                  and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                  and comp_compra_tipo in ('C', 'I'))
               ), 0.00) +
        ifnull((select sum(vent_venta_detalle_cantidad)
                from vent_venta_detalle
                where vent_venta_detalle_producto_id = ap.alm_producto_id
                  and vent_venta_detalle_venta_id in
                      (select vent_venta_id
                       from vent_venta
                       where vent_venta_confirmado = 1
                         and vent_venta_estado = 1
                         and vent_venta_almacen_id = '$alm_almacen_id3'
                         and DATE(vent_venta_fecha) <= DATE('$fecha')
                         and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                from doc_tipo_comprabante
                                                                where doc_tipo_comprobante_codigo in ('07')))),
               0.00)) - (
        ifnull((select sum(comp_compra_detalle_cantidad)
                from comp_compra_detalle
                where comp_compra_detalle_producto_id = ap.alm_producto_id
                  and comp_compra_compra_id in (select comp_compra_id
                                                from comp_compra
                                                where comp_compra_confirmado = 1
                                                  and comp_compra_almacen_id = '$alm_almacen_id3'
                                                  and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                  and comp_compra_tipo in ('S'))
               ), 0.00)+

               ifnull((select sum(vent_venta_detalle_cantidad)
                       from vent_venta_detalle
                       where vent_venta_detalle_producto_id = ap.alm_producto_id
                         and vent_venta_detalle_venta_id in
                             (select vent_venta_id
                              from vent_venta
                              where vent_venta_confirmado = 1
                                and vent_venta_estado = 1
                                and vent_venta_almacen_id = '$alm_almacen_id3'
                                and DATE(vent_venta_fecha) <= DATE('$fecha')
                                and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                       from doc_tipo_comprabante
                                                                       where doc_tipo_comprobante_codigo in ('03', '01', '99')))),
                      0.00) +
               ifnull((select sum(comp_compra_detalle_cantidad)
                       from comp_compra_detalle
                       where comp_compra_detalle_producto_id = ap.alm_producto_id
                         and comp_compra_compra_id in (select comp_compra_id
                                                       from comp_compra
                                                       where comp_compra_confirmado = 1
                                                         and comp_compra_almacen_id = '$alm_almacen_id3'
                                                         and DATE(comp_compra_fecha) <= DATE('$fecha')
                                                         and comp_compra_tipo in ('T'))
                      ), 0.00)) as stock_almacen3
from alm_producto as ap,
     alm_producto as cat
where ap.Parent_alm_producto_id = cat.alm_producto_id
  and   ap.alm_producto_estado='1'
  and ap.alm_producto_nivel=2 $query_data $query_categoria";
            $Query = DB::select($sql);
        } catch (\Exception $exception) {
            dd($exception);
        }
        return $Query;
    }


}

