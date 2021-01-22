<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 06/11/18
 * Time: 06:06 PM
 */

namespace App\Http\Controllers;

namespace App\Http\Controllers\Almacen;


use App\Http\Controllers\Controller;
use App\Http\Data\util\ConsultaRucDni;


use App\Http\Data\util\IdGenerador;
use App\Http\Data\util\Pagination;
use App\Models\Almacen\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class AlmProveedorController extends Controller
{
    public function listar_proveedores(Request $request)
    {
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Proveedor::all(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de Proveedores'], 200);

    }

    public function listar_proveedor($id)
    {
        return response()->json(['success' => true,
            'data' => Proveedor::find($id),
            'message' => 'Lista de Proveedor'], 200);

    }

    public function buscar_proveedor_por_ruc($ruc)
    {
        $proveedor_data = new Proveedor();
        $response_status = true;
        if (Proveedor::where('alm_proveedor_ruc', $ruc)->exists()) {
            $proveedor_data = Proveedor::where('alm_proveedor_ruc', $ruc)->first();
        } else {
            $proveedor = new Proveedor();

            if (strlen($ruc) == 8) {
                $proveedor_ruc = ConsultaRucDni::dni($ruc);
                $data = json_decode($proveedor_ruc);
                if ($data->success) {
                    $alm_proveedor_id = IdGenerador::generaId();
                    $proveedor->alm_proveedor_id = $alm_proveedor_id;
                    $proveedor->alm_proveedor_razon_social = $data->data->nombres . ' '  .$data->data->apellido_paterno . ' ' . $data->data->apellido_materno;
                    $proveedor->alm_proveedor_ruc = $data->data->dni;
                    $proveedor->save();
                    $proveedor_data = Proveedor::find($alm_proveedor_id);
                } else {
                    $response_status = false;
                }
            } elseif (ConsultaRucDni::ruc($ruc) > 8) {
                $proveedor_ruc = ConsultaRucDni::ruc($ruc);
                $data = json_decode($proveedor_ruc);
                if ($data->success) {
                    $alm_proveedor_id = IdGenerador::generaId();
                    $proveedor->alm_proveedor_id = $alm_proveedor_id;
                    $proveedor->alm_proveedor_razon_social = $data->data->razon_social;
                    $proveedor->alm_proveedor_ruc = $data->data->ruc;
                    $proveedor->alm_proveedor_tipo_contribuyente = null;
                    $proveedor->alm_proveedor_nombre_comercial = $data->data->nombre_comercial;
                    $proveedor->alm_proveedor_fecha_inscripcion = null;
                    $proveedor->alm_proveedor_fecha_inicio_actividades = null;
                    $proveedor->alm_proveedor_estado_contribuyente = $data->data->estado;
                    $proveedor->alm_proveedor_condicion_contribuyente = $data->data->condicion;
                    $proveedor->alm_proveedor_direccion = $data->data->direccion;
                    $proveedor->alm_proveedor_sistema_emicion_comprobante = $data->data->sistema_emision;
                    $proveedor->alm_proveedor_actividad_comercio_exterior = $data->data->actividad_exterior;
                    $proveedor->alm_proveedor_sistema_contabilidad = $data->data->sistema_contabilidad;
                    $proveedor->save();
                    $proveedor_data = Proveedor::find($alm_proveedor_id);
                } else {
                    $response_status = false;
                }
            }


        }
        return response()->json(['success' => $response_status,
            'data' => $proveedor_data,
            'message' => 'Proveedor'], 200);
    }


    public function registrar_proveedor(Request $request)
    {
        request()->validate([
            'alm_proveedor_razon_social' => 'required',
            'alm_proveedor_ruc' => 'required',
        ]);
        $proveedor = new Proveedor($request->all());
        $proveedor->alm_proveedor_id = IdGenerador::generaId();
        $proveedor->save();
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Proveedor::all(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de Proveedores'], 200);

    }


    public function actualizar_proveedor(Request $request, $id)
    {
        Proveedor::find($id)->update($request->all());
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Proveedor::all(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de Proveedores'], 200);
    }


    public function eliminar_proveedor(Request $request, $id)
    {
        Proveedor::find($id)->delete();
        return response()->json(['success' => true,
            'data' => Pagination::paginatorFacturaPeru(Proveedor::all(), $request->input('ver_por_pagina'), $request),
            'message' => 'Lista de Proveedores'], 200);
    }


    public function listar_proveedor_autocomplete(Request $request)
    {
        $provedores = [];
        if (!is_null($data = $request->input('dato')) && strlen($request->input('dato')) > 2) {
            $provedores = Proveedor::select(DB::raw(' alm_proveedor_id,
       alm_proveedor_ruc,
       alm_proveedor_razon_social,
       concat(alm_proveedor_razon_social," (",alm_proveedor_ruc,")") as proveedor'))
                ->where(DB::raw('CONCAT(alm_proveedor_razon_social, "  ", alm_proveedor_ruc)'), 'like', '%' . $data . '%')
                ->get();
            if (count($provedores) == 0) {
                $consulta = json_decode(ConsultaRucDni::ruc($data));
                if ($consulta->success) {
                    Proveedor::create(array('alm_proveedor_ruc' => $consulta->data->ruc,
                        'alm_proveedor_razon_social' => $consulta->data->razon_social,
                        'alm_proveedor_importacion' => false,
                        'alm_proveedor_direccion' => $consulta->data->direccion,
                        'alm_proveedor_estado_contribuyente' => $consulta->data->estado,
                        'alm_proveedor_condicion_contribuyente' => $consulta->data->condicion,
                        'alm_proveedor_id' => IdGenerador::generaId(),
                    ));
                    $provedores = Proveedor::select(DB::raw(' alm_proveedor_id,
       alm_proveedor_ruc,
       alm_proveedor_razon_social,
       concat(alm_proveedor_razon_social," (",alm_proveedor_ruc,")") as proveedor'))
                        ->where(DB::raw('CONCAT(alm_proveedor_razon_social, "  ", alm_proveedor_ruc)'), 'like', '%' . $data . '%')
                        ->get();
                    return response()->json(
                        ['success' => true,
                            'data' => $provedores,
                            'message' => 'Operacion Correcta'],
                        200);
                }

            } else {
                return response()->json(
                    ['success' => true,
                        'data' => $provedores,
                        'message' => 'Intenta buscar por Ruc'],
                    200);
            }

        }

        return response()->json(
            ['success' => true,
                'data' => $provedores,
                'message' => 'Operacion Correcta'],
            200);
    }

}

