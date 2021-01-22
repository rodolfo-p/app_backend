<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 28/09/18
 * Time: 04:46 PM
 */

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Http\Data\Setup\SegMenu;
use Illuminate\Http\Request;
use Exception;
use App\Http\Controllers\SecurityToken;

class SegMenuController
{

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    /** Lista el manu  de un  usuario  segun el rol que tiene */

    public function listar_menu()
    {
        $menu = [];
        try {
            $list_padre_mudulo = SegMenu::listar_menu_por_usuario_padre(auth()->user()->id);
            foreach ($list_padre_mudulo as $key => $dataPadre) {
                $lista = $dataPadre;
                $list = SegMenu::listar_menu_por_usuario_hijo(auth()->user()->id, $dataPadre->Parent_seg_modulo_id);
                $lista->children = $list;
                array_push($menu, $lista);
            }
            $jResponse['success'] = true;
            $jResponse['data'] = $menu;
        } catch (Exception $e) {
            dd($e);
        }

        return response()->json(['success' => true,
            'data' => $menu,
            'message' => 'Lista de Menu'], 200);


    }
    /** Lista el manu  de un  usuario  segun el rol que tiene */

}
