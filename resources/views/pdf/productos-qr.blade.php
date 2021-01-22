<!DOCTYPE html>
<html>
<head lang="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    <style>

        img{

            margin-left: 5px!important;
            margin-right: 5px!important;
            margin-top: 0px!important;
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
        .codigo-qr{
            text-align: center!important;
        }
        b{margin-bottom: -10px!important;}

    </style>
</head>
<body>
<h4>Reporte de productos por código QR</h4>
<table style="width:100%">
    <tr>
        <th>Producto</th>
        <th>Categoría</th>
        <th>U.Medida</th>
        <th>Código Barras</th>
    </tr>
    @foreach($data as $value)
        <tr>
            <td>{{$value->producto}}</td>
            <td>{{$value->categoria}}</td>
            <td>{{$value->unidad_medida_simbolo}}</td>
            <td class="codigo-qr"><strong align="center">{{$value->producto}}</strong><br>
                <b align="center">S/. {{$value->precio_venta}}</b> <br>
                <img src="data:image/png;base64,{{DNS2D::getBarcodePNG($value->producto_id, 'QRCODE')}}" alt="barcode"
                     width="80" height="80" align="center"/><br>
                <samp align="center">{{$value->alm_producto_codigo}}</samp>

            </td>

        </tr>
    @endforeach

</table>
</body>
</html>

