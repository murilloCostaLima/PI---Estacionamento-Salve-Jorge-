<?php
require_once(__DIR__."/../config/conexao.php");

    class vaga
    {
        private int     $id_vaga;
        private int     $codigo_vaga;
        private bool    $disponibilidade;

        public function __construct(
            int $id_vaga = 0,
            int $codigo_vaga,
            bool $disponibilidade,
        ){
            $this->id_vaga             = $id_vaga;
            $this->codigo_vaga         = $codigo_vaga;
            $this->disponibilidade     = $disponibilidade;
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
                case "id_vaga":
                    $this->id_vaga = (int)$valor;
                break;
                case "codigo_vaga":
                    $this->codigo_vaga = (int)$valor;
                break;
                case "disponibilidade":
                    $this->disponibilidade = (bool)$valor;
                break;
                default:
                    throw new Exception("Propriedade {$prop} não permitida");
            }
        }
        private static function getConexao()
        {
            return (new Conexao())->conexao();
        }

        public function inserir()
        {
            $pdo = self::getConexao();

            $sql = " INSERT INTO `Vaga` (`codigo_vaga`, `disponibilidade`)
            VALUES (:codigo_vaga, :disponibilidade)";

            $stmt= $pdo->prepare($sql);

            $stmt->execute([
                ':codigo_vaga'     => $this->codigo_vaga,
                ':disponibilidade'    => $this->disponibilidade,
            ]);

            $ultimoID = $pdo->lastInsertId();

            if($ultimoID<=0)
                {
                    throw new Exception("Não foi Possível inserir a vaga");
                }
            return $ultimoID;
        }
        public static function listar()
        {
            $pdo = self::getConexao();

            $sql = "SELECT v.id_vaga,
            v.codigo_vaga, 
            v.disponibilidade
            FROM vaga v 
            ORDER BY v.codigo_vaga";

            $stmt = $pdo->query($sql);

            $vagas = [];

            while($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                $vaga = new vaga(
                    id_vaga:            $row['id_vaga'],
                    codigo_vaga:        $row['codigo_vaga'],
                    disponibilidade:    (bool)$row['disponibilidade']
                );

                array_push($vagas, $vaga);
            }

            return $vagas;
        }

        public static function BuscarPorID(int $id)
        {
            $pdo = self::getConexao();

            $sql = "SELECT v.id_vaga,
            v.codigo_vaga, 
            v.disponibilidade
            FROM vaga v 
            WHERE v.id_vaga = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id'=>$id]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$row)
                {
                    new Exception("ID da vaga não Existe. Tente Outro ou, Adicione um usuário com este respectivo ID.");
                    return null;
                }

            $vaga = new vaga(
                id_vaga:             $row['id_vaga'],
                codigo_vaga:         $row['codigo_vaga'],
                disponibilidade:     (bool)$row['disponibilidade']
            );

            return $vaga;
        }

        public static function excluir(int $id)
        {
            $pdo = self::getConexao();

            $sql = "DELETE FROM vaga WHERE id_vaga = :id";
            $stmt = $pdo->prepare($sql);

            $stmt->execute([':id'=>$id]);

            return $stmt->rowCount() > 0;
        }

        public function atualizar()
        {
            $pdo = self::getConexao();

            $sql = "UPDATE `vaga` SET `codigo_vaga` = :codigo_vaga, 
                `disponibilidade` = :disponibilidade WHERE `id_vaga` = :id";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':codigo_vaga'        => $this->codigo_vaga,
                ':disponibilidade'    => $this->disponibilidade ]);

            if($stmt->rowCount()===0)
                {
                    return false;
                }
            return true;
        }
    }
    // echo "<pre>";
    // print_r(Usuario::excluir(3)); 
?>