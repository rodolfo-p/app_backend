<?php


namespace App\Models\Configuracion;


use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    protected $table = 'cont_periodo';
    public $timestamps = false;
    protected $keyType = 'string';

    protected $casts = [
        'cont_periodo_estado' => 'boolean',
    ];

    protected $primaryKey = 'cont_periodo_id';
    protected $fillable = ['cont_periodo_id',
        'cont_periodo_periodo',
        'cont_periodo_igv',
        'cont_periodo_estado',
        'cont_periodo_anio'];
}

