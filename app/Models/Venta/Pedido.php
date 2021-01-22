<?php


namespace App\Models\Venta;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pedido extends Model
{
    protected $table = 'vent_pedido';
    public $timestamps = false;
    protected $keyType = 'string';

    protected $casts = [
        'vent_pedido_estado' => 'boolean',
        'vent_pedido_estado_entrega' => 'boolean'
    ];
    protected $primaryKey = 'vent_pedido_id';
    protected $fillable = ['vent_pedido_id',
        'vent_pedido_numero',
        'vent_pedido_periodo_id',
        'vent_pedido_serie',
        'vent_pedido_numero_documento_cliente',
        'vent_pedido_numero_talonario',
        'vent_pedido_importe',
        'vent_pedido_estado',
        'vent_pedido_usuario',
        'vent_pedido_almacen_id',
        'vent_pedido_fecha',
        'vent_pedido_fecha_entrega',
        'vent_pedido_estado_entrega',
        'vent_pedido_cliente',
        'vent_pedido_estado_pago'];

    public static function listar_pedidos_por_cobrar()
    {
        try {


            $sql = "select vent_pedido_id,
       vent_pedido_numero,
       vent_pedido_serie,
       vent_pedido_numero_documento_cliente,
       vent_pedido_numero_talonario,
       vent_pedido_importe,
       vent_pedido_fecha,
       vent_pedido_fecha_entrega,
       vent_pedido_estado_entrega,
       vent_pedido_cliente,
       vent_pedido_estado_pago,
       ifnull((select sum(vent_pago_importe)
               from vent_pago
               where vent_pago_venta_id = vent_pedido_id
               group by vent_pago_venta_id), 0.00)                       acuenta,
       vent_pedido_importe - ifnull((select sum(vent_pago_importe)
                                     from vent_pago
                                     where vent_pago_venta_id = vent_pedido_id
                                     group by vent_pago_venta_id), 0.00) saldo
from vent_pedido where vent_pedido_estado_pago=0";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }

    public static function listar_pedido_por_cobrar($id)
    {
        try {


            $sql = "select vent_pedido_id,
       vent_pedido_numero,
       vent_pedido_serie,
       vent_pedido_numero_documento_cliente,
       vent_pedido_numero_talonario,
       vent_pedido_importe,
       vent_pedido_fecha,
       vent_pedido_fecha_entrega,
       vent_pedido_estado_entrega,
       vent_pedido_cliente,
       ifnull((select sum(vent_pago_importe)
               from vent_pago
               where vent_pago_venta_id = vent_pedido_id
               group by vent_pago_venta_id), 0.00)                       acuenta,
       vent_pedido_importe - ifnull((select sum(vent_pago_importe)
                                     from vent_pago
                                     where vent_pago_venta_id = vent_pedido_id
                                     group by vent_pago_venta_id), 0.00) saldo
from vent_pedido where vent_pedido_estado_pago=0 and vent_pedido_id='$id'";
            $Query = DB::select($sql);
        } catch (\Exception $e) {
            dd($e);

        }
        return $Query;
    }
}



