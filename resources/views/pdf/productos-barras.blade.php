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
        h4{
            text-align: center;
        }

        .codigo-barras{
            text-align: center!important;
        }
        b{margin-bottom: -10px!important;}
    </style>
</head>
<body>
<h4>Reporte de productos por código de barras</h4>
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
            <td class="codigo-barras">    <strong align="center">{{$value->producto}}</strong><br>
                <b align="center">S/. {{$value->precio_venta}}</b> <br>
                <img src="data:image/png;base64,{{DNS1D::getBarcodePNG($value->alm_producto_codigo, 'C93')}}" alt="barcode"  width="250" height="40" align="center"/><br>
                <samp align="center">{{$value->alm_producto_codigo}}</samp>

            </td>
        </tr>
    @endforeach

</table>
</body>
</html>

