<?php


namespace App\Models\Almacen;


use Illuminate\Database\Eloquent\Model;

class PagoProveedores extends Model
{
    protected $table = 'comp_pago_proveedores';
    public $timestamps = false;
    protected $keyType = 'string';

    /*protected $casts = [
        'comp_compra_estado_pago' => 'boolean',
    ];*/
    protected $primaryKey = 'comp_pago_proveedores_id';
    protected $fillable = ['comp_pago_proveedores_id',
        'comp_pago_proveedores_importe',
        'comp_pago_proveedores_fecha',
        'comp_pago_proveedores_numero_pago',
        'comp_pago_proveedores_tipo_pago',
        'comp_pago_proveedores_id_compra',
        'comp_pago_proveedores_id_user',
        'comp_pago_proveedores_vuelto',
        'comp_pago_proveedores_pago',
        'comp_pago_proveedores_periodo_id','comp_pago_proveedores_serie'];
}
