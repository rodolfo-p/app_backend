<?php


namespace App\Http\Controllers\Ventas;


use App\Models\Configuracion\TipoComprobante;
use App\Models\Venta\FlujoSerie;
use App\Models\Venta\UsuarioSerie;
use Illuminate\Routing\Controller;

class VentUsuarioSerieController extends Controller
{
    public function listar_usuario_serie()
    {
        $lista_series = array();
        $lista = array();

        foreach (UsuarioSerie::where('vent_usuario_user_id', auth()->user()->id)->get() as $item) {
            $item->series = FlujoSerie::select('*')
                ->where('vent_flujo_serie.vent_num_cajero', $item->vent_num_cajero)
                ->leftJoin('doc_tipo_comprabante', 'vent_flujo_serie.vent_tipo_comprobante_id', 'doc_tipo_comprabante.doc_tipo_comprobante_id')
                ->get();
            array_push($lista_series, $item->series);
        }
        $dato = $lista_series[0];
        $usuario_serie = new \stdClass();
        $tipo_comprobante = TipoComprobante::where('doc_tipo_comprobante_codigo', '99')->first();
        $usuario_serie->vent_serie = Date('Y') . '-' . $dato[0]->vent_num_cajero;
        $usuario_serie->vent_tipo_comprobante_id = $tipo_comprobante->doc_tipo_comprobante_id;
        $usuario_serie->vent_numero = "0";
        $usuario_serie->vent_num_cajero = $dato[0]->vent_num_cajero;
        $usuario_serie->vent_almacen_id = $dato[0]->vent_almacen_id;
        $usuario_serie->doc_tipo_comprobante_id = $tipo_comprobante->doc_tipo_comprobante_id;
        $usuario_serie->doc_tipo_comprobante_nombre = $tipo_comprobante->doc_tipo_comprobante_nombre;
        $usuario_serie->doc_tipo_comprobante_codigo = $tipo_comprobante->doc_tipo_comprobante_codigo;
        array_push($lista, $usuario_serie);

        $usuario_serie_profroma = new \stdClass();
        $tipo_comprobante = TipoComprobante::where('doc_tipo_comprobante_codigo', '88')->first();
        $usuario_serie_profroma->vent_serie = 'P-'.Date('Y') . '-' . $dato[0]->vent_num_cajero;
        $usuario_serie_profroma->vent_tipo_comprobante_id = $tipo_comprobante->doc_tipo_comprobante_id;
        $usuario_serie_profroma->vent_numero = "0";
        $usuario_serie_profroma->vent_num_cajero = $dato[0]->vent_num_cajero;
        $usuario_serie_profroma->vent_almacen_id = $dato[0]->vent_almacen_id;
        $usuario_serie_profroma->doc_tipo_comprobante_id = $tipo_comprobante->doc_tipo_comprobante_id;
        $usuario_serie_profroma->doc_tipo_comprobante_nombre = $tipo_comprobante->doc_tipo_comprobante_nombre;
        $usuario_serie_profroma->doc_tipo_comprobante_codigo = $tipo_comprobante->doc_tipo_comprobante_codigo;
        array_push($lista, $usuario_serie_profroma);
        return response()->json(['success' => true,
            'data' => array_merge($lista, $item->series->toArray()),
            'message' => 'Lista de Almacenes'], 200);

    }
}
