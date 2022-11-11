<?php

namespace Test;

use CaioMarcatti12\Repository\Connector\MongoDB;
use CaioMarcatti12\Repository\Connector\Mysql;
use CaioMarcatti12\Repository\DestinatarioObjetoValor;
use CaioMarcatti12\Repository\MensagemEntidade;
use CaioMarcatti12\Repository\ModeloMensagem;
use CaioMarcatti12\Repository\TipoEnum;
use CaioMarcatti12\Data\ObjectMapper;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase {
    public function testObjectMapperToArray():void{
        $destinatarioObjetoValor = new DestinatarioObjetoValor('caio', 'test');
        $mensagemEntidade = new MensagemEntidade($destinatarioObjetoValor, TipoEnum::EMAIL);
        $mensagemEntidade = new \stdClass();
        $mensagemEntidade->teste = 1;
        $mensagemEntidade->caio = new \stdClass();
        $mensagemEntidade->caio->teste = 2;
        $array = ObjectMapper::toArray($mensagemEntidade);

        var_dump($array);
    }

    public function testConnectionMongoDB():void{
        $mongoDB = new MongoDB();
        $mongoDB->handler();

        $modelo = new ModeloMensagem();
        $modelo->mensagem = 'test';
        $modelo->save();
    }

    public function testConnectionMysql():void{
        $mysql = new Mysql();
        $mysql->handler();
    }
}