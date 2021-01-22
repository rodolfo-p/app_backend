<?php


namespace App\Models\Almacen;


use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    protected $table = 'alm_unidad_medida';
    public $timestamps = false;
    protected $keyType = 'string';

    protected $casts = [
        'alm_unidad_medida_estado' => 'boolean',
    ];

    protected $primaryKey = 'alm_unidad_medida_id';
    protected $fillable = ['alm_unidad_medida_id',
        'alm_unidad_medida_nombre',
        'alm_unidad_medida_estado',
        'alm_unidad_medida_simbolo',
        'alm_unidad_medida_simbolo_impresion'];
}
