<?php


namespace App\Models\Configuracion;


use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'seg_rol';
    public $timestamps = false;
    protected $keyType = 'string';

    protected $casts = [
        'seg_rol_estado' => 'boolean',
    ];
    /*protected $attributes = [
        'seg_rol_estado' => true
    ];*/
    protected $primaryKey = 'seg_rol_id';
    protected $fillable = ['seg_rol_id',
        'seg_rol_nombre',
        'seg_rol_estado'];
}
