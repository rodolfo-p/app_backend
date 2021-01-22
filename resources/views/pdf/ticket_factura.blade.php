<!DOCTYPE html>

<head lang="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    <style>
        * {
            font-size: 12px;
            font-family: 'Times New Roman';
        }

        /********************************************/

        .container {
            font-family: monospace;
            grid-template-columns: auto;
            padding-right: -30px !important;
            padding-left: -30px !important;
            /* margin-right: auto;
             margin-left: auto;*/
        }

        .item {
            /*background-color: rgba(255, 255, 255, 0.8);*/
            /* border: 1px solid rgba(0, 0, 0, 0.8);*/
            padding: 1px;
            font-size: 10px;
            text-align: center;
        }

        .texto {
            font-size: 10px !important;
            text-align: center;
            font-weight: bold;
        }

        .item2 {
            padding: 1px;
            font-size: 10px !important;
            text-align: left;
        }

        table {
            font-family: monospace;
            font-size: 10px !important;
            width: 100%;
            border: none !important;
            margin-right: 20px !important;

        }

        tr {
            border-top-style: dashed !important;
        }

        th, td {
            border: none !important;
            text-align: left;
            padding: 3px;

        }

        .total {
            text-align: right !important;
        }

        .total-p {
            text-align: right !important;
            margin-right: 20px !important;
        }

        .line {

        }

        .line2 {
            margin-bottom: -8px !important;
            margin-top: -8px !important;
        }

        .qr {
            grid-template-columns: auto;
            position: relative;
            margin-left: 5rem;
            margin-top: 10px !important;
            margin-bottom: 10px !important;
        }

    </style>
</head>
<body>
<div class="container">
    <div class="item">{{$data->empresa->emp_empresa_razon_social}} - RUC: {{$data->empresa->emp_empresa_ruc}}</div>
    <div class="item">{{$data->alm_almacen_direccion}} </div>
    <div class="item">CEL.:{{$data->alm_almacen_telefono}}</div>
    <div class="item"><span>****************************************</span></div>
    <div class="item"><span class="texto">FACTURA ELECTRÓNICA {{$data->vent_venta_serie}}
            - {{$data->vent_venta_numero}}</span></div>
    <div class="item2">FECHA EMISIÓN: {{$data->vent_venta_fecha}}</div>
    <div class="item2">CLIENTE: {{$data->cliente}}</div>
    <div class="item2">RUC.: {{$data->vent_venta_cliente_numero_documento}}</div>
    @if($data->cliente_direccion!=null&&$data->cliente_direccion!="")
        <div class="item2">DIRECCIÓN.: {{$data->cliente_direccion}}</div>
    @endif


    @if($data->cliente_telefono!=null&&$data->cliente_telefono!="")
        <div class="item2">CEL.: {{$data->cliente_telefono}}</div>
    @endif
    <table>
        <tr>
            <td colspan="4" class="line">****************************************************</td>
        </tr>
        <tr>
            <!--<th class="texto">CODIGO</th>-->
            <th class="texto">DESCRIPCION</th>
            <th class="texto">CANT.</th>
            <th class="texto">P.UNIT.</th>
            <th class="texto">IMPORTE</th>
        </tr>
        @foreach($data->detalle as $value)
            <tr>
                <td colspan="4" class="line2">****************************************************</td>
            </tr>
            <tr>
                <td>{{$value->categoria}} {{$value->alm_producto_nombre}}</td>
                <td class="total">{{$value->vent_venta_detalle_cantidad}}</td>
                <td class="total">{{$value->vent_venta_detalle_precio_unitario}}</td>
                <td class="total-p">{{$value->vent_venta_detalle_precio_cobro}}</td>
            </tr>

        @endforeach

        <tr>
            <td colspan="4" class="line2">****************************************************</td>
        </tr>
        <tr>
            <td colspan="3" class="total">TOTAL A PAGAR:S/.</td>
            <td class="total-p">{{$data->vent_venta_precio_cobrado}}</td>
        </tr>

        <tr>
            <td colspan="3" class="total">OP. GRAVADAS: S/.</td>
            <td class="total-p">{{$data->vent_venta_bi}}</td>
        </tr>

        <tr>
            <td colspan="3" class="total">IGV - 18 %</td>
            <td class="total-p">{{$data->vent_venta_igv}}</td>
        </tr>
        <tr>
            <td colspan="3" class="total">IMPORTE TOTAL: S/.</td>
            <td class="total-p">{{$data->vent_venta_precio_cobrado}}</td>
        </tr>
        <tr>
            <td colspan="4" class="line2">****************************************************</td>
        </tr>
        @if($data->fise !='' ||$data->fise !=null)
            <tr>
                <td> Codigo Fise</td>
                <td colspan="3" class="total-p"> {{$data->fise}}</td>
            </tr>
        @endif
    </table>
    <div class="item2">SON: {{$data->vent_venta_precio_cobrado_letras}} SOLES</div>

    <div class="qr">
        <img src="data:image/png;base64,{{DNS2D::getBarcodePNG($data->qr, 'QRCODE')}}" alt="barcode"
             width="150" height="150">
    </div>
    <div class="item">
        GRACIAS POR SU COMPRA!
    </div>
    @if($data->distribuidor!=null)
        <div>
            <p>Repartidor: {{$data->distribuidor}}</p>
        </div>
    @endif
</div>
</body>


