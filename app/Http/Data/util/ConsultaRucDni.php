<?php


namespace App\Http\Data\util;


use App\Http\Controllers\Controller;

class ConsultaRucDni extends Controller
{
    public static function dni($dni)
    {

        /*$Sql = "select emp_empresa_llave_ruc_dni from emp_empresa";
        $Result = DB::select($Sql);
        $password = $Result[0]->emp_empresa_llave_ruc_dni;*/
        // $ruta = "https://facturalahoy.com/api/persona/" . $dni . '/' . $password;

        $ruta = "http://ruc-dni.factura-peru.com/api/auth/dni/" . $dni;
        $curl = curl_init();

        /*      $headers = array(
                  'Content-type:application/json',
                  'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImFkZGQ0YjA4NDUyMjc1N2FkZTA5M2MzMWFmNDkxZGZkOWE0NTA5YjU3NjFhZjA1M2U2ZjdhNDEyNDY1MzFkY2MyNGEwNjUxN2Y5N2NhZGYzIn0.eyJhdWQiOiIxIiwianRpIjoiYWRkZDRiMDg0NTIyNzU3YWRlMDkzYzMxYWY0OTFkZmQ5YTQ1MDliNTc2MWFmMDUzZTZmN2E0MTI0NjUzMWRjYzI0YTA2NTE3Zjk3Y2FkZjMiLCJpYXQiOjE1NTMwMDc3MTYsIm5iZiI6MTU1MzAwNzcxNiwiZXhwIjoxNTg0NjMwMTE2LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.HHeWWHaLJuhwiVTXsBHoCm-XwtujsEjnay32PlgZH8pHHudHHObl8I_Lq_RfrQdeb6_HN5Zv5K4kL8NRtH3yNA0wbuSbB5Op6gC2QsIMJfkS6y_1QFRJIWMGVdie8Gowr5i5VME7iKyBymo6n4Rd1v4SAHoNJXFDF8gXKrsXDfhfLSGWHw3crgI2WDYNSjGvPBNoz7tBhpfdky_HLSuB3DXNG_K6ozTwnWmSVWJhVToxg-VYHaUsv6x9GVO1kqEP6QBkeZQ0WEgfPnws7kWcDnbI3nhJI12IccBsCCaLiCKNQfWNBFAr2JZ0UJNL1y-gogGdLSUZqyzAyE6g9-foOBYc_wMxhzq51_6G-duyF_thEFJb_Ri0mCvx4WMpwrR5R0hRpYnEqQPqoAeO_1wpR2uyFHJGEiDdXcBILUsPaTUhRqptPbJ-Ip5Ex48SgLmJPgBDo4sO7d_MDY-QaTW4uwmv8bN02gGXIQ4GPD1c3LkeSVDDsQnNwVAVm5iPhuWpBGFgNiwWlHppHzTqwM2qPqILI30ddadf8ZL-hkl7B3lJI__zfFNA5XEnkyeS3lM0HtB3Ina8HCD77GcYvFwhzNZfWsdZIn73QFRIo316BMazorm2Cwa3_OyuFNIUz5OqWIMRLX51J9ahkrUcrwwvLwTD_p4xot-rRP6Oxw7KUwI'
              );
      */


        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $ruta,
            CURLOPT_HTTPHEADER
        ));
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
    }


    public static function ruc($ruc)
    {
        /*$Sql = "select emp_empresa_llave_ruc_dni from emp_empresa";
        $Result = DB::select($Sql);
        $password = $Result[0]->emp_empresa_llave_ruc_dni;*/
        $ruta = "http://ruc-dni.factura-peru.com/api/auth/ruc/" . $ruc;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $ruta,CURLOPT_HTTPHEADER
        ));
        $resp = curl_exec($curl);
        curl_close($curl);
        return $resp;
    }

}
