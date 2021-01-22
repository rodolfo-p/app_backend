<?php


namespace App\Models\Venta;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Exception;
class FlujoSerie extends Model
{
    protected $table = 'vent_flujo_serie';
    public $timestamps = false;
    protected $keyType = 'string';

    /*protected $casts = [
        'cont_asiento_estado' => 'boolean',
    ];*/

    protected $primaryKey = 'vent_serie';
    protected $fillable = ['vent_serie',
        'vent_tipo_comprobante_id',
        'vent_numero',
        'vent_num_cajero',
        'vent_almacen_id'];
    public static function genera_serie($alm_almacen_id)
    {


        try {
            $sql = "insert into vent_flujo_serie

select
  concat(  substr(vent_serie,1,1) , lpad(substr(vent_serie,2,3)+1,3,0)),
  doc_tipo_comprobante_id,
  '0',
  (select max(vent_num_cajero) from vent_flujo_serie)+1,
  '" . $alm_almacen_id . "'
from (  select
         vs.vent_serie,
         dtc.doc_tipo_comprobante_nombre, dtc.doc_tipo_comprobante_id, dtc.doc_tipo_comprobante_codigo,
         alm.alm_almacen_id, alm.alm_almacen_nombre,
         vs.vent_num_cajero
       from   vent_flujo_serie vs,
              alm_almacen alm,
              doc_tipo_comprabante dtc

        where vs.vent_almacen_id = alm.alm_almacen_id
         and   vs.vent_tipo_comprobante_id=dtc.doc_tipo_comprobante_id
         and   vent_num_cajero=(select max(vent_num_cajero) from vent_flujo_serie)
     ) nueva_serie";
            DB::select($sql);


        } catch (Exception $exception) {
            dd($exception);
        }
        return 'ok';
    }

    public static function asignar_serie_usuario($usuario_id, $vent_num_cajero, $vent_almacen_id)
    {
        try {
            $query = "delete from vent_usuario_serie
                where vent_usuario_user_id = '$usuario_id' ";
            DB::delete($query);
            DB::table('vent_usuario_serie')->insert(
                array('vent_usuario_serie_id' => IdGenerador::generaId(),
                    'vent_usuario_user_id' => $usuario_id,
                    'vent_num_cajero' => $vent_num_cajero,
                    'vent_almacen_id' => $vent_almacen_id
                )
            );
            $response = self::ver_series_asignados_usuario($usuario_id, $vent_almacen_id);
        } catch (\Exception $e) {
            $response = $e;
        }
        return $response;
    }


}
