<html>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>


<body>
<div>
    <table>
        <tr>
            <th style="background-color: #6495ed; color: #F8F8F8; font-size: small;">SERIE</th>
            <th style="background-color: #6495ed; color: #F8F8F8; font-size: small;">Nº VENTA</th>
            <th style="background-color: #6495ed; color: #F8F8F8; font-size: small;">FECHA</th>
            <th style="background-color: #6495ed; color: #F8F8F8; font-size: small;">COMPROBANTE</th>
            <th style="background-color: #6495ed; color: #F8F8F8; font-size: small;" colspan="2">CLIENTE</th>
            <th style="background-color: #6495ed; color: #F8F8F8; font-size: small;">Nº CLIENTE</th>
            <th style="background-color: #6495ed; color: #F8F8F8; font-size: small;">IGV</th>
            <th style="background-color: #6495ed; color: #F8F8F8; font-size: small;">BASE IMP.</th>
            <th style="background-color: #6495ed; color: #F8F8F8; font-size: small;">TOTAL</th>
        </tr>
        @foreach($data->ventas as $valueP)
            <tr>
                <td style="background-color: #eded47;">{{$valueP->vent_venta_serie}}</td>
                <td style="background-color: #eded47;">{{$valueP->vent_venta_numero}}</td>
                <td style="background-color: #eded47;">{{$valueP->vent_venta_fecha}}</td>
                <td style="background-color: #eded47;">{{$valueP->doc_tipo_comprobante_nombre}}</td>
                <td colspan="2" style="background-color: #eded47;">{{$valueP->cliente}}</td>
                <td style="background-color: #eded47;">{{$valueP->vent_venta_cliente_numero_documento}}</td>
                <td style="background-color: #eded47;">{{$valueP->vent_venta_igv}}</td>
                <td style="background-color: #eded47;">{{$valueP->vent_venta_bi}}</td>
                <td style="background-color: #eded47;">{{$valueP->vent_venta_precio_cobrado}}</td>
            </tr>
            <tr>
                <th></th>
                <th colspan="3" style="color: #121212; font-size: small;" align="center">ARTÍCULO</th>
                <th style="color: #121212; font-size: small;" align="center">U. MEDIDA</th>
                <th style="color: #121212; font-size: small;" align="center">CANTIDAD</th>
                <th style="color: #121212; font-size: small;" align="center">PRECIO UNITARIO</th>
                <th style="color: #121212; font-size: small;" align="center">IGV</th>
                <th style="color: #121212; font-size: small;" align="center">BASE IMPONIBLE</th>
                <th style="color: #121212; font-size: small;" align="center"> PRECIO</th>
            </tr>
            @foreach($valueP->detalle as $value)
                <tr>
                    <td></td>
                    <td colspan="3">{{$value->categoria}} {{$value->alm_producto_nombre}}</td>
                    <td>{{$value->alm_unidad_medida_simbolo}}</td>
                    <td>{{$value->vent_venta_detalle_cantidad}}</td>
                    <td>{{$value->vent_venta_detalle_precio_unitario}}</td>
                    <td>{{$value->vent_venta_detalle_igv}}</td>
                    <td>{{$value->vent_venta_detalle_bi}}</td>
                    <td>{{$value->vent_venta_detalle_precio_cobro}}</td>

                </tr>
            @endforeach
        @endforeach
        <tr>
            <td colspan="7"><b>Totales Vendidos</b></td>
            <td align="right" style="background-color: #eded47;">{{$data->total->igv_total}}</td>
            <td align="right" style="background-color: #eded47;">{{$data->total->base_total}}</td>
            <td align="right" style="background-color: #eded47;">{{$data->total->importe_total}}</td>
        </tr>
        <tr>
            <td colspan="7"><b>Totales Al Crédito</b></td>

            <td align="right" style="background-color: #eded47;">{{$data->total->igv_credito}}</td>
            <td align="right" style="background-color: #eded47;">{{$data->total->bi_credito}}</td>
            <td align="right" style="background-color: #eded47;">{{$data->total->total_credito}}</td>

        </tr>
        <tr>
            <td colspan="7"><b>Totales Al Contado</b></td>
            <td align="right" style="background-color: #eded47;">{{$data->total->igv_contado}}</td>
            <td align="right" style="background-color: #eded47;">{{$data->total->bi_contado}}</td>
            <td align="right" style="background-color: #eded47;">{{$data->total->total_contado}}</td>
        </tr>
    </table>
</div>
</body>

