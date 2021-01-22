<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 04/06/19
 * Time: 05:30 PM
 */

namespace App\Http\Data\util;


use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;

class Pagination extends Controller
{
    public static function paginatorFacturaPeru($items, $perPage, $request)
    {
        if (!$perPage) {
            $perPage = 15;
        }
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($items);
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        $paginatedItems = new LengthAwarePaginator($currentPageItems, count($itemCollection), $perPage);
        $paginatedItems->setPath($request->url());
        $paginacion_data = new \stdClass();
        $lista = array();
        foreach ($paginatedItems->all() as $key => $value) {
            array_push($lista, $value);
        }
        $paginacion_data->data = $lista;
        $paginacion = new \stdClass();
        $paginacion->total_lista = $paginatedItems->total();
        $paginacion->ultima_pagina = $paginatedItems->lastPage();
        $paginacion->pagina_actual = $paginatedItems->currentPage();
        $paginacion->ver_por_pagina = intval($paginatedItems->perPage());
        $paginacion->total_por_pagina = count($lista);
        if ($paginatedItems->currentPage() == 1) {
            $paginacion->pagina_anterior = 1;
        } else {
            $paginacion->pagina_anterior = $paginatedItems->currentPage() - 1;
        }
        if ($paginatedItems->currentPage() == $paginatedItems->total()) {
            $paginacion->pagina_siguiente = $paginatedItems->total();
        } else {
            $paginacion->pagina_siguiente = $paginatedItems->currentPage() + 1;
        }
        $paginacion_data->paginacion = $paginacion;

        return $paginacion_data;
    }

}
