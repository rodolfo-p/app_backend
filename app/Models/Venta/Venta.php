<?php


namespace App\Models\Venta;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Venta extends Model
{
    protected $table = 'vent_venta';
    public $timestamps = false;
    protected $keyType = 'string';

    protected $casts = [
        'vent_venta_estado_envio_sunat' => 'boolean',
        'vent_venta_confirmado' => 'boolean',
        'vent_venta_estado_pago' => 'boolean',
        'vent_venta_estado' => 'boolean',
    ];
    protected $primaryKey = 'vent_venta_id';
    protected $fillable = ['vent_venta_id',
        'vent_venta_serie',
        'vent_venta_numero',
        'vent_venta_total',
        'vent_venta_precio_descuento_total',
        'vent_venta_precio_cobrado',
        'vent_venta_bi',
        'vent_venta_igv',
        'vent_venta_estado',
        'vent_venta_cliente_numero_documento',
        'vent_venta_user_id',
        'vent_venta_tipo_comprobante_id',
        'vent_venta_almacen_id',
        'vent_venta_periodo_id',
        'vent_venta_fecha',
        'vent_venta_tipo_venta',
        'vent_venta_motivo_nota',
        'vent_venta_nota_codigo',
        'vent_venta_estado_envio_sunat',
        'vent_venta_ruta_xml',
        'vent_venta_ruta_cdr',
        'vent_venta_hash',
        'vent_venta_fecha_envio_sunat',
        'vent_venta_cuenta_cliente',
        'vent_venta_cuenta_igv',
        'vent_venta_precio_cobrado_letras',
        'vent_venta_ip',
        'vent_venta_sistema_operativo',
        'vent_venta_ruta_baja_xml',
        'vent_venta_ruta_baja_cdr',
        'vent_venta_estado_pago',
        'vent_venta_distribuidor_id',
        'vent_venta_descripcion',
        'vent_venta_comprobante_referenciado',
        'vent_venta_tipo_comprobante_referenciado',
        'vent_venta_comprovante_referenciado_nota',
        'vent_venta_comprado_por',
        'vent_venta_fecha_registro',
        'vent_venta_cliente_id',
        'vent_venta_confirmado',
        'vent_venta_cod_sunat',
        'vent_venta_hash_cdr',
        'vent_venta_mensaje_sunat',
        'vent_venta_qr',
        'vent_venta_porcentaje_descuento',
        'vent_venta_bi_real',
        'vent_venta_observaciones'];

    public static function listar_ventas_por_cobrar($data = "")
    {
        try {
            $query_data = "";
            if (!is_null($data) && $data != "") {
                $query_data = "and concat(ifnull(v.vent_venta_serie,''), ' ', ifnull(v.vent_venta_numero,''), ' ',
                ifnull(sc.seg_cliente_razon_social,''), ' ', ifnull(v.vent_venta_cliente_numero_documento,'') , ' ', ifnull(v.vent_venta_total,'')) LIKE '%" . $data . "%'";

            }

            $sql = "select v.vent_venta_id,
       v.vent_venta_cliente_numero_documento,
       v.vent_venta_serie,
       v.vent_venta_numero,
       v.vent_venta_total,
       sc.seg_cliente_razon_social,
       ifnull((select sum(vent_pago_importe)
               from vent_pago
               where vent_pago_venta_id = v.vent_venta_id
               group by vent_pago_venta_id), 0.00)                      acuenta,
       v.vent_venta_total - ifnull((select sum(vent_pago_importe)
                                    from vent_pago
                                    where vent_pago_venta_id = v.vent_venta_id
                                    group by vent_pago_venta_id), 0.00) saldo
from vent_venta as v,
     seg_cliente as sc

where v.vent_venta_estado_pago = 0
  and v.vent_venta_cliente_id = sc.seg_cliente_id
  and v.vent_venta_confirmado = 1
  and v.vent_venta_comprobante_referenciado is null
  and v.vent_venta_serie in (select vent_venta_serie from vent_venta where vent_venta_tipo_comprobante_id <> 'NO15455239275629160')
  and v.vent_venta_numero in (select vent_venta_numero from vent_venta where vent_venta_tipo_comprobante_id <> 'NO15455239275629160')
  $query_data";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }


    public static function listar_ventas_por_cobrar_calendario()
    {
        try {


            $sql = "select sc.seg_cliente_razon_social,
       seg_cliente_numero_doc,
       vv.vent_venta_id,
       vv.vent_venta_total,
       vv.vent_venta_serie,
       vv.vent_venta_numero,
       vvfp.vent_venta_fecha_pago_fecha,
       vvfp.vent_venta_fecha_pago_id,
       vvfp.vent_venta_fecha_pago_comentario,
       vvfp.vent_venta_fecha_pago_cuota,
       vvfp.vent_venta_fecha_pago_importe,
       vp.vent_pago_nro_cuota,
       'PAGADO' as estado
from vent_venta_fecha_pago as vvfp
         join vent_venta as vv on vvfp.vent_venta_fecha_pago_venta_id = vv.vent_venta_id
         join seg_cliente as sc on vv.vent_venta_cliente_id = sc.seg_cliente_id
         join vent_pago as vp on vvfp.vent_venta_fecha_pago_venta_id = vp.vent_pago_venta_id
where vvfp.vent_venta_fecha_pago_cuota = vp.vent_pago_nro_cuota
and vv.vent_venta_comprobante_referenciado is null
  and vv.vent_venta_confirmado=1
  and vv.vent_venta_serie in (select vent_venta_serie from vent_venta where vent_venta_tipo_comprobante_id <> 'NO15455239275629160')
  and vv.vent_venta_numero in (select vent_venta_numero from vent_venta where vent_venta_tipo_comprobante_id <> 'NO15455239275629160')
union all
select sc.seg_cliente_razon_social,
       seg_cliente_numero_doc,
       vv.vent_venta_id,
       vv.vent_venta_total,
       vv.vent_venta_serie,
       vv.vent_venta_numero,
       vvfp.vent_venta_fecha_pago_fecha,
       vvfp.vent_venta_fecha_pago_id,
       vvfp.vent_venta_fecha_pago_comentario,
       vvfp.vent_venta_fecha_pago_cuota,
       vvfp.vent_venta_fecha_pago_importe,
       0       as vent_pago_nro_cuota,
       'FALTA' as estado
from vent_venta_fecha_pago as vvfp
         join vent_venta as vv on vvfp.vent_venta_fecha_pago_venta_id = vv.vent_venta_id
         join seg_cliente as sc on vv.vent_venta_cliente_id = sc.seg_cliente_id
where vv.vent_venta_id not in (select vent_pago_venta_id
                               from vent_pago
                               where vent_pago_venta_id = vv.vent_venta_id
                                 and vent_pago_nro_cuota = vvfp.vent_venta_fecha_pago_cuota)
  and vv.vent_venta_comprobante_referenciado is null
  and vv.vent_venta_confirmado=1
  and vv.vent_venta_serie in (select vent_venta_serie from vent_venta where vent_venta_tipo_comprobante_id <> 'NO15455239275629160')
  and vv.vent_venta_numero in (select vent_venta_numero from vent_venta where vent_venta_tipo_comprobante_id <> 'NO15455239275629160')";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }


    public static function listar_ventas_avance($categoria_id, $fecha_inicio, $fecha_fin, $almacen_id)
    {
        try {
            $query_categoria = "";
            if (!is_null($categoria_id) && $categoria_id != "") {
                $query_categoria = "and ap.Parent_alm_producto_id='$categoria_id'";
            }
            $query_almacen = "";
            if (!is_null($almacen_id) && $almacen_id != "") {
                $query_almacen = " and vent_venta_almacen_id= '$almacen_id'";
            }
            $sql = "select substring(alm_producto_codigo,1,10) as alm_producto_codigo,
       alm_producto_nombre,
       vent_venta_detalle_producto_id,
       sum(vent_venta_detalle_cantidad) as vent_venta_detalle_cantidad,
       categoria
from (select ap.alm_producto_codigo,
             ap.alm_producto_nombre,
             vvd.vent_venta_detalle_producto_id,
             sum(vvd.vent_venta_detalle_cantidad)                as vent_venta_detalle_cantidad,
             (select alm_producto_nombre
              from alm_producto
              where alm_producto_id = ap.Parent_alm_producto_id) as categoria
      from vent_venta_detalle as vvd,
           alm_producto as ap
      where vvd.vent_venta_detalle_producto_id = ap.alm_producto_id
        and vvd.vent_venta_detalle_venta_id in (select vent_venta_id
                                                from vent_venta
                                                where date(vent_venta_fecha) between date('$fecha_inicio') and date('$fecha_fin')
                                                  and vent_venta_estado = 1
                                                  and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                                         from doc_tipo_comprabante
                                                                                         where doc_tipo_comprobante_codigo in ('01', '03', '99')) $query_almacen)
$query_categoria
      group by ap.alm_producto_codigo, ap.alm_producto_nombre, vvd.vent_venta_detalle_producto_id,
               (select alm_producto_nombre from alm_producto where alm_producto_id = ap.Parent_alm_producto_id)
      union ALL
      select ap.alm_producto_codigo,
             ap.alm_producto_nombre,
             vvd.vent_venta_detalle_producto_id,
             sum(vvd.vent_venta_detalle_cantidad)                as vent_venta_detalle_cantidad,
             (select alm_producto_nombre
              from alm_producto
              where alm_producto_id = ap.Parent_alm_producto_id) as categoria
      from vent_venta_detalle as vvd,
           alm_producto as ap
      where vvd.vent_venta_detalle_producto_id = ap.alm_producto_id
        and vvd.vent_venta_detalle_venta_id in (select vent_venta_id
                                                from vent_venta
                                                where date(vent_venta_fecha) between date('$fecha_inicio') and date('$fecha_fin')
                                                  and vent_venta_estado = 1
                                                  and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                                         from doc_tipo_comprabante
                                                                                         where doc_tipo_comprobante_codigo in ('07'))$query_almacen)
$query_categoria
      group by ap.alm_producto_codigo, ap.alm_producto_nombre, vvd.vent_venta_detalle_producto_id,
               (select alm_producto_nombre
                from alm_producto
                where alm_producto_id = ap.Parent_alm_producto_id)) as temporal
group by alm_producto_codigo, alm_producto_nombre, vent_venta_detalle_producto_id, categoria
order by categoria";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }

    public static function listar_venta_por_cobrar($id)
    {
        try {
            $sql = "select v.vent_venta_id,
       v.vent_venta_cliente_numero_documento,
       v.vent_venta_serie,
       v.vent_venta_numero,
       v.vent_venta_total,
       sc.seg_cliente_razon_social,
       ifnull((select sum(vent_pago_importe)
               from vent_pago
               where vent_pago_venta_id = v.vent_venta_id
               group by vent_pago_venta_id), 0.00)                               acuenta,
       v.vent_venta_total - ifnull((select sum(vent_pago_importe)
                                             from vent_pago
                                             where vent_pago_venta_id = v.vent_venta_id
                                             group by vent_pago_venta_id), 0.00) saldo
from vent_venta as v, seg_cliente as sc
where v.vent_venta_estado_pago =0 and v.vent_venta_cliente_id=sc.seg_cliente_id and v.vent_venta_id='$id'";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }

    public static function listar_venta_utilidad($fecha_inicio, $fecha_fin, $data, $categoria_id, $almacen_id)
    {
        try {
            $query_categoria = "";
            if (!is_null($categoria_id) && $categoria_id != "") {
                $query_categoria = "and aP.alm_producto_id='$categoria_id'";
            }
            $query_almacen = "";
            if (!is_null($almacen_id) && $almacen_id != "") {
                $query_almacen = "and aa.alm_almacen_id='$almacen_id'";
            }
            $query_data = "";
            if (!is_null($data) && $data != "") {
                $query_data = "and concat(ifnull(ap.alm_producto_nombre,''), ' ', ifnull(ap.alm_producto_codigo,''), ' ', ifnull(ap.alm_producto_marca,'')) like '%" . $data . "%'";
            }

            $sql = "select aP.alm_producto_nombre as categoria,
       ap.alm_producto_codigo,
       ap.alm_producto_nombre,
       aa.alm_almacen_nombre,
       vv.vent_venta_serie,
       vv.vent_venta_numero,
       vv.vent_venta_fecha_registro,
       vvd.vent_venta_detalle_cantidad,

 ifnull((select comp_compra_detalle_precio_unitario
               from comp_compra_detalle
               where comp_compra_detalle_producto_id = vvd.vent_venta_detalle_producto_id
                 and comp_compra_detalle_precio_unitario > 0.00

                 and comp_compra_detalle_fecha_registro = (select max(comp_compra_detalle_fecha_registro)
                                                           from comp_compra_detalle
                                                           where comp_compra_detalle_producto_id = vvd.vent_venta_detalle_producto_id
                                                             and comp_compra_detalle_precio_unitario > 0.00
                                                            limit 1
                                                           )

               limit 1),0.00) as comp_compra_detalle_precio_unitario,
       vvd.vent_venta_detalle_precio_unitario
from vent_venta_detalle as vvd
         left join alm_producto ap on ap.alm_producto_id = vvd.vent_venta_detalle_producto_id
         left join alm_producto aP on aP.alm_producto_id = ap.Parent_alm_producto_id
left join vent_venta vv on vvd.vent_venta_detalle_venta_id = vv.vent_venta_id
left join alm_almacen aa on vv.vent_venta_almacen_id = aa.alm_almacen_id
where vvd.vent_venta_detalle_venta_id in (select vent_venta_id
                                          from vent_venta
                                          where vent_venta_estado = 1
                                            and vent_venta_confirmado = 1
                                            and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                                                   from doc_tipo_comprabante
                                                                                   where doc_tipo_comprobante_codigo in ('01', '03', '99'))
)
  and vvd.vent_venta_detalle_venta_id not in (select vent_venta_id
                                              from vent_venta
                                              where vent_venta_estado = 1
                                                and vent_venta_confirmado = 1
                                                and vent_venta_serie in
                                                    (select substr(vent_venta_comprobante_referenciado, 1, 4)
                                                     from vent_venta
                                                     where vent_venta_tipo_comprobante_referenciado like '%0%'
                                                       and vent_venta_comprobante_referenciado like '%00%')
                                                and vent_venta_numero in
                                                    (select substr(vent_venta_comprobante_referenciado, 6, 14)
                                                     from vent_venta
                                                     where vent_venta_tipo_comprobante_referenciado like '%0%'
                                                       and vent_venta_comprobante_referenciado like '%00%')
)
and vv.vent_venta_serie is not null
and vv.vent_venta_numero is not null
and  vv.vent_venta_fecha between '$fecha_inicio' and '$fecha_fin'
$query_categoria $query_almacen $query_data

order by aP.alm_producto_nombre,
         ap.alm_producto_codigo,
         ap.alm_producto_nombre,
         aa.alm_almacen_nombre,
         vv.vent_venta_serie,
         vv.vent_venta_numero,
         vv.vent_venta_fecha_registro";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }


    public static function listar_ventas_por_comision($vendedor_id)
    {
        try {
            $sql = "
select vp.vent_pago_id,
       vp.vent_pago_importe,
       vv.vent_venta_fecha,
       vv.vent_venta_serie,
       vv.vent_venta_numero,
       vp.vent_pago_venta_id,
       vp.vent_pago_fecha,
       vp.vent_pago_numero_pago,
       vp.vent_pago_cliente_documento,
       vp.vent_pago_serie,
       vp.vent_pago_comision_id,
       vp.vent_pago_comision_pagado,
       vp.vent_pago_comision_importe,
       concat(ad.alm_distribuidor_nombres, ' ', ad.alm_distribuidor_apellidos) as vendedor
from vent_pago as vp,
     vent_venta as vv,
     alm_distribuidor as ad
where vp.vent_pago_venta_id = vv.vent_venta_id
  and vp.vent_pago_comision_id = ad.alm_distribuidor_id
  and vv.vent_venta_estado=1
  and vv.vent_venta_confirmado=1
  and vp.vent_pago_comision_pagado=0
and vp.vent_pago_comision_id='$vendedor_id'";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }


    public static function listar_ventas_avance_por_dia($almacen_id, $fecha_inicio, $fecha_fin)
    {
        $query_almacen = "";
        if (!is_null($almacen_id) && $almacen_id != "") {
            $query_almacen = " and vent_venta_almacen_id= '$almacen_id'";
        }

        try {
            $sql = "select fecha, ifnull(sum(contado),0) as contado, ifnull(sum(credito),0) as credito
from (
         select date(vent_venta_fecha_registro) as fecha,
                sum(vent_venta_total)           as contado,
                ''                              as credito
         from vent_venta
         where date(vent_venta_fecha_registro)  between date ('$fecha_inicio') and date ('$fecha_fin')
           and vent_venta_confirmado = 1
           and vent_venta_tipo_venta = '01'
$query_almacen
         group by date(vent_venta_fecha_registro)
         union all
         select date(vent_venta_fecha_registro) as fecha,
                ''                              as contado,
                sum(vent_venta_total)           as credito

         from vent_venta
         where date(vent_venta_fecha_registro)  between date ('$fecha_inicio') and date ('$fecha_fin')
$query_almacen

           and vent_venta_confirmado = 1
           and vent_venta_tipo_venta = '02'

         group by date(vent_venta_fecha_registro)) as t
group by fecha

";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }


    public static function listar_ventas_avance_por_mes($almacen_id, $mes_inicio, $mes_fin, $anio)
    {
        $query_almacen = "";
        if (!is_null($almacen_id) && $almacen_id != "") {
            $query_almacen = " and vent_venta_almacen_id= '$almacen_id'";
        }

        try {
            $sql = "select
    CASE
    WHEN mes = 1 THEN 'ENERO'
    WHEN mes = 2 THEN 'FEBRERO'
    WHEN mes = 3 THEN 'MARZO'
    WHEN mes = 4 THEN 'ABRIL'
    WHEN mes = 5 THEN 'MAYO'
    WHEN mes = 6 THEN 'JUNIO'
    WHEN mes = 7 THEN 'JULIO'
    WHEN mes = 8 THEN 'AGOSTO'
    WHEN mes = 9 THEN 'SEPTIEMBRE'
    WHEN mes = 10 THEN 'OCTUBRE'
    WHEN mes = 11 THEN 'NOVIEMBRE'
    WHEN mes = 12 THEN 'DICIEMBRE'
END as mes
     , sum(contado) as contado, sum(credito) as credito
from (select month(vent_venta_fecha_registro) as mes,
             sum(vent_venta_total)            as contado,
             ''                               as credito
      from vent_venta
      where month(vent_venta_fecha_registro) between '$mes_inicio' and '$mes_fin'
        and vent_venta_confirmado = 1
        and vent_venta_tipo_venta = '01'
        and year(vent_venta_fecha_registro)='$anio' $query_almacen
        -- and vent_venta_almacen_id=''
      group by month(vent_venta_fecha_registro)
      union all
      select month(vent_venta_fecha_registro) as mes,
             ''                               as contado,
             sum(vent_venta_total)            as credito

      from vent_venta
      where month(vent_venta_fecha_registro) between '$mes_inicio' and '$mes_fin'
        and vent_venta_confirmado = 1
        and vent_venta_tipo_venta = '02'
        and year(vent_venta_fecha_registro)='$anio' $query_almacen
        -- and vent_venta_almacen_id=''
      group by month(vent_venta_fecha_registro)) as t
group by mes

";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }


    public static function listar_ranking_ventas_compras_pedidos($almacen_id, $fecha_inicio, $fecha_fin)
    {
        $query_almacen = "";
        if (!is_null($almacen_id) && $almacen_id != "") {
            $query_almacen = " and vent_venta_almacen_id= '$almacen_id'";
            $query_almacen_compra = " and comp_compra_almacen_id= '$almacen_id'";
        }

        try {
            $sql = "select date(fecha) as fecha, ifnull(sum(venta),0) as venta, ifnull(sum(compra),0) as compra, ifnull(sum(pedido),0) as pedido
from (
         select date(vent_venta_fecha_registro) as fecha,
                sum(vent_venta_total)           as venta,

                ''                              as compra,
                ''                              as pedido

         from vent_venta
         where date(vent_venta_fecha_registro) between date('$fecha_inicio') and date('$fecha_fin')
           and vent_venta_confirmado = 1
           and vent_venta_estado = 1
           $query_almacen
           and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                  from doc_tipo_comprabante
                                                  where doc_tipo_comprobante_codigo in ('01', '03', '99'))
         group by date(vent_venta_fecha_registro)
         union all
         select date(vent_venta_fecha_registro) as fecha,
                ''                              as venta,

                ''                              as compra,
                sum(vent_venta_total)           as pedido
         from vent_venta
         where date(vent_venta_fecha_registro) between date('$fecha_inicio') and date('$fecha_fin')
           and vent_venta_estado = 1
           $query_almacen
           and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                  from doc_tipo_comprabante
                                                  where doc_tipo_comprobante_codigo in ('88'))
         group by date(vent_venta_fecha_registro)
         union all
         select date(comp_compra_fecha_registro) as fecha,
                ''                               as venta,
                sum(comp_compra_total)           as compra,
                ''                               as pedido
         from comp_compra
         where comp_compra_confirmado = 1
           and comp_compra_tipo = 'C'
           $query_almacen_compra
           and comp_compra_estado = 'REGISTRADO'
           and date(comp_compra_fecha_registro) between date('$fecha_inicio') and date('$fecha_fin')
         group by date(comp_compra_fecha_registro)

         order by fecha) as t
group by fecha";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }


    public static function listar_ranking_ventas_compras_pedidos_mes($almacen_id, $mes_inicio, $mes_fin, $anio)
    {
        $query_almacen = "";
        if (!is_null($almacen_id) && $almacen_id != "") {
            $query_almacen = " and vent_venta_almacen_id= '$almacen_id'";
            $query_almacen_compra = " and comp_compra_almacen_id= '$almacen_id'";
        }

        try {
            $sql = "select  CASE
            WHEN mes = 1 THEN 'ENERO'
            WHEN mes = 2 THEN 'FEBRERO'
            WHEN mes = 3 THEN 'MARZO'
            WHEN mes = 4 THEN 'ABRIL'
            WHEN mes = 5 THEN 'MAYO'
            WHEN mes = 6 THEN 'JUNIO'
            WHEN mes = 7 THEN 'JULIO'
            WHEN mes = 8 THEN 'AGOSTO'
            WHEN mes = 9 THEN 'SEPTIEMBRE'
            WHEN mes = 10 THEN 'OCTUBRE'
            WHEN mes = 11 THEN 'NOVIEMBRE'
            WHEN mes = 12 THEN 'DICIEMBRE'
            END as mes, sum(venta) as venta, sum(compra) as compra, sum(pedido) as pedido
from (
         select month(vent_venta_fecha_registro) as mes,
                sum(vent_venta_total)            as venta,

                ''                               as compra,
                ''                               as pedido
         from vent_venta
         where month(vent_venta_fecha_registro) between '$mes_inicio' and '$mes_fin'
           and year(vent_venta_fecha_registro) = '$anio'
           and vent_venta_confirmado = 1
           and vent_venta_estado = 1 $query_almacen
           -- and vent_venta_almacen_id = ''
           and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                  from doc_tipo_comprabante
                                                  where doc_tipo_comprobante_codigo in ('01', '03', '99'))
         group by month(vent_venta_fecha_registro)
         union all
         select month(vent_venta_fecha_registro) as mes,
                ''                               as venta,

                ''                               as compra,
                sum(vent_venta_total)            as pedido
         from vent_venta
         where month(vent_venta_fecha_registro) between '$mes_inicio' and '$mes_fin'
           and vent_venta_estado = 1
           and year(vent_venta_fecha_registro) = '$anio'
           and vent_venta_confirmado = 1 $query_almacen
           -- and vent_venta_almacen_id = ''
           and vent_venta_tipo_comprobante_id in (select doc_tipo_comprobante_id
                                                  from doc_tipo_comprabante
                                                  where doc_tipo_comprobante_codigo in ('88'))
         group by month(vent_venta_fecha_registro)
         union all
         select month(comp_compra_fecha_registro) as mes,
                ''                                as venta,
                sum(comp_compra_total)            as compra,
                ''                                as pedido
         from comp_compra
         where comp_compra_confirmado = 1
           and month(comp_compra_fecha_registro) between '$mes_inicio' and '$mes_fin'
           and year(comp_compra_fecha_registro)='$anio'
           and comp_compra_tipo = 'C' $query_almacen_compra
           -- and comp_compra_almacen_id = ''
           and comp_compra_estado = 'REGISTRADO'
         group by month(comp_compra_fecha_registro)
         order by mes) as t
group by mes";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }


}


