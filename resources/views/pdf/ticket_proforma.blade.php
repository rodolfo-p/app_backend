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
    <div class="item">{{$data->proforma->alm_almacen_direccion}} </div>
    <div class="item">CEL.:{{$data->proforma->alm_almacen_telefono}}</div>
    <div class="item"><span>****************************************</span></div>
    <div class="item"><span class="texto">PROFORMA {{$data->proforma->vent_proforma_serie}}
            - {{$data->proforma->vent_proforma_numero}}</span></div>
    <div class="item2">FECHA: {{$data->proforma->vent_proforma_fecha}}</div>
    <div class="item2">CLIENTE: {{$data->proforma->cliente}}</div>
    <div class="item2">DOC. IDENT.: {{$data->proforma->vent_proforma_cliente_numero_documento}}</div>

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
                <td>{{$value->alm_producto_nombre}}</td>
                <td class="total">{{$value->vent_proforma_detalle_cantidad}}</td>
                <td class="total">{{$value->vent_proforma_detalle_precio_unitario}}</td>
                <td class="total-p">{{$value->vent_proforma_detalle_precio}}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4" class="line2">****************************************************</td>
        </tr>
        <tr>
            <td colspan="3" class="total">TOTAL:S/.</td>
            <td class="total-p">{{$data->proforma->vent_proforma_total}}</td>
        </tr>
        <tr>
            <td colspan="4" class="line2">****************************************************</td>
        </tr>

    </table>

    <div class="item">
        GRACIAS POR SU PREFERENCIA!
    </div>

</div>
</body>


