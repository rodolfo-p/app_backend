<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 13/12/18
 * Time: 11:57 AM
 */

namespace App\Http\Controllers\FacturacionElectronica\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacturacionElectronica\api_signature\XMLSecurityDSig;
use App\Http\Controllers\FacturacionElectronica\api_signature\XMLSecurityKey;
class signature extends Controller
{


    public static function signature_xml($flg_firma, $ruta, $ruta_firma, $pass_firma) {
        //flg_firma:
        //          01, 03, 07, 08: Firmar en el nodo uno.
        //          00: Firmar en el Nodo Cero (para comprobantes de Percepción o Retención)

        $doc = new \DOMDocument();

        $doc->formatOutput = FALSE;
        $doc->preserveWhiteSpace = TRUE;
        $doc->load($ruta . '.XML');

        $objDSig = new XMLSecurityDSig(FALSE);
        $objDSig->setCanonicalMethod(XMLSecurityDSig::C14N);
        $options['force_uri'] = TRUE;
        $options['id_name'] = 'ID';
        $options['overwrite'] = FALSE;

        $objDSig->addReference($doc, XMLSecurityDSig::SHA1, array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'), $options);
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type' => 'private'));

        $pfx = file_get_contents($ruta_firma);
        $key = array();

        openssl_pkcs12_read($pfx, $key, $pass_firma);
        $objKey->loadKey($key["pkey"]);
        $objDSig->add509Cert($key["cert"], TRUE, FALSE);
        $objDSig->sign($objKey, $doc->documentElement->getElementsByTagName("ExtensionContent")->item($flg_firma));

        $atributo = $doc->getElementsByTagName('Signature')->item(0);
        $atributo->setAttribute('Id', 'SignatureSP');

        //===================rescatamos Codigo(HASH_CPE)==================
        $hash_cpe = $doc->getElementsByTagName('DigestValue')->item(0)->nodeValue;

        $doc->save($ruta . '.XML');
        $resp['respuesta'] = 'ok';
        $resp['hash_cpe'] = $hash_cpe;
        return $resp;
    }
}