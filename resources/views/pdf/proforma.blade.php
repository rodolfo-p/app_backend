<!DOCTYPE html>
<html lang="en">
<head>
    <title>Formato Commprobante</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <style>
        .border1px {
            border: 1px solid #e9ecef;
        }

        .fontsize {
            font-size: 13px;
            text-align: justify;
        }

        .fontsize2 {
            font-size: 13px;
        }

        .text-center {
            text-align: center;
        }

        .color-gris {
            background: #e9ecef;
            padding: 0.5em 0;
        }

        table {
            width: 100%;
        }

        .table3 {
            border: 1px solid #e9ecef;
        }

        .table3 .border-bottom td, th {
            border-bottom: 1px solid #e9ecef;
            border-left: 1px solid #e9ecef;
        }

        .text-right {
            text-align: right;
        }

        .row {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            width: 100%;
        }

        .column {
            display: flex;
            flex-direction: column;
            flex-basis: 100%;
            flex: 1;
        }

        .column1 {
            width: 40%;
            position: absolute;
        }

        .column2 {
            width: 60%;
            position: relative;
            margin-left: 10em;
        }
    </style>
</head>
<body>
<div class="container-fluid">


    <!--==============PRESENTACION=============-->
    <table>
        <tbody>
        <tr>
            <td style="width:20%">
                <img class="img-center"
                     src="{{$data->empresa->emp_empresa_logo_url}}" height="80" width="180">
            </td>
            <td class="col-md-7 text-center fontsize" style="width:50%">
                <p>
                    <b>{{$data->empresa->emp_empresa_razon_social}}</b><br>
                    <span>{{$data->proforma->alm_almacen_direccion}}</span><br>
                    <span> <b>Email: </b>{{$data->proforma->alm_almacen_email}} </span><br>
                    <span> <b>Cel.: </b>{{$data->proforma->alm_almacen_telefono}} </span>
                </p>
            </td>
            <td class="border1px fontsize" style="width:30%">
                <div class="text-center">
                    <b>RUC: {{$data->empresa->emp_empresa_ruc}}</b>
                </div>
                <div class="color-gris text-center">
                    <b>PROFORMA</b>
                </div>
                <div class="text-center">
                    <b>{{$data->proforma->vent_proforma_serie}}</b> - <b>{{$data->proforma->vent_proforma_numero}}</b>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

    <br>
    <!--==============PRESENTACION=============-->
    <table class="fontsize2 border1px">
        <tbody>
        <tr>
            <td style="width:20%"><b>FECHA DE EMISIÓN:</b></td>
            <td style="width:80%">
                {{$data->proforma->vent_proforma_fecha}}
            </td>
        </tr>
        <tr>
            <td style="width:20%"><b>NRO. DOC.:</b></td>
            <td style="width:80%"> {{$data->proforma->vent_proforma_cliente_numero_documento}}</td>
        </tr>
        <tr>
            <td style="width:20%"><b>CLIENTE: </b></td>
            <td style="width:80%">{{$data->proforma->cliente}}</td>
        </tr>
        </tbody>
    </table>

    <br>
    <!--==============ITEMS=============-->
    <div class="row">
        <table class="fontsize2 table3">
            <thead>
            <tr class="border-bottom">
                <th style="width: 50%">DESCRIPCION</th>
                <th style="width: 16%">CANT</th>
                <th style="width: 16%">P.UNIT.</th>
                <th style="width: 18%">IMPORTE</th>

            </tr>
            </thead>
            <tbody>
            @foreach($data->detalle as $value)
                <tr class="border-bottom">
                    <td>{{$value->alm_producto_nombre}}</td>
                    <td>{{$value->vent_proforma_detalle_cantidad}}</td>
                    <td>{{$value->vent_proforma_detalle_precio_unitario}} </td>
                    <td class="text-right">{{$value->vent_proforma_detalle_precio}}</td>

                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td colspan="3" class="text-right">TOTAL:</td>
                <td class="text-right"> {{$data->proforma->vent_proforma_total}}</td>

            </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
