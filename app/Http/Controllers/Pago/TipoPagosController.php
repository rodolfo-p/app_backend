<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 20/11/18
 * Time: 05:38 PM
 */

namespace App\Http\Controllers;

namespace App\Http\Controllers\Pago;

use App\Models\Configuracion\TipoPago;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;


class TipoPagosController extends Controller
{

    public function listar_tipo_pagos()
    {
        return response()->json(['success' => true,
            'data' => TipoPago::all(),
            'message' => 'Lista de Almacenes'], 200);
    }

}
