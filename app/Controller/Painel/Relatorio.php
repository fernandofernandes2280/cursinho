<?php
namespace App\Controller\Painel;

class Relatorio extends Page{

    public static function getPdfAluno($request){
        $request->getRouter()->redirect('/alunos');
        return '';
    }

}
