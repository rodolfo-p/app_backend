<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 12/02/19
 * Time: 05:16 PM
 */

namespace App\Http\Controllers\Compras;


use App\Http\Controllers\Controller;
use App\Http\Data\util\GeneraNumero;
use App\Http\Data\util\IdGenerador;
use App\Models\Almacen\Compra;
use App\Models\Almacen\PagoProveedores;
use App\Models\Configuracion\Periodo;
use Illuminate\Http\Request;

class CompPagoProveedoresController extends Controller
{
    public function listar_compras_por_pagar()
    {
        return response()->json(['success' => true,
            'data' => Compra::listar_compras_por_pagar(),
            'message' => 'Registro de compras por pagar'], 200);
    }


    public function listar_compra_por_pagar($id)
    {
        $compra = Compra::listar_compra_por_pagar($id)[0];
        $compra->pagos = PagoProveedores::where('comp_pago_proveedores_id_compra', $compra->comp_compra_id)->get();
        return response()->json(['success' => true,
            'data' => $compra,
            'message' => 'Registro de compras por pagar'], 200);
    }

    public function registrar_pago_proveedor(Request $request)
    {
        if (!is_null($request->input('comp_pago_importe')) && $request->input('comp_pago_importe') != "" && $request->input('comp_pago_importe') > 0) {
            $pago_proveedor = new PagoProveedores();
            $pago_proveedor->comp_pago_proveedores_id = IdGenerador::generaId();
            $pago_proveedor->comp_pago_proveedores_importe = $request->input('comp_pago_importe');
            $pago_proveedor->comp_pago_proveedores_fecha = date('Y-m-d');
            $pago_proveedor->comp_pago_proveedores_serie = 'PP01';
            $pago_proveedor->comp_pago_proveedores_numero_pago = GeneraNumero::genera_numero_pago_proveedor('PP01');
            $pago_proveedor->comp_pago_proveedores_tipo_pago = '02';
            $pago_proveedor->comp_pago_proveedores_id_compra = $request->input('comp_compra_id');
            $pago_proveedor->comp_pago_proveedores_id_user = auth()->user()->id;
            $pago_proveedor->comp_pago_proveedores_vuelto = 0.00;
            $pago_proveedor->comp_pago_proveedores_pago = $request->input('comp_pago_importe');
            $pago_proveedor->comp_pago_proveedores_periodo_id = Periodo::where('cont_periodo_estado', true)->first()->cont_periodo_id;
            $pago_proveedor->save();
            $compra_data = new Compra();
            $compra_data->comp_compra_estado_pago = $request->input('comp_pago_importe') >= $request->input('saldo') ? true : false;
            Compra::find($request->input('comp_compra_id'))->update($compra_data->toArray());

        }


        return response()->json(['success' => true,
            'data' => Compra::listar_compras_por_pagar(),
            'message' => 'Registro de compras por pagar'], 200);
    }
}
