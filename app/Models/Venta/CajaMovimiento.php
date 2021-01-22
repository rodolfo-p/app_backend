<?php


namespace App\Models\Venta;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CajaMovimiento extends Model
{
    protected $table = 'caja_movimiento';
    public $timestamps = false;
    protected $keyType = 'string';
    /*
        protected $casts = [
            'seg_cliente_estado' => 'boolean',
        ];*/
    protected $primaryKey = 'caja_movimiento_id';
    protected $fillable = ['caja_movimiento_id',
        'caja_movimiento_monto',
        'caja_movimiento_user',
        'caja_movimiento_almacen_id',
        'caja_movimiento_fecha',
        'caja_movimiento_periodo_id',
        'caja_movimiento_tipo',
        'caja_movimiento_doc_identidad',
        'caja_movimiento_descripcion',
        'caja_movimiento_serie_ref',
        'caja_movimiento_numero_ref'];

    public static function listar_movimiento_por_fecha($alamcen_id, $fecha)
    {
        try {
            $sql = "select vp.vent_pago_fecha                            as fecha,
       vp.vent_pago_numero_pago                      as numero,
       vp.vent_pago_serie                            as serie,
       case
           when vp.vent_pago_modalidad = '01' then 'Efectivo'
           when vp.vent_pago_modalidad = '02' then concat('Visa Nro:', vp.vent_pago_numero_transaccion)
           when vp.vent_pago_modalidad = '03' then concat('MasterCard Nro: ', vp.vent_pago_numero_transaccion)
           when vp.vent_pago_modalidad = '04' then concat('InterBank Nro: ', vp.vent_pago_numero_transaccion)
           when vp.vent_pago_modalidad = '05' then concat('BCP Nro: ', vp.vent_pago_numero_transaccion)
           end                                          modalidad_pago,
       vp.vent_pago_importe                          as importe,
       u.email                                       as usuario,
       vp.vent_pago_cliente_documento                as num_documento,
       (select concat('COBRANZA DE VENTA ', vent_venta_serie, ' ', vent_venta_numero)
        from vent_venta
        where vent_venta_id = vp.vent_pago_venta_id) as descripcion,
       'INGRESO'                                     as tipo
from vent_pago as vp,
     users as u
where vp.vent_pago_user_id = u.id
  and date(vp.vent_pago_fecha) = '$fecha'
  and vp.vent_pago_almacen_id = '$alamcen_id'
  and vp.vent_pago_venta_id in (select vent_venta_id from vent_venta where vent_venta_tipo_comprobante_id in ('BO15455238811363142','FA15455239119328121','NV152121212121212') )
union all
select vp.vent_pago_fecha                            as fecha,
       vp.vent_pago_numero_pago                      as numero,
       vp.vent_pago_serie                            as serie,
       case
           when vp.vent_pago_modalidad = '01' then 'Efectivo'
           when vp.vent_pago_modalidad = '02' then concat('Visa Nro:', vp.vent_pago_numero_transaccion)
           when vp.vent_pago_modalidad = '03' then concat('MasterCard Nro: ', vp.vent_pago_numero_transaccion)
           when vp.vent_pago_modalidad = '04' then concat('InterBank Nro: ', vp.vent_pago_numero_transaccion)
           when vp.vent_pago_modalidad = '05' then concat('BCP Nro: ', vp.vent_pago_numero_transaccion)
           end                                          modalidad_pago,
       vp.vent_pago_importe                          as importe,
       u.email                                       as usuario,
       vp.vent_pago_cliente_documento                as num_documento,
       (select concat('COBRANZA DE VENTA ', vent_venta_serie, ' ', vent_venta_numero)
        from vent_venta
        where vent_venta_id = vp.vent_pago_venta_id) as descripcion,
       'SALIDA'                                     as tipo
from vent_pago as vp,
     users as u
where vp.vent_pago_user_id = u.id
  and date(vp.vent_pago_fecha) = '$fecha'
  and vp.vent_pago_almacen_id = '$alamcen_id'
  and vp.vent_pago_venta_id in (select vent_venta_id from vent_venta where vent_venta_tipo_comprobante_id in ('NO15455239275629160') )

union all
select cm.caja_movimiento_fecha         as fecha,
       ''                               as numero,
       ''                               as serie,
       'Efectivo'                       as modalidad_pago,
       cm.caja_movimiento_monto         as importe,
       u.email                          as usuario,
       cm.caja_movimiento_doc_identidad as num_documento,
       cm.caja_movimiento_descripcion   as descripcion,

       cm.caja_movimiento_tipo          as tipo

from caja_movimiento as cm,
     users as u
where cm.caja_movimiento_user = u.id
and cm.caja_movimiento_almacen_id='$alamcen_id'
  and date(cm.caja_movimiento_fecha) = '$fecha'";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }


    public static function listar_movimiento_por_fecha_usuario($alamcen_id, $fecha, $usuario_id)
    {
        try {
            $sql = "select vp.vent_pago_fecha             as fecha,
       vp.vent_pago_numero_pago       as numero,
       vp.vent_pago_serie             as serie,
       case
           when vp.vent_pago_modalidad = '01' then 'Efectivo'
           when vp.vent_pago_modalidad = '02' then concat('Visa Nro:', vp.vent_pago_numero_transaccion)
           when vp.vent_pago_modalidad = '03' then concat('MasterCard Nro: ', vp.vent_pago_numero_transaccion)
           when vp.vent_pago_modalidad = '04' then concat('InterBank Nro: ', vp.vent_pago_numero_transaccion)
           when vp.vent_pago_modalidad = '05' then concat('BCP Nro: ', vp.vent_pago_numero_transaccion)
           end                           modalidad_pago,
       vp.vent_pago_importe           as importe,
       u.email                        as usuario,
       vp.vent_pago_cliente_documento as num_documento,
       (select concat('COBRANZA DE VENTA ', vent_venta_serie, ' ', vent_venta_numero)
        from vent_venta
        where vent_venta_id = vp.vent_pago_venta_id
          and vent_venta_confirmado = 1)
                                      as descripcion,
       'INGRESO'                      as tipo
from vent_pago as vp,
     users as u
where vp.vent_pago_user_id = u.id
  and vp.vent_pago_venta_id in (select vent_venta_id from vent_venta where vent_venta_confirmado=1)
  and date(vp.vent_pago_fecha) = '$fecha'
  and vp.vent_pago_almacen_id = '$alamcen_id'
  and vp.vent_pago_user_id='1'
  and vp.vent_pago_venta_id in (select vent_venta_id from vent_venta where vent_venta_tipo_comprobante_id in  ('BO15455238811363142','FA15455239119328121','NV152121212121212'))


union all

select vp.vent_pago_fecha             as fecha,
       vp.vent_pago_numero_pago       as numero,
       vp.vent_pago_serie             as serie,
       case
           when vp.vent_pago_modalidad = '01' then 'Efectivo'
           when vp.vent_pago_modalidad = '02' then concat('Visa Nro:', vp.vent_pago_numero_transaccion)
           when vp.vent_pago_modalidad = '03' then concat('MasterCard Nro: ', vp.vent_pago_numero_transaccion)
           when vp.vent_pago_modalidad = '04' then concat('InterBank Nro: ', vp.vent_pago_numero_transaccion)
           when vp.vent_pago_modalidad = '05' then concat('BCP Nro: ', vp.vent_pago_numero_transaccion)
           end                           modalidad_pago,
       vp.vent_pago_importe           as importe,
       u.email                        as usuario,
       vp.vent_pago_cliente_documento as num_documento,
       (select concat('DEVOLUCION POR NOTA DE CREDITO ', vent_venta_serie, ' ', vent_venta_numero)
        from vent_venta
        where vent_venta_id = vp.vent_pago_venta_id
          and vent_venta_confirmado = 1)
                                      as descripcion,
       'SALIDA'                      as tipo
from vent_pago as vp,
     users as u
where vp.vent_pago_user_id = u.id
  and vp.vent_pago_venta_id in (select vent_venta_id from vent_venta where vent_venta_confirmado=1)
  and date(vp.vent_pago_fecha) = '$fecha'
  and vp.vent_pago_almacen_id = '$alamcen_id'
  and vp.vent_pago_user_id='$usuario_id'
  and vp.vent_pago_venta_id in (select vent_venta_id from vent_venta where vent_venta_tipo_comprobante_id in  ('NO15455239275629160'))


union all

select vp.vent_pago_fecha             as fecha,
       vp.vent_pago_numero_pago       as numero,
       vp.vent_pago_serie             as serie,
       case
           when vp.vent_pago_modalidad = '01' then 'Efectivo'
           when vp.vent_pago_modalidad = '02' then concat('Visa Nro:', vp.vent_pago_numero_transaccion)
           when vp.vent_pago_modalidad = '03' then concat('MasterCard Nro: ', vp.vent_pago_numero_transaccion)
           when vp.vent_pago_modalidad = '04' then concat('InterBank Nro: ', vp.vent_pago_numero_transaccion)
           when vp.vent_pago_modalidad = '05' then concat('BCP Nro: ', vp.vent_pago_numero_transaccion)
           end                           modalidad_pago,
       vp.vent_pago_importe           as importe,
       u.email                        as usuario,
       vp.vent_pago_cliente_documento as num_documento,
       (select concat('COBRANZA DE PEDIDO ', vent_pedido_serie, ' ', vent_pedido_numero)
        from vent_pedido
        where vent_pedido_id = vp.vent_pago_venta_id
       )
                                      as descripcion,
       'INGRESO'                      as tipo
from vent_pago as vp,
     users as u
where vp.vent_pago_user_id = u.id
  and vp.vent_pago_venta_id in (select vent_pedido_id from vent_pedido)
  and date(vp.vent_pago_fecha) = '$fecha'
  and vp.vent_pago_almacen_id = '$alamcen_id'
  and vp.vent_pago_user_id='$usuario_id'


union all


select cm.caja_movimiento_fecha         as fecha,
       ''                               as numero,
       ''                               as serie,
       'Efectivo'                       as modalidad_pago,
       cm.caja_movimiento_monto         as importe,
       u.email                          as usuario,
       cm.caja_movimiento_doc_identidad as num_documento,
       cm.caja_movimiento_descripcion   as descripcion,

       cm.caja_movimiento_tipo          as tipo

from caja_movimiento as cm,
     users as u
where cm.caja_movimiento_user = u.id
  and cm.caja_movimiento_almacen_id='$alamcen_id'
  and cm.caja_movimiento_user='$usuario_id'
  and date(cm.caja_movimiento_fecha) = '$fecha'";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }

    public static function listar_movimiento_por_mes($alamcen_id, $mes, $anio)
    {
        try {
            $sql = "select date(temporal.fecha) as fecha, sum(temporal.importe) as importe, temporal.tipo
from (select vp.vent_pago_fecha   as fecha,
             vp.vent_pago_importe as importe,
             'INGRESO'            as tipo
      from vent_pago as vp,
           users as u
      where vp.vent_pago_user_id = u.id
        and MONTH(vp.vent_pago_fecha) = '$anio'
        and year(vp.vent_pago_fecha) = '$mes'
        and vp.vent_pago_almacen_id = '$alamcen_id'
        and vp.vent_pago_venta_id in (select vent_venta_id from vent_venta where vent_venta_tipo_comprobante_id in  ('BO15455238811363142','FA15455239119328121','NV152121212121212'))

      union all

      select vp.vent_pago_fecha   as fecha,
             vp.vent_pago_importe as importe,
             'SALIDA'            as tipo
      from vent_pago as vp,
           users as u
      where vp.vent_pago_user_id = u.id
        and MONTH(vp.vent_pago_fecha) = '$anio'
        and year(vp.vent_pago_fecha) = '$mes'
        and vp.vent_pago_almacen_id = '$alamcen_id'
        and vp.vent_pago_venta_id in (select vent_venta_id from vent_venta where vent_venta_tipo_comprobante_id in  ('NO15455239275629160'))

      UNION ALL

      select cm.caja_movimiento_fecha as fecha,
             cm.caja_movimiento_monto as importe,
             cm.caja_movimiento_tipo  as tipo
      from caja_movimiento as cm,
           users as u
      where cm.caja_movimiento_user = u.id
        and cm.caja_movimiento_almacen_id = '$alamcen_id'
        and MONTH(cm.caja_movimiento_fecha) = '$mes'
        and year(cm.caja_movimiento_fecha) = '$anio'
     ) as temporal
group by date(fecha), tipo";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }

    public static function listar_movimiento_por_mes_usuario($alamcen_id, $mes, $anio, $usuario)
    {
        try {
            $sql = "select date(temporal.fecha) as fecha, sum(temporal.importe) as importe, temporal.tipo
from (select vp.vent_pago_fecha   as fecha,
             vp.vent_pago_importe as importe,
             'INGRESO'            as tipo
      from vent_pago as vp,
           users as u
      where vp.vent_pago_user_id = u.id
        and MONTH(vp.vent_pago_fecha) = '$mes'
        and year(vp.vent_pago_fecha) = '$anio'
        and vp.vent_pago_almacen_id = '$alamcen_id'
        and vp.vent_pago_user_id = '$usuario'
union all
      select cm.caja_movimiento_fecha as fecha,
             cm.caja_movimiento_monto as importe,
             cm.caja_movimiento_tipo  as tipo
      from caja_movimiento as cm,
           users as u
      where cm.caja_movimiento_user = u.id
        and cm.caja_movimiento_almacen_id = '$alamcen_id'
        and MONTH(cm.caja_movimiento_fecha) = '$mes'
        and year(cm.caja_movimiento_fecha) = '$anio'
        and cm.caja_movimiento_user = '$usuario'
     ) as temporal
group by date(fecha), tipo";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }

}




