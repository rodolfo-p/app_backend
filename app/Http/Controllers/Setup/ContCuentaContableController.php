<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 11/01/19
 * Time: 09:38 AM
 */

namespace App\Http\Controllers\Setup;


use App\Http\Controllers\Controller;
use App\Http\Data\util\IdGenerador;
use App\Models\Configuracion\CuentaContable;
use Validator;

use Illuminate\Http\Request;

//use Mail;

class ContCuentaContableController extends Controller
{

    private function listar_data()
    {
        $lista = array();
        foreach (CuentaContable::select('*')
                     ->where('cont_asiento_nivel', 1)
                     ->orderBy('cont_asiento_cuenta')
                     ->get() as $key => $value) {
            $item = ContCuentaContableController::recursivo_cuentas($value->cont_asiento_id);
            $value->collapse = false;
            $value->children = $item;
            array_push($lista, $value);
        }
        return $lista;
    }

    private function recursivo_cuentas($cont_asiento_id)
    {
        $lista_hijos = array();
        foreach (CuentaContable::select('*')
                     ->where('Parent_cont_asiento_id', $cont_asiento_id)
                     ->orderBy('cont_asiento_cuenta')
                     ->get() as $key => $value) {
            $items = $this->recursivo_cuentas($value->cont_asiento_id);
            $lista_hijos[] = ['cont_asiento_id' => $value->cont_asiento_id,
                'cont_asiento_nombre' => $value->cont_asiento_nombre . '[' . $value->cont_asiento_cuenta . ']',
                'cont_asiento_cuenta' => $value->cont_asiento_cuenta,
                'cont_asiento_nivel' => $value->cont_asiento_nivel,
                'cont_asiento_estado' => $value->cont_asiento_estado,
                'Parent_cont_asiento_id' => $value->Parent_cont_asiento_id,
                'cont_asiento_clase' => $value->cont_asiento_clase,
                'collapse' => false,
                'children' => $items];
        }
        return $lista_hijos;
    }


    public function listar_asientos_contables()
    {
        return response()->json(
            ['success' => true,
                'data' => $this->listar_data(),
                'message' => 'Operacion Correcta'],
            200);
    }


    public function registrar_asiento_contable(Request $request)
    {
        CuentaContable::create(array('cont_asiento_nombre' => $request->input('cont_asiento_nombre'),
            'cont_asiento_cuenta' => $request->input('cont_asiento_cuenta'),
            'cont_asiento_nivel' => $request->input('cont_asiento_nivel'),
            'Parent_cont_asiento_id' => $request->input('Parent_cont_asiento_id'),
            'cont_asiento_estado' => $request->input('cont_asiento_estado'),
            'cont_asiento_clase' => $request->input('cont_asiento_clase'),
            'cont_asiento_id' => IdGenerador::generaId(),
        ));
        return response()->json(['success' => true,
            'data' => $this->listar_data(),
            'message' => 'Registro correcto de Rol'], 201);
    }


    public function listar_cuenta($id)
    {
        return response()->json(['success' => true,
            'data' => CuentaContable::find($id),
            'message' => 'Busqueda Correcta de Asiento Contable'], 200);
    }


    public function editar_asiento_contable($id, Request $request)
    {
        CuentaContable::find($id)->update($request->all());
        return response()->json(['success' => true,
            'data' => $this->listar_data(),
            'message' => 'Actualizacion correcta de Asiento Contable'], 200);
    }


    public function eliminar_asiento_contable($id)
    {
        CuentaContable::find($id)->delete();
        return response()->json(['success' => true,
            'data' => $this->listar_data(),
            'message' => 'Eliminacion Correcra de Asiento Contable'], 200);
    }

    public function listar_cuenta_60()
    {
        $lista = array();
        $this->cuenta = 60;
        foreach (CuentaContable::whereIn('Parent_cont_asiento_id', function ($query) {
            $query->select('cont_asiento_id')
                ->from(with(new CuentaContable)->getTable())
                ->whereIn('Parent_cont_asiento_id', function ($query2) {
                    $query2->select('cont_asiento_id')
                        ->from(with(new CuentaContable)->getTable())
                        ->where('cont_asiento_cuenta', $this->cuenta);
                });

        })->get() as $key => $value) {
            $value->asiento_nombre = CuentaContable::where('cont_asiento_id', $value->Parent_cont_asiento_id)
                    ->first()->cont_asiento_nombre . ' ' .
                $value->cont_asiento_nombre .
                ' [' . $value->cont_asiento_cuenta . ']';

            array_push($lista, $value);

        }

        return response()->json(['success' => true,
            'data' => $lista,
            'message' => 'Operacion Correcta'], 200);
    }

    public function listar_cuenta_70()
    {
        $lista = array();
        $this->cuenta = 70;
        foreach (CuentaContable::whereIn('Parent_cont_asiento_id', function ($query) {
            $query->select('cont_asiento_id')
                ->from(with(new CuentaContable)->getTable())
                ->whereIn('Parent_cont_asiento_id', function ($query2) {
                    $query2->select('cont_asiento_id')
                        ->from(with(new CuentaContable)->getTable())
                        ->where('cont_asiento_cuenta', $this->cuenta);
                });

        })->get() as $key => $value) {
            $value->asiento_nombre = CuentaContable::where('cont_asiento_id', $value->Parent_cont_asiento_id)
                    ->first()->cont_asiento_nombre . ' ' .
                $value->cont_asiento_nombre .
                ' [' . $value->cont_asiento_cuenta . ']';

            array_push($lista, $value);

        }

        return response()->json(['success' => true,
            'data' => $lista,
            'message' => 'Operacion Correcta'], 200);
    }
}
