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
use App\Models\Almacen\ListaPrecioDetalle;
use Illuminate\Http\Request;
use Validator;


class AlmListaPrecioDetalleController extends Controller
{
    public function listar_lista_precios_detalle(Request $request)
    {
        return response()->json(['success' => true,
            'data' => ListaPrecioDetalle::lista_precios_por_articulo($request->input('categoria_id'), $request->input('alm_lista_precio_id'),$request->input('articulo') ),
            'message' => 'Lista de precios'], 200);

    }

    public function registrar_lista_precios_detalle(Request $request)
    {
        foreach ($request->input('lista') as $item) {
            $precio = (object)$item;
            if (ListaPrecioDetalle::where('alm_lista_precio_detalle_articulo_id', $precio->alm_producto_id)
                ->where('alm_lista_precio_detalle_lista_precio_id', $request->input('alm_lista_precio_id'))->exists()) {
                ListaPrecioDetalle::where('alm_lista_precio_detalle_articulo_id', $precio->alm_producto_id)
                    ->where('alm_lista_precio_detalle_lista_precio_id', $request->input('alm_lista_precio_id'))->update(array(
                        'alm_lista_precio_detalle_precio' => $precio->alm_lista_precio_detalle_precio
                    ));
            } else {
                $lista_precio_detalle = new ListaPrecioDetalle();
                $lista_precio_detalle->alm_lista_precio_detalle_id = IdGenerador::generaId();
                $lista_precio_detalle->alm_lista_precio_detalle_lista_precio_id = $request->input('alm_lista_precio_id');
                $lista_precio_detalle->alm_lista_precio_detalle_articulo_id = $precio->alm_producto_id;
                $lista_precio_detalle->alm_lista_precio_detalle_precio = $precio->alm_lista_precio_detalle_precio;
                $lista_precio_detalle->save();
            }

        }
        return response()->json(['success' => true,
            'data' => ListaPrecioDetalle::lista_precios_por_articulo($request->input('categoria_id'), $request->input('alm_lista_precio_id')),
            'message' => 'Lista de precios'], 200);

    }
}
