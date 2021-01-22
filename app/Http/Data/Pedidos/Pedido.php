<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 07/02/19
 * Time: 11:46 AM
 */

namespace App\Http\Data\Pedidos;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Data\util\IdGenerador;

class Pedido extends Controller
{

    public static function registrar_pedido($usuario,
                                            $cliente,
                                            $precioTotalPedido,
                                            $almacen,
                                            $tipoPago,
                                            $importe,
                                            $modalidad,
                                            $numeroTranccion,
                                            $vuelto, $codigo_pedido_fisico, $fecha_entrega, $pedidos)
    {
        $sql = "select cont_periodo_id, cont_periodo_anio from cont_periodo where cont_periodo_estado= 1";
        $QueryPeriodo = DB::select($sql);
        $periodo_id = $QueryPeriodo[0]->cont_periodo_id;


        $sqlNumero = " select max(vent_pedido_numero)as  numero from  vent_pedido  where vent_pedido_almacen_id= '" . $almacen . "'";
        $QueryNumero = DB::select($sqlNumero);

        if (count($QueryNumero) >= 1) {
            $pedido_numero = str_pad($QueryNumero[0]->numero + 1, 8, "0", STR_PAD_LEFT);

        } else {
            $pedido_numero = '00000001';
        }

        $pedido_id = IdGenerador::generaId();
        DB::table('vent_pedido')->insert(
            array('vent_pedido_id' => $pedido_id,
                'vent_pedido_numero' => $pedido_numero,
                'vent_pedido_periodo_id' => $periodo_id,
                'vent_pedido_serie' => 'P-' . $QueryPeriodo[0]->cont_periodo_anio,
                'vent_pedido_numero_documento_cliente' => $cliente,
                'vent_pedido_numero_talonario' => $codigo_pedido_fisico,
                'vent_pedido_importe' => $precioTotalPedido,
                'vent_pedido_estado' => 1,
                'vent_pedido_usuario' => $usuario,
                'vent_pedido_almacen_id' => $almacen,
                'vent_pedido_fecha' => date('Y-m-d H:i:s'),
                'vent_pedido_fecha_entrega' => $fecha_entrega,


            )
        );

        foreach ($pedidos as $key => $pedido) {
            DB::table('vent_pedido_detalle')->insert(
                array('vent_pedido_detalle_id' => IdGenerador::generaId(),
                    'vent_pedido_detalle_cantidad' => $pedido->vent_pedido_detalle_cantidad,
                    'vent_pedido_detalle_descripcion' => $pedido->vent_pedido_detalle_descripcion,
                    'vent_pedido_detalle_precio_initario' => $pedido->vent_pedido_detalle_precio_initario,
                    'vent_pedido_detalle_precio' => $pedido->vent_pedido_detalle_precio,
                    'vent_pedido_detalle_pedido_id' => $pedido_id,
                    'vent_pedido_detalle_estado' => 1
                )
            );
        }

        DB::table('vent_pago')->insert(
            array('vent_pago_id' => IdGenerador::generaId(),
                'vent_pago_importe' => $precioTotalPedido,
                'vent_pago_fecha' => date('Y-m-d H:i:s', strtotime("now")),
                'vent_pago_numero_pago' => IdGenerador::generaNumeroPago(),
                'vent_pago_venta_id' => $pedido_id,
                'vent_pago_tipo_pago' => $tipoPago,
                'vent_pago_user_id' => $usuario,
                'vent_pago_pago' => $importe,
                'vent_pago_vuelto' => $vuelto,
                'vent_pago_cliente_documento' => $cliente,
                'vent_pago_modalidad' => $modalidad,
                'vent_pago_numero_transaccion' => $numeroTranccion,
                'vent_pago_almacen_id' => $almacen,
                'vent_pago_periodo_id' => $periodo_id,

            )
        );

        return self::listar_pedidos();
    }


    public static function listar_pedidos()
    {


        try {
            $sql = "select p.vent_pedido_id,
 p.vent_pedido_numero,
 case
  when character_length(p. vent_pedido_numero_documento_cliente)=8 then
    (select concat(seg_per_apellido_paterno,' ', seg_per_apellido_materno,' ', seg_per_nombres)
     from seg_cliente where seg_per_dni=p. vent_pedido_numero_documento_cliente)
  when p. vent_pedido_numero_documento_cliente ='0000000000' then 'Clientes Varios'
  else (select alm_proveedor_razon_social from alm_proveedor where alm_proveedor_ruc= p. vent_pedido_numero_documento_cliente)
  end as cliente,
per.cont_periodo_anio,
p. vent_pedido_serie,
p. vent_pedido_numero_documento_cliente,
 p.vent_pedido_numero_talonario,
 p.vent_pedido_importe,
 p.vent_pedido_estado,
u.email,
alm.alm_almacen_nombre,
 p.vent_pedido_fecha,
 p.vent_pedido_fecha_entrega,
 p.vent_pedido_estado_entrega
 from vent_pedido as p, cont_periodo as per, alm_almacen alm, users as u
 where p.vent_pedido_periodo_id= per.cont_periodo_id
 and p.vent_pedido_almacen_id= alm.alm_almacen_id and p.vent_pedido_usuario= u.id";
            $Querys = DB::select($sql);
            foreach ($Querys as $key => $query) {
                $query->depositos = self::listar_depositos_por_pedido($query->vent_pedido_id);
            }
        } catch (\Exception $e) {
            $Querys = $e;
        }
        return $Querys;
    }

    public static function listar_pedido_detalle($id)
    {
        try {
            $sql = "select vent_pedido_detalle_id, vent_pedido_detalle_cantidad,
 vent_pedido_detalle_descripcion,
 vent_pedido_detalle_precio_initario,
 vent_pedido_detalle_precio,vent_pedido_detalle_pedido_id,vent_pedido_detalle_estado
 from vent_pedido_detalle where vent_pedido_detalle_pedido_id='" . $id . "' ";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            $Query = $e;
        }
        return $Query;
    }


    public static function listar_pedidos_por_pagar($documento_cliente_numero)
    {
        try {
            $sql = "select p.vent_pedido_id, p.vent_pedido_numero,
p.vent_pedido_serie, p.vent_pedido_numero_documento_cliente,
p.vent_pedido_numero_talonario, p.vent_pedido_fecha,p.vent_pedido_fecha_entrega,p.vent_pedido_importe,
ifnull((select sum(vent_pago_pago)
 from vent_pago where vent_pago_venta_id =p.vent_pedido_id),0.00) as total_depositado,
ifnull( p.vent_pedido_importe,0.00)-ifnull((select sum(vent_pago_pago)
 from vent_pago where vent_pago_venta_id =p.vent_pedido_id),0.00) a_cuenta
from vent_pedido as p, vent_pago as vp where p.vent_pedido_id=vp.vent_pago_venta_id
and   ifnull( p.vent_pedido_importe,0.00)>ifnull((select sum(vent_pago_pago)
 from vent_pago where vent_pago_venta_id =p.vent_pedido_id),0.00) and p.vent_pedido_numero_documento_cliente=vp.vent_pago_cliente_documento
 and p.vent_pedido_numero_documento_cliente='" . $documento_cliente_numero . "'  group by p.vent_pedido_id, p.vent_pedido_numero";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            $Query = $e;
        }
        return $Query;
    }


    public static function listar_depositos_por_pedido($pedido_id)
    {
        try {
            $sql = "select vent_pago_pago,
 vent_pago_fecha,
vent_pago_numero_pago,
vent_pago_cliente_documento
from vent_pago where vent_pago_venta_id= '" . $pedido_id . "'";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            $Query = $e;
        }
        return $Query;

    }

    public static function registar_deposito_deposito($usuario, $venta_id, $documento_cliente_nuemero, $importe, $almacen_id, $importe_total)
    {
        $sql = "select cont_periodo_id, cont_periodo_anio from cont_periodo where cont_periodo_estado= 1";
        $QueryPeriodo = DB::select($sql);
        $comp_compra_periodo_id = $QueryPeriodo[0]->cont_periodo_id;
        if ($importe_total <= $importe) {
            $query = "UPDATE vent_pedido SET vent_pedido_estado_entrega = '1'
                    WHERE vent_pedido_id = '" . $venta_id . "'";
            DB::update($query);
        }
        DB::table('vent_pago')->insert(
            array('vent_pago_id' => IdGenerador::generaId(),
                'vent_pago_fecha' => date('Y-m-d H:i:s', strtotime("now")),
                'vent_pago_numero_pago' => IdGenerador::generaNumeroPago(),
                'vent_pago_venta_id' => $venta_id,
                'vent_pago_tipo_pago' => '02',
                'vent_pago_user_id' => $usuario,
                'vent_pago_pago' => $importe,
                'vent_pago_vuelto' => 0.00,
                'vent_pago_cliente_documento' => $documento_cliente_nuemero,
                'vent_pago_modalidad' => '01',
                'vent_pago_almacen_id' => $almacen_id,
                'vent_pago_periodo_id' => $comp_compra_periodo_id,
            )
        );
        return true;
    }

}
