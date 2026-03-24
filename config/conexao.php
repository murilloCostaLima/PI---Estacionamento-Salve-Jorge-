<?php
    class Conexao
    {   // variaveis de conexao
        private $host = '10.91.45.33';       //ip do banco de dados
        private $bd   = 'bd_estacionamento'; // Nome do Banco
        private $user = 'admin';             // Usuario
        private $pass = '123456';            //senha de acesso

        public function conexao()
        {
            try
            {
                $strCon = "mysql:host={$this->host}; dbname={$this->bd}; charset=utf8";
                $pdo = new PDO($strCon, $this->user, $this->pass);

                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                return $pdo;
            }
            catch(PDOException $err)
            {
                die("Erro na Conexao". $err->getMessage());
                return null;
            }
        }
    }
?>