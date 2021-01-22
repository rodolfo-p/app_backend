<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 09/11/18
 * Time: 10:16 AM
 */

namespace App\Http\Controllers;

namespace App\Http\Controllers\Almacen;

use App\Http\Controllers\Controller;
use App\Http\Data\util\IdGenerador;
use App\Models\Almacen\ListaPrecio;
use Illuminate\Http\Request;
use Validator;


class AlmListaPrecioController extends Controller
{
    public function listar_lista_precios()
    {
        return response()->json(['success' => true,
            'data' => ListaPrecio::all(),
            'message' => 'Lista de precios'], 200);

    }

    public function listar_lista_precio($id)
    {
        return response()->json(['success' => true,
            'data' => ListaPrecio::find($id),
            'message' => 'Lista de Precio'], 200);


    }

    public function insertar_lista_precio(Request $request)
    {
        $lista_precio = new ListaPrecio($request->all());
        $lista_precio->alm_lista_precio_id = IdGenerador::generaId();
        $lista_precio->save();
        return response()->json(['success' => true,
            'data' => ListaPrecio::all(),
            'message' => 'Lista de Precios'], 200);
    }

    public function eliminar_lista_precio($id)
    {
        ListaPrecio::find($id)->delete();
        return response()->json(['success' => true,
            'data' => ListaPrecio::all(),
            'message' => 'Lista de Precios'], 200);
    }

    public function editar_lista_precio(Request $request, $id)
    {
        ListaPrecio::find($id)->update($request->all());
        return response()->json(['success' => true,
            'data' => ListaPrecio::all(),
            'message' => 'Lista de Precios'], 200);
    }
}
