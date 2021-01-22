<?php


namespace App\Models\Configuracion;


use Illuminate\Database\Eloquent\Model;

class TipoComprobante extends Model
{
    protected $table = 'doc_tipo_comprabante';
    public $timestamps = false;
    protected $keyType = 'string';

    /* protected $casts = [
         'cont_periodo_estado' => 'boolean',
     ];*/

    protected $primaryKey = 'doc_tipo_comprobante_id';
    protected $fillable = ['doc_tipo_comprobante_id',
        'doc_tipo_comprobante_nombre',
        'doc_tipo_comprobante_codigo'];
}

