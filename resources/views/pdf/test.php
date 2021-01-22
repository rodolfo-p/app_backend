<!DOCTYPE html>
<html>
<head lang="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: 'Source Sans Pro';
        }

        p {
            font-family: sans-serif;
            font-size: 12px;
            color: #444444;
            text-align: left;
            line-height: 15px;
        }

        body {
            padding: 15px;
            box-sizing: border-box;
            color: #444444;
            font-family: sans-serif;
            font-size: 12px;
        }

        .wrap {
            width: 1050px;
            max-width: 100%;
            height: auto;
            margin: 0 auto;
            text-align: center;
        }

        .logofactura {
            max-width: 290px;
            height: auto;
            margin: 5px 0;
        }

        table {
            /*width: 100%;*/
        }

        table td {
            padding: 2px 4px;
            box-sizing: border-box;
        }

        .tabletop td {
            text-align: center;
            font-size: 14px;
            padding: 0 0 20px 0;
        }

        .tablareceipt {
            width: 100%;
        }

        .tablareceipt td {
            text-align: center;
            width: 300px;
        }

        .tablareceipt td p {
            text-transform: uppercase;
            font-size: 12px;
            line-height: 10px;
            font-weight: bold;
            color: #5e5e5e;
            padding: 0;
            margin: 0;
            text-align: center;
        }

        .tablareceipt i {
            color: #444444;
        }

        .datoruc {
            width: auto;
            float: right;
            border: solid 1px #000;
        }

        .datoruc p {
            font-size: 25px !important;
            line-height: 30px !important;
            margin: 5px 0 !important;
        }

        .iconoimpresion a {
            text-align: right;
        }

        .tabladatos td {
            width: inherit;
            padding: 0;
        }

        .tabladatos p {
            text-align: center;
            color: #444444;
            text-align: left;
        }

        .tdatoslabel {
            padding: 0;
        }

        .tdatodlabeldos {

        }

        .tdatoslabel p {
            width: auto !important;
            float: left;
            background: #fff;
            position: relative;
            padding: 0 5px 0 0;
        }

        .tdatoslabel p:after {
            content: "";
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #fff;
            z-index: 2;
        }

        div.tablageneral {
            text-align: center;
            /*text-align: center;
            border: solid 1px #a09f9f;
            height: 5em;
            margin: 3px 0 0 0;*/
        }
        div.tablageneral table {
            margin: 0 auto;
            text-align: left;
            border: 1px solid #ddd;
        }
        .tablageneral table tr {
            border-bottom: 1px solid #ddd !important;
        }
        /* div.titulotable td {
             border-bottom: solid 1px #a09f9f;
         }*/
        .invoice {
            width: 200px;
            text-align: right;
        }

        .titulotable {
            font-weight: bold;
            border-spacing: 0;
            background: #f4f4f4;
        }

        .detalletable td, .titulotable td {
            text-align: left;
            vertical-align: middle;
        }

        .ulttable {
            border-right: none !important;
        }

        .nombredetalle, .cantidadtabla {
            width: 50px;
            text-align: left !important;
        }

        .preciotabla {
            width: 106px;
        }

        .sinborde td {
            border-top: solid 0px #d7d6d6 !important;
        }

        .tablegracias {
            margin: 30px 0 0 0;
        }

        .tablegracias td {
            text-align: left;
            font-weight: normal;
            font-size: 15px;
            padding: 0;
        }

        .tablacostos {
            margin: 0 0 0 1px;
            border-top: none;
            border-bottom: none;
            border-left: none;
        }

        .tablacostos td {
            position: relative;
        }

        .imprenta {
            width: 280px;
            border-right: none !important;
        }

        .imprenta p {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: auto;
            padding: 10px;
            box-sizing: border-box;
            text-align: center;
            color: #444444;
        }

        .cancelado {
            text-align: center !important;
        }

        .cancelado p {
            width: auto;
            padding: 0 60px;
            border-top: dashed 1px rgba(0, 0, 0, 0.9);
            text-align: center;
            display: inline-block;
            float: none;
            color: #444444;
        }

        .fintabla {
            border-bottom: solid 1px #a09f9f !important;
        }

        .boletanombre p {
            font-size: 15px !important;
            line-height: 22px !important;
            text-transform: none !important;
            font-weight: normal !important;
        }

        .tdatofecha {
            width: 160px !important;
            border-bottom: dashed 1px #000 !important;
            margin: 5px 0 !important;
        }

        .tdatomes {
            width: 110px !important;
        }

        .tdatonombre {
            width: 700px !important;
            border-bottom: dashed 1px #000 !important;
            margin: 5px 0 !important;
        }

        .sinborde {
            border-right: none !important;
        }

        .fintablados {
            background: #f4f4f4;
        }

        .tablacan td {
            width: 33.3%;
        }

        .tablacan p {
            text-align: center;
            line-height: 30px;
            position: relative;
        }

        .tablacan p span, .caneladotd span {
            width: 200px;
            height: 1px;
            position: absolute;
            bottom: 30px;
            left: 50%;
            margin-left: -100px;
            border-top: dashed 1px #000 !important;
        }

        .tdatoemision {
            width: 970px !important;
        }

        .tdcanc {
            text-align: center;
        }

        .caneladotd {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: auto;
            text-align: center;
        }

        .tdatocre {
            width: calc(50% - 20px) !important;
            margin: 0 20px 0 0 !important;
        }

        .tdatocons {
            border-bottom: none !important;
        }

        .flotantediv {
            position: absolute;
            top: 0;
            left: 0;
        }

        .motivcred {
            width: auto;
            padding: 5px 0;
        }

        .motivcred p {
            font-size: 12px;
        }

        .motivcred label {
            width: 200px;
        }

        .reception {
            width: calc(100% - 20px);
            height: auto;
            padding: 5px 0;
            margin: 5px 0;
            border-top: dashed 1px rgba(0, 0, 0, 0.5);
            float: left;
            box-sizing: border-box;
        }

        .datoappcred {
            border-bottom: solid 1px #000;
            position: relative;
            min-height: 21px;
            width: 800px;
            float: left;
        }

        .datoappcred span {
            position: absolute;
            background: #fff;
            bottom: -1px;
            left: 0;
            z-index: 1;
        }

        .datoappcred2 {
            width: 180px;
        }

        .datoappcred3 {
            width: 250px;
        }

        .tablacandos {
            margin: 70px 0 0 0;
            border: none !important;
        }

        .tablacandos td {
            border: none !important;
        }

        .codigofac {
            border-radius: 20px;
            border: solid 1px rgba(0, 0, 0, 0.3);
            padding: 5px 10px;
            font-size: 13px;
            font-weight: bold;
            background: rgba(0, 0, 0, 0.1);
        }

        .codigoqr {
            float: right;
            width: 70px;
        }

        .codigoqr img {
            width: 100%;
            height: auto;
        }

        input[type=checkbox] {
            display: inline;
        }
    </style>
</head>
<body>
<table class="tablareceipt">
    <tbody>
    <tr>
        <td>
            <p>{{$data->empresa->emp_empresa_razon_social}}</p>

            <p style="text-transform: none !important;"> {{$data->empresa->emp_empresa_direccion}}</p>
            <p style="text-transform: none !important;"> Cel.: {{$data->empresa->emp_empresa_telefono}}</p>
        </td>
        <td class="datoruc">
            <p>R.U.C. {{$data->empresa->emp_empresa_ruc}}</p>
            <p>GUÍA DE REMISIÓN ELECTRÓNICO</p>
            <p>SERIE: {{$data->guia_remision_seria_comprobante}}</p>
            <p>NÚMERO: {{$data->guia_remision_numero_comprobante}}</p>
        </td>
    </tr>
    </tbody>
</table>
<br>
<table class="tabladatos">
    <tbody>
    <tr>
        <td class="tdatoslabel">
            <div style="padding-bottom: 10px; padding-right: 19px;">
                <span style="float: left;">Cliente:</span>
                <div style="margin-left: 95px; border-bottom: solid 1px #000;">{{$data->guia_remision_cliente_nombre}}</div>
            </div>
        </td>
        <td class="tdatoslabel tdatodlabeldos">
            <div>
                <span style="float: left;">Punto de partida:</span>
                <div style="margin-left: 118px; border-bottom: solid 1px #000;"> {{$data->guia_remision_dir_partida}}</div>
            </div>
        </td>
    </tr>
    <tr>
        <td class="tdatoslabel">
            <div style="padding-bottom: 10px; padding-right: 19px;">
                <span style="float: left;">RUC N°:</span>
                <div style="margin-left: 95px; border-bottom: solid 1px #000;"> {{$data->guia_remision_cliente_numerodocumento}} </div>
            </div>
        </td>
        <td class="tdatoslabel tdatodlabeldos">
            <div>
                <span style="float: left;">Punto de llegada: </span>
                <div style="margin-left: 118px; border-bottom: solid 1px #000;">{{$data->guia_remision_dir_destino}}</div>
            </div>
        </td>
    </tr>
    <tr>
        <td class="tdatoslabel">
            <div style="padding-bottom: 10px; padding-right: 19px;">
                <span style="float: left;">Fecha:</span>
                <div style="margin-left: 95px; border-bottom: solid 1px #000;">  {{$data->guia_remision_fecha_comprobante}}
                </div>
            </div>
        </td>

        <td class="tdatoslabel">
            <div style="padding-bottom: 10px; padding-right: 19px;">
                <span style="float: left;">Transporte:</span>
                <div style="margin-left: 95px; border-bottom: solid 1px #000;">  {{$data->guia_remision_razon_social_tranporte}}
                </div>
            </div>
        </td>
    </tr>


    <tr>
        <td class="tdatoslabel">
            <div style="padding-bottom: 10px; padding-right: 19px;">
                <span style="float: left;">Nº Paquetes:</span>
                <div style="margin-left: 95px; border-bottom: solid 1px #000;">  {{$data->guia_remision_numero_paquetes}}
                </div>
            </div>
        </td>
    </tr>
    </tbody>
</table>

<div class="tablageneral">
    <table>
        <tbody>
        <tr class="titulotable">
            <td width="5%">ITEM</td>
            <td width="45%">DESCRIPCIÓN</td>
            <td width="30%" class="cantidadtabla">CANTIDAD</td>
            <td width="20%" class="preciotabla ulttable">PESO</td>
        </tr>
        @foreach($data->detalle as $value)
        <tr class="detalletable">
            <td>{{$value->guia_remision_detalle_item}}</td>
            <td>{{$value->guia_remision_detalle_descripcion}}</td>
            <td>{{$value->guia_remision_detalle_cantidad}}</td>
            <td class="ulttable">{{$value->guia_remision_detalle_peso}}</td>
        </tr>
        @endforeach
        <tr class="titulotable">
            <td colspan="2"></td>
            <td>Total Peso:</td>
            <td>{{$data->guia_remision_peso}}</td>
        </tr>

        </tbody>
    </table>
</div>
</body>
</html>

