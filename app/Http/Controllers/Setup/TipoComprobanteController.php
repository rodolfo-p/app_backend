<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 19/11/18
 * Time: 05:50 PM
 */

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;

use App\Http\Data\util\IdGenerador;
use App\Models\Configuracion\TipoComprobante;
use Illuminate\Http\Request;
use Exception;

class TipoComprobanteController
{
    public function listar_tipo_comprobantes()
    {
        return response()->json(['success' => true,
            'data' => TipoComprobante::all(),
            'message' => 'Lista de tipo de comprobante'], 200);
    }

    public function listar_tipo_comprobante($id)
    {
        return response()->json(['success' => true,
            'data' => TipoComprobante::find($id),
            'message' => 'Lista de tipo de comprobante'], 200);
    }

    public function insertar_tipo_comprovante(Request $request)
    {
        request()->validate([
            'doc_tipo_comprobante_nombre' => 'required',
            'doc_tipo_comprobante_codigo' => 'required',
        ]);
        $tipo_comprobante = new TipoComprobante($request->all());
        $tipo_comprobante->doc_tipo_comprobante_id = IdGenerador::generaId();
        $tipo_comprobante->save();
        return response()->json(['success' => true,
            'data' => TipoComprobante::all(),
            'message' => 'Lista de tipo de comprobante'], 200);
    }

    public function eliminar_tipo_comprobante($id)

    {
        TipoComprobante::find($id)->delete();
        return response()->json(['success' => true,
            'data' => TipoComprobante::all(),
            'message' => 'Lista de tipo de comprobante'], 200);
    }

    public function actualizar_tipo_comprobante(Request $request, $id)
    {
        request()->validate([
            'doc_tipo_comprobante_nombre' => 'required',
            'doc_tipo_comprobante_codigo' => 'required',
        ]);
        TipoComprobante::find($id)->update($request->all());
        return response()->json(['success' => true,
            'data' => TipoComprobante::all(),
            'message' => 'Lista de tipo de comprobante'], 200);

    }
}
