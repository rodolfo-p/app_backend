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
                    @if($data->empresa->emp_empresa_ruc=="20448351859")
                        <span>JR. ENRIQUE MEIGGS 103 - JULIACA - PUNO</span><br>
                        <span>URB. VILLA AGRICULTURA K-18 - JLB Y RIVERO - AREQUIPA  </span><br>
                    @else
                        <span>{{$data->alm_almacen_direccion}}</span><br>
                        <span> <b>Email: </b>{{$data->alm_almacen_email}} </span><br>
                        <span> <b>Cel.: </b>{{$data->alm_almacen_telefono}} </span>
                    @endif
                </p>
            </td>
            <td class="border1px fontsize" style="width:30%">
                <div class="text-center">
                    <b>RUC: {{$data->empresa->emp_empresa_ruc}}</b>
                </div>
                <div class="color-gris text-center">
                    <b>{{$data->doc_tipo_comprobante_nombre}} <br>ELECTRÓNICA</b>
                </div>
                <div class="text-center">
                    <b>{{$data->vent_venta_serie}}</b> - <b>{{$data->vent_venta_numero}}</b>
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
                {{$data->vent_venta_fecha}}
            </td>
        </tr>
        @if(!$data->vent_venta_comprado_por)
            <tr>
                <td style="width:20%"><b>NRO. DOC.:</b></td>
                <td style="width:80%"> {{$data->vent_venta_cliente_numero_documento}}</td>
            </tr>
        @endif
        <tr>
            <td style="width:20%"><b>CLIENTE: </b></td>

            @if(!$data->vent_venta_comprado_por)
                <td style="width:80%">{{$data->cliente}}</td>
            @else
                <td style="width:80%"> {{$data->vent_venta_comprado_por}}</td>
            @endif
        </tr>
        @if(!$data->vent_venta_comprado_por)
            <tr>
                <td style="width:20%"><b>DIRECCIÓN:</b></td>
                <td style="width:80%"> {{$data->cliente_direccion}}</td>
            </tr>
        @endif
        <!--@if($data->vent_venta_comprado_por!="X")
            <tr>
                <td style="width:20%"><b>ATENDIDO A:</b></td>
                <td style="width:80%"> {{$data->cliente}}</td>
            </tr>
        @endif-->

        </tbody>
    </table>

    <br>
    <!--==============ITEMS=============-->
    <div class="row">
        <table class="fontsize2 table3">
            <thead>
            <tr class="border-bottom">
                <th style="width: 10%">CANTIDAD</th>
                <th style="width: 20%">UNI. MEDIDA</th>
                <th style="width: 45%">PRODUCTO</th>
                <th style="width: 15%">PRECIO UNIT.</th>
                <th style="width: 10%">TOTAL</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data->detalle as $value)
                <tr class="border-bottom">
                    <td>{{$value->vent_venta_detalle_cantidad}}</td>
                    @if($value->alm_unidad_medida_simbolo_impresion)
                        <td>{{$value->alm_unidad_medida_simbolo_impresion}}</td>
                    @else
                        <td>{{$value->alm_unidad_medida_id}}</td>
                    @endif
                    <td>{{$value->alm_producto_nombre}} {{$value->alm_producto_marca}} </td>
                    <td class="text-right">{{$value->vent_venta_detalle_precio_unitario}}</td>
                    <td class="text-right">{{$value->vent_venta_detalle_precio_cobro}}</td>
                </tr>
            @endforeach
            <tr class="border-bottom">
                <td colspan="5"><br>
                    &nbsp;&nbsp; SON: {{$data->vent_venta_precio_cobrado_letras}} SOLES
                </td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="4" class="text-right">TOTAL GRABADOS:</td>
                <td class="text-right"> {{$data->totales->gravado}}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right">TOTAL EXONERADOS:</td>
                <td class="text-right"> {{$data->totales->exonerado}}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right">TOTAL INAFECTOS:</td>
                <td class="text-right"> {{$data->totales->inafecto}}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right">IGV:</td>
                <td class="text-right"> {{$data->vent_venta_igv}}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right">SUBTOTAL:</td>
                <td class="text-right"> {{$data->vent_venta_bi}}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right">TOTAL:</td>
                <td class="text-right"> {{$data->vent_venta_precio_cobrado}}</td>

            </tr>
            </tfoot>
        </table>
    </div>
    <br><br>
    <p></p>
    <div class="row">
        <div class="column">
            <div class="column1">
                <img src="data:image/png;base64,{{DNS2D::getBarcodePNG($data->vent_venta_qr, 'QRCODE')}}" alt="barcode"
                     width="130" height="130" align="center"/>
            </div>
            <div class="column2 border1px">
                <p class="text-center">Representación impresa de la {{$data->doc_tipo_comprobante_nombre}} electrónica,
                    generada desde el sistema del contribuyente.</p>
            </div>
        </div>
    </div>


</body>
</html>
