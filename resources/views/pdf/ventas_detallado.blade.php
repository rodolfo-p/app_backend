<!DOCTYPE html>
<head lang="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    <style>
        th {
            text-align: center;
            font-size: 9px;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td {
            text-align: center;
            font-size: 9px;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        }

        h4 {
            margin-top: -20px !important;
        }

    </style>
</head>
<body>
<div class="container">
    <h4 align="center">REPORTE DETALLADO DE VENTAS</h4>
    <table border="1" cellpadding="1">
        <tr style="background: rgba(60,138,254,0.82)">
            <th style="width: 15px;">SERIE</th>
            <th style="width: 15px;">Nº VENTA</th>
            <th style="width: 15px;">FECHA</th>
            <th style="width: 15px;">COMPROBANTE</th>
            <th style="width: 15px;">CLIENTE</th>
            <th style="width: 15px;">Nº CLIENTE</th>
            <th style="width: 15px;">IGV</th>
            <th style="width: 15px;">BASE IMP.</th>
            <th style="width: 15px;">TOTAL</th>
        </tr>
        @foreach($data->ventas as $valueP)
            <tr style="background: rgba(54,163,71,0.82)">
                <td>{{$valueP->vent_venta_serie}}</td>
                <td>{{$valueP->vent_venta_numero}}</td>
                <td>{{$valueP->vent_venta_fecha}}</td>
                <td>{{$valueP->doc_tipo_comprobante_nombre}}</td>
                <td>{{$valueP->cliente}}</td>
                <td>{{$valueP->vent_venta_cliente_numero_documento}}</td>
                <td>{{$valueP->vent_venta_igv}}</td>
                <td>{{$valueP->vent_venta_bi}}</td>
                <td>{{$valueP->vent_venta_precio_cobrado}}</td>
            </tr>
            <tr>
                <th colspan="3">ARTÍCULO</th>
                <th style="width: 15px;">U. MEDIDA</th>
                <th style="width: 15px;">CANTIDAD</th>
                <th style="width: 15px;">PRECIO UNITARIO</th>
                <th style="width: 15px;">IGV</th>
                <th style="width: 15px;">BASE IMPONIBLE</th>
                <th style="width: 15px;"> PRECIO</th>
            </tr>
            @foreach($valueP->detalle as $value)
                <tr>
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
            <td colspan="6"><b>Totales Vendidos</b></td>
            <td>{{$data->total->igv_total}}</td>
            <td>{{$data->total->base_total}}</td>
            <td>{{$data->total->importe_total}}</td>
        </tr>
        <tr >
            <td colspan="6"><b>Totales Al Crédito</b></td>

            <td>{{$data->total->igv_credito}}</td>
            <td>{{$data->total->bi_credito}}</td>
            <td>{{$data->total->total_credito}}</td>

        </tr>
        <tr>
            <td colspan="6"><b>Totales Al Contado</b></td>
            <td>{{$data->total->igv_contado}}</td>
            <td>{{$data->total->bi_contado}}</td>
            <td>{{$data->total->total_contado}}</td>
        </tr>
    </table>
</div>
</body>


