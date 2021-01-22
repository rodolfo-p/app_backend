<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 21/10/18
 * Time: 10:14 PM
 */

namespace App\Http\Controllers;

namespace App\Http\Controllers\Almacen;

use App\Http\Controllers\Controller;
use App\Http\Data\util\GeneraNumero;
use App\Http\Data\util\IdGenerador;
use App\Http\Data\util\Pagination;
use App\Models\Almacen\Almacen;
use App\Models\Almacen\Compra;
use App\Models\Almacen\CompraDetalle;
use App\Models\Almacen\ListaPrecio;
use App\Models\Almacen\ListaPrecioDetalle;
use App\Models\Almacen\Producto;
use App\Models\Configuracion\Empresa;
use App\Models\Configuracion\Periodo;
use App\Models\Configuracion\TipoComprobante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Validator;


class AlmProductoController extends Controller
{
    public function producto($id)
    {
        return response()->json(['success' => true,
            'data' => Producto::find($id),
            'message' => 'Lista de Almacenes'], 200);


    }

    public function productos(Request $request)
    {
        $dato = $request->input('dato');
        $condicional_dato = !is_null($request->input('dato')) || $request->input('dato') != "" ? 'like' : '<>';
        $condicional_parametro = !is_null($request->input('dato')) || $request->input('dato') != "" ? '%' . $dato . '%' : null;

        $condicional_categoria = !is_null($request->input('alm_categoria_id')) ? '=' : '<>';
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(DB::table('alm_producto')
                ->select(DB::raw('alm_producto.alm_producto_nombre as categoria,
                b.alm_producto_id,
                b.alm_producto_nombre,
                b.alm_producto_codigo,
                b.alm_producto_controla_stock,
                b.alm_producto_marca,
                b.alm_producto_serie,
                b.alm_producto_tipo_operacion,
                c.alm_unidad_medida_nombre'))
                ->join('alm_producto as b', 'alm_producto.alm_producto_id', '=', 'b.Parent_alm_producto_id')
                ->join('alm_unidad_medida as c', 'b.alm_unidad_medida_id', '=', 'c.alm_unidad_medida_id')
                ->where('b.Parent_alm_producto_id', $condicional_categoria, is_null($request->input('alm_categoria_id')) ? null : $request->input('alm_categoria_id'))
                ->where(DB::raw('CONCAT(alm_producto.alm_producto_nombre, " ", b.alm_producto_nombre, " ", b.alm_producto_codigo)'), $condicional_dato, $condicional_parametro)
                ->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de Almacenes'], 200);

    }

    public function importar_producto_exel(Request $request)
    {
        if ($request->hasFile('importar_archivo')) {
            Excel::load($request->file('importar_archivo')->getRealPath(), function ($reader) {
                //  $contador = 0;
                foreach ($reader->toArray() as $key => $row) {

                    $data_exel = $row;
                    $categoria_data = $data_exel['categoria'];
                    $id_categoria = IdGenerador::generaId();
                    if (!empty($categoria_data)) {
                        if (!Producto::where('alm_producto_codigo', $data_exel['codigocategoria'])->where('alm_producto_nivel', '1')->exists()) {
                            $categoria = new Producto();
                            $categoria->alm_producto_id = $id_categoria;
                            $categoria->alm_producto_nombre = $categoria_data;
                            $categoria->alm_producto_nivel = '1';
                            $categoria->alm_producto_codigo = $data_exel['codigocategoria'];
                            $categoria->alm_producto_estado = 1;
                            $categoria->save();

                        } else {
                            $id_categoria = Producto::select('alm_producto_id')->where('alm_producto_codigo', $data_exel['codigocategoria'])
                                ->where('alm_producto_nivel', '1')
                                ->first()->alm_producto_id;;
                        }
                    }
                    if (!Producto::where('alm_producto_codigo', $data_exel['codigoarticulo'])->where('alm_producto_nivel', '2')->exists()) {
                        $producto = new Producto();
                        $alm_producto_id = IdGenerador::generaId();
                        $producto->alm_producto_id = $alm_producto_id;
                        $producto->alm_producto_nombre = $data_exel['nombrearticulos'];
                        $producto->alm_producto_nivel = '2';
                        $producto->alm_producto_codigo = $data_exel['codigoarticulo'];
                        $producto->alm_producto_estado = 1;
                        $producto->Parent_alm_producto_id = $id_categoria;
                        $producto->alm_unidad_medida_id = $data_exel['unidadmedida'];
                        $producto->alm_producto_tipo_operacion = $data_exel['tipoafectacion'];
                        $producto->alm_producto_marca = $data_exel['marca'];
                        $producto->alm_producto_controla_stock = $data_exel['controlastock'] === "si" ? 1 : 0;
                        $producto->alm_producto_serie = $data_exel['serie'] === "si" ? 1 : 0;
                        $producto->alm_producto_cuenta_compra = $data_exel['cuentacompra'];
                        $producto->alm_producto_cuenta_venta = $data_exel['cuentaventa'];
                        $producto->alm_producto_descripcion = $data_exel['descripcion'];
                        $producto->alm_producto_foto = $data_exel['urlimagen'] === null || !empty($data_exel['motor']) ? $data_exel['urlimagen'] : "";
                        $producto->alm_producto_modelo = $data_exel['modelo'] === null ? "" : $data_exel['modelo'];
                        $producto->alm_producto_color = $data_exel['color'] === null ? "" : $data_exel['color'];
                        $producto->alm_producto_motor = !empty($data_exel['motor']) || $data_exel['motor'] === null ? "" : $data_exel['motor'];
                        $producto->alm_producto_chasis = !empty($data_exel['chasis']) || $data_exel['chasis'] === null ? "" : $data_exel['chasis'];
                        $producto->alm_producto_dua = !empty($data_exel['dua']) || $data_exel['dua'] === null ? "" : $data_exel['dua'];
                        $producto->alm_producto_item = !empty($data_exel['item']) || $data_exel['item'] === null ? "" : $data_exel['item'];
                        $producto->alm_producto_vehiculo = !empty($data_exel['chasis']) || $data_exel['chasis'] === null ? 0 : 1;
                        if (!is_null($producto->alm_producto_nombre)) {
                            $producto->save();
                        }
                        if (!empty($data_exel['precioventa']) || $data_exel['precioventa'] === null) {
                            $lista_precio_detalle = new ListaPrecioDetalle();
                            $lista_precio_detalle->alm_lista_precio_detalle_id = IdGenerador::generaId();
                            $lista_precio_detalle->alm_lista_precio_detalle_lista_precio_id = 'MA1583880589264896';
                            $lista_precio_detalle->alm_lista_precio_detalle_articulo_id = $alm_producto_id;
                            $empresa = Empresa::all()->first();
                            if ($empresa->emp_empresa_calculo_total) {
                                $lista_precio_detalle->alm_lista_precio_detalle_precio = $data_exel['precioventa'];
                            } else {
                                $lista_precio_detalle->alm_lista_precio_detalle_precio = $data_exel['precioventa'] / ((Periodo::select('cont_periodo_igv')->where('cont_periodo_estado', true)->first()->cont_periodo_igv / 100) + 1);
                            }

                            $lista_precio_detalle->save();


                        }


                    }

                    // $contador = $contador + 1;
                }
            });


        }
        $condicional_categoria = !is_null($request->input('alm_categoria_id')) ? '=' : '<>';
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(DB::table('alm_producto')
                ->select(DB::raw('alm_producto.alm_producto_nombre as categoria,
                b.alm_producto_id,
                b.alm_producto_nombre,
                b.alm_producto_codigo,
                b.alm_producto_controla_stock,
                b.alm_producto_marca,
                b.alm_producto_tipo_operacion,
                c.alm_unidad_medida_nombre'))
                ->join('alm_producto as b', 'alm_producto.alm_producto_id', '=', 'b.Parent_alm_producto_id')
                ->join('alm_unidad_medida as c', 'b.alm_unidad_medida_id', '=', 'c.alm_unidad_medida_id')
                ->where('b.Parent_alm_producto_id', $condicional_categoria, is_null($request->input('alm_categoria_id')) ? null : $request->input('alm_categoria_id'))
                ->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de Almacenes'], 200);
    }

    public function insertar_producto(Request $request)
    {
        request()->validate([
            'alm_producto_codigo' => 'required',
            'alm_producto_nombre' => 'required',
            'alm_unidad_medida_id' => 'required',
            'alm_producto_tipo_operacion' => 'required',
        ]);
        $producto = new Producto($request->all());
        $alm_producto_id = IdGenerador::generaId();
        $producto->alm_producto_id = $alm_producto_id;
        $producto->alm_producto_nivel = 2;
        $producto->alm_producto_modelo = $request->input('alm_producto_modelo') != null ? $request->input('alm_producto_modelo') : "";
        $producto->alm_producto_color = $request->input('alm_producto_color') != null ? $request->input('alm_producto_color') : "";
        $producto->alm_producto_motor = $request->input('alm_producto_motor') != null ? $request->input('alm_producto_motor') : "";
        $producto->alm_producto_chasis = $request->input('alm_producto_chasis') != null ? $request->input('alm_producto_chasis') : "";
        $producto->alm_producto_dua = $request->input('alm_producto_dua') != null ? $request->input('alm_producto_dua') : "";
        $producto->alm_producto_item = $request->input('alm_producto_item') != null ? $request->input('alm_producto_item') : "";
        $producto->alm_producto_nombre = strtoupper($request->input('alm_producto_nombre'));
        $producto->alm_producto_marca = strtoupper($request->input('alm_producto_marca'));
        $producto->save();
        $condicional_categoria = !is_null($request->input('alm_categoria_id')) ? '=' : '<>';
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(DB::table('alm_producto')
                ->select(DB::raw('alm_producto.alm_producto_nombre as categoria,
                b.alm_producto_id,
                b.alm_producto_nombre,
                b.alm_producto_codigo,
                b.alm_producto_controla_stock,
                b.alm_producto_marca,
                b.alm_producto_tipo_operacion,
                c.alm_unidad_medida_nombre'))
                ->join('alm_producto as b', 'alm_producto.alm_producto_id', '=', 'b.Parent_alm_producto_id')
                ->join('alm_unidad_medida as c', 'b.alm_unidad_medida_id', '=', 'c.alm_unidad_medida_id')
                ->where('b.Parent_alm_producto_id', $condicional_categoria, is_null($request->input('alm_categoria_id')) ? null : $request->input('alm_categoria_id'))
                ->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de Almacenes'], 200);

    }

    public function editar_producto(Request $request, $id)
    {
        request()->validate([
            'alm_producto_codigo' => 'required',
            'alm_producto_nombre' => 'required',
            'alm_unidad_medida_id' => 'required',
            'alm_producto_tipo_operacion' => 'required',
        ]);
        $producto = new Producto($request->all());
        $producto->alm_producto_nivel = 2;
        $producto->alm_producto_modelo = $request->input('alm_producto_modelo') != null ? $request->input('alm_producto_modelo') : "";
        $producto->alm_producto_color = $request->input('alm_producto_color') != null ? $request->input('alm_producto_color') : "";
        $producto->alm_producto_motor = $request->input('alm_producto_motor') != null ? $request->input('alm_producto_motor') : "";
        $producto->alm_producto_chasis = $request->input('alm_producto_chasis') != null ? $request->input('alm_producto_chasis') : "";
        $producto->alm_producto_dua = $request->input('alm_producto_dua') != null ? $request->input('alm_producto_dua') : "";
        $producto->alm_producto_item = $request->input('alm_producto_item') != null ? $request->input('alm_producto_item') : "";
        $producto->alm_producto_nombre = strtoupper($request->input('alm_producto_nombre'));
        $producto->alm_producto_marca = strtoupper($request->input('alm_producto_marca'));
        Producto::find($id)->update($producto->toArray());
        $condicional_categoria = !is_null($request->input('alm_categoria_id')) ? '=' : '<>';
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(DB::table('alm_producto')
                ->select(DB::raw('alm_producto.alm_producto_nombre as categoria,
                b.alm_producto_id,
                b.alm_producto_nombre,
                b.alm_producto_codigo,
                b.alm_producto_controla_stock,
                b.alm_producto_marca,
                b.alm_producto_tipo_operacion,
                c.alm_unidad_medida_nombre'))
                ->join('alm_producto as b', 'alm_producto.alm_producto_id', '=', 'b.Parent_alm_producto_id')
                ->join('alm_unidad_medida as c', 'b.alm_unidad_medida_id', '=', 'c.alm_unidad_medida_id')
                ->where('b.Parent_alm_producto_id', $condicional_categoria, is_null($request->input('alm_categoria_id')) ? null : $request->input('alm_categoria_id'))
                ->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de Almacenes'], 200);

    }

    public function buscar_producto_autocomplete(Request $request)
    {
        $productos_lista = [];
        if (!is_null($request->input('dato')) && strlen($request->input('dato')) > 1) {
            $productos = Producto::producto_autocomplete($request->input('dato'), $request->input('almacen_id'));
            foreach ($productos as $key => $producto) {
                if ($producto->alm_producto_controla_stock === 1) {
                    $producto->alm_producto_controla_stock = true;
                } else {
                    $producto->alm_producto_controla_stock = false;
                }
                if ($producto->alm_producto_serie === 1) {
                    $producto->alm_producto_serie = true;
                } else {
                    $producto->alm_producto_serie = false;
                }
                $producto->lista_precios = ListaPrecioDetalle::select('alm_lista_precio.alm_lista_precio_id',
                    'alm_lista_precio.alm_lista_precio_nombre', 'alm_lista_precio_detalle.alm_lista_precio_detalle_precio')
                    ->where('alm_lista_precio_detalle.alm_lista_precio_detalle_articulo_id', $producto->alm_producto_id)
                    ->where('alm_lista_precio.alm_lista_precio_almacen_id', $request->input('almacen_id'))
                    ->where('alm_lista_precio.alm_lista_precio_fecha_vigencia_inicio', '<=', date('Y-m-d'))
                    ->where('alm_lista_precio.alm_lista_precio_fecha_vigencia_fin', '>=', date('Y-m-d'))
                    ->orderBy('alm_lista_precio_detalle.alm_lista_precio_detalle_precio', 'DESC')
                    ->leftJoin('alm_lista_precio', 'alm_lista_precio_detalle.alm_lista_precio_detalle_lista_precio_id', 'alm_lista_precio.alm_lista_precio_id')
                    ->get();
                $producto->producto_nombre = $producto->producto_nombre . ' (Stock ' . $producto->stock . ' ' . $producto->alm_unidad_medida_simbolo . ')';
                array_push($productos_lista, $producto);
            }
        }
        return response()->json(['success' => true,
            'data' => $productos_lista,
            'message' => 'Lista de Articulos'], 200);
    }

    public function buscar_producto_autocomplete_codigo(Request $request)
    {
        $productos_lista = [];
        if (!is_null($request->input('dato')) && strlen($request->input('dato')) > 1) {
            $productos = Producto::producto_autocomplete_codigo($request->input('dato'), $request->input('almacen_id'));
            foreach ($productos as $key => $producto) {
                if ($producto->alm_producto_controla_stock === 1) {
                    $producto->alm_producto_controla_stock = true;
                } else {
                    $producto->alm_producto_controla_stock = false;
                }
                if ($producto->alm_producto_serie === 1) {
                    $producto->alm_producto_serie = true;
                } else {
                    $producto->alm_producto_serie = false;
                }
                $producto->lista_precios = ListaPrecioDetalle::select('alm_lista_precio.alm_lista_precio_id',
                    'alm_lista_precio.alm_lista_precio_nombre', 'alm_lista_precio_detalle.alm_lista_precio_detalle_precio')
                    ->where('alm_lista_precio_detalle.alm_lista_precio_detalle_articulo_id', $producto->alm_producto_id)
                    ->where('alm_lista_precio.alm_lista_precio_almacen_id', $request->input('almacen_id'))
                    ->where('alm_lista_precio.alm_lista_precio_fecha_vigencia_inicio', '<=', date('Y-m-d'))
                    ->where('alm_lista_precio.alm_lista_precio_fecha_vigencia_fin', '>=', date('Y-m-d'))
                    ->orderBy('alm_lista_precio_detalle.alm_lista_precio_detalle_precio', 'DESC')
                    ->leftJoin('alm_lista_precio', 'alm_lista_precio_detalle.alm_lista_precio_detalle_lista_precio_id', 'alm_lista_precio.alm_lista_precio_id')
                    ->get();
                $producto->producto_nombre = $producto->producto_nombre . ' (Stock ' . $producto->stock . ' ' . $producto->alm_unidad_medida_simbolo . ')';
                array_push($productos_lista, $producto);
            }
        }
        return response()->json(['success' => true,
            'data' => $productos_lista,
            'message' => 'Lista de Articulo'], 200);
    }

    public function buscar_producto_autocomplete_venta(Request $request)
    {
        $productos_lista = [];
        if (!is_null($request->input('dato')) && strlen($request->input('dato')) > 1) {
            $productos = Producto::producto_autocomplete_venta($request->input('dato'), $request->input('almacen_id'));
            foreach ($productos as $key => $producto) {
                if ($producto->alm_producto_controla_stock === 1) {
                    $producto->alm_producto_controla_stock = true;
                } else {
                    $producto->alm_producto_controla_stock = false;
                }
                if ($producto->alm_producto_serie === 1) {
                    $producto->alm_producto_serie = true;
                } else {
                    $producto->alm_producto_serie = false;
                }
                $producto->lista_precios = ListaPrecioDetalle::select('alm_lista_precio.alm_lista_precio_id',
                    'alm_lista_precio.alm_lista_precio_nombre', 'alm_lista_precio_detalle.alm_lista_precio_detalle_precio')
                    ->where('alm_lista_precio_detalle.alm_lista_precio_detalle_articulo_id', $producto->alm_producto_id)
                    ->where('alm_lista_precio.alm_lista_precio_almacen_id', $request->input('almacen_id'))
                    ->where('alm_lista_precio.alm_lista_precio_fecha_vigencia_inicio', '<=', date('Y-m-d'))
                    ->where('alm_lista_precio.alm_lista_precio_fecha_vigencia_fin', '>=', date('Y-m-d'))
                    ->orderBy('alm_lista_precio_detalle.alm_lista_precio_detalle_precio', 'DESC')
                    ->leftJoin('alm_lista_precio', 'alm_lista_precio_detalle.alm_lista_precio_detalle_lista_precio_id', 'alm_lista_precio.alm_lista_precio_id')
                    ->get();
                $producto->producto_nombre = $producto->producto_nombre . ' (Stock ' . $producto->stock . ' ' . $producto->alm_unidad_medida_simbolo . ')';
                array_push($productos_lista, $producto);
            }
        }
        return response()->json(['success' => true,
            'data' => $productos_lista,
            'message' => 'Lista de Articulos'], 200);
    }

    public function buscar_producto_autocomplete_venta_codigo(Request $request)
    {
        if (!is_null($request->input('dato')) && strlen($request->input('dato')) > 1) {
            $data_producto = Producto::producto_autocomplete_venta_serie_codigo($request->input('dato'), $request->input('almacen_id'));
            $producto = count($data_producto) >= 1 ? $data_producto[0] : new \stdClass();
            if (count($data_producto) >= 1) {
                if ($producto->alm_producto_controla_stock === 1) {
                    $producto->alm_producto_controla_stock = true;
                } else {
                    $producto->alm_producto_controla_stock = false;
                }
                if ($producto->alm_producto_serie === 1) {
                    $producto->alm_producto_serie = true;
                } else {
                    $producto->alm_producto_serie = false;
                }
                $producto->lista_precios = ListaPrecioDetalle::select('alm_lista_precio.alm_lista_precio_id',
                    'alm_lista_precio.alm_lista_precio_nombre', 'alm_lista_precio_detalle.alm_lista_precio_detalle_precio')
                    ->where('alm_lista_precio_detalle.alm_lista_precio_detalle_articulo_id', $producto->alm_producto_id)
                    ->where('alm_lista_precio.alm_lista_precio_almacen_id', $request->input('almacen_id'))
                    ->where('alm_lista_precio.alm_lista_precio_fecha_vigencia_inicio', '<=', date('Y-m-d'))
                    ->where('alm_lista_precio.alm_lista_precio_fecha_vigencia_fin', '>=', date('Y-m-d'))
                    ->orderBy('alm_lista_precio_detalle.alm_lista_precio_detalle_precio', 'DESC')
                    ->leftJoin('alm_lista_precio', 'alm_lista_precio_detalle.alm_lista_precio_detalle_lista_precio_id', 'alm_lista_precio.alm_lista_precio_id')
                    ->get();
                $producto->producto_nombre = $producto->producto_nombre . ' (Stock ' . $producto->stock . ' ' . $producto->alm_unidad_medida_simbolo . ')';
            }
        }

        return response()->json(['success' => true,
            'data' => $producto,
            'message' => 'Lista de Articulos'], 200);
    }


    public function eliminar_producto(Request $request, $id)
    {
        Producto::find($id)->delete();
        $condicional_categoria = !is_null($request->input('alm_categoria_id')) ? '=' : '<>';
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(DB::table('alm_producto')
                ->select(DB::raw('alm_producto.alm_producto_nombre as categoria,
                b.alm_producto_id,
                b.alm_producto_nombre,
                b.alm_producto_codigo,
                b.alm_producto_controla_stock,
                b.alm_producto_marca,
                b.alm_producto_tipo_operacion,
                c.alm_unidad_medida_nombre'))
                ->join('alm_producto as b', 'alm_producto.alm_producto_id', '=', 'b.Parent_alm_producto_id')
                ->join('alm_unidad_medida as c', 'b.alm_unidad_medida_id', '=', 'c.alm_unidad_medida_id')
                ->where('b.Parent_alm_producto_id', $condicional_categoria, is_null($request->input('alm_categoria_id')) ? null : $request->input('alm_categoria_id'))
                ->get(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de Almacenes'], 200);
    }


    public function productos_stock(Request $request)

    {
        $lista_productos = Producto::producto_stock($request->input('articulo'),
            $request->input('alm_almacen_id'),
            $request->input('fecha'),
            $request->input('categoria_id'), $request->input('marca') );
        $total= new \stdClass();
        $total->stock=0;
        $total->costo=0;
        foreach ($lista_productos as $lista_producto){
            $total->stock=$total->stock+ $lista_producto->stock;
            $total->costo=$total->costo+ $lista_producto->costo;
        }
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru($lista_productos, $request->input('ver_por_pagina'), $request),
            'dataTotal'=>$total,
            'message' => 'Lista de Productos'], 200);
    }


    public function producto_kardex(Request $request)
    {
        $total = 0;
        $lista = array();
        $lista_kardex = Producto::producto_kardex($request->input('alm_almacen_id'),
            $request->input('fecha_inicio'),
            $request->input('fecha_fin'),
            $request->input('alm_producto_id'));
        foreach ($lista_kardex as $item) {

            if ($item->tipo == "INGRESO" && $item->estado == "REGISTRADO" || $item->descripcion == "SALDO ANTERIOR") {
                $total = $total + floatval($item->cantidad);
            } else if ($item->tipo == "SALIDA" && $item->estado == "REGISTRADO" || $item->descripcion == "SALDO ANTERIOR") {
                $total = $total - floatval($item->cantidad);
            }
            $item->cantidad = $total;
            array_push($lista, $item);
        }
        return response()->json(['success' => true,
            'data' => $lista,
            'message' => 'Lista de productos'], 200);

    }

    public function producto_kardex_todo(Request $request)
    {

        $lista = array();
        $productos = Producto::all();

        foreach ($productos as $producto) {
            $total = 0;
            $lista_kardex = Producto::producto_kardex($request->input('alm_almacen_id'),
                $request->input('fecha_inicio'),
                $request->input('fecha_fin'),
                $producto->alm_producto_id);
            foreach ($lista_kardex as $item) {
                if ($item->tipo == "INGRESO" && $item->estado == "REGISTRADO" || $item->descripcion == "SALDO ANTERIOR") {
                    $total = $total + floatval($item->cantidad);
                } else if ($item->tipo == "SALIDA" && $item->estado == "REGISTRADO" || $item->descripcion == "SALDO ANTERIOR") {
                    $total = $total - floatval($item->cantidad);
                }
                $item->cantidad = $total;
                array_push($lista, $item);
            }

        }

        return response()->json(['success' => true,
            'data' => $lista,
            'message' => 'Lista de productos'], 200);

    }


    public function generar_codigo_barras_qr(Request $request)
    {
        $lista = array();
        $data = new \stdClass();
        $data_producto = Producto::producto_imprimir_codigo_barras($request->input('alm_producto_id'))[0];
        for ($i = 1; $i <= $request->input('cantidad') / 4; $i++) {
            array_push($lista, $data_producto);
        }
        $data->detalle = $lista;
        $data->empresa = Empresa::all()->first();
        $data->producto = $data_producto->producto;
        if ($request->input('codigo') == "01") {
            $plantilla = 'productos_barras_cantidad';
        } else {
            $plantilla = 'productos_qr_cantidad';
        }

        return $this->generate_pdf_barras_qr_cantidad($data, $plantilla);
    }

    public function generate_pdf_barras_qr_cantidad($reporte_productos, $plantilla)
    {

        $data = $reporte_productos;
        $pdf = DOMPDF::loadView('pdf.' . $plantilla, compact('data'))->setPaper('a4');
        return $pdf->stream('reporte' . ' demo.pdf');

        //$data = $venta;
        // $pdf = DOMPDF::loadView('pdf.' . 'ventas', compact('data'));
        //return $pdf->stream('ventas' . '.pdf');
    }

    /*

       public function listar_kardex_general($almacen_id)
       {
           $lista = [];
           $cantidad_ultimo = 0;
           $precio_ultimo = 0.00;
           $codigo_producto = '';
           $contador = 0;
           $lista_kardex = Producto::listar_productos_kardex($almacen_id);

           foreach ($lista_kardex as $key => $kardex) {
               if ($contador == 0 && $codigo_producto != $kardex->codigo) {
                   if (substr($kardex->cantidad, 1, 1) == ' - ') {
                       $cantidad_ultimo = $cantidad_ultimo - $kardex->cantidad;
                   } else {
                       $cantidad_ultimo = $cantidad_ultimo + $kardex->cantidad;
                   }
                   if (substr($kardex->precio, 1, 1) == ' - ') {
                       $precio_ultimo = $precio_ultimo - $kardex->precio;
                   } else {
                       $precio_ultimo = $precio_ultimo + $kardex->precio;
                   }
                   $kardex->cantidad_total = $cantidad_ultimo;
                   $kardex->precio_total = $precio_ultimo;

                   array_push($lista, $kardex);
                   $codigo_producto = $kardex->codigo;
               } else if ($codigo_producto == $kardex->codigo) {
                   if (substr($kardex->cantidad, 1, 1) == ' - ') {
                       $cantidad_ultimo = $cantidad_ultimo - $kardex->cantidad;
                   } else {
                       $cantidad_ultimo = $cantidad_ultimo + $kardex->cantidad;
                   }
                   if (substr($kardex->precio, 1, 1) == ' - ') {
                       $precio_ultimo = $precio_ultimo - $kardex->precio;
                   } else {
                       $precio_ultimo = $precio_ultimo + $kardex->precio;
                   }
                   $kardex->cantidad_total = $cantidad_ultimo;
                   $kardex->precio_total = $precio_ultimo;

                   array_push($lista, $kardex);
                   $codigo_producto = $kardex->codigo;
               } else {
                   $cantidad_ultimo = 0;
                   $precio_ultimo = 0.00;
                   if (substr($kardex->cantidad, 1, 1) == ' - ') {
                       $cantidad_ultimo = $cantidad_ultimo - $kardex->cantidad;
                   } else {
                       $cantidad_ultimo = $cantidad_ultimo + $kardex->cantidad;
                   }
                   if (substr($kardex->precio, 1, 1) == ' - ') {
                       $precio_ultimo = $precio_ultimo - $kardex->precio;
                   } else {
                       $precio_ultimo = $precio_ultimo + $kardex->precio;
                   }
                   $kardex->cantidad_total = $cantidad_ultimo;
                   $kardex->precio_total = $precio_ultimo;
                   array_push($lista, $kardex);
                   $codigo_producto = $kardex->codigo;
               }
               $contador = $contador + 1;

           }
           $jResponse['success'] = true;
           $jResponse['data'] = $lista;
           return response()->json($jResponse, 200);

       }

       public function kardex_general_exel($almacen_id)
       {

           $lista = [];
           $cantidad_ultimo = 0;
           $precio_ultimo = 0.00;
           $codigo_producto = '';
           $contador = 0;
           $lista_kardex = Producto::listar_productos_kardex($almacen_id);

           foreach ($lista_kardex as $key => $kardex) {
               if ($contador == 0 && $codigo_producto != $kardex->codigo) {
                   if (substr($kardex->cantidad, 1, 1) == ' - ') {
                       $cantidad_ultimo = $cantidad_ultimo - $kardex->cantidad;
                   } else {
                       $cantidad_ultimo = $cantidad_ultimo + $kardex->cantidad;
                   }
                   if (substr($kardex->precio, 1, 1) == ' - ') {
                       $precio_ultimo = $precio_ultimo - $kardex->precio;
                   } else {
                       $precio_ultimo = $precio_ultimo + $kardex->precio;
                   }
                   $kardex->cantidad_total = $cantidad_ultimo;
                   $kardex->precio_total = $precio_ultimo;

                   array_push($lista, $kardex);
                   $codigo_producto = $kardex->codigo;
               } else if ($codigo_producto == $kardex->codigo) {
                   if (substr($kardex->cantidad, 1, 1) == ' - ') {
                       $cantidad_ultimo = $cantidad_ultimo - $kardex->cantidad;
                   } else {
                       $cantidad_ultimo = $cantidad_ultimo + $kardex->cantidad;
                   }
                   if (substr($kardex->precio, 1, 1) == ' - ') {
                       $precio_ultimo = $precio_ultimo - $kardex->precio;
                   } else {
                       $precio_ultimo = $precio_ultimo + $kardex->precio;
                   }
                   $kardex->cantidad_total = $cantidad_ultimo;
                   $kardex->precio_total = $precio_ultimo;

                   array_push($lista, $kardex);
                   $codigo_producto = $kardex->codigo;
               } else {
                   $cantidad_ultimo = 0;
                   $precio_ultimo = 0.00;
                   if (substr($kardex->cantidad, 1, 1) == ' - ') {
                       $cantidad_ultimo = $cantidad_ultimo - $kardex->cantidad;
                   } else {
                       $cantidad_ultimo = $cantidad_ultimo + $kardex->cantidad;
                   }
                   if (substr($kardex->precio, 1, 1) == ' - ') {
                       $precio_ultimo = $precio_ultimo - $kardex->precio;
                   } else {
                       $precio_ultimo = $precio_ultimo + $kardex->precio;
                   }
                   $kardex->cantidad_total = $cantidad_ultimo;
                   $kardex->precio_total = $precio_ultimo;
                   array_push($lista, $kardex);
                   $codigo_producto = $kardex->codigo;
               }
               $contador = $contador + 1;

           }
           foreach ($lista as $list) {
               $data[] = array(
                   "Fecha" => $list->fecha,
                   "Usuario" => $list->usuario,
                   "Documento" => $list->documento,
                   "Categoria" => $list->categoria,
                   "Articulo" => $list->articulo,
                   "Código Artículo" => $list->articulo,
                   "Cantidad" => $list->cantidad,
                   "Precio Unitario" => $list->pu,
                   "Precio" => $list->precio,
                   "Cantidad Total" => $list->cantidad_total,
                   "Precio Total" => $list->precio_total,
               );
           }
           return Excel::create('ventas_usuario', function ($excel) use ($data) {
               $excel->sheet('Sheet', function ($sheet) use ($data) {
                   $sheet->cells('A1:N1', function ($cells) {
                       $cells->setBackground('#204694');
                       $cells->setFontColor('#ffffff');
                       $cells->setFontFamily('roboto');
                       $cells->setFontSize(12);
                       $cells->setFontWeight('bold');
                   });
                   $sheet->fromArray($data);
               });
           })->
           export('xls');
       }*/


    public function producto_series_almacen(Request $request)
    {
        return response()->json(['success' => true,
            'data' => Producto::producto_series_almacen($request->input('alm_producto_id'), $request->input('alm_almacen_id')),
            'message' => 'Lista de productos'], 200);
    }

    public function importar_importacion(Request $request)
    {

        if ($request->hasFile('importar_archivo')) {

            Excel::load($request->file('importar_archivo')->getRealPath(), function ($reader) {
                $conatdor = 1;
                $compra = new Compra();
                $idcompra = IdGenerador::generaId();
                $compra->comp_compra_id = $idcompra;
                $compra->comp_compra_confirmado = false;
                $compra->comp_compra_serie = 'F001';
                $compra->comp_compra_estado = 'REGISTRADO';
                $compra->comp_compra_numero_venta = GeneraNumero::genera_numero_movimiento($compra->comp_compra_serie);
                $compra->comp_compra_estado_pago = false;
                $compra->comp_compra_tipo = 'C';
                $compra->comp_compra_fecha = date('Y-m-d');
                $compra->comp_compra_fecha_registro = date('Y-m-d H:i:s', strtotime("now"));
                $compra->comp_compra_tipo_comprobante_id = TipoComprobante::where('doc_tipo_comprobante_codigo', '01')->first()->doc_tipo_comprobante_id;
                $compra->comp_compra_numero_venta = GeneraNumero::genera_numero_movimiento($compra->comp_compra_serie);
                $compra->save();
                foreach ($reader->toArray() as $key => $row) {
                    $data_exel = $row;
                    $categoria_data = $data_exel['type'];
                    $id_categoria = IdGenerador::generaId();
                    $codigo_producto = str_replace(' ', '', $data_exel['reference'] . '-' . $data_exel['type'] . $data_exel['color'] . $data_exel['upper'] . $data_exel['tienda']);
                    try {
                        if (!empty($categoria_data)) {
                            if (!Producto::where('alm_producto_codigo', $categoria_data)->where('alm_producto_nivel', '1')->exists()) {
                                $categoria = new Producto();
                                $categoria->alm_producto_id = $id_categoria;
                                $categoria->alm_producto_nombre = $categoria_data;
                                $categoria->alm_producto_nivel = '1';
                                $categoria->alm_producto_codigo = $categoria_data;
                                $categoria->alm_producto_estado = 1;
                                $categoria->save();
                            } else {
                                $id_categoria = Producto::select('alm_producto_id')->where('alm_producto_codigo', $data_exel['type'])
                                    ->where('alm_producto_nivel', '1')
                                    ->first()->alm_producto_id;;
                            }
                        }
                        if (!Producto::where('alm_producto_codigo', $codigo_producto)->where('alm_producto_nivel', '2')->exists()) {
                            $producto = new Producto();
                            $alm_producto_id = IdGenerador::generaId();
                            $producto->alm_producto_id = $alm_producto_id;
                            $producto->alm_producto_nombre = $data_exel['color'] . ' ' . $data_exel['upper'];
                            $producto->alm_producto_nivel = '2';
                            $producto->alm_producto_codigo = $codigo_producto;
                            $producto->alm_producto_estado = 1;
                            $producto->Parent_alm_producto_id = $id_categoria;
                            $producto->alm_producto_marca = $data_exel['marca'];
                            $producto->alm_unidad_medida_id = 'NIU';
                            $producto->alm_producto_tipo_operacion = '10';
                            // $producto->alm_producto_marca = $data_exel['marca'];
                            $producto->alm_producto_controla_stock = 1;
                            $producto->alm_producto_serie = 0;
                            $producto->alm_producto_cuenta_compra = '6011';
                            $producto->alm_producto_cuenta_venta = '7011';
                            $producto->alm_producto_descripcion = '';

                            // $producto->alm_producto_foto = $data_exel['urlimagen'] === null || !empty($data_exel['motor']) ? $data_exel['urlimagen'] : "";
                            // $producto->alm_producto_modelo = $conatdor;
                            // $producto->alm_producto_color = $data_exel['color'] === null ? "" : $data_exel['color'];
                            // $producto->alm_producto_motor = !empty($data_exel['motor']) || $data_exel['motor'] === null ? "" : $data_exel['motor'];
                            // $producto->alm_producto_chasis = !empty($data_exel['chasis']) || $data_exel['chasis'] === null ? "" : $data_exel['chasis'];
                            // $producto->alm_producto_dua = !empty($data_exel['dua']) || $data_exel['dua'] === null ? "" : $data_exel['dua'];
                            // $producto->alm_producto_item = !empty($data_exel['item']) || $data_exel['item'] === null ? "" : $data_exel['item'];
                            // $producto->alm_producto_vehiculo = !empty($data_exel['chasis']) || $data_exel['chasis'] === null ? 0 : 1;
                            if (!empty($producto->alm_producto_codigo) && $producto->alm_producto_codigo !== "-") {
                                $producto->save();
                            }
                            /*if (!empty($data_exel['precioventa']) || $data_exel['precioventa'] === null) {
                                $lista_precio_detalle = new ListaPrecioDetalle();
                                $lista_precio_detalle->alm_lista_precio_detalle_id = IdGenerador::generaId();
                                $lista_precio_detalle->alm_lista_precio_detalle_lista_precio_id = 'MA1583880589264896';
                                $lista_precio_detalle->alm_lista_precio_detalle_articulo_id = $alm_producto_id;
                                $empresa = Empresa::all()->first();
                                if ($empresa->emp_empresa_calculo_total) {
                                    $lista_precio_detalle->alm_lista_precio_detalle_precio = $data_exel['precioventa'];
                                } else {
                                    $lista_precio_detalle->alm_lista_precio_detalle_precio = $data_exel['precioventa'] / ((Periodo::select('cont_periodo_igv')->where('cont_periodo_estado', true)->first()->cont_periodo_igv / 100) + 1);
                                }

                                $lista_precio_detalle->save();


                            }*/


                        }


                    } catch (\Exception $exception) {
                        dd($producto);
                        dd($exception->getMessage());
                        /*if (!empty($producto->alm_producto_codigo) && $producto->alm_producto_codigo !== "") {
                            dd(empty($producto->alm_producto_nombre));
                        }*/
                    }
                    if ($codigo_producto !== "-") {
                        $producto_data = Producto::where('alm_producto_codigo', $codigo_producto)->first();
                        $precio_unitario = (($data_exel['unitprice'] + $data_exel['flete']) + (($data_exel['unitprice'] + $data_exel['flete']) * $data_exel['porcentajeutilidad']) / 100) * $data_exel['cambio'];
                        $compra_detalle = new CompraDetalle();
                        $compra_detalle->comp_compra_detalle_precio_unitario = $precio_unitario;
                        $compra_detalle->comp_compra_detalle_precio = $precio_unitario * $data_exel['pairs'];
                        $compra_detalle->comp_compra_detalle_igv = ($precio_unitario * $data_exel['pairs']) - (($precio_unitario * $data_exel['pairs']) / 1.18);
                        $compra_detalle->comp_compra_detalle_bi = ($precio_unitario * $data_exel['pairs']) / 1.18;
                        $compra_detalle->comp_compra_detalle_cantidad = $data_exel['pairs'];
                        $compra_detalle->comp_compra_detalle_tipo_cambio = $data_exel['cambio'];
                        $compra_detalle->comp_compra_detalle_id = IdGenerador::generaId();
                        $compra_detalle->comp_compra_compra_id = $idcompra;
                        $compra_detalle->comp_compra_detalle_producto_id = $producto_data->alm_producto_id;
                        $compra_detalle->comp_compra_detalle_precio_dolar = $data_exel['unitprice'];
                        $compra_detalle->comp_compra_detalle_flete = $data_exel['flete'];
                        $compra_detalle->comp_compra_detalle_item = $conatdor;
                        $compra_detalle->comp_compra_detalle_cuenta_compra = '6011';
                        $compra_detalle->comp_compra_detalle_serie_estado = 0;
                        $compra_detalle->comp_compra_detalle_tipo_operacion = '10';
                        $compra_detalle->comp_compra_detalle_costo_real_unitario = $data_exel['costoreal'];
                        $compra_detalle->comp_compra_detalle_producto = $data_exel['type'] . ' ' . $producto_data->alm_producto_nombre;
                        $compra_detalle->comp_compra_detalle_fecha_registro = date('Y-m-d H:i:s', strtotime("now"));
                        $compra_detalle->save();
                    }

                    /*$empresa = Empresa::all()->first();
                    if (!$empresa->emp_empresa_calculo_total) {
                        $compra_detalle->comp_compra_detalle_precio_unitario = $compra_detalle->comp_compra_detalle_precio_unitario / ((Periodo::select('cont_periodo_igv')->where('cont_periodo_estado', true)->first()->cont_periodo_igv / 100) + 1);
                    }
                    ListaPrecioDetalle::where('alm_lista_precio_detalle_articulo_id', $compra_detalle->comp_compra_detalle_producto_id)->first();
                    if (ListaPrecioDetalle::where('alm_lista_precio_detalle_articulo_id', $compra_detalle->comp_compra_detalle_producto_id)->first()) {
                        ListaPrecioDetalle::where('alm_lista_precio_detalle_articulo_id', $compra_detalle->comp_compra_detalle_producto_id)
                            ->where('alm_lista_precio_detalle_lista_precio_id', ListaPrecio::all()->first()->alm_lista_precio_id)->update(array('alm_lista_precio_detalle_precio' => $compra_detalle->comp_compra_detalle_precio_unitario));
                    } else {
                        $listaPrecioDetalle = new  ListaPrecioDetalle();
                        $listaPrecioDetalle->alm_lista_precio_detalle_id = IdGenerador::generaId();
                        $listaPrecioDetalle->alm_lista_precio_detalle_lista_precio_id = ListaPrecio::all()->first()->alm_lista_precio_id;
                        $listaPrecioDetalle->alm_lista_precio_detalle_articulo_id = $compra_detalle->comp_compra_detalle_producto_id;
                        $listaPrecioDetalle->alm_lista_precio_detalle_precio = $compra_detalle->comp_compra_detalle_precio_unitario;
                        $listaPrecioDetalle->save();
                    }*/


                    $conatdor = $conatdor + 1;
                    /* 'comp_compra_detalle_precio',
         'comp_compra_detalle_precio_unitario',
         'comp_compra_detalle_cantidad',
         'comp_compra_detalle_igv',
         'comp_compra_detalle_bi',

         'comp_compra_detalle_producto_id',
         'comp_compra_detalle_tipo_operacion',
         'comp_compra_detalle_cuenta_compra',
         'comp_compra_detalle_producto',
         'comp_compra_detalle_serie',
         'comp_compra_detalle_vendido',
         'comp_compra_detalle_serie_estado',
         'comp_compra_detalle_item',
         'comp_compra_detalle_fecha_registro',
         'comp_compra_detalle_precio_dolar',
         'comp_compra_detalle_flete',
         'comp_compra_detalle_tipo_cambio'*/

                }
            });
        }
        return response()->json(['success' => true,
            'data' => 'Ok',
            'message' => 'Registro de compras'], 200);
    }

    public function listar_gararantias_por_serie(Request $request)
    {


        return response()->json(['success' => true,
            'data' => count(Producto::producto_consulta_garantia_por_serie($request->input('serie'))) > 0 ? Producto::producto_consulta_garantia_por_serie($request->input('serie'))[0] : new \stdClass(),
            'message' => 'Registro de compras'], 200);
    }


    public function listar_stock_por_almacenes(Request $request)
    {
        $lista = [];
        if (!is_null($request->input('fecha'))) {
            $condicional_fecha = $request->input('fecha');
        } else {
            $condicional_fecha = date('Y-m-d');
        }
        $lista_productos = Producto::producto_stock_por_almacenes($request->input('articulo'), 'PR15469593836888181', '158197244214786052','160623021155453403', $condicional_fecha, $request->input('categoria_id'));
        foreach ($lista_productos as $producto) {
            $producto->stock_total = $producto->stock_almacen1 + $producto->stock_almacen2+$producto->stock_almacen3;
            array_push($lista, $producto);
        }

        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru($lista, $request->input('ver_por_pagina'), $request),
            'message' => 'Lista Productos'], 200);
    }


    public function listar_marcas(Request $request)
    {
        return response()->json(['success' => true,
            'data' => Producto::producto_marcas(),
            'message' => 'Lista Marcas'], 200);
    }
}
