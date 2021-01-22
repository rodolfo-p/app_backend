<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 14/11/18
 * Time: 06:10 PM
 */

namespace App\Http\Controllers;

namespace App\Http\Controllers\Ventas;

use App\Http\Data\util\ConsultaRucDni;
use App\Http\Data\util\IdGenerador;
use App\Http\Data\util\Pagination;
use App\Models\Venta\Cliente;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Http\Request;
use PDF;


//require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/../../../../vendor/autoload.php'; // Autoload files using Composer autoload
use NumeroALetras\NumeroALetras;

class VentClienteController extends Controller
{
    public function listar_cliente_autocomplete(Request $request)
    {
        $clientes = [];
        if (!is_null($data = $request->input('dato')) && strlen($request->input('dato')) > 2) {
            $clientes = Cliente::select(DB::raw(' seg_cliente_id,
       seg_cliente_numero_doc,
       seg_cliente_razon_social,
       concat(seg_cliente_razon_social," (",seg_cliente_numero_doc,")") as cliente'))
                ->where(DB::raw('CONCAT(seg_cliente_razon_social, " ", seg_cliente_numero_doc)'), 'like', '%' . $data . '%')
                ->get();
            if (count($clientes) == 0 && strlen($request->input('dato')) == 11) {
                $consulta = json_decode(ConsultaRucDni::ruc($data));
                if ($consulta->success) {
                    Cliente::create(array('seg_cliente_numero_doc' => $consulta->data->ruc,
                        'seg_cliente_razon_social' => $consulta->data->razon_social,
                        'seg_cliente_direccion' => $consulta->data->direccion,
                        'seg_cliente_estado' => true,
                        'seg_cliente_tipo_documento' => 6,
                        'seg_cliente_id' => IdGenerador::generaId(),
                    ));
                    $clientes = Cliente::select(DB::raw(' seg_cliente_id,
       seg_cliente_numero_doc,
       seg_cliente_razon_social,
       concat(seg_cliente_razon_social," (",seg_cliente_numero_doc,")") as cliente'))
                        ->where(DB::raw('CONCAT(seg_cliente_razon_social, " ", seg_cliente_numero_doc)'), 'like', '%' . $data . '%')
                        ->get();
                    return response()->json(
                        ['success' => true,
                            'data' => $clientes,
                            'message' => 'Operacion Correcta'],
                        200);
                }

            } elseif (count($clientes) == 0 && strlen($request->input('dato')) == 8) {
                $consulta = json_decode(ConsultaRucDni::dni($data));
                if ($consulta->success) {
                    Cliente::create(array('seg_cliente_numero_doc' => $consulta->data->dni,
                        'seg_cliente_razon_social' => $consulta->data->nombres . ' ' . $consulta->data->apellido_paterno . ' ' . $consulta->data->apellido_materno,
                        'seg_cliente_estado' => true,
                        'seg_cliente_nombres' => $consulta->data->nombres,
                        'seg_cliente_apellido_paterno' => $consulta->data->apellido_paterno,
                        'seg_cliente_apellido_materno' => $consulta->data->apellido_materno,
                        'seg_cliente_tipo_documento' => 1,
                        'seg_cliente_id' => IdGenerador::generaId(),
                    ));
                    $clientes = Cliente::select(DB::raw(' seg_cliente_id,
       seg_cliente_numero_doc,
       seg_cliente_razon_social,
       concat(seg_cliente_razon_social," (",seg_cliente_numero_doc,")") as cliente'))
                        ->where(DB::raw('CONCAT(seg_cliente_razon_social, " ", seg_cliente_numero_doc)'), 'like', '%' . $data . '%')
                        ->get();
                    return response()->json(
                        ['success' => true,
                            'data' => $clientes,
                            'message' => 'Operacion Correcta'],
                        200);
                }
            } else {
                return response()->json(
                    ['success' => true,
                        'data' => $clientes,
                        'message' => 'Intenta buscar por Ruc'],
                    200);
            }

        }

        return response()->json(
            ['success' => true,
                'data' => $clientes,
                'message' => 'Operacion Correcta'],
            200);
    }


    public function listar_clientes(Request $request)
    {
        $dato = $request->input('dato');
        $condicional_dato = !is_null($request->input('dato')) || $request->input('dato') != "" ? 'like' : '<>';
        $condicional_parametro = !is_null($request->input('dato')) || $request->input('dato') != "" ? '%' . $dato . '%' : null;
        $cliente = Cliente::select('*')
            ->where(DB::raw('CONCAT(seg_cliente_razon_social, " ", seg_cliente_numero_doc)'), $condicional_dato, $condicional_parametro)
            ->get();
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru($cliente, $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de Clientes'], 200);


    }

    public function buscar_cliente($numero_documento)
    {
        $cliente = new \stdClass();
        $mensaje = "Operacion Correcta";
        if (strlen($numero_documento) == 8) {
            $data = json_decode(ConsultaRucDni::dni($numero_documento));
            if ($data->success) {
                $persona = new \stdClass();
                $persona->nombres = $data->data->nombres;
                $persona->apellido_paterno = $data->data->apellido_paterno;
                $persona->apellido_materno = $data->data->apellido_materno;
                $cliente->nombre = $persona;
                $cliente->numero = $data->data->dni;
            } else {
                $mensaje = "DNI no Encontrado";
            }

        } elseif (strlen($numero_documento) == 11) {
            $data = json_decode(ConsultaRucDni::ruc($numero_documento));
            if ($data->success) {
                $cliente->nombre = $data->data->razon_social;
                $cliente->numero = $data->data->ruc;
            } else {
                $mensaje = "RUC no Encontrado";
            }

        }
        return response()->json(['success' => true,
            'data' => $cliente,
            'message' => $mensaje], 200);
    }

    public function registrar_cliente(Request $request)
    {
        $cliente = new Cliente($request->all());
        $cliente->seg_cliente_id = IdGenerador::generaId();
        $cliente->seg_cliente_razon_social = is_null($request->input('seg_cliente_razon_social')) ?
            $request->input('seg_cliente_nombres')
            . ' ' . $request->input('seg_cliente_apellido_paterno')
            . ' ' . $request->input('seg_cliente_apellido_materno') : $request->input('seg_cliente_razon_social');
        $cliente->save();
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Cliente::all(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de Clientes'], 200);


    }

    public function listar_cliente($id)
    {
        return response()->json(['success' => true,
            'data' => Cliente::find($id),
            'message' => 'Lista de Clientes'], 200);
    }

    public function actualizar_cliente(Request $request, $id)
    {

        Cliente::find($id)->update($request->all());
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Cliente::all(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de Clientes'], 200);


    }

    public function eliminar_cliente(Request $request, $id)
    {
        Cliente::find($id)->delete();
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Cliente::all(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de Clientes'], 200);

    }
}

