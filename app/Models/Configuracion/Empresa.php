<?php


namespace App\Models\Configuracion;


use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'emp_empresa';
    public $timestamps = false;
    protected $keyType = 'string';

    protected $casts = [
        'emp_empresa_calculo_total' => 'boolean',
        'emp_empresa_ose'=>'boolean',
    ];

    protected $primaryKey = 'emp_empresa_id';
    protected $fillable = ['emp_empresa_id',
        'emp_empresa_ruc',
        'emp_empresa_razon_social',
        'emp_empresa_nombre_comercial',
        'emp_empresa_telefono',
        'emp_empresa_direccion',
        'emp_empresa_codigo_ubigeo',
        'emp_empresa_direccion_departamento',
        'emp_empresa_direccion_provincia',
        'emp_empresa_direccion_distrito',
        'emp_empresa_codigopais',
        'emp_empresa_usuariosol',
        'emp_empresa_clavesol',
        'emp_empresa_tipoproceso',
        'emp_empresa_llave_ruc_dni',
        'emp_empresa_formato_doc_imp',
        'emp_empresa_email',
        'emp_empresa_regimen_tributario',
        'emp_empresa_delivery',
        'emp_empresa_firma_digital_passwd',
        'emp_empresa_logo_url',
        'emp_empresa_firma_digital',
        'emp_empresa_calculo_total',
        'emp_empresa_ose'];
}
