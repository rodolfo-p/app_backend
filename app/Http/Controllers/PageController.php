<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 25/03/19
 * Time: 12:41 PM
 */

namespace App\Http\Controllers;
use App\Http\Data\Setup\Empresa;

class PageController
{

    public function page(){
        //dd("jhgjg");
        $empresa=Empresa::empresa();

        return view('welcome', compact('empresa'));
    }
}