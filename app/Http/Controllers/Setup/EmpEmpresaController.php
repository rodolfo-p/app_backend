<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 09/12/18
 * Time: 04:01 PM
 */


namespace App\Http\Controllers;

namespace App\Http\Controllers\Setup;

use App\Models\Configuracion\Empresa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use Mail;
use Validator;


class EmpEmpresaController extends Controller
{

    public function empresa()
    {
        return response()->json(['success' => true,
            'data' => Empresa::all()->first(),
            'message' => 'Datos Empresa'], 200);

    }

    public function actualizar_empresa(Request $request, $id)
    {
        Empresa::find($id)->update($request->all());
        return response()->json(['success' => true,
            'data' => Empresa::all()->first(),
            'message' => 'Datos Empresa'], 200);
    }

    public function subir_certificado_digital(Request $request)
    {
        $empresa = Empresa::all()->first();
        if ($request->hasFile('emp_empresa_firma_digital')) {
            $firma_digital = $request->file('emp_empresa_firma_digital');
            $firma_digital->move(realpath(__DIR__ . '/../../../../../' . 'comprobantes/certificados/produccion/'), $empresa->emp_empresa_ruc . '.pfx');
        }
        Empresa::find($empresa->emp_empresa_id)->update(
            array('emp_empresa_firma_digital' => $empresa->emp_empresa_ruc . '.pfx',
                'emp_empresa_firma_digital_passwd' => $request->input('emp_empresa_firma_digital_passwd')
            )
        );
        return response()->json(['success' => true,
            'data' => Empresa::all()->first(),
            'message' => 'Datos Empresa'], 200);
    }


}
