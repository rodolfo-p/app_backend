<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 11/10/18
 * Time: 04:12 PM
 */

namespace App\Http\Controllers;

namespace App\Http\Controllers\Setup;

use App\Http\Data\util\IdGenerador;
use App\Models\Configuracion\Rol;
use App\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use Mail;
use Validator;
use App\Http\Data\Setup\SegRol;

class RolController extends Controller

{

    public function index()
    {
        $roles = Rol::all();
        $jResponse['success'] = true;
        $jResponse['data'] = $roles;

        return response()->json(['success' => true,
            'data' => Rol::all(),
            'message' => 'Lista de Roles'], 200);

    }


    public function create(Request $request)
    {
        request()->validate([
            'seg_rol_nombre' => 'required',
        ]);
        Rol::create(array('seg_rol_nombre' => $request->input('seg_rol_nombre'),
            'seg_rol_descripcion' => $request->input('seg_rol_descripcion'),
            'seg_rol_estado' => true,
            'seg_rol_id' => IdGenerador::generaId(),
        ));
        return response()->json(['success' => true,
            'data' => Rol::all(),
            'message' => 'Lista de Roles'], 200);
    }


    public function detail($id)
    {
        return response()->json(['success' => true,
            'data' => Rol::find($id),
            'message' => 'Lista de Roles'], 200);
    }

    public function destroy($id)
    {
        Rol::find($id)->delete();
        return response()->json(['success' => true,
            'data' => Rol::all(),
            'message' => 'Lista de Roles'], 200);


    }

    public function update(Request $request, $id)
    {
        request()->validate([
            'seg_rol_nombre' => 'required',
            'seg_rol_estado' => 'required',
        ]);
        Rol::find($id)->update(array('seg_rol_nombre' => $request->input('seg_rol_nombre'),
            'seg_rol_estado' => $request->input('seg_rol_estado'),
        ));
        return response()->json(['success' => true,
            'data' => Rol::all(),
            'message' => 'Lista de Roles'], 200);
    }

}

