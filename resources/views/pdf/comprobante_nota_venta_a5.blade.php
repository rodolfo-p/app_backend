<!DOCTYPE html>
<html>
<head lang="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title></title>
    <style>
        *{
            margin: 0;padding: 0;font-family:'Source Sans Pro';
        }
        p{
            font-family:sans-serif; font-size: 14px;color: #444444;text-align: left;line-height: 21px;
        }
        body{
            padding: 20px;
            box-sizing: border-box;
            color: #444444;
            font-family:sans-serif; font-size: 14px;
        }
        .wrap{
            width: 1050px;
            max-width: 100%;
            height: auto;
            margin: 0 auto;
            text-align: center;
        }
        .logofactura{
            max-width: 290px;
            height: auto;
            margin: 5px 0;
        }
        table{
            width: 100%;
        }
        table td{
            padding: 10px 15px;
            box-sizing: border-box;
        }
        .tabletop td{
            text-align: center;
            font-size: 14px;
            padding: 0 0 20px 0;
        }
        .tablareceipt{
            width: 60%;
        }
        .tablareceipt td{
            text-align: center;
            width: 200px;
        }
        .tablareceipt td p{
            text-transform: uppercase;
            font-size: 10px;
            line-height: 10px;
            font-weight: bold;
            color: #5e5e5e;
            padding: 0;
            margin: 0;
            text-align: center;
        }
        .tablareceipt i{
            color: #444444;
        }
        .datoruc{
            width: auto;
            float: right;
            border: solid 1px #000;
        }
        .datoruc p{
            font-size: 10px !important;
            line-height: 10px !important;
            margin: 1px 0 !important;
        }
        .iconoimpresion a{
            text-align: right;
        }

        .tabladatos td{
            width: inherit;
            padding: 0;
        }
        .tabladatos p{
            text-align: center;
            color: #444444;
            text-align: left;
        }
        .tdatoslabel{
            padding: 0;
        }
        .tdatodlabeldos{

        }
        .tdatoslabel p{
            width: auto !important;
            float: left;
            background: #fff;
            position: relative;
            padding: 0 5px 0 0;
        }
        .tdatoslabel p:after{
            content: "";
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #fff;
            z-index: 2;
        }
        .tablageneral{
            border: solid 1px #a09f9f;
            width: 100%;
            height: auto;
            margin: 20px 0 0 0;
        }
        .invoice{
            width: 200px;
            text-align: right;
        }
        .titulotable{
            font-weight: bold;
            border-spacing: 0;
            background: #f4f4f4;
        }
        .detalletable td, .titulotable td{
            text-align: left;
            vertical-align: middle;
            border-right: solid 1px #a09f9f;
        }
        .ulttable{
            border-right: none !important;
        }
        .titulotable td{
            border-bottom: solid 1px #a09f9f;
        }
        .nombredetalle, .cantidadtabla{
            width: 50px;
            text-align: left !important;
        }
        .preciotabla{
            width: 106px;
        }
        .sinborde td{
            border-top: solid 0px #d7d6d6 !important;
        }
        .tablegracias{
            margin: 30px 0 0 0;
        }
        .tablegracias td{
            text-align: left;
            font-weight: normal;
            font-size: 15px;
            padding: 0;
        }
        .tablacostos{
            margin: 0 0 0 1px;
            border-top: none;
            border-bottom: none;
            border-left: none;
        }
        .tablacostos td{
            position: relative;
        }
        .imprenta{
            width: 280px;
            border-right: none !important;
        }
        .imprenta p{
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
        .cancelado{
            text-align: center !important;
        }
        .cancelado p{
            width: auto;
            padding: 0 60px;
            border-top: dashed 1px rgba(0,0,0,0.9);
            text-align: center;
            display: inline-block;
            float: none;
            color: #444444;
        }
        .fintabla{
            border-bottom: solid 1px #a09f9f !important;
        }
        .boletanombre p{
            font-size: 15px !important;
            line-height: 22px !important;
            text-transform: none !important;
            font-weight: normal !important;
        }
        .tdatofecha{
            width: 160px !important;
            border-bottom: dashed 1px #000 !important;
            margin: 5px 0 !important;
        }
        .tdatomes{
            width: 110px !important;
        }
        .tdatonombre{
            width: 700px !important;
            border-bottom: dashed 1px #000 !important;
            margin: 5px 0 !important;
        }
        .sinborde{
            border-right: none !important;
        }
        .fintablados{
            background: #f4f4f4;
        }
        .tablacan td{
            width: 33.3%;
        }
        .tablacan p{
            text-align: center;
            line-height: 30px;
            position: relative;
        }
        .tablacan p span, .caneladotd span{
            width: 200px;
            height: 1px;
            position: absolute;
            bottom: 30px;
            left: 50%;
            margin-left: -100px;
            border-top: dashed 1px #000 !important;
        }
        .tdatoemision{
            width: 970px !important;
        }
        .tdcanc{
            text-align: center;
        }
        .caneladotd{
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: auto;
            text-align: center;
        }
        .tdatocre{
            width: calc(50% - 20px) !important;
            margin: 0 20px 0 0 !important;
        }
        .tdatocons{
            border-bottom: none !important;
        }
        .flotantediv{
            position: absolute;
            top: 0;
            left: 0;
        }
        .motivcred{
            width: auto;
            padding: 5px 0;
        }
        .motivcred p{
            font-size: 12px;
        }
        .motivcred label{
            width: 200px;
        }

        .reception{
            width: calc(100% - 20px);
            height: auto;
            padding: 5px 0;
            margin: 5px 0;
            border-top: dashed 1px rgba(0,0,0,0.5);
            float: left;
            box-sizing: border-box;
        }
        .datoappcred{
            border-bottom: solid 1px #000;
            position: relative;
            min-height: 21px;
            width: 800px;
            float: left;
        }
        .datoappcred span{
            position: absolute;
            background: #fff;
            bottom: -1px;
            left: 0;
            z-index: 1;
        }
        .datoappcred2{
            width: 180px;
        }
        .datoappcred3{
            width: 250px;
        }
        .tablacandos{
            margin: 70px 0 0 0;
            border: none !important;
        }
        .tablacandos td{
            border: none !important;
        }
        .codigofac{
            border-radius: 20px;
            border: solid 1px rgba(0,0,0,0.3);
            padding: 5px 10px;
            font-size: 13px;
            font-weight: bold;
            background: rgba(0,0,0,0.1);
        }
        .codigoqr{
            float: right;
            width: 70px;
        }
        .codigoqr img{
            width: 100%;
            height: auto;
        }
        input[type=checkbox] { display: inline; }
    </style>
</head>
<body>


<body>
<table class="tablareceipt">
    <tbody>
    <tr>
        <td>
            <p> {{$data->empresa->emp_empresa_razon_social}}</p>
            <p style="text-transform: none !important;"> {{$data->alm_almacen_direccion}}</p>
            <p style="text-transform: none !important;">Cel.: {{$data->alm_almacen_telefono}}
            </p>
            <p style="text-transform: none !important;"> Email: {{$data->alm_almacen_emaill}}</p>
        </td>
        <td class="datoruc">
            <p>R.U.C.: {{$data->empresa->emp_empresa_ruc}}</p>
            <p>NOTA DE PEDIDO</p>
            <p>NÚMERO: {{$data->vent_venta_numero}}</p>
        </td>
    </tr>
    </tbody>
</table>

<table class="tabladatos">
    <tbody>
    <tr>
        <td class="tdatoslabel">
            <div style="padding-bottom: 10px; padding-right: 19px;">
                <span style="float: left;">Nombre Cliente:</span>
                <div style="margin-left: 105px; border-bottom: solid 1px #000;">{{$data->cliente}}</div>
            </div>
        </td>
        <td class="tdatoslabel tdatodlabeldos">
            <div>
                <span style="float: left;">Fecha de Emisión:</span>
                <div style=" border-bottom: solid 1px #000;"> {{$data->vent_venta_fecha}}</div>
            </div>
        </td>
    </tr>
    <tr>
        <td class="tdatoslabel">
            <div style="padding-bottom: 10px; padding-right: 19px;">
                <span style="float: left;">DNI N°:</span>
                <div style="margin-left: 105px; border-bottom: solid 1px #000;"> {{$data->vent_venta_cliente_numero_documento}}</div>
            </div>
        </td>
        <td class="tdatoslabel tdatodlabeldos">
            <div>
                <span style="float: left;">Moneda: </span>
                <div style=" border-bottom: solid 1px #000;">Soles</div>
            </div>
        </td>
    </tr>
    <tr>
        <td class="tdatoslabel">
            <div style="padding-bottom: 10px; padding-right: 19px;">
                <span style="float: left;">Dirección:</span>
                <div style="margin-left: 105px; border-bottom: solid 1px #000;">  {{$data->cliente_direccion}}
                </div>
            </div>
        </td>
    </tr>
    </tbody>
</table>

<div class="tablageneral">
    <table cellpadding="0" cellspacing="0">
        <tbody>

        <tr class="titulotable">
            <td class="cantidadtabla">Cantidad</td>
            <td>Descripción</td>
            <td class="preciotabla">Precio Unitario</td>
            <td class="preciotabla ulttable">Valor de venta</td>
        </tr>
        @foreach($data->detalle as $value)
            <tr class="detalletable">
                <td class="nombredetalle">{{$value->vent_venta_detalle_cantidad}}</td>
                <td>{{$value->alm_producto_nombre}}</td>
                <td>{{$value->vent_venta_detalle_precio_unitario}}</td>
                <td class="ulttable">{{$value->vent_venta_detalle_precio_cobro}}</td>
            </tr>
        @endforeach

        </tbody>
    </table>
</div>
<div class="tablageneral tablacostos tablaboleta">
    <table cellpadding="0" cellspacing="0">
        <tbody>
        <tr class="detalletable">
            <td colspan="2">SON: {{$data->vent_venta_precio_cobrado_letras}} y 00/100 soles</td>
            <td class="preciotabla fintabla fintabla fintablados"><b>TOTAL</b></td>
            <td class="preciotabla ulttable fintabla">{{$data->vent_venta_precio_cobrado}}</td>
        </tr>
        </tbody>
    </table>
</div>

</body>
</html>

