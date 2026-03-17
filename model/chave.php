<?php
require_once(__DIR__."/../config/conexao.php");
require_once(__DIR__."/../config/autoload.php");

    class chave
    {
        private int  $id;
        private int  $idVaga;
        private int  $idVeiculo;

        public function __construct(
            int $id = 0,
            int $idVaga,
            int $idVeiculo,)
        {
            $this->id        = $id;
            $this->idVaga    = $idVaga;
            $this->idVeiculo = $idVeiculo;
        }

        public function __get(string $prop)
        {
            if(property_exists($this,$prop))
            {
                return $this->$prop;
            }
                throw new Exception("Propriedade {$prop} não existe");
        }

        public function __set(string $prop, $valor)
        {
            switch($prop)
            {
                case "id":
                    $this->id = (int)$valor;
                break;
                case "idVaga":
                    $this->idVaga = (int)$valor;
                break;
                case "idVeiculo":
                    $this->idVeiculo = (int)$valor;
                default:
                    throw new Exception("Propriedade {$prop} não permitida");
            }
        }
        private static function getConexao()
        {
            return (new Conexao())->conexao();
        }
    }
?>