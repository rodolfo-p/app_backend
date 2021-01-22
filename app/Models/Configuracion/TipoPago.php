<?php


namespace App\Models\Configuracion;


use Illuminate\Database\Eloquent\Model;

class TipoPago extends Model
{
    protected $table = 'pag_tipo_pago';
    public $timestamps = false;
    protected $keyType = 'string';
    /*
        protected $casts = [
            'cont_periodo_estado' => 'boolean',
        ];*/

    protected $primaryKey = 'pago_tipo_pago_id';
    protected $fillable = ['pago_tipo_pago_id',
        'pago_tipo_pago_nombre',
        'pago_tipo_pago_codigo'];
}

