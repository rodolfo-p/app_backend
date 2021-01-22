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
                    <span>{{$data->alm_almacen_direccion}}</span><br>
                    <span> <b>Email: </b>{{$data->alm_almacen_email}} </span><br>
                    <span> <b>Cel.: </b>{{$data->alm_almacen_telefono}} </span>
                </p>
            </td>
            <td class="border1px fontsize" style="width:30%">
                <div class="text-center">
                    <b>RUC: {{$data->empresa->emp_empresa_ruc}}</b>
                </div>
                <div class="color-gris text-center">
                    <b>GUÍA DE REMISIÓN <br>ELECTRÓNICO</b>
                </div>
                <div class="text-center">
                    <b>{{$data->guia_remision_seria_comprobante}}</b> -
                    <b>{{$data->guia_remision_numero_comprobante}}</b>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

    <br>
    <!--==============PRESENTACION=============-->
    <fieldset>
        <legend class="fontsize"><b>DATOS DESTINO:</b></legend>
        <table class="fontsize2">
            <tbody>
            <tr>
                <td style="width:40%"><b>FECHA:</b></td>
                <td style="width:60%"> {{$data->guia_remision_fecha_comprobante}}</td>

            </tr>
            <tr>
                <td style="width:40%"><b>Nº PAQUETES: </b></td>
                <td style="width:60%">{{$data->guia_remision_numero_paquetes}}</td>

            </tr>
            <tr>
                <td style="width:40%"><b>N° DOCUMENTO:</b></td>
                <td style="width:60%"> {{$data->guia_remision_cliente_numerodocumento}}</td>
            </tr>
            <tr>
                <td style="width:40%"><b>CLIENTE: </b></td>
                <td style="width:60%">{{$data->guia_remision_cliente_nombre}}</td>
            </tr>
            <tr>
                <td style="width:40%"><b>PUNTO DE PARTIDA:</b></td>
                <td style="width:60%"> {{$data->guia_remision_dir_partida}}</td>
            </tr>
            <tr>
                <td style="width:40%"><b>PUNTO DE LLEGADA:</b></td>
                <td style="width:60%">{{$data->guia_remision_dir_destino}}</td>
            </tr>
            </tbody>
        </table>
    </fieldset>
    <br>
    <fieldset>
        <legend class="fontsize"><b>DATOS DE TRANSPORTE:</b></legend>
        <table class="fontsize2">
            <tbody>
            <tr>
                <td style="width:40%"><b>N° DOCUMENTO:</b></td>
                <td style="width:60%"> {{$data->guia_remision_nro_documento_transporte}}</td>
            </tr>
            <tr>
                <td style="width:40%"><b>TRANSPORTE: </b></td>
                <td style="width:60%">{{$data->guia_remision_razon_social_tranporte}}</td>
            </tr>
            <tr>
                <td style="width:40%"><b>N° DOCUMENTO CONDUCTOR:</b></td>
                <td style="width:60%"> {{$data->guia_remision_num_doc_conductor}}</td>
            </tr>
            <tr>
                <td style="width:40%"><b>PLACA VEHICULO:</b></td>
                <td style="width:60%"> {{$data->guia_remision_placa_vehiculo}}</td>
            </tr>
            <tr>
                <td style="width:40%"><b>FECHA DE PARTIDA:</b></td>
                <td style="width:60%">{{$data->guia_remision_fecha_partida}}</td>
            </tr>
            </tbody>
        </table>
    </fieldset>
    <br>
    <!--==============ITEMS=============-->
    <div class="row">
        <table class="fontsize2 table3">
            <thead>
            <tr class="border-bottom">
                <th width="5%">ITEM</th>
                <th width="45%">DESCRIPCION</th>
                <th width="30%">CANTIDAD</th>
                <th width="20%">PESO</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data->detalle as $value)
                <tr class="border-bottom">
                    <td>{{$value->guia_remision_detalle_item}}</td>
                    <td>{{$value->guia_remision_detalle_descripcion}} -</td>
                    <td class="text-right">{{$value->guia_remision_detalle_cantidad}} </td>
                    <td class="text-right">{{$value->guia_remision_detalle_peso}}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <td colspan="3" class="text-right">TOTAL PESO:</td>
                <td class="text-right">{{$data->guia_remision_peso}}</td>
            </tr>
            </tfoot>
        </table>
    </div>
    <br><br>
    <p></p>
    <div class="row">
        <div class="column">
            <div class="column2 border1px">
                <p class="text-center">Representación impresa de GUÍA REMISIÓN electrónico, generada desde el sistema
                    del contribuyente.</p>
            </div>
        </div>
    </div>


</body>
</html>
