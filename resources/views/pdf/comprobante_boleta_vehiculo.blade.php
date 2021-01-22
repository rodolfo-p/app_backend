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
        .border2px{
            border: 1px solid #242525;
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
        <tr>
            <td style="width:20%"><b>NRO. DOC.:</b></td>
            <td style="width:80%"> {{$data->vent_venta_cliente_numero_documento}}</td>
        </tr>
        <tr>
            <td style="width:20%"><b>CLIENTE: </b></td>
            <td style="width:80%">{{$data->cliente}}</td>
        </tr>
        <tr>
            <td style="width:20%"><b>DIRECCIÓN:</b></td>
            <td style="width:80%"> {{$data->cliente_direccion}}</td>
        </tr>
        </tbody>
    </table>

    <br>
    <!--==============ITEMS=============-->
    <div class="row">
        <table class="fontsize2 table3">
            <thead>
            <tr class="border-bottom">
                <th style="width: 25%">CANTIDAD</th>
                    <th style="width: 50%" colspan="2">DESCRIPCIÓN</th>

                <th style="width: 25%">TOTAL</th>
            </tr>
            </thead>
            <tbody>

            <tr>
                <td rowspan="9"  style="text-align: center">01</td>
                <td>{{$data->detalle->alm_producto_nombre}}</td>
                <td></td>
                <td rowspan="9"  style="text-align: center">{{$data->detalle->vent_venta_detalle_precio_cobro}}</td>

            </tr>
            <tr>

                <td>MARCA</td>
                <td>{{$data->detalle->alm_producto_marca}}</td>

            </tr>
            <tr>

                <td>MODELO</td>
                <td>{{$data->detalle->alm_producto_modelo}}</td>

            </tr>
            <tr>

                <td>COLOR</td>
                <td>{{$data->detalle->alm_producto_color}}</td>

            </tr>
            <tr>

                <td>MOTOR</td>
                <td>{{$data->detalle->alm_producto_motor}}</td>

            </tr>
            <tr>

                <td>CHASIS</td>
                <td>{{$data->detalle->alm_producto_chasis}}</td>

            </tr>
            <tr>

                <td>DUA</td>
                <td>{{$data->detalle->alm_producto_dua}}</td>

            </tr>
            <tr>

                <td>ITEM</td>
                <td>{{$data->detalle->alm_producto_item}}</td>

            </tr>
            <tr>

                <td>CODIGO</td>
                <td>{{$data->detalle->alm_producto_codigo}}</td>

            </tr>
            <tr>
                <td colspan="4"><br>
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
<body>

<table class="fontsize2" border="0" style="text-align:center;">
    <tbody>
    <tr>
        <td style="width:100%"><b> <img src="{{$data->empresa->emp_empresa_logo_url}}" alt="barcode" height="80"
                                        width="180"></b></td>
    </tr>
    <tr>
        <td>@if($data->empresa->emp_empresa_ruc=="20448351859")
                    <span>JR. ENRIQUE MEIGGS 103 - JULIACA - PUNO</span><br>
                    <span>URB. VILLA AGRICULTURA K-18 - JLB Y RIVERO - AREQUIPA  </span><br>
                  @endif

        </td>
    </tr>
    </tbody>
</table>
<table class="fontsize2">
    <tbody>
    <tr>
        <td style="width:100%; text-align:center"><h3><u>DECLARACIÓN JURADA DE MEDIO DE PAGO</u></h3></td>

    </tr>
    <tr>
        <td style="width:100%"><b>Señores:</b></td>
    </tr>
    <tr>
        <td style="width:100%"><b>SUPERINTENDENCIA NACIONAL DE REGISTROS PUBLICOS:</b></td>
    </tr>
    <tr>
        <td style="width:100%"><b>Registro de Propiedad Vehicular</b></td>
    </tr>
    <tr>
        <td style="width:100%"><b>Presente.-</b></td>
    </tr>
    <tr>
        <td style="width:100%"><P>{{$data->empresa->emp_empresa_razon_social}}. con
                RUC.: {{$data->empresa->emp_empresa_ruc}} ,
                debidamente inscrito en la partida Electrónica Nº 11101212 del Registro de Personas Jurídicas
                de Juliaca, en su calidad de empresa VENDEDORA y en su calidad de COMPRADOR:
            </P>

        </td>
    </tr>
    </tbody>
</table>
<br>
<table>
    <tbody>
    <tr>
        <td style="width: 20%" class="fontsize2 ">Nombre:</td>
        <td style="width: 80%" class="fontsize2">{{$data->cliente}}</td>
    </tr>
    <tr>
        <td style="width: 20%" class="fontsize2">Dni/Ruc:</td>
        <td style="width: 80%" class="fontsize2">{{$data->vent_venta_cliente_numero_documento}}</td>
    </tr>
    </tbody>
</table>

<p class="fontsize">Declaramos la compra del vehículo:</p>

<table class="border2px">
    <tbody>
    <tr>
        <td style="width: 17%" class="fontsize2"><b><u>DESCRIPCIÓN:</u></b></td>
        <td style="width: 17%" class="fontsize2"><b><u>MARCA:</u></b></td>
        <td style="width: 17%" class="fontsize2"><b><u>MODELO:</u></b></td>
        <td style="width: 17%" class="fontsize2"><b><u>Nº MOTOR:</u></b></td>
        <td style="width: 17%" class="fontsize2"><b><u>Nº CHASIS:</u></b></td>
    </tr>

    <tr>
        <td style="width: 17%" class="fontsize2">{{$data->detalle->alm_producto_nombre}}</td>
        <td style="width: 17%" class="fontsize2">{{$data->detalle->alm_producto_marca}}</td>
        <td style="width: 17%" class="fontsize2">{{$data->detalle->alm_producto_modelo}}</td>
        <td style="width: 17%" class="fontsize2">{{$data->detalle->alm_producto_motor}}</td>
        <td style="width: 17%" class="fontsize2">{{$data->detalle->alm_producto_chasis}}</td>
    </tr>

    </tbody>
</table>
<br>

<table class="border2px">
    <tbody>
    <tr>
        <td style="width: 25%" class="fontsize2"><b><u>Comprobante/Número:</u></b></td>
        <td style="width: 25%" class="fontsize2"><b><u>Fecha de Venta:</u></b></td>
        <td style="width: 50%" class="fontsize2"><b><u>Monto total de la operación:</u></b></td>
    </tr>
    <tr>
        <td style="width: 25%" class="fontsize2">{{$data->vent_venta_serie}}/{{$data->vent_venta_numero}}</td>
        <td style="width: 25%" class="fontsize2">{{$data->vent_venta_fecha}}</td>
        <td style="width: 50%" class="fontsize2">S/. {{$data->vent_venta_precio_cobrado}} SOLES
        </td>
    </tr>
    </tbody>
</table>
<br>
<table>
    <tbody>
    <tr>
        <td style="width: 100%" class="fontsize2" colspan="3">
            <p>Que con relación a la operación de COMPRA Y VENTA contenida en el comprobante
                de la referencia, detallamos que:
                ..................................................................................................................................................................................................................
                ..................................................................................................................................................................................................................
                ..................................................................................................................................................................................................................</p>
        </td>
    </tr>
    <tr>

        <td style="width: 100%" class="fontsize2" colspan="3">
            <p>Estando ambas partes de acuerdo, procedemos a firmar el presente documento.</p>
        </td>
    </tr>

    <tr>
        <td style="width: 14%" colspan="2"></td>
        <td style="width: 43%" class="fontsize2">
            <p style="text-align:center;">___________________________<br>
                {{$data->cliente}}<br>
                DNI/RUC {{$data->vent_venta_cliente_numero_documento}}
            </p></td>

    </tr>
    </tbody>
</table>
</body>
</html>
