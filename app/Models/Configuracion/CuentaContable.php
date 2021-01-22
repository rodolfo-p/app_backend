<?php


namespace App\Models\Configuracion;


use Illuminate\Database\Eloquent\Model;

class CuentaContable extends Model
{
    protected $table = 'cont_asiento_contable';
    public $timestamps = false;
    protected $keyType = 'string';

    protected $casts = [
        'cont_asiento_estado' => 'boolean',
    ];

    protected $primaryKey = 'cont_asiento_id';
    protected $fillable = ['cont_asiento_id',
        'cont_asiento_nombre',
        'cont_asiento_cuenta',
        'cont_asiento_nivel',
        'cont_asiento_estado',
        'Parent_cont_asiento_id',
        'cont_asiento_clase'];
}

