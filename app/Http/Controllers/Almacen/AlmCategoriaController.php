<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 30/10/18
 * Time: 01:50 PM
 */

namespace App\Http\Controllers;

namespace App\Http\Controllers\Almacen;
use App\Http\Data\util\IdGenerador;
use App\Models\Almacen\Producto;
use Illuminate\Http\Request;
use Validator;


class AlmCategoriaController
{
    public function listar_categorias()
    {
        return response()->json(['success' => true,
            'data' => Producto::select(
                'alm_producto_codigo',
                'alm_producto_estado',
                'alm_producto_id',
                'alm_producto_nombre'
            )->where('alm_producto_nivel', 1)
                ->orderBy('alm_producto_nombre')
                ->get(),
            'message' => 'Lista de Almacenes'], 200);
    }

    public function listar_categoria($id)
    {
        return response()->json(['success' => true,
            'data' => Producto::select(
                'alm_producto_codigo',
                'alm_producto_estado',
                'alm_producto_id',
                'alm_producto_nombre'
            )->find($id),
            'message' => 'Lista de Almacenes'], 200);

    }

    public function registrar_categoria(Request $request)
    {
        request()->validate([
            'alm_producto_nombre' => 'required',
            'alm_producto_codigo' => 'required',
        ]);
        $categoria = new Producto($request->all());
        $categoria->alm_producto_id = IdGenerador::generaId();
        $categoria->alm_producto_nivel = 1;
        $categoria->save();
        return response()->json(['success' => true,
            'data' => Producto::select(
                'alm_producto_codigo',
                'alm_producto_estado',
                'alm_producto_id',
                'alm_producto_nombre'
            )->where('alm_producto_nivel', 1)->get(),
            'message' => 'Lista de Almacenes'], 200);
    }

    public function eliminar_categoria($id)
    {

        Producto::find($id)->delete();
        return response()->json(['success' => true,
            'data' => Producto::select(
                'alm_producto_codigo',
                'alm_producto_estado',
                'alm_producto_id',
                'alm_producto_nombre'
            )->where('alm_producto_nivel', 1)->get(),
            'message' => 'Lista de Almacenes'], 200);
    }

    public function editar_categoria(Request $request, $id)
    {
        request()->validate([
            'alm_producto_nombre' => 'required',
            'alm_producto_codigo' => 'required',
        ]);
        Producto::find($id)->update($request->all());
        return response()->json(['success' => true,
            'data' => Producto::select(
                'alm_producto_codigo',
                'alm_producto_estado',
                'alm_producto_id',
                'alm_producto_nombre'
            )->where('alm_producto_nivel', 1)->get(),
            'message' => 'Lista de Almacenes'], 200);
    }

}
