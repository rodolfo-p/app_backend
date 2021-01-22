<?php


namespace App\Exports;

use Illuminate\Contracts\View\View;


class VentasExport
{  private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    public function view(): View
    {
        $ventas = array();
  /*      foreach (Venta::where('vent_venta.vent_venta_serie', 'like', 'B' . '%')
                     ->orWhere('vent_venta.vent_venta_serie', 'like', 'F' . '%')->get() as $venta) {
            $venta->tipo_comprobante = TipoComprobante::find($venta->vent_venta_tipo_documento_id)->cont_tipo_comprobante_codigo;
            $venta->tipo_documento_identidad = Cliente::find($venta->vent_venta_cliente_id)->cliente_cliente_tipo_doc_identidad;
            array_push($ventas, $venta);
        }
*/
        return view('exports.ventas', [
            'invoices' => $ventas
        ]);
    }
}
