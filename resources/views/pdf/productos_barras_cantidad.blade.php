<!DOCTYPE html>
<html>
<head lang="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    <style>

        img {

            margin-left: 5px !important;
            margin-right: 5px !important;
            margin-top: 0px !important;
        }

        table {
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        h4 {
            text-align: center;
        }

        .codigo-barras {
            text-align: center !important;
        }

        b {
            margin-bottom: -30px !important;
            font-size: 9px !important;

        }

        .info {
            font-size: 12px !important;
            margin-top: -40px !important;
        }

        .precio {
            font-size: 12px !important;
            margin-top: -20px !important;
        }

        strong {
            font-size: 12px !important;
            margin-top: -5px !important;
        }

        samp {
            font-size: 12px !important;
        }

        br {
            margin-top: -2px !important;
            margin-bottom: -2px !important;
        }

    </style>
</head>
<body>
<h4>Reporte de productos por código de barras {{$data->producto}}</h4>
<table style="width:100%">
    <tr>
        <th>Código Barras</th>
        <th>Código Barras</th>
        <th>Código Barras</th>
    </tr>
    @foreach($data->detalle as $value)
        <tr>
            <td class="codigo-barras"><strong align="center">{{$value->producto}}</strong><br>
                <b align="center" class="precio">S/. {{$value->precio_venta}}</b> <br>
                <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($value->alm_producto_codigo, 'C39')}}"
                     alt="barcode" width="200" height="35" align="center"/><br>
                <samp align="center">{{$value->alm_producto_codigo}}</samp> <br>
                <b align="center" class="info">{{$data->empresa->emp_empresa_razon_social}}</b> <br>
                <b align="center" class="info">RUC: {{$data->empresa->emp_empresa_ruc}}</b><br>
                <b align="center" class="info">CEL: {{$data->empresa->emp_empresa_telefono}}</b>
            </td>
            <td class="codigo-barras"><strong align="center">{{$value->producto}}</strong><br>
                <b align="center" class="precio">S/. {{$value->precio_venta}}</b> <br>
                <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($value->alm_producto_codigo, 'C39')}}"
                     alt="barcode" width="200" height="35" align="center"/><br>
                <samp align="center">{{$value->alm_producto_codigo}}</samp> <br>
                <b align="center" class="info">{{$data->empresa->emp_empresa_razon_social}}</b> <br>
                <b align="center" class="info">RUC: {{$data->empresa->emp_empresa_ruc}}</b><br>
                <b align="center" class="info">CEL: {{$data->empresa->emp_empresa_telefono}}</b>
            </td>
            <td class="codigo-barras"><strong align="center">{{$value->producto}}</strong><br>
                <b align="center" class="precio">S/. {{$value->precio_venta}}</b> <br>
                <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($value->alm_producto_codigo, 'C39')}}"
                     alt="barcode" width="200" height="35" align="center"/><br>
                <samp align="center">{{$value->alm_producto_codigo}}</samp> <br>
                <b align="center" class="info">{{$data->empresa->emp_empresa_razon_social}}</b> <br>
                <b align="center" class="info">RUC: {{$data->empresa->emp_empresa_ruc}}</b><br>
                <b align="center" class="info">CEL: {{$data->empresa->emp_empresa_telefono}}</b>
            </td>
        </tr>
    @endforeach
</table>
</body>
</html>
