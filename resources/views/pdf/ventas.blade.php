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

        h4{
            margin-top: -20px!important;
        }

    </style>
</head>
<body>
<div class="container">
    <h4 align="center">REPORTE VENTAS</h4>
    <table border="1" cellpadding="1">
        <tr>
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
        @foreach($data->ventas as $value)
            <tr>
                <td>{{$value->vent_venta_serie}}</td>
                <td>{{$value->vent_venta_numero}}</td>
                <td>{{$value->vent_venta_fecha}}</td>
                <td>{{$value->doc_tipo_comprobante_nombre}}</td>
                <td>{{$value->cliente}}</td>
                <td>{{$value->vent_venta_cliente_numero_documento}}</td>
                <td>{{$value->vent_venta_igv}}</td>
                <td>{{$value->vent_venta_bi}}</td>
                <td>{{$value->vent_venta_precio_cobrado}}</td>
            </tr>
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


