<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 13/12/18
 * Time: 11:28 AM
 */

namespace App\Http\Controllers\FacturacionElectronica\Controllers;

use App\Http\Controllers\Controller;

use Exception;
use App\Http\Requests;
use Illuminate\Http\Request;
use ZipArchive;


class apisunat_2_1 extends Controller
{
    public static function procesar_boleta_factura_nuevo($cabecera, $detalle, $ruta)
    {
        $validacion = new validaciondedatos();
        $doc = new \DOMDocument();
        $doc->formatOutput = FALSE;
        $doc->preserveWhiteSpace = TRUE;
        $xmlCPE = '<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
   <ext:UBLExtensions>
      <ext:UBLExtension>
         <ext:ExtensionContent />
      </ext:UBLExtension>
   </ext:UBLExtensions>
   <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
   <cbc:CustomizationID>2.0</cbc:CustomizationID>
   <cbc:ID>' . $cabecera["NRO_COMPROBANTE"] . '</cbc:ID>
   <cbc:IssueDate>' . $cabecera["FECHA_DOCUMENTO"] . '</cbc:IssueDate>
   <cbc:IssueTime>00:00:00</cbc:IssueTime>
   <cbc:DueDate>' . $cabecera["FECHA_VTO"] . '</cbc:DueDate>
   <cbc:InvoiceTypeCode listID="0101">' . $cabecera["COD_TIPO_DOCUMENTO"] . '</cbc:InvoiceTypeCode>
   <cbc:Note languageLocaleID="1000"><![CDATA[SON ' . $cabecera["TOTAL_LETRAS"] . ']]></cbc:Note>
   <cbc:DocumentCurrencyCode>' . $cabecera["COD_MONEDA"] . '</cbc:DocumentCurrencyCode>
   <cac:Signature>
      <cbc:ID>' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
      <cbc:Note>GREENTER</cbc:Note>
      <cac:SignatoryParty>
         <cac:PartyIdentification>
            <cbc:ID>' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
         </cac:PartyIdentification>
         <cac:PartyName>
            <cbc:Name><![CDATA[' . $cabecera["RAZON_SOCIAL_EMPRESA"] . ']]></cbc:Name>
         </cac:PartyName>
      </cac:SignatoryParty>
      <cac:DigitalSignatureAttachment>
         <cac:ExternalReference>
            <cbc:URI>#' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:URI>
         </cac:ExternalReference>
      </cac:DigitalSignatureAttachment>
   </cac:Signature>
   <cac:AccountingSupplierParty>
      <cac:Party>
         <cac:PartyIdentification>
            <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_EMPRESA"] . '">' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
         </cac:PartyIdentification>
         <cac:PartyName>
            <cbc:Name><![CDATA[' . $cabecera["NOMBRE_COMERCIAL_EMPRESA"] . ']]></cbc:Name>
         </cac:PartyName>
         <cac:PartyLegalEntity>
            <cbc:RegistrationName><![CDATA[' . $cabecera["RAZON_SOCIAL_EMPRESA"] . ']]></cbc:RegistrationName>
            <cac:RegistrationAddress>
               <cbc:ID>' . $cabecera["CODIGO_UBIGEO"] . '</cbc:ID>
               <cbc:AddressTypeCode>0001</cbc:AddressTypeCode>
               <cbc:CitySubdivisionName>NONE</cbc:CitySubdivisionName>
               <cbc:CityName>' . $cabecera["PROVINCIA_EMPRESA"] . '</cbc:CityName>
               <cbc:CountrySubentity>' . $cabecera["DEPARTAMENTO_EMPRESA"] . '</cbc:CountrySubentity>
               <cbc:District>' . $cabecera["DISTRITO_EMPRESA"] . '</cbc:District>
               <cac:AddressLine>
                  <cbc:Line><![CDATA[' . $cabecera["DIRECCION_EMPRESA"] . ']]></cbc:Line>
               </cac:AddressLine>
               <cac:Country>
                  <cbc:IdentificationCode>' . $cabecera["CODIGO_PAIS_EMPRESA"] . '</cbc:IdentificationCode>
               </cac:Country>
            </cac:RegistrationAddress>
         </cac:PartyLegalEntity>
      </cac:Party>
   </cac:AccountingSupplierParty>
   <cac:AccountingCustomerParty>
      <cac:Party>
         <cac:PartyIdentification>
            <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_CLIENTE"] . '">' . $cabecera["NRO_DOCUMENTO_CLIENTE"] . '</cbc:ID>
         </cac:PartyIdentification>
         <cac:PartyLegalEntity>
            <cbc:RegistrationName><![CDATA[' . $cabecera["RAZON_SOCIAL_CLIENTE"] . ']]></cbc:RegistrationName>
            <cac:RegistrationAddress>
               <cac:AddressLine>
                  <cbc:Line><![CDATA[' . $cabecera["DIRECCION_EMPRESA"] . ']]></cbc:Line>
               </cac:AddressLine>
               <cac:Country>
                  <cbc:IdentificationCode>' . $cabecera["CODIGO_PAIS_EMPRESA"] . '</cbc:IdentificationCode>
               </cac:Country>
            </cac:RegistrationAddress>
         </cac:PartyLegalEntity>
      </cac:Party>
   </cac:AccountingCustomerParty>';
        if ($cabecera["TOTAL_DESCUENTO"] !== "0.00") {
            $xmlCPE = $xmlCPE . '<cac:AllowanceCharge>
        <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
            <cbc:AllowanceChargeReasonCode>00</cbc:AllowanceChargeReasonCode>
                <cbc:MultiplierFactorNumeric>' . $cabecera["PROCENTAJE_DESCUENTO"] . '</cbc:MultiplierFactorNumeric>
            <cbc:Amount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_DESCUENTO"] . '</cbc:Amount>
        <cbc:BaseAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_SIN_DESCUENTO"] . '</cbc:BaseAmount>
        </cac:AllowanceCharge>';
        }
        $xmlCPE = $xmlCPE . '
   <cac:TaxTotal>
           <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_IGV"] . '</cbc:TaxAmount>';
        if ($cabecera["TOTAL_IGV"] !== "0.00") {
            $xmlCPE = $xmlCPE .
                '<cac:TaxSubtotal>
         <cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_GRAVADAS"] . '</cbc:TaxableAmount>
         <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_IGV"] . '</cbc:TaxAmount>
         <cac:TaxCategory>
            <cac:TaxScheme>
               <cbc:ID>1000</cbc:ID>
               <cbc:Name>IGV</cbc:Name>
               <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
            </cac:TaxScheme>
         </cac:TaxCategory>
      </cac:TaxSubtotal>';
        }
        if ($cabecera["TOTAL_EXONERADAS"] !== "0.00") {
            $xmlCPE = $xmlCPE . '<cac:TaxSubtotal>
         <cbc:TaxableAmount currencyID="PEN">' . $cabecera["TOTAL_EXONERADAS"] . '</cbc:TaxableAmount>
         <cbc:TaxAmount currencyID="PEN">0.00</cbc:TaxAmount>
         <cac:TaxCategory>
            <cac:TaxScheme>
               <cbc:ID>9997</cbc:ID>
               <cbc:Name>EXO</cbc:Name>
               <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
            </cac:TaxScheme>
         </cac:TaxCategory>
      </cac:TaxSubtotal>';
        }
        if ($cabecera["TOTAL_INAFECTA"] !== "0.00") {
            $xmlCPE = $xmlCPE . '<cac:TaxSubtotal>
         <cbc:TaxableAmount currencyID="PEN">' . $cabecera["TOTAL_INAFECTA"] . '</cbc:TaxableAmount>
         <cbc:TaxAmount currencyID="PEN">0.00</cbc:TaxAmount>
         <cac:TaxCategory>
            <cac:TaxScheme>
               <cbc:ID>9998</cbc:ID>
               <cbc:Name>INA</cbc:Name>
               <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
            </cac:TaxScheme>
         </cac:TaxCategory>
      </cac:TaxSubtotal>';
        }
        $xmlCPE = $xmlCPE .
            '</cac:TaxTotal>
   <cac:LegalMonetaryTotal>
      <cbc:LineExtensionAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["SUB_TOTAL"] . '</cbc:LineExtensionAmount>
         <cbc:TaxInclusiveAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL"] . '</cbc:TaxInclusiveAmount>
         <cbc:AllowanceTotalAmount currencyID="PEN">' . $cabecera["TOTAL_DESCUENTO"] . '</cbc:AllowanceTotalAmount>
         <cbc:ChargeTotalAmount currencyID="' . $cabecera["COD_MONEDA"] . '">0.00</cbc:ChargeTotalAmount>
        <cbc:PrepaidAmount currencyID="' . $cabecera["COD_MONEDA"] . '">0.00</cbc:PrepaidAmount>
        <cbc:PayableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL"] . '</cbc:PayableAmount>
   </cac:LegalMonetaryTotal>';

        for ($i = 0; $i < count($detalle); $i++) {
            if ($detalle[$i]->COD_TIPO_OPERACION_DET == "10") {
                $xmlCPE = $xmlCPE . '<cac:InvoiceLine>
      <cbc:ID>' . $detalle[$i]->ITEM_DET . '</cbc:ID>
      <cbc:InvoicedQuantity unitCode="' . $detalle[$i]->UNIDAD_MEDIDA_DET . '">' . $detalle[$i]->CANTIDAD_DET . '</cbc:InvoicedQuantity>
      <cbc:LineExtensionAmount currencyID="PEN">' . $detalle[$i]->IMPORTE_DET . '</cbc:LineExtensionAmount>
      <cac:PricingReference>
         <cac:AlternativeConditionPrice>
            <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->SUB_TOTAL_DET . '</cbc:PriceAmount>
            <cbc:PriceTypeCode>' . $detalle[$i]->PRECIO_TIPO_CODIGO . '</cbc:PriceTypeCode>
         </cac:AlternativeConditionPrice>
      </cac:PricingReference>
      <cac:TaxTotal>
         <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
         <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IMPORTE_DET . '</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="PEN">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
            <cac:TaxCategory>
               <cbc:Percent>' . $cabecera["POR_IGV"] . '</cbc:Percent>
               <cbc:TaxExemptionReasonCode>' . $detalle[$i]->COD_TIPO_OPERACION_DET . '</cbc:TaxExemptionReasonCode>
               <cac:TaxScheme>
                  <cbc:ID>1000</cbc:ID>
                  <cbc:Name>IGV</cbc:Name>
                  <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
               </cac:TaxScheme>
            </cac:TaxCategory>
         </cac:TaxSubtotal>
      </cac:TaxTotal>
      <cac:Item>
         <cbc:Description><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]->DESCRIPCION_DET)) ? $detalle[$i]->DESCRIPCION_DET : "") . ']]></cbc:Description>
         <cac:SellersItemIdentification>
            <cbc:ID>' . $validacion->replace_invalid_caracters((isset($detalle[$i]->CODIGO_DET)) ? $detalle[$i]->CODIGO_DET : "") . '</cbc:ID>
         </cac:SellersItemIdentification>
      </cac:Item>
      <cac:Price>
         <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->PRECIO_DET . '</cbc:PriceAmount>
      </cac:Price>
   </cac:InvoiceLine>';
            } else if ($detalle[$i]->COD_TIPO_OPERACION_DET == "20") {
                $xmlCPE = $xmlCPE . '<cac:InvoiceLine>
      <cbc:ID>' . $detalle[$i]->ITEM_DET . '</cbc:ID>
      <cbc:InvoicedQuantity unitCode="' . $detalle[$i]->UNIDAD_MEDIDA_DET . '">' . $detalle[$i]->CANTIDAD_DET . '</cbc:InvoicedQuantity>
      <cbc:LineExtensionAmount currencyID="PEN">' . $detalle[$i]->IMPORTE_DET . '</cbc:LineExtensionAmount>
      <cac:PricingReference>
         <cac:AlternativeConditionPrice>
            <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->SUB_TOTAL_DET . '</cbc:PriceAmount>
            <cbc:PriceTypeCode>' . $detalle[$i]->PRECIO_TIPO_CODIGO . '</cbc:PriceTypeCode>
         </cac:AlternativeConditionPrice>
      </cac:PricingReference>
      <cac:TaxTotal>
         <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
         <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IMPORTE_DET . '</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="PEN">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
            <cac:TaxCategory>
               <cbc:Percent>' . $cabecera["POR_IGV"] . '</cbc:Percent>
               <cbc:TaxExemptionReasonCode>' . $detalle[$i]->COD_TIPO_OPERACION_DET . '</cbc:TaxExemptionReasonCode>
               <cac:TaxScheme>
                  <cbc:ID>9997</cbc:ID>
                  <cbc:Name>EXO</cbc:Name>
                  <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
               </cac:TaxScheme>
            </cac:TaxCategory>
         </cac:TaxSubtotal>
      </cac:TaxTotal>
      <cac:Item>
         <cbc:Description><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]->DESCRIPCION_DET)) ? $detalle[$i]->DESCRIPCION_DET : "") . ']]></cbc:Description>
         <cac:SellersItemIdentification>
            <cbc:ID>' . $validacion->replace_invalid_caracters((isset($detalle[$i]->CODIGO_DET)) ? $detalle[$i]->CODIGO_DET : "") . '</cbc:ID>
         </cac:SellersItemIdentification>
      </cac:Item>
      <cac:Price>
         <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->PRECIO_DET . '</cbc:PriceAmount>
      </cac:Price>
   </cac:InvoiceLine>';
            } else if ($detalle[$i]->COD_TIPO_OPERACION_DET == "30") {
                $xmlCPE = $xmlCPE . '<cac:InvoiceLine>
      <cbc:ID>' . $detalle[$i]->ITEM_DET . '</cbc:ID>
      <cbc:InvoicedQuantity unitCode="' . $detalle[$i]->UNIDAD_MEDIDA_DET . '">' . $detalle[$i]->CANTIDAD_DET . '</cbc:InvoicedQuantity>
      <cbc:LineExtensionAmount currencyID="PEN">' . $detalle[$i]->IMPORTE_DET . '</cbc:LineExtensionAmount>
      <cac:PricingReference>
         <cac:AlternativeConditionPrice>
            <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->SUB_TOTAL_DET . '</cbc:PriceAmount>
            <cbc:PriceTypeCode>' . $detalle[$i]->PRECIO_TIPO_CODIGO . '</cbc:PriceTypeCode>
         </cac:AlternativeConditionPrice>
      </cac:PricingReference>
      <cac:TaxTotal>
         <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
         <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IMPORTE_DET . '</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="PEN">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
            <cac:TaxCategory>
               <cbc:Percent>' . $cabecera["POR_IGV"] . '</cbc:Percent>
               <cbc:TaxExemptionReasonCode>' . $detalle[$i]->COD_TIPO_OPERACION_DET . '</cbc:TaxExemptionReasonCode>
               <cac:TaxScheme>
                  <cbc:ID>9998</cbc:ID>
                  <cbc:Name>INA</cbc:Name>
                  <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
               </cac:TaxScheme>
            </cac:TaxCategory>
         </cac:TaxSubtotal>
      </cac:TaxTotal>
      <cac:Item>
         <cbc:Description><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]->DESCRIPCION_DET)) ? $detalle[$i]->DESCRIPCION_DET : "") . ']]></cbc:Description>
         <cac:SellersItemIdentification>
            <cbc:ID>' . $validacion->replace_invalid_caracters((isset($detalle[$i]->CODIGO_DET)) ? $detalle[$i]->CODIGO_DET : "") . '</cbc:ID>
         </cac:SellersItemIdentification>
      </cac:Item>
      <cac:Price>
         <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->PRECIO_DET . '</cbc:PriceAmount>
      </cac:Price>
   </cac:InvoiceLine>';
            }

        }
        $xmlCPE = $xmlCPE . '</Invoice>';
        try {
            $doc->loadXML($xmlCPE);
            $doc->save($ruta . '.XML');
            $resp['respuesta'] = 'ok';
            $resp['url_xml'] = $ruta . '.XML';
        } catch (Exception $exception) {
            $resp['respuesta'] = 'error';
            $resp['url_xml'] = "";
        }
        return $resp;
    }

    public static function crear_xml_factura($cabecera, $detalle, $ruta)
    {
        $validacion = new validaciondedatos();
        $doc = new \DOMDocument();
        $doc->formatOutput = FALSE;
        $doc->preserveWhiteSpace = TRUE;
        //$doc->encoding = 'ISO-8859-1';
        $doc->encoding = 'utf-8';

        $xmlCPE = '<?xml version="1.0" encoding = "ISO-8859-1"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
         xmlns:ccts="urn:un:unece:uncefact:documentation:2"
         xmlns:cec="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
         xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
         xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
         xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2"
         xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1"
         xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2"
         xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<ext:UBLExtensions>
		<ext:UBLExtension>
			<ext:ExtensionContent>
			</ext:ExtensionContent>
		</ext:UBLExtension>
	</ext:UBLExtensions>
	<cbc:UBLVersionID>2.1</cbc:UBLVersionID>
	<cbc:CustomizationID schemeAgencyName="PE:SUNAT">2.0</cbc:CustomizationID>
	<cbc:ProfileID schemeName="Tipo de Operacion" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51">' . $cabecera["TIPO_OPERACION"] . '</cbc:ProfileID>
	<cbc:ID>' . $cabecera["NRO_COMPROBANTE"] . '</cbc:ID>
	<cbc:IssueDate>' . $cabecera["FECHA_DOCUMENTO"] . '</cbc:IssueDate>
	<cbc:IssueTime>00:00:00</cbc:IssueTime>
	<cbc:DueDate>' . $cabecera["FECHA_VTO"] . '</cbc:DueDate>
	<cbc:InvoiceTypeCode listAgencyName="PE:SUNAT" listName="SUNAT:Identificador de Tipo de Documento" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01" listID="0101" name="Tipo de Operacion" listSchemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51">' . $cabecera["COD_TIPO_DOCUMENTO"] . '</cbc:InvoiceTypeCode>';
        if ($cabecera["TOTAL_LETRAS"] <> "") {
            $xmlCPE = $xmlCPE .
                '<cbc:Note languageLocaleID="1000">' . $cabecera["TOTAL_LETRAS"] . '</cbc:Note>';
        }
        $xmlCPE = $xmlCPE .
            '<cbc:DocumentCurrencyCode listID="ISO 4217 Alpha" listName="Currency" listAgencyName="United Nations Economic Commission for Europe">' . $cabecera["COD_MONEDA"] . '</cbc:DocumentCurrencyCode>
            <cbc:LineCountNumeric>' . count($detalle) . '</cbc:LineCountNumeric>';
        if ($cabecera["NRO_OTR_COMPROBANTE"] <> "") {
            $xmlCPE = $xmlCPE .
                '<cac:OrderReference>
                    <cbc:ID>' . $cabecera["NRO_OTR_COMPROBANTE"] . '</cbc:ID>
            </cac:OrderReference>';
        }
        if ($cabecera["NRO_GUIA_REMISION"] <> "") {
            $xmlCPE = $xmlCPE .
                '<cac:DespatchDocumentReference>
		<cbc:ID>' . $cabecera["NRO_GUIA_REMISION"] . '</cbc:ID>
		<cbc:IssueDate>' . $cabecera["FECHA_GUIA_REMISION"] . '</cbc:IssueDate>
		<cbc:DocumentTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Documento" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01">' . $cabecera["COD_GUIA_REMISION"] . '</cbc:DocumentTypeCode>
            </cac:DespatchDocumentReference>';
        }
        $xmlCPE = $xmlCPE .
            '<cac:Signature>
		<cbc:ID>' . $cabecera["NRO_COMPROBANTE"] . '</cbc:ID>
		<cac:SignatoryParty>
			<cac:PartyIdentification>
				<cbc:ID>' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
			</cac:PartyIdentification>
			<cac:PartyName>
				<cbc:Name>' . $cabecera["RAZON_SOCIAL_EMPRESA"] . '</cbc:Name>
			</cac:PartyName>
		</cac:SignatoryParty>
		<cac:DigitalSignatureAttachment>
			<cac:ExternalReference>
				<cbc:URI>#' . $cabecera["NRO_COMPROBANTE"] . '</cbc:URI>
			</cac:ExternalReference>
		</cac:DigitalSignatureAttachment>
	</cac:Signature>
	<cac:AccountingSupplierParty>
		<cac:Party>
			<cac:PartyIdentification>
				<cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_EMPRESA"] . '" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
			</cac:PartyIdentification>
			<cac:PartyName>
				<cbc:Name><![CDATA[' . $cabecera["NOMBRE_COMERCIAL_EMPRESA"] . ']]></cbc:Name>
			</cac:PartyName>
			<cac:PartyTaxScheme>
				<cbc:RegistrationName><![CDATA[' . $cabecera["RAZON_SOCIAL_EMPRESA"] . ']]></cbc:RegistrationName>
				<cbc:CompanyID schemeID="' . $cabecera["TIPO_DOCUMENTO_EMPRESA"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:CompanyID>
				<cac:TaxScheme>
					<cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_EMPRESA"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
				</cac:TaxScheme>
			</cac:PartyTaxScheme>
			<cac:PartyLegalEntity>
				<cbc:RegistrationName><![CDATA[' . $cabecera["RAZON_SOCIAL_EMPRESA"] . ']]></cbc:RegistrationName>
				<cac:RegistrationAddress>
					<cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI" />
					<cbc:AddressTypeCode listAgencyName="PE:SUNAT" listName="Establecimientos anexos">' . $cabecera["CODIGO_UBIGEO"] . '</cbc:AddressTypeCode>
					<cbc:CityName><![CDATA[' . $cabecera["DEPARTAMENTO_EMPRESA"] . ']]></cbc:CityName>
					<cbc:CountrySubentity><![CDATA[' . $cabecera["PROVINCIA_EMPRESA"] . ']]></cbc:CountrySubentity>
					<cbc:District><![CDATA[' . $cabecera["DISTRITO_EMPRESA"] . ']]></cbc:District>
					<cac:AddressLine>
						<cbc:Line><![CDATA[' . $cabecera["DIRECCION_EMPRESA"] . ']]></cbc:Line>
					</cac:AddressLine>
					<cac:Country>
						<cbc:IdentificationCode listID="ISO 3166-1" listAgencyName="United Nations Economic Commission for Europe" listName="Country">' . $cabecera["CODIGO_PAIS_EMPRESA"] . '</cbc:IdentificationCode>
					</cac:Country>
				</cac:RegistrationAddress>
			</cac:PartyLegalEntity>
			<cac:Contact>
				<cbc:Name><![CDATA[' . $cabecera["CONTACTO_EMPRESA"] . ']]></cbc:Name>
			</cac:Contact>
		</cac:Party>
	</cac:AccountingSupplierParty>
	<cac:AccountingCustomerParty>
		<cac:Party>
			<cac:PartyIdentification>
				<cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_CLIENTE"] . '" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $cabecera["NRO_DOCUMENTO_CLIENTE"] . '</cbc:ID>
			</cac:PartyIdentification>
			<cac:PartyName>
				<cbc:Name><![CDATA[' . $cabecera["RAZON_SOCIAL_CLIENTE"] . ']]></cbc:Name>
			</cac:PartyName>
			<cac:PartyTaxScheme>
				<cbc:RegistrationName><![CDATA[' . $cabecera["RAZON_SOCIAL_CLIENTE"] . ']]></cbc:RegistrationName>
				<cbc:CompanyID schemeID="' . $cabecera["TIPO_DOCUMENTO_CLIENTE"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $cabecera["NRO_DOCUMENTO_CLIENTE"] . '</cbc:CompanyID>
				<cac:TaxScheme>
					<cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_CLIENTE"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $cabecera["NRO_DOCUMENTO_CLIENTE"] . '</cbc:ID>
				</cac:TaxScheme>
			</cac:PartyTaxScheme>
			<cac:PartyLegalEntity>
				<cbc:RegistrationName><![CDATA[' . $cabecera["RAZON_SOCIAL_CLIENTE"] . ']]></cbc:RegistrationName>
				<cac:RegistrationAddress>
					<cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI">' . $cabecera["COD_UBIGEO_CLIENTE"] . '</cbc:ID>
					<cbc:CityName><![CDATA[' . $cabecera["DEPARTAMENTO_CLIENTE"] . ']]></cbc:CityName>
					<cbc:CountrySubentity><![CDATA[' . $cabecera["PROVINCIA_CLIENTE"] . ']]></cbc:CountrySubentity>
					<cbc:District><![CDATA[' . $cabecera["DISTRITO_CLIENTE"] . ']]></cbc:District>
					<cac:AddressLine>
						<cbc:Line><![CDATA[' . $cabecera["DIRECCION_CLIENTE"] . ']]></cbc:Line>
					</cac:AddressLine>
					<cac:Country>
						<cbc:IdentificationCode listID="ISO 3166-1" listAgencyName="United Nations Economic Commission for Europe" listName="Country">' . $cabecera["COD_PAIS_CLIENTE"] . '</cbc:IdentificationCode>
					</cac:Country>
				</cac:RegistrationAddress>
			</cac:PartyLegalEntity>
		</cac:Party>
	</cac:AccountingCustomerParty>
	<cac:AllowanceCharge>
		<cbc:ChargeIndicator>false</cbc:ChargeIndicator>
		<cbc:AllowanceChargeReasonCode listName="Cargo/descuento" listAgencyName="PE:SUNAT" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo53">02</cbc:AllowanceChargeReasonCode>
		<cbc:MultiplierFactorNumeric>0.00</cbc:MultiplierFactorNumeric>
		<cbc:Amount currencyID="' . $cabecera["COD_MONEDA"] . '">0.00</cbc:Amount>
		<cbc:BaseAmount currencyID="' . $cabecera["COD_MONEDA"] . '">0.00</cbc:BaseAmount>
	</cac:AllowanceCharge>
	<cac:TaxTotal>
		<cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_IGV"] . '</cbc:TaxAmount>
		<cac:TaxSubtotal>
			<cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_GRAVADAS"] . '</cbc:TaxableAmount>
			<cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_IGV"] . '</cbc:TaxAmount>
			<cac:TaxCategory>
				<cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">S</cbc:ID>
				<cac:TaxScheme>
					<cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">1000</cbc:ID>
					<cbc:Name>IGV</cbc:Name>
					<cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
				</cac:TaxScheme>
			</cac:TaxCategory>
		</cac:TaxSubtotal>';
        //TOTAL=GRAVADA+IGV+EXONERADA
        //NO ENTRA GRATUITA(INAFECTA) NI DESCUENTO
        //SUB_TOTAL=PRECIO(SIN IGV) * CANTIDAD
        $xmlCPE = $xmlCPE .
            '</cac:TaxTotal>
	<cac:LegalMonetaryTotal>
		<cbc:LineExtensionAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["SUB_TOTAL"] . '</cbc:LineExtensionAmount>
		<cbc:TaxInclusiveAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL"] . '</cbc:TaxInclusiveAmount>
		<cbc:AllowanceTotalAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_DESCUENTO"] . '</cbc:AllowanceTotalAmount>
		<cbc:ChargeTotalAmount currencyID="' . $cabecera["COD_MONEDA"] . '">0.00</cbc:ChargeTotalAmount>
		<cbc:PayableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL"] . '</cbc:PayableAmount>
	</cac:LegalMonetaryTotal>';
        for ($i = 0; $i < count($detalle); $i++) {
            //dd($detalle[$i]);
            // dd($cabecera);
            $xmlCPE = $xmlCPE . '<cac:InvoiceLine>

		<cbc:ID>' . $detalle[$i]->ITEM_DET . '</cbc:ID>
		<cbc:InvoicedQuantity unitCode="' . $detalle[$i]->UNIDAD_MEDIDA_DET . '" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">' . $detalle[$i]->CANTIDAD_DET . '</cbc:InvoicedQuantity>
		<cbc:LineExtensionAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IMPORTE_DET . '</cbc:LineExtensionAmount>
		<cac:PricingReference>
			<cac:AlternativeConditionPrice>
				<cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->PRECIO_DET . '</cbc:PriceAmount>
				<cbc:PriceTypeCode listName="Tipo de Precio" listAgencyName="PE:SUNAT" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">' . $detalle[$i]->PRECIO_TIPO_CODIGO . '</cbc:PriceTypeCode>
			</cac:AlternativeConditionPrice>
		</cac:PricingReference>
		<cac:TaxTotal>
			<cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
			<cac:TaxSubtotal>
				<cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IMPORTE_DET . '</cbc:TaxableAmount>
				<cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
				<cac:TaxCategory>
					<cbc:ID schemeID="UN/ECE 5305" schemeName="Tax Category Identifier" schemeAgencyName="United Nations Economic Commission for Europe">S</cbc:ID>
					<cbc:Percent>' . $cabecera["POR_IGV"] . '</cbc:Percent>
					<cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="SUNAT:Codigo de Tipo de Afectación del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">' . $detalle[$i]->COD_TIPO_OPERACION_DET . '</cbc:TaxExemptionReasonCode>
					<cac:TaxScheme>
						<cbc:ID schemeID="UN/ECE 5153" schemeName="Tax Scheme Identifier" schemeAgencyName="United Nations Economic Commission for Europe">1000</cbc:ID>
						<cbc:Name>IGV</cbc:Name>
						<cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
					</cac:TaxScheme>
				</cac:TaxCategory>
			</cac:TaxSubtotal>
		</cac:TaxTotal>
		<cac:Item>
			<cbc:Description><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]->DESCRIPCION_DET)) ? $detalle[$i]->DESCRIPCION_DET : "") . ']]></cbc:Description>
			<cac:SellersItemIdentification>
				<cbc:ID><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]->CODIGO_DET)) ? $detalle[$i]->CODIGO_DET : "") . ']]></cbc:ID>
			</cac:SellersItemIdentification>
		</cac:Item>
		<cac:Price>
			<cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->PRECIO_SIN_IGV_DET . '</cbc:PriceAmount>
		</cac:Price>
	</cac:InvoiceLine>';
        }

        $xmlCPE = $xmlCPE . '</Invoice>';
        $doc->loadXML($xmlCPE);
        //dd($doc);
        //dd($ruta);
        $doc->save($ruta . '.XML');
        $resp['respuesta'] = 'ok';
        $resp['url_xml'] = $ruta . '.XML';
        return $resp;
    }


    public static function xml_nota_credito($cabecera, $detalle, $ruta)
    {
        $validacion = new validaciondedatos();
        $doc = new \DOMDocument();
        $doc->formatOutput = FALSE;
        $doc->preserveWhiteSpace = TRUE;
        //$doc->encoding = 'ISO-8859-1';
        $doc->encoding = 'utf-8';

        $xmlCPE = '<?xml version="1.0" encoding="UTF-8"?>
<CreditNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2"
xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
    <ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent>
            </ext:ExtensionContent>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
   <cbc:CustomizationID>2.0</cbc:CustomizationID>
   <cbc:ID>' . $cabecera["NRO_COMPROBANTE"] . '</cbc:ID>
   <cbc:IssueDate>' . $cabecera["FECHA_DOCUMENTO"] . '</cbc:IssueDate>
   <cbc:IssueTime>00:00:01</cbc:IssueTime>
   <cbc:Note languageLocaleID="1000"><![CDATA[SON ' . $cabecera["TOTAL_LETRAS"] . ']]></cbc:Note>
   <cbc:DocumentCurrencyCode>' . $cabecera["COD_MONEDA"] . '</cbc:DocumentCurrencyCode>
   <cac:DiscrepancyResponse>
      <cbc:ReferenceID>' . $cabecera["NRO_DOCUMENTO_MODIFICA"] . '</cbc:ReferenceID>
      <cbc:ResponseCode>' . $cabecera["COD_TIPO_MOTIVO"] . '</cbc:ResponseCode>
      <cbc:Description>' . $cabecera["DESCRIPCION_MOTIVO"] . '</cbc:Description>
   </cac:DiscrepancyResponse>
   <cac:BillingReference>
      <cac:InvoiceDocumentReference>
         <cbc:ID>' . $cabecera["NRO_DOCUMENTO_MODIFICA"] . '</cbc:ID>
         <cbc:DocumentTypeCode>' . $cabecera["TIPO_COMPROBANTE_MODIFICA"] . '</cbc:DocumentTypeCode>
      </cac:InvoiceDocumentReference>
   </cac:BillingReference>
   <cac:Signature>
      <cbc:ID>' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
      <cbc:Note>GREENTER</cbc:Note>
      <cac:SignatoryParty>
         <cac:PartyIdentification>
            <cbc:ID>' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
         </cac:PartyIdentification>
         <cac:PartyName>
            <cbc:Name><![CDATA[' . $cabecera["RAZON_SOCIAL_EMPRESA"] . ']]></cbc:Name>
         </cac:PartyName>
      </cac:SignatoryParty>
      <cac:DigitalSignatureAttachment>
         <cac:ExternalReference>
            <cbc:URI>#SIGN-GREEN</cbc:URI>
         </cac:ExternalReference>
      </cac:DigitalSignatureAttachment>
   </cac:Signature>
   <cac:AccountingSupplierParty>
      <cac:Party>
         <cac:PartyIdentification>
            <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_EMPRESA"] . '">' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
         </cac:PartyIdentification>
         <cac:PartyName>
            <cbc:Name><![CDATA[' . $cabecera["NOMBRE_COMERCIAL_EMPRESA"] . ']]></cbc:Name>
         </cac:PartyName>
       <cac:PartyLegalEntity>
            <cbc:RegistrationName><![CDATA[' . $cabecera["RAZON_SOCIAL_EMPRESA"] . ']]></cbc:RegistrationName>
            <cac:RegistrationAddress>
               <cbc:ID>' . $cabecera["CODIGO_UBIGEO"] . '</cbc:ID>
               <cbc:AddressTypeCode>0001</cbc:AddressTypeCode>
               <cbc:CitySubdivisionName>NONE</cbc:CitySubdivisionName>
               <cbc:CityName>' . $cabecera["PROVINCIA_EMPRESA"] . '</cbc:CityName>
               <cbc:CountrySubentity>' . $cabecera["DEPARTAMENTO_EMPRESA"] . '</cbc:CountrySubentity>
               <cbc:District>' . $cabecera["DISTRITO_EMPRESA"] . '</cbc:District>
               <cac:AddressLine>
                  <cbc:Line><![CDATA[' . $cabecera["DIRECCION_EMPRESA"] . ']]></cbc:Line>
               </cac:AddressLine>
               <cac:Country>
                  <cbc:IdentificationCode>' . $cabecera["CODIGO_PAIS_EMPRESA"] . '</cbc:IdentificationCode>
               </cac:Country>
            </cac:RegistrationAddress>
         </cac:PartyLegalEntity>
          </cac:Party>
   </cac:AccountingSupplierParty>
    <cac:AccountingCustomerParty>
      <cac:Party>
         <cac:PartyIdentification>
            <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_CLIENTE"] . '">' . $cabecera["NRO_DOCUMENTO_CLIENTE"] . '</cbc:ID>
         </cac:PartyIdentification>
         <cac:PartyLegalEntity>
            <cbc:RegistrationName><![CDATA[' . $cabecera["RAZON_SOCIAL_CLIENTE"] . ']]></cbc:RegistrationName>
            <cac:RegistrationAddress>
               <cac:AddressLine>
                  <cbc:Line><![CDATA[' . $cabecera["DIRECCION_EMPRESA"] . ']]></cbc:Line>
               </cac:AddressLine>
               <cac:Country>
                  <cbc:IdentificationCode>' . $cabecera["CODIGO_PAIS_EMPRESA"] . '</cbc:IdentificationCode>
               </cac:Country>
            </cac:RegistrationAddress>
         </cac:PartyLegalEntity>
      </cac:Party>
   </cac:AccountingCustomerParty>';
        if ($cabecera["TOTAL_DESCUENTO"] !== "0.00") {
            $xmlCPE = $xmlCPE . '<cac:AllowanceCharge>
        <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
            <cbc:AllowanceChargeReasonCode>00</cbc:AllowanceChargeReasonCode>
                <cbc:MultiplierFactorNumeric>' . $cabecera["PROCENTAJE_DESCUENTO"] . '</cbc:MultiplierFactorNumeric>
            <cbc:Amount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_DESCUENTO"] . '</cbc:Amount>
        <cbc:BaseAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_SIN_DESCUENTO"] . '</cbc:BaseAmount>
        </cac:AllowanceCharge>';
        }
        $xmlCPE = $xmlCPE . '
        <cac:TaxTotal>
           <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_IGV"] . '</cbc:TaxAmount>';
        if ($cabecera["TOTAL_IGV"] !== "0.00") {
            $xmlCPE = $xmlCPE .
                '<cac:TaxSubtotal>
         <cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_GRAVADAS"] . '</cbc:TaxableAmount>
         <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_IGV"] . '</cbc:TaxAmount>
         <cac:TaxCategory>
            <cac:TaxScheme>
               <cbc:ID>1000</cbc:ID>
               <cbc:Name>IGV</cbc:Name>
               <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
            </cac:TaxScheme>
         </cac:TaxCategory>
      </cac:TaxSubtotal>';
        }
        if ($cabecera["TOTAL_EXONERADAS"] !== "0.00") {
            $xmlCPE = $xmlCPE . '<cac:TaxSubtotal>
         <cbc:TaxableAmount currencyID="PEN">' . $cabecera["TOTAL_EXONERADAS"] . '</cbc:TaxableAmount>
         <cbc:TaxAmount currencyID="PEN">0.00</cbc:TaxAmount>
         <cac:TaxCategory>
            <cac:TaxScheme>
               <cbc:ID>9997</cbc:ID>
               <cbc:Name>EXO</cbc:Name>
               <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
            </cac:TaxScheme>
         </cac:TaxCategory>
      </cac:TaxSubtotal>';
        }
        if ($cabecera["TOTAL_INAFECTA"] !== "0.00") {
            $xmlCPE = $xmlCPE . '<cac:TaxSubtotal>
         <cbc:TaxableAmount currencyID="PEN">' . $cabecera["TOTAL_INAFECTA"] . '</cbc:TaxableAmount>
         <cbc:TaxAmount currencyID="PEN">0.00</cbc:TaxAmount>
         <cac:TaxCategory>
            <cac:TaxScheme>
               <cbc:ID>9998</cbc:ID>
               <cbc:Name>INA</cbc:Name>
               <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
            </cac:TaxScheme>
         </cac:TaxCategory>
      </cac:TaxSubtotal>';
        }
        $xmlCPE = $xmlCPE .
            '</cac:TaxTotal>
   <cac:LegalMonetaryTotal>
      <cbc:LineExtensionAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["SUB_TOTAL"] . '</cbc:LineExtensionAmount>
         <cbc:TaxInclusiveAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL"] . '</cbc:TaxInclusiveAmount>
         <cbc:AllowanceTotalAmount currencyID="PEN">' . $cabecera["TOTAL_DESCUENTO"] . '</cbc:AllowanceTotalAmount>
         <cbc:ChargeTotalAmount currencyID="' . $cabecera["COD_MONEDA"] . '">0.00</cbc:ChargeTotalAmount>
        <cbc:PrepaidAmount currencyID="' . $cabecera["COD_MONEDA"] . '">0.00</cbc:PrepaidAmount>
        <cbc:PayableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL"] . '</cbc:PayableAmount>
   </cac:LegalMonetaryTotal>';
        for ($i = 0; $i < count($detalle); $i++) {
            // dd($detalle[$i]);
            if ($detalle[$i]->COD_TIPO_OPERACION_DET == "10") {
                $xmlCPE = $xmlCPE . '
      <cac:CreditNoteLine>
   <cbc:ID>' . $detalle[$i]->ITEM_DET . '</cbc:ID>
      <cbc:CreditedQuantity unitCode="' . $detalle[$i]->UNIDAD_MEDIDA_DET . '">' . $detalle[$i]->CANTIDAD_DET . '</cbc:CreditedQuantity>
      <cbc:LineExtensionAmount currencyID="PEN">' . $detalle[$i]->IMPORTE_DET . '</cbc:LineExtensionAmount>
      <cac:PricingReference>
         <cac:AlternativeConditionPrice>
            <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->SUB_TOTAL_DET . '</cbc:PriceAmount>
            <cbc:PriceTypeCode>' . $detalle[$i]->PRECIO_TIPO_CODIGO . '</cbc:PriceTypeCode>
         </cac:AlternativeConditionPrice>
      </cac:PricingReference>
      <cac:TaxTotal>
         <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
         <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IMPORTE_DET . '</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="PEN">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
            <cac:TaxCategory>
               <cbc:Percent>' . $cabecera["POR_IGV"] . '</cbc:Percent>
               <cbc:TaxExemptionReasonCode>' . $detalle[$i]->COD_TIPO_OPERACION_DET . '</cbc:TaxExemptionReasonCode>
               <cac:TaxScheme>
                  <cbc:ID>1000</cbc:ID>
                  <cbc:Name>IGV</cbc:Name>
                  <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
               </cac:TaxScheme>
            </cac:TaxCategory>
         </cac:TaxSubtotal>
      </cac:TaxTotal>
      <cac:Item>
         <cbc:Description><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]->DESCRIPCION_DET)) ? $detalle[$i]->DESCRIPCION_DET : "") . ']]></cbc:Description>
         <cac:SellersItemIdentification>
            <cbc:ID>' . $validacion->replace_invalid_caracters((isset($detalle[$i]->CODIGO_DET)) ? $detalle[$i]->CODIGO_DET : "") . '</cbc:ID>
         </cac:SellersItemIdentification>
      </cac:Item>
      <cac:Price>
         <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->PRECIO_DET . '</cbc:PriceAmount>
      </cac:Price>
   </cac:CreditNoteLine>';
            } else if ($detalle[$i]->COD_TIPO_OPERACION_DET == "20") {
                $xmlCPE = $xmlCPE . '
      <cac:CreditNoteLine>
       <cbc:ID>' . $detalle[$i]->ITEM_DET . '</cbc:ID>
      <cbc:CreditedQuantity unitCode="' . $detalle[$i]->UNIDAD_MEDIDA_DET . '">' . $detalle[$i]->CANTIDAD_DET . '</cbc:CreditedQuantity>
      <cbc:LineExtensionAmount currencyID="PEN">' . $detalle[$i]->IMPORTE_DET . '</cbc:LineExtensionAmount>
      <cac:PricingReference>
         <cac:AlternativeConditionPrice>
            <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->SUB_TOTAL_DET . '</cbc:PriceAmount>
            <cbc:PriceTypeCode>' . $detalle[$i]->PRECIO_TIPO_CODIGO . '</cbc:PriceTypeCode>
         </cac:AlternativeConditionPrice>
      </cac:PricingReference>
      <cac:TaxTotal>
         <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
         <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IMPORTE_DET . '</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="PEN">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
            <cac:TaxCategory>
               <cbc:Percent>' . $cabecera["POR_IGV"] . '</cbc:Percent>
               <cbc:TaxExemptionReasonCode>' . $detalle[$i]->COD_TIPO_OPERACION_DET . '</cbc:TaxExemptionReasonCode>
               <cac:TaxScheme>
                  <cbc:ID>9997</cbc:ID>
                  <cbc:Name>EXO</cbc:Name>
                  <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
               </cac:TaxScheme>
            </cac:TaxCategory>
         </cac:TaxSubtotal>
      </cac:TaxTotal>
      <cac:Item>
         <cbc:Description><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]->DESCRIPCION_DET)) ? $detalle[$i]->DESCRIPCION_DET : "") . ']]></cbc:Description>
         <cac:SellersItemIdentification>
            <cbc:ID>' . $validacion->replace_invalid_caracters((isset($detalle[$i]->CODIGO_DET)) ? $detalle[$i]->CODIGO_DET : "") . '</cbc:ID>
         </cac:SellersItemIdentification>
      </cac:Item>
      <cac:Price>
         <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->PRECIO_DET . '</cbc:PriceAmount>
      </cac:Price>
   </cac:CreditNoteLine>';
            } else if ($detalle[$i]->COD_TIPO_OPERACION_DET == "30") {
                $xmlCPE = $xmlCPE . '
      <cac:CreditNoteLine>
      <cbc:ID>' . $detalle[$i]->ITEM_DET . '</cbc:ID>
      <cbc:CreditedQuantity unitCode="' . $detalle[$i]->UNIDAD_MEDIDA_DET . '">' . $detalle[$i]->CANTIDAD_DET . '</cbc:CreditedQuantity>
      <cbc:LineExtensionAmount currencyID="PEN">' . $detalle[$i]->IMPORTE_DET . '</cbc:LineExtensionAmount>
      <cac:PricingReference>
         <cac:AlternativeConditionPrice>
            <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->SUB_TOTAL_DET . '</cbc:PriceAmount>
            <cbc:PriceTypeCode>' . $detalle[$i]->PRECIO_TIPO_CODIGO . '</cbc:PriceTypeCode>
         </cac:AlternativeConditionPrice>
      </cac:PricingReference>
      <cac:TaxTotal>
         <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
         <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IMPORTE_DET . '</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="PEN">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
            <cac:TaxCategory>
               <cbc:Percent>' . $cabecera["POR_IGV"] . '</cbc:Percent>
               <cbc:TaxExemptionReasonCode>' . $detalle[$i]->COD_TIPO_OPERACION_DET . '</cbc:TaxExemptionReasonCode>
               <cac:TaxScheme>
                  <cbc:ID>9998</cbc:ID>
                  <cbc:Name>INA</cbc:Name>
                  <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
               </cac:TaxScheme>
            </cac:TaxCategory>
         </cac:TaxSubtotal>
      </cac:TaxTotal>
      <cac:Item>
         <cbc:Description><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]->DESCRIPCION_DET)) ? $detalle[$i]->DESCRIPCION_DET : "") . ']]></cbc:Description>
         <cac:SellersItemIdentification>
            <cbc:ID>' . $validacion->replace_invalid_caracters((isset($detalle[$i]->CODIGO_DET)) ? $detalle[$i]->CODIGO_DET : "") . '</cbc:ID>
         </cac:SellersItemIdentification>
      </cac:Item>
      <cac:Price>
         <cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->PRECIO_DET . '</cbc:PriceAmount>
      </cac:Price>
   </cac:CreditNoteLine>';
            }
        }


        $xmlCPE = $xmlCPE . '</CreditNote>';
        $doc->loadXML($xmlCPE);
        $doc->save($ruta . '.XML');

        $resp['respuesta'] = 'ok';
        $resp['url_xml'] = $ruta . '.XML';
        return $resp;


    }


    public static function crear_xml_nota_credito($cabecera, $detalle, $ruta)
    {
        $validacion = new validaciondedatos();
        $doc = new \DOMDocument();
        $doc->formatOutput = FALSE;
        $doc->preserveWhiteSpace = TRUE;
        //$doc->encoding = 'ISO-8859-1';
        $doc->encoding = 'utf-8';

        $xmlCPE = '<?xml version="1.0" encoding="UTF-8"?>
<CreditNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent>
            </ext:ExtensionContent>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>2.0</cbc:CustomizationID>
    <cbc:ID>' . $cabecera["NRO_COMPROBANTE"] . '</cbc:ID>
    <cbc:IssueDate>' . $cabecera["FECHA_DOCUMENTO"] . '</cbc:IssueDate>
    <cbc:IssueTime>00:00:00</cbc:IssueTime>
    <cbc:DocumentCurrencyCode>' . $cabecera["COD_MONEDA"] . '</cbc:DocumentCurrencyCode>
    <cac:DiscrepancyResponse>
        <cbc:ReferenceID>' . $cabecera["NRO_DOCUMENTO_MODIFICA"] . '</cbc:ReferenceID>
        <cbc:ResponseCode>' . $cabecera["COD_TIPO_MOTIVO"] . '</cbc:ResponseCode>
        <cbc:Description><![CDATA[' . $cabecera["DESCRIPCION_MOTIVO"] . ']]></cbc:Description>
    </cac:DiscrepancyResponse>
    <cac:BillingReference>
        <cac:InvoiceDocumentReference>
            <cbc:ID>' . $cabecera["NRO_DOCUMENTO_MODIFICA"] . '</cbc:ID>
            <cbc:DocumentTypeCode>' . $cabecera["TIPO_COMPROBANTE_MODIFICA"] . '</cbc:DocumentTypeCode>
        </cac:InvoiceDocumentReference>
    </cac:BillingReference>
    <cac:Signature>
        <cbc:ID>IDSignST</cbc:ID>
        <cac:SignatoryParty>
            <cac:PartyIdentification>
                <cbc:ID>' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[' . $cabecera["RAZON_SOCIAL_EMPRESA"] . ']]></cbc:Name>
            </cac:PartyName>
        </cac:SignatoryParty>
        <cac:DigitalSignatureAttachment>
            <cac:ExternalReference>
                <cbc:URI>#SignatureSP</cbc:URI>
            </cac:ExternalReference>
        </cac:DigitalSignatureAttachment>
    </cac:Signature>
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_EMPRESA"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[' . $cabecera["NOMBRE_COMERCIAL_EMPRESA"] . ']]></cbc:Name>
            </cac:PartyName>
            <cac:PartyLegalEntity>
<cbc:RegistrationName><![CDATA[' . $cabecera["RAZON_SOCIAL_EMPRESA"] . ']]></cbc:RegistrationName>
                <cac:RegistrationAddress>
                    <cbc:AddressTypeCode>0001</cbc:AddressTypeCode>
                </cac:RegistrationAddress>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingSupplierParty>
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_CLIENTE"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $cabecera["NRO_DOCUMENTO_CLIENTE"] . '</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
<cbc:RegistrationName><![CDATA[' . $cabecera["RAZON_SOCIAL_CLIENTE"] . ']]></cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_IGV"] . '</cbc:TaxAmount>
        <cac:TaxSubtotal>
<cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_GRAVADAS"] . '</cbc:TaxableAmount>
<cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_IGV"] . '</cbc:TaxAmount>
            <cac:TaxCategory>
                <cac:TaxScheme>
                    <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    <cac:LegalMonetaryTotal>
        <cbc:PayableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL"] . '</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>';


        for ($i = 0; $i < count($detalle); $i++) {
            //  dd($detalle[$i]);
            $xmlCPE = $xmlCPE . '<cac:CreditNoteLine>
        <cbc:ID>' . $detalle[$i]->ITEM_DET . '</cbc:ID>
<cbc:CreditedQuantity unitCode="' . $detalle[$i]->UNIDAD_MEDIDA_DET . '">' . $detalle[$i]->CANTIDAD_DET . '</cbc:CreditedQuantity>
<cbc:LineExtensionAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IMPORTE_DET . '</cbc:LineExtensionAmount>
        <cac:PricingReference>
            <cac:AlternativeConditionPrice>
<cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->PRECIO_DET . '</cbc:PriceAmount>
                <cbc:PriceTypeCode>' . $detalle[$i]->PRECIO_TIPO_CODIGO . '</cbc:PriceTypeCode>
            </cac:AlternativeConditionPrice>
        </cac:PricingReference>
        <cac:TaxTotal>
<cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
            <cac:TaxSubtotal>
<cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IMPORTE_DET . '</cbc:TaxableAmount>
<cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>' . $cabecera["POR_IGV"] . '</cbc:Percent>
<cbc:TaxExemptionReasonCode>' . $detalle[$i]->COD_TIPO_OPERACION_DET . '</cbc:TaxExemptionReasonCode>
                    <cac:TaxScheme>
                        <cbc:ID>1000</cbc:ID>
                        <cbc:Name>IGV</cbc:Name>
                        <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        </cac:TaxTotal>
        <cac:Item>
<cbc:Description><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]->DESCRIPCION_DET)) ? $detalle[$i]->DESCRIPCION_DET : "") . ']]></cbc:Description>
            <cac:SellersItemIdentification>
                <cbc:ID><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]->CODIGO_DET)) ? $detalle[$i]->CODIGO_DET : "") . ']]></cbc:ID>
            </cac:SellersItemIdentification>
        </cac:Item>
        <cac:Price>
<cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->PRECIO_DET . '</cbc:PriceAmount>
        </cac:Price>
    </cac:CreditNoteLine>';
        }

        $xmlCPE = $xmlCPE . '</DebitNote>';
        $doc->loadXML($xmlCPE);
        $doc->save($ruta . '.XML');

        $resp['respuesta'] = 'ok';
        $resp['url_xml'] = $ruta . '.XML';
        return $resp;
    }

    public static function crear_xml_nota_debito($cabecera, $detalle, $ruta)
    {
        $validacion = new validaciondedatos();
        $doc = new \DOMDocument();
        $doc->formatOutput = FALSE;
        $doc->preserveWhiteSpace = TRUE;
        //$doc->encoding = 'ISO-8859-1';
        $doc->encoding = 'utf-8';

        $xmlCPE = '<?xml version="1.0" encoding="UTF-8"?>
<DebitNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent>
            </ext:ExtensionContent>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>2.0</cbc:CustomizationID>
    <cbc:ID>' . $cabecera["NRO_COMPROBANTE"] . '</cbc:ID>
    <cbc:IssueDate>' . $cabecera["FECHA_DOCUMENTO"] . '</cbc:IssueDate>
    <cbc:IssueTime>00:00:00</cbc:IssueTime>
    <cbc:DocumentCurrencyCode>' . $cabecera["COD_MONEDA"] . '</cbc:DocumentCurrencyCode>
    <cac:DiscrepancyResponse>
        <cbc:ReferenceID>' . $cabecera["NRO_DOCUMENTO_MODIFICA"] . '</cbc:ReferenceID>
        <cbc:ResponseCode>' . $cabecera["COD_TIPO_MOTIVO"] . '</cbc:ResponseCode>
        <cbc:Description><![CDATA[' . $cabecera["DESCRIPCION_MOTIVO"] . ']]></cbc:Description>
    </cac:DiscrepancyResponse>
    <cac:BillingReference>
        <cac:InvoiceDocumentReference>
            <cbc:ID>' . $cabecera["NRO_DOCUMENTO_MODIFICA"] . '</cbc:ID>
            <cbc:DocumentTypeCode>' . $cabecera["TIPO_COMPROBANTE_MODIFICA"] . '</cbc:DocumentTypeCode>
        </cac:InvoiceDocumentReference>
    </cac:BillingReference>
    <cac:Signature>
        <cbc:ID>IDSignST</cbc:ID>
        <cac:SignatoryParty>
            <cac:PartyIdentification>
                <cbc:ID>' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[' . $cabecera["RAZON_SOCIAL_EMPRESA"] . ']]></cbc:Name>
            </cac:PartyName>
        </cac:SignatoryParty>
        <cac:DigitalSignatureAttachment>
            <cac:ExternalReference>
                <cbc:URI>#SignatureSP</cbc:URI>
            </cac:ExternalReference>
        </cac:DigitalSignatureAttachment>
    </cac:Signature>
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_EMPRESA"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name><![CDATA[' . $cabecera["NOMBRE_COMERCIAL_EMPRESA"] . ']]></cbc:Name>
            </cac:PartyName>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[' . $cabecera["RAZON_SOCIAL_EMPRESA"] . ']]></cbc:RegistrationName>
                <cac:RegistrationAddress>
                    <cbc:AddressTypeCode>0001</cbc:AddressTypeCode>
                </cac:RegistrationAddress>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingSupplierParty>
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="' . $cabecera["TIPO_DOCUMENTO_CLIENTE"] . '" schemeName="SUNAT:Identificador de Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $cabecera["NRO_DOCUMENTO_CLIENTE"] . '</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
<cbc:RegistrationName><![CDATA[' . $cabecera["RAZON_SOCIAL_CLIENTE"] . ']]></cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_IGV"] . '</cbc:TaxAmount>
        <cac:TaxSubtotal>
<cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_GRAVADAS"] . '</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL_IGV"] . '</cbc:TaxAmount>
            <cac:TaxCategory>
                <cac:TaxScheme>
                    <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    <cac:RequestedMonetaryTotal>
<cbc:PayableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $cabecera["TOTAL"] . '</cbc:PayableAmount>
    </cac:RequestedMonetaryTotal>';

        for ($i = 0; $i < count($detalle); $i++) {
            //dd($detalle[$i]);
            $xmlCPE = $xmlCPE . '
    <cac:DebitNoteLine>
        <cbc:ID>' . $detalle[$i]->ITEM_DET . '</cbc:ID>
<cbc:DebitedQuantity unitCode="' . $detalle[$i]->UNIDAD_MEDIDA_DET . '">' . $detalle[$i]->CANTIDAD_DET . '</cbc:DebitedQuantity>
<cbc:LineExtensionAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IMPORTE_DET . '</cbc:LineExtensionAmount>
        <cac:PricingReference>
            <cac:AlternativeConditionPrice>
<cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->PRECIO_DET . '</cbc:PriceAmount>
<cbc:PriceTypeCode>' . $detalle[$i]->PRECIO_TIPO_CODIGO . '</cbc:PriceTypeCode>
            </cac:AlternativeConditionPrice>
        </cac:PricingReference>
        <cac:TaxTotal>
<cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IMPORTE_DET . '</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->IGV_DET . '</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:Percent>' . $cabecera["POR_IGV"] . '</cbc:Percent>
<cbc:TaxExemptionReasonCode>' . $detalle[$i]->COD_TIPO_OPERACION_DET . '</cbc:TaxExemptionReasonCode>
                    <cac:TaxScheme>
                        <cbc:ID>1000</cbc:ID>
                        <cbc:Name>IGV</cbc:Name>
                        <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        </cac:TaxTotal>

<cac:Item>
<cbc:Description><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]->DESCRIPCION_DET)) ? $detalle[$i]->DESCRIPCION_DET : "") . ']]></cbc:Description>
            <cac:SellersItemIdentification>
                <cbc:ID><![CDATA[' . $validacion->replace_invalid_caracters((isset($detalle[$i]->CODIGO_DET)) ? $detalle[$i]->CODIGO_DET : "") . ']]></cbc:ID>
            </cac:SellersItemIdentification>
        </cac:Item>
<cac:Price>
<cbc:PriceAmount currencyID="' . $cabecera["COD_MONEDA"] . '">' . $detalle[$i]->PRECIO_DET . '</cbc:PriceAmount>
</cac:Price>
    </cac:DebitNoteLine>';
        }

        $xmlCPE = $xmlCPE . '</DebitNote>';

        $doc->loadXML($xmlCPE);
        $doc->save($ruta . '.XML');

        $resp['respuesta'] = 'ok';
        $resp['url_xml'] = $ruta . '.XML';
        return $resp;
    }

    public function crear_xml_resumen_documentos($cabecera, $detalle, $ruta)
    {
        $validacion = new validaciondedatos();
        $doc = new \DOMDocument();
        $doc->formatOutput = FALSE;
        $doc->preserveWhiteSpace = TRUE;
        $doc->encoding = 'ISO-8859-1';
        $xmlCPE = '<?xml version="1.0" encoding="iso-8859-1" standalone="no"?>
        <SummaryDocuments
        xmlns="urn:sunat:names:specification:ubl:peru:schema:xsd:SummaryDocuments-1"
        xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
        xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
        xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
        xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
        xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1"
        xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2"
        xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2">
        <ext:UBLExtensions>
            <ext:UBLExtension>
                            <ext:ExtensionContent>
                </ext:ExtensionContent>
            </ext:UBLExtension>
        </ext:UBLExtensions>
        <cbc:UBLVersionID>2.0</cbc:UBLVersionID>
        <cbc:CustomizationID>1.1</cbc:CustomizationID>
        <cbc:ID>' . $cabecera["CODIGO"] . '-' . $cabecera["SERIE"] . '-' . $cabecera["SECUENCIA"] . '</cbc:ID>
        <cbc:ReferenceDate>' . $cabecera["FECHA_REFERENCIA"] . '</cbc:ReferenceDate>
        <cbc:IssueDate>' . $cabecera["FECHA_DOCUMENTO"] . '</cbc:IssueDate>
        <cac:Signature>
            <cbc:ID>' . $cabecera["CODIGO"] . '-' . $cabecera["SERIE"] . '-' . $cabecera["SECUENCIA"] . '</cbc:ID>
            <cac:SignatoryParty>
                <cac:PartyIdentification>
                    <cbc:ID>' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:ID>
                </cac:PartyIdentification>
                <cac:PartyName>
                    <cbc:Name>' . $cabecera["RAZON_SOCIAL_EMPRESA"] . '</cbc:Name>
                </cac:PartyName>
            </cac:SignatoryParty>
            <cac:DigitalSignatureAttachment>
                <cac:ExternalReference>
                    <cbc:URI>' . $cabecera["CODIGO"] . '-' . $cabecera["SERIE"] . '-' . $cabecera["SECUENCIA"] . '</cbc:URI>
                </cac:ExternalReference>
            </cac:DigitalSignatureAttachment>
        </cac:Signature>
        <cac:AccountingSupplierParty>
            <cbc:CustomerAssignedAccountID>' . $cabecera["NRO_DOCUMENTO_EMPRESA"] . '</cbc:CustomerAssignedAccountID>
            <cbc:AdditionalAccountID>' . $cabecera["TIPO_DOCUMENTO_EMPRESA"] . '</cbc:AdditionalAccountID>
            <cac:Party>
                <cac:PartyLegalEntity>
                    <cbc:RegistrationName>' . $cabecera["RAZON_SOCIAL_EMPRESA"] . '</cbc:RegistrationName>
                </cac:PartyLegalEntity>
            </cac:Party>
        </cac:AccountingSupplierParty>';
        for ($i = 0; $i < count($detalle); $i++) {
            $xmlCPE = $xmlCPE . '<sac:SummaryDocumentsLine>
            <cbc:LineID>' . $detalle[$i]["ITEM"] . '</cbc:LineID>
            <cbc:DocumentTypeCode>' . $detalle[$i]["TIPO_COMPROBANTE"] . '</cbc:DocumentTypeCode>
            <cbc:ID>' . $detalle[$i]["NRO_COMPROBANTE"] . '</cbc:ID>
            <cac:AccountingCustomerParty>
                <cbc:CustomerAssignedAccountID>' . $detalle[$i]["NRO_DOCUMENTO"] . '</cbc:CustomerAssignedAccountID>
                <cbc:AdditionalAccountID>' . $detalle[$i]["TIPO_DOCUMENTO"] . '</cbc:AdditionalAccountID>
            </cac:AccountingCustomerParty>';
            if ($detalle[$i]["TIPO_COMPROBANTE"] == "07" || $detalle[$i]["TIPO_COMPROBANTE"] == "08") {
                $xmlCPE = $xmlCPE . '<cac:BillingReference>
                <cac:InvoiceDocumentReference>
                    <cbc:ID>' . $detalle[$i]["NRO_COMPROBANTE_REF"] . '</cbc:ID>
                    <cbc:DocumentTypeCode>' . $detalle[$i]["TIPO_COMPROBANTE_REF"] . '</cbc:DocumentTypeCode>
                </cac:InvoiceDocumentReference>
            </cac:BillingReference>';
            }
            $xmlCPE = $xmlCPE . '<cac:Status>
                <cbc:ConditionCode>' . $detalle[$i]["STATUS"] . '</cbc:ConditionCode>
            </cac:Status>
            <sac:TotalAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["TOTAL"] . '</sac:TotalAmount>

                    <sac:BillingPayment>
                <cbc:PaidAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["GRAVADA"] . '</cbc:PaidAmount>
                <cbc:InstructionID>01</cbc:InstructionID>
            </sac:BillingPayment>';

            if (intval($detalle[$i]["EXONERADO"]) > 0) {
                $xmlCPE = $xmlCPE . '<sac:BillingPayment>
                <cbc:PaidAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["EXONERADO"] . '</cbc:PaidAmount>
                <cbc:InstructionID>02</cbc:InstructionID>
            </sac:BillingPayment>';
            }

            if (intval($detalle[$i]["INAFECTO"]) > 0) {
                $xmlCPE = $xmlCPE . '<sac:BillingPayment>
                <cbc:PaidAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["INAFECTO"] . '</cbc:PaidAmount>
                <cbc:InstructionID>03</cbc:InstructionID>
            </sac:BillingPayment>';
            }

            if (intval($detalle[$i]["EXPORTACION"]) > 0) {
                $xmlCPE = $xmlCPE . '<sac:BillingPayment>
                <cbc:PaidAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["EXPORTACION"] . '</cbc:PaidAmount>
                <cbc:InstructionID>04</cbc:InstructionID>
            </sac:BillingPayment>';
            }

            if (intval($detalle[$i]["GRATUITAS"]) > 0) {
                $xmlCPE = $xmlCPE . '<sac:BillingPayment>
                <cbc:PaidAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["GRATUITAS"] . '</cbc:PaidAmount>
                <cbc:InstructionID>05</cbc:InstructionID>
            </sac:BillingPayment>';
            }

            if (intval($detalle[$i]["MONTO_CARGO_X_ASIG"]) > 0) {
                $xmlCPE = $xmlCPE . '<cac:AllowanceCharge>';
                if ($detalle[$i]["CARGO_X_ASIGNACION"] == 1) {
                    $xmlCPE = $xmlCPE . '<cbc:ChargeIndicator>true</cbc:ChargeIndicator>';
                } else {
                    $xmlCPE = $xmlCPE . '<cbc:ChargeIndicator>false</cbc:ChargeIndicator>';
                }
                $xmlCPE = $xmlCPE . '<cbc:Amount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["MONTO_CARGO_X_ASIG"] . '</cbc:Amount>
                        </cac:AllowanceCharge>';
            }
            if (intval($detalle[$i]["ISC"]) > 0) {
                $xmlCPE = $xmlCPE . '<cac:TaxTotal>
                <cbc:TaxAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["ISC"] . '</cbc:TaxAmount>
                <cac:TaxSubtotal>
                    <cbc:TaxAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["ISC"] . '</cbc:TaxAmount>
                    <cac:TaxCategory>
                        <cac:TaxScheme>
                            <cbc:ID>2000</cbc:ID>
                            <cbc:Name>ISC</cbc:Name>
                            <cbc:TaxTypeCode>EXC</cbc:TaxTypeCode>
                        </cac:TaxScheme>
                    </cac:TaxCategory>
                </cac:TaxSubtotal>
            </cac:TaxTotal>';
            }
            $xmlCPE = $xmlCPE . '<cac:TaxTotal>
                <cbc:TaxAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["IGV"] . '</cbc:TaxAmount>
                <cac:TaxSubtotal>
                    <cbc:TaxAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["IGV"] . '</cbc:TaxAmount>
                    <cac:TaxCategory>
                        <cac:TaxScheme>
                            <cbc:ID>1000</cbc:ID>
                            <cbc:Name>IGV</cbc:Name>
                            <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                        </cac:TaxScheme>
                    </cac:TaxCategory>
                </cac:TaxSubtotal>
            </cac:TaxTotal>';

            if (intval($detalle[$i]["OTROS"]) > 0) {
                $xmlCPE = $xmlCPE . '<cac:TaxTotal>
                <cbc:TaxAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["OTROS"] . '</cbc:TaxAmount>
                <cac:TaxSubtotal>
                    <cbc:TaxAmount currencyID="' . $detalle[$i]["COD_MONEDA"] . '">' . $detalle[$i]["OTROS"] . '</cbc:TaxAmount>
                    <cac:TaxCategory>
                        <cac:TaxScheme>
                            <cbc:ID>9999</cbc:ID>
                            <cbc:Name>OTROS</cbc:Name>
                            <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                        </cac:TaxScheme>
                    </cac:TaxCategory>
                </cac:TaxSubtotal>
            </cac:TaxTotal>';
            }
            $xmlCPE = $xmlCPE . '</sac:SummaryDocumentsLine>';
        }
        $xmlCPE = $xmlCPE . '</SummaryDocuments>';

        $doc->loadXML($xmlCPE);
        $doc->save($ruta . '.XML');
        $resp['respuesta'] = 'ok';
        $resp['url_xml'] = $ruta . '.XML';
        return $resp;
    }

    public static function enviar_documento($ruc, $usuario_sol, $pass_sol, $ruta_archivo, $ruta_archivo_cdr, $archivo, $ruta_ws)
    {
        //=================ZIPEAR ================

        $zip = new ZipArchive();
        $filenameXMLCPE = $ruta_archivo . '.ZIP';
        //dd($filenameXMLCPE);
        if ($zip->open($filenameXMLCPE, ZIPARCHIVE::CREATE) === true) {
            $zip->addFile($ruta_archivo . '.XML', $archivo . '.XML'); //ORIGEN, DESTINO
            //dd("===============");
            $zip->close();
        }

        //===================ENVIO FACTURACION=====================
        $soapUrl = $ruta_ws;
        // xml post structure
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe"
        xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
        <soapenv:Header>
            <wsse:Security>
                <wsse:UsernameToken>
                    <wsse:Username>' . $ruc . $usuario_sol . '</wsse:Username>
                    <wsse:Password>' . $pass_sol . '</wsse:Password>
                </wsse:UsernameToken>
            </wsse:Security>
        </soapenv:Header>
        <soapenv:Body>
            <ser:sendBill>
                <fileName>' . $archivo . '.ZIP</fileName>
                <contentFile>' . base64_encode(file_get_contents($ruta_archivo . '.ZIP')) . '</contentFile>
            </ser:sendBill>
        </soapenv:Body>
        </soapenv:Envelope>';

        $headers = array(
            'Content-Type: text/xml; charset=utf-8',
            //'Accept-Encoding: gzip,deflate',
            'SOAPAction: urn:sendBill',
            'Accept: text/xml',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            //"SOAPAction: ',
            'Content-length: ' . strlen($xml_post_string),
        );


        //'ssl' => array( 'allow_self_signed' => true, ) ); $sslContext = stream_context_create($contextOptions);
        //$soap = new SoapClient('https://domain.com/webservice.asmx?wsdl', array('stream_context' => $sslContext)
        $url = $soapUrl;
        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        // curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);


        // curl_setopt($ch, CURLOPT_VERBOSE, false);
        //curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);


        //$xmli = new \SimpleXMLElement($response);


        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode == 200) {
            $doc = new \DOMDocument();
            $doc->loadXML($response);


            //===================VERIFICAMOS SI HA ENVIADO CORRECTAMENTE EL COMPROBANTE=====================
            $archivo_cdr = "";
            if (isset($doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue)) {
                $xmlCDR = $doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue;
                file_put_contents($ruta_archivo_cdr . 'R-' . $archivo . '.ZIP', base64_decode($xmlCDR));

                //extraemos archivo zip a xml
                $zip = new ZipArchive;
                if ($zip->open($ruta_archivo_cdr . 'R-' . $archivo . '.ZIP') === TRUE) {
                    $zip->extractTo($ruta_archivo_cdr);
                    $zip->close();
                }

                //eliminamos los archivos Zipeados
                unlink($ruta_archivo . '.ZIP');
                unlink($ruta_archivo_cdr . 'R-' . $archivo . '.ZIP');

                //=============hash CDR=================

                $doc_cdr = new \DOMDocument();
                if (file_exists($ruta_archivo_cdr . 'R-' . $archivo . '.XML')) {
                    $archivo_cdr = 'R-' . $archivo . '.XML';
                    $doc_cdr->load($ruta_archivo_cdr . $archivo_cdr);
                } else if (file_exists($ruta_archivo_cdr . 'R-' . $archivo . '.xml')) {
                    $archivo_cdr = 'R-' . $archivo . '.xml';
                    $doc_cdr->load($ruta_archivo_cdr . $archivo_cdr);
                }
                $resp['respuesta'] = 'ok';
                $resp['ruta_cdr'] = $archivo_cdr;
                $resp['cod_sunat'] = $doc_cdr->getElementsByTagName('ResponseCode')->item(0)->nodeValue;
                $resp['mensaje'] = $doc_cdr->getElementsByTagName('Description')->item(0)->nodeValue;
                $resp['hash_cdr'] = $doc_cdr->getElementsByTagName('DigestValue')->item(0)->nodeValue;
            } else {

                $resp['respuesta'] = 'error';
                $resp['ruta_cdr'] = $archivo_cdr;
                $resp['respuesta_error'] = 'error_comprobante';
                $resp['cod_sunat'] = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
                $resp['mensaje'] = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
                $resp['hash_cdr'] = "";
            }
        } else {
            $resp['respuesta'] = 'error';
            $resp['cod_sunat'] = "0000";
            $resp['ruta_cdr'] = "";
            $resp['mensaje'] = "Api sunat Fuera de servicio o comprobante con error";
            $resp['hash_cdr'] = "";
        }
        return $resp;
    }


    //require_once('decode_64.php');
    public function enviar_documento_prueba($ruc, $usuario_sol, $pass_sol, $ruta_archivo, $ruta_archivo_cdr, $archivo, $ruta_ws)
    {
        try {
            //=================ZIPEAR ================
            $zip = new ZipArchive();
            $filenameXMLCPE = $ruta_archivo . '.ZIP';

            if ($zip->open($filenameXMLCPE, ZIPARCHIVE::CREATE) === true) {
                $zip->addFile($ruta_archivo . '.XML', $archivo . '.XML'); //ORIGEN, DESTINO
                $zip->close();
            }

            //===================ENVIO FACTURACION=====================
            $soapUrl = $ruta_ws; //"https://e-beta.sunat.gob.pe:443/ol-ti-itcpfegem-beta/billService"; // asmx URL of WSDL
            $soapUser = "";  //  username
            $soapPassword = ""; // password
            // xml post structure
            $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe"
    xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
    <soapenv:Header>
        <wsse:Security>
            <wsse:UsernameToken>
                <wsse:Username>' . $ruc . $usuario_sol . '</wsse:Username>
                <wsse:Password>' . $pass_sol . '</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </soapenv:Header>
    <soapenv:Body>
        <ser:sendBill>
            <fileName>' . $archivo . '.ZIP</fileName>
            <contentFile>' . base64_encode(file_get_contents($ruta_archivo . '.ZIP')) . '</contentFile>
        </ser:sendBill>
    </soapenv:Body>
    </soapenv:Envelope>';

            $headers = array(
                "Content-type: text/xml;charset=\"utf-8\"",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "SOAPAction: ",
                "Content-length: " . strlen($xml_post_string),
            ); //SOAPAction: your op URL

            $url = $soapUrl;

            //echo $xml_post_string;

            // PHP cURL  for https connection with auth
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // converting
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            //echo $httpcode;
            //echo $response;
            //if ($httpcode == 200) {//======LA PAGINA SI RESPONDE
            //echo $httpcode.'----'.$response;
            //convertimos de base 64 a archivo fisico
            $doc = new \DOMDocument();
            $doc->loadXML($response);


            //===================VERIFICAMOS SI HA ENVIADO CORRECTAMENTE EL COMPROBANTE=====================
            if (isset($doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue)) {
                $xmlCDR = $doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue;
                file_put_contents($ruta_archivo_cdr . 'R-' . $archivo . '.ZIP', base64_decode($xmlCDR));

                //extraemos archivo zip a xml
                $zip = new ZipArchive;
                if ($zip->open($ruta_archivo_cdr . 'R-' . $archivo . '.ZIP') === TRUE) {
                    $zip->extractTo($ruta_archivo_cdr, 'R-' . $archivo . '.XML');
                    $zip->close();
                }

                //eliminamos los archivos Zipeados
                unlink($ruta_archivo . '.ZIP');
                unlink($ruta_archivo_cdr . 'R-' . $archivo . '.ZIP');

                //=============hash CDR=================
                $doc_cdr = new \DOMDocument();
                $doc_cdr->load(dirname(__FILE__) . '/' . $ruta_archivo_cdr . 'R-' . $archivo . '.XML');

                $mensaje['cod_sunat'] = $doc_cdr->getElementsByTagName('ResponseCode')->item(0)->nodeValue;
                $mensaje['msj_sunat'] = $doc_cdr->getElementsByTagName('Description')->item(0)->nodeValue;
                $mensaje['hash_cdr'] = $doc_cdr->getElementsByTagName('DigestValue')->item(0)->nodeValue;
            } else {
                //$mensaje['cod_sunat'] = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
                //$mensaje['msj_sunat'] = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
                //$mensaje['hash_cdr'] = "";

                $mensaje['cod_sunat'] = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
                $mensaje['msj_sunat'] = $doc->getElementsByTagName('message')->item(0)->nodeValue;
                $mensaje['hash_cdr'] = "";
            }
        } catch (Exception $e) {
            $mensaje['cod_sunat'] = "0000";
            $mensaje['msj_sunat'] = "SUNAT ESTA FUERA SERVICIO: " . $e->getMessage();
            $mensaje['hash_cdr'] = "";
        }
        //print_r($mensaje);
        return $mensaje;
        //$xmlCDR = $doc->getElementsByTagName('applicationResponse')->item(0)->nodeValue;
    }


    public static function crear_xml_guia_remision($cabecera, $detalle, $ruta)
    {
        $validacion = new validaciondedatos();
        $doc = new \DOMDocument();
        $doc->formatOutput = FALSE;
        $doc->preserveWhiteSpace = TRUE;
        $doc->encoding = 'ISO-8859-1';
        $xmlCPE = '<?xml version="1.0" encoding="iso-8859-1"?>
    <DespatchAdvice xmlns="urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2"
xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
xmlns:ccts="urn:un:unece:uncefact:documentation:2"
xmlns:cec="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2"
xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1"
xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2"
xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <ext:UBLExtensions>
            <ext:UBLExtension>
                <ext:ExtensionContent>
                </ext:ExtensionContent>
            </ext:UBLExtension>
        </ext:UBLExtensions>
        <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
        <cbc:CustomizationID>1.0</cbc:CustomizationID>
        <cbc:ID>' . $cabecera->serie_comprobante . '-' . $cabecera->numero_comprobante . '</cbc:ID>
    <cbc:IssueDate>' . $cabecera->fecha_comprobante . '</cbc:IssueDate>
    <cbc:DespatchAdviceTypeCode>' . $cabecera->cod_tipo_documento . '</cbc:DespatchAdviceTypeCode>
    <cbc:Note>' . $cabecera->nota . '</cbc:Note>
      <cac:Signature>
        <cbc:ID>IDSign' . $cabecera->emisor->razon_social . '</cbc:ID>
        <cac:SignatoryParty>
            <cac:PartyIdentification>
                <cbc:ID>' . $cabecera->emisor->ruc . '</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>' . $cabecera->emisor->razon_social . '</cbc:Name>
            </cac:PartyName>
        </cac:SignatoryParty>
        <cac:DigitalSignatureAttachment>
            <cac:ExternalReference>
                <cbc:URI>#signature' . $cabecera->emisor->razon_social . '</cbc:URI>
            </cac:ExternalReference>
        </cac:DigitalSignatureAttachment>
    </cac:Signature>
    <cac:DespatchSupplierParty>
        <cbc:CustomerAssignedAccountID schemeID="6">' . $cabecera->emisor->ruc . '</cbc:CustomerAssignedAccountID>
        <cac:Party>
            <cac:PostalAddress>
                <cbc:ID>' . $cabecera->ubigeo_partida . '</cbc:ID>
                <cbc:StreetName>' . $cabecera->dir_partida . '</cbc:StreetName>
                <cbc:CitySubdivisionName>' . $cabecera->dir_partida_prov . '</cbc:CitySubdivisionName>
                <cbc:CityName>' . $cabecera->dir_partida_distrito . '</cbc:CityName>
                <cbc:CountrySubentity>' . $cabecera->dir_partida_dep . '</cbc:CountrySubentity>
                <cbc:District>' . $cabecera->dir_partida_distrito . '</cbc:District>
                <cac:Country>
                    <cbc:IdentificationCode>PE</cbc:IdentificationCode>
                </cac:Country>
            </cac:PostalAddress>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[' . $validacion->replace_invalid_caracters($cabecera->emisor->razon_social) . ']]></cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:DespatchSupplierParty>
    <cac:DeliveryCustomerParty>
        <cbc:CustomerAssignedAccountID schemeID="' . $cabecera->cliente_tipodocumento . '">' . $cabecera->cliente_numerodocumento . '</cbc:CustomerAssignedAccountID>
            <cac:Party>
                <cac:PartyLegalEntity>
                    <cbc:RegistrationName><![CDATA[' . $cabecera->cliente_nombre . ']]></cbc:RegistrationName>
                </cac:PartyLegalEntity>
                     <cac:Contact>
                        <cbc:ElectronicMail>noe.tipo@gmail.com</cbc:ElectronicMail>
                    </cac:Contact>
            </cac:Party>
     </cac:DeliveryCustomerParty>
      <cac:Shipment>
            <cbc:ID>1</cbc:ID>
            <cbc:HandlingCode>' . $cabecera->codmotivo_traslado . '</cbc:HandlingCode>
            <cbc:Information>' . $cabecera->motivo_traslado . '</cbc:Information>
            <cbc:GrossWeightMeasure unitCode="KGM">' . $cabecera->peso . '</cbc:GrossWeightMeasure>
    <cbc:TotalTransportHandlingUnitQuantity>' . $cabecera->numero_paquetes . '</cbc:TotalTransportHandlingUnitQuantity>
        <cac:ShipmentStage>
                <cbc:TransportModeCode>' . $cabecera->codtipo_transportista . '</cbc:TransportModeCode>
                <cac:TransitPeriod>
                    <cbc:StartDate>' . $cabecera->fecha_comprobante . '</cbc:StartDate>
                </cac:TransitPeriod>
                <cac:CarrierParty>
                    <cac:PartyIdentification>
                        <cbc:ID schemeID="' . $cabecera->tipo_documento_transporte . '">' . $cabecera->nro_documento_transporte . '</cbc:ID>
                    </cac:PartyIdentification>
                    <cac:PartyName>
                        <cbc:Name><![CDATA[' . $cabecera->razon_social_transporte . ']]></cbc:Name>
                    </cac:PartyName>
                </cac:CarrierParty>
                <cac:TransportMeans>
                    <cac:RoadTransport>
                        <cbc:LicensePlateID>' . $cabecera->guia_remision_placa_vehiculo . '</cbc:LicensePlateID>
                    </cac:RoadTransport>
                </cac:TransportMeans>
                <cac:DriverPerson>
                    <cbc:ID schemeID="1">' . $cabecera->guia_remision_num_doc_conductor . '</cbc:ID>
                </cac:DriverPerson>
            </cac:ShipmentStage>
            <cac:Delivery>
                <cac:DeliveryAddress>
                    <cbc:ID>' . $cabecera->ubigeo_destino . '</cbc:ID>
                    <cbc:StreetName>' . $cabecera->dir_destino . '</cbc:StreetName>
                      <cac:Country>
                    <cbc:IdentificationCode>PE</cbc:IdentificationCode>
                </cac:Country>
                </cac:DeliveryAddress>
            </cac:Delivery>
            <cac:OriginAddress>
                <cbc:ID>' . $cabecera->ubigeo_partida . '</cbc:ID>
                <cbc:StreetName>' . $cabecera->dir_partida . '</cbc:StreetName>
                <cac:Country>
                    <cbc:IdentificationCode>PE</cbc:IdentificationCode>
                </cac:Country>
            </cac:OriginAddress>

        </cac:Shipment>';
        for ($i = 0; $i < count($detalle); $i++) {
            $xmlCPE = $xmlCPE . '<cac:DespatchLine>
            <cbc:ID>' . $detalle[$i]->guia_remision_detalle_item . '</cbc:ID>
    <cbc:DeliveredQuantity unitCode="NIU">' . $detalle[$i]->guia_remision_detalle_peso . '</cbc:DeliveredQuantity>
    <cac:OrderLineReference>
    <cbc:LineID>' . $detalle[$i]->guia_remision_detalle_peso . '</cbc:LineID>
    </cac:OrderLineReference>

    <cac:Item>
                <cbc:Name><![CDATA[' . $validacion->replace_invalid_caracters($detalle[$i]->guia_remision_detalle_descripcion) . ']]></cbc:Name>
                <cac:SellersItemIdentification>
                    <cbc:ID>' . $detalle[$i]->guia_remision_detalle_codigo_producto . '</cbc:ID>
                </cac:SellersItemIdentification>
            </cac:Item>
        </cac:DespatchLine>';
        }
        $xmlCPE = $xmlCPE . '</DespatchAdvice>';
        $doc->loadXML($xmlCPE);
        $doc->save($ruta . '.XML');
        $resp['respuesta'] = 'ok';
        $resp['url_xml'] = $ruta . '.XML';
        return $resp;
    }


    public static function crear_xml_baja_sunat($cabecera, $detalle, $ruta)
    {
        $validacion = new validaciondedatos();
        $doc = new \DOMDocument();
        $doc->formatOutput = FALSE;
        $doc->preserveWhiteSpace = TRUE;
        $doc->encoding = 'ISO-8859-1';
        $xmlCPE = '<?xml version="1.0" encoding="ISO-8859-1" standalone="no"?><VoidedDocuments xmlns="urn:sunat:names:specification:ubl:peru:schema:xsd:VoidedDocuments-1" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <ext:UBLExtensions>
    <ext:UBLExtension>
    <ext:ExtensionContent>
    </ext:ExtensionContent>
    </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>2.0</cbc:UBLVersionID>
    <cbc:CustomizationID>1.0</cbc:CustomizationID>
    <cbc:ID>' . $cabecera->codigo . '-' . $cabecera->serie . '-' . $cabecera->secuencia . '</cbc:ID>
    <cbc:ReferenceDate>' . $cabecera->fecha_referencia . '</cbc:ReferenceDate>
    <cbc:IssueDate>' . $cabecera->fecha_baja . '</cbc:IssueDate>
    <cac:Signature>
    <cbc:ID>IDSignKG</cbc:ID>
    <cac:SignatoryParty>
    <cac:PartyIdentification>
    <cbc:ID>' . $cabecera->emisor->ruc . '</cbc:ID>
    </cac:PartyIdentification>
    <cac:PartyName>
    <cbc:Name>' . $validacion->replace_invalid_caracters($cabecera->emisor->nom_comercial) . '</cbc:Name>
    </cac:PartyName>
    </cac:SignatoryParty>
    <cac:DigitalSignatureAttachment>
    <cac:ExternalReference>
    <cbc:URI>#' . $cabecera->serie . '-' . $cabecera->secuencia . '</cbc:URI>
    </cac:ExternalReference>
    </cac:DigitalSignatureAttachment>
    </cac:Signature>
    <cac:AccountingSupplierParty>
    <cbc:CustomerAssignedAccountID>' . $cabecera->emisor->ruc . '</cbc:CustomerAssignedAccountID>
    <cbc:AdditionalAccountID>' . $cabecera->emisor->tipo_doc . '</cbc:AdditionalAccountID>
    <cac:Party>
    <cac:PartyLegalEntity>
    <cbc:RegistrationName><![CDATA[' . $validacion->replace_invalid_caracters($cabecera->emisor->razon_social) . ']]></cbc:RegistrationName>
    </cac:PartyLegalEntity>
    </cac:Party>
    </cac:AccountingSupplierParty>';
        for ($i = 0; $i < count($detalle); $i++) {
            $xmlCPE = $xmlCPE . '<sac:VoidedDocumentsLine>
    <cbc:LineID>' . $detalle[$i]->item . '</cbc:LineID>
    <cbc:DocumentTypeCode>' . $detalle[$i]->tipo_comprobante . '</cbc:DocumentTypeCode>
    <sac:DocumentSerialID>' . $detalle[$i]->serie . '</sac:DocumentSerialID>
    <sac:DocumentNumberID>' . $detalle[$i]->numero . '</sac:DocumentNumberID>
    <sac:VoidReasonDescription><![CDATA[' . $validacion->replace_invalid_caracters($detalle[$i]->motivo) . ']]></sac:VoidReasonDescription>
    </sac:VoidedDocumentsLine>';
        }
        $xmlCPE = $xmlCPE . '</VoidedDocuments>';

        $doc->loadXML($xmlCPE);
        $doc->save($ruta . '.XML');
        $resp['respuesta'] = 'ok';
        $resp['url_xml'] = $ruta . '.XML';
        return $resp;
    }

    public static function enviar_documento_para_baja($ruc, $usuario_sol, $pass_sol, $ruta_archivo, $ruta_archivo_cdr, $archivo, $ruta_ws)
    {

        try {
            //=================ZIPEAR ================
            $zip = new ZipArchive();
            $filenameXMLCPE = $ruta_archivo . '.ZIP';

            if ($zip->open($filenameXMLCPE, ZIPARCHIVE::CREATE) === true) {
                $zip->addFile($ruta_archivo . '.XML', $archivo . '.XML'); //ORIGEN, DESTINO
                $zip->close();
            }

            //===================ENVIO FACTURACION=====================
            $soapUrl = $ruta_ws;
            $soapUser = "";
            $soapPassword = "";
            // xml post structure
            $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
            xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe"
            xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <soapenv:Header>
                <wsse:Security>
                    <wsse:UsernameToken>
                        <wsse:Username>' . $ruc . $usuario_sol . '</wsse:Username>
                        <wsse:Password>' . $pass_sol . '</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soapenv:Header>
            <soapenv:Body>
                <ser:sendSummary>
                    <fileName>' . $archivo . '.ZIP</fileName>
                    <contentFile>' . base64_encode(file_get_contents($ruta_archivo . '.ZIP')) . '</contentFile>
                </ser:sendSummary>
            </soapenv:Body>
            </soapenv:Envelope>';

            $headers = array(
                "Content-type: text/xml;charset=\"utf-8\"",
                "Accept: text/xml",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "SOAPAction: ",
                "Content-length: " . strlen($xml_post_string),
            ); //SOAPAction: your op URL

            $url = $soapUrl;

            // PHP cURL  for https connection with auth
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // converting
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            //convertimos de base 64 a archivo fisico
            $doc = new \DOMDocument();
            $doc->loadXML($response);


            //===================VERIFICAMOS SI HA ENVIADO CORRECTAMENTE EL COMPROBANTE=====================
            if (isset($doc->getElementsByTagName('ticket')->item(0)->nodeValue)) {
                $ticket = $doc->getElementsByTagName('ticket')->item(0)->nodeValue;

                unlink($ruta_archivo . '.ZIP');
                $mensaje['respuesta'] = 'ok';
                $mensaje['cod_ticket'] = $ticket;

                // $mensaje['extra'] = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue . ' - ' . $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
            } else {
                $mensaje['respuesta'] = 'error';
                $mensaje['cod_sunat'] = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
                $mensaje['mensaje'] = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
                $mensaje['hash_cdr'] = "";
            }

        } catch (Exception $e) {

            //dd($e);
            $mensaje['respuesta'] = 'error';
            $mensaje['cod_sunat'] = "0000";
            $mensaje['mensaje'] = "SUNAT ESTA FUERA SERVICIO: " . $e->getMessage();
            $mensaje['hash_cdr'] = "";
        }
        return $mensaje;
    }

    public function enviar_resumen_boletas($ruc, $usuario_sol, $pass_sol, $ruta_archivo, $ruta_archivo_cdr, $archivo, $ruta_ws)
    {
        //=================ZIPEAR ================
        $zip = new ZipArchive();
        $filenameXMLCPE = $ruta_archivo . '.ZIP';

        if ($zip->open($filenameXMLCPE, ZIPARCHIVE::CREATE) === true) {
            $zip->addFile($ruta_archivo . '.XML', $archivo . '.XML'); //ORIGEN, DESTINO
            $zip->close();
        }

        //===================ENVIO FACTURACION=====================
        $soapUrl = $ruta_ws;
        $soapUser = "";
        $soapPassword = "";
        // xml post structure
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
        xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe"
        xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
        <soapenv:Header>
            <wsse:Security>
                <wsse:UsernameToken>
                    <wsse:Username>' . $ruc . $usuario_sol . '</wsse:Username>
                    <wsse:Password>' . $pass_sol . '</wsse:Password>
                </wsse:UsernameToken>
            </wsse:Security>
        </soapenv:Header>
        <soapenv:Body>
            <ser:sendSummary>
                <fileName>' . $archivo . '.ZIP</fileName>
                <contentFile>' . base64_encode(file_get_contents($ruta_archivo . '.ZIP')) . '</contentFile>
            </ser:sendSummary>
        </soapenv:Body>
        </soapenv:Envelope>';

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: ",
            "Content-length: " . strlen($xml_post_string),
        ); //SOAPAction: your op URL

        $url = $soapUrl;

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode == 200) {//======LA PAGINA SI RESPONDE

            //convertimos de base 64 a archivo fisico
            $doc = new \DOMDocument();
            $doc->loadXML($response);

            //===================VERIFICAMOS SI HA ENVIADO CORRECTAMENTE EL COMPROBANTE=====================
            if (isset($doc->getElementsByTagName('ticket')->item(0)->nodeValue)) {
                $ticket = $doc->getElementsByTagName('ticket')->item(0)->nodeValue;

                unlink($ruta_archivo . '.ZIP');
                $mensaje['respuesta'] = 'ok';
                $mensaje['cod_ticket'] = $ticket;
                $mensaje['extra'] = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue . ' - ' . $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;

            } else {

                $mensaje['respuesta'] = 'error';
                $mensaje['cod_sunat'] = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
                $mensaje['mensaje'] = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
                $mensaje['hash_cdr'] = "";
            }

        } else {
            //echo "no responde web";
            $mensaje['respuesta'] = 'error';
            $mensaje['cod_sunat'] = "0000";
            $mensaje['mensaje'] = "SUNAT ESTA FUERA SERVICIO: " . $e->getMessage();
            $mensaje['hash_cdr'] = "";
        }
        return $mensaje;
    }

    public static function consultar_envio_ticket($ruc, $usuario_sol, $pass_sol, $ticket, $archivo, $ruta_archivo_cdr, $ruta_ws)
    {
        $xml_post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
        <soapenv:Header>
        <wsse:Security>
        <wsse:UsernameToken>
        <wsse:Username>' . $ruc . $usuario_sol . '</wsse:Username>
        <wsse:Password>' . $pass_sol . '</wsse:Password>
        </wsse:UsernameToken>
        </wsse:Security>
        </soapenv:Header>
        <soapenv:Body>
        <ser:getStatus>
        <ticket>' . $ticket . '</ticket>
        </ser:getStatus>
        </soapenv:Body>
        </soapenv:Envelope>';

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: ",
            "Content-length: " . strlen($xml_post_string),
        ); //SOAPAction: your op URL

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $ruta_ws);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_USERPWD, $soapUser.":".$soapPassword); // username and password - declared at the top of the doc
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode == 200) {//======LA PAGINA SI RESPONDE
            //echo $httpcode.'----'.$response;
            //convertimos de base 64 a archivo fisico
            $doc = new \DOMDocument();
            $doc->loadXML($response);


            //===================VERIFICAMOS SI HA ENVIADO CORRECTAMENTE EL COMPROBANTE=====================
            if (isset($doc->getElementsByTagName('content')->item(0)->nodeValue)) {
                $xmlCDR = $doc->getElementsByTagName('content')->item(0)->nodeValue;
                file_put_contents($ruta_archivo_cdr . 'R-' . $archivo . '.ZIP', base64_decode($xmlCDR));

                //extraemos archivo zip a xml
                $zip = new ZipArchive;
                if ($zip->open($ruta_archivo_cdr . 'R-' . $archivo . '.ZIP') === TRUE) {
                    $zip->extractTo($ruta_archivo_cdr, 'R-' . $archivo . '.XML');
                    $zip->close();
                }

                //eliminamos los archivos Zipeados
                //unlink($ruta_archivo . '.ZIP');
                unlink($ruta_archivo_cdr . 'R-' . $archivo . '.ZIP');

                //=============hash CDR=================
                $doc_cdr = new \DOMDocument();

                // dd($ruta_archivo_cdr . 'R-' . $archivo . '.XML');
                $doc_cdr->load($ruta_archivo_cdr . 'R-' . $archivo . '.XML');

                $mensaje['respuesta'] = 'ok';
                $mensaje['cod_sunat'] = $doc_cdr->getElementsByTagName('ResponseCode')->item(0)->nodeValue;
                $mensaje['msj_sunat'] = $doc_cdr->getElementsByTagName('Description')->item(0)->nodeValue;
                $mensaje['mensaje'] = $doc_cdr->getElementsByTagName('Description')->item(0)->nodeValue;
                $mensaje['hash_cdr'] = $doc_cdr->getElementsByTagName('DigestValue')->item(0)->nodeValue;

            } else {
                $mensaje['cod_sunat'] = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
                $mensaje['msj_sunat'] = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
                $mensaje['hash_cdr'] = "";

                $mensaje['respuesta'] = 'error';
                $mensaje['cod_sunat'] = $doc->getElementsByTagName('faultcode')->item(0)->nodeValue;
                $mensaje['mensaje'] = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
                $mensaje['msj_sunat'] = $doc->getElementsByTagName('faultstring')->item(0)->nodeValue;
                $mensaje['hash_cdr'] = "";

            }
        } else {
            //echo "no responde web";
            $mensaje['respuesta'] = 'error';
            $mensaje['cod_sunat'] = "0000";
            $mensaje['mensaje'] = "SUNAT ESTA FUERA SERVICIO: ";
            $mensaje['hash_cdr'] = "";
        }
        return $mensaje;
    }
}
