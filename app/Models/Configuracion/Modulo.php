<?php


namespace App\Models\Configuracion;


use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $table = 'seg_modulo';
    public $timestamps = false;
    protected $keyType = 'string';

    protected $casts = [
        'seg_modulo_estado' => 'boolean',
    ];

    protected $primaryKey = 'seg_modulo_id';
    protected $fillable = ['seg_modulo_id',
        'seg_modulo_nombre',
        'seg_modulo_icono',
        'seg_modulo_estado',
        'seg_modulo_orden',
        'seg_modulo_nivel',
        'seg_modulo_url',
        'Parent_seg_modulo_id'];
}
