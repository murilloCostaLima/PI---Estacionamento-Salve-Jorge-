<?php
require_once(__DIR__."/../config/conexao.php");

    class Vaga
    {
        private int     $id_vaga;
        private int     $codigo_vaga;
        private bool    $disponibilidade;

        public function __construct(
            int $id_vaga = 0,
            int $codigo_vaga,
            bool $disponibilidade,)
        {
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

            $sql = "SELECT u.id_usuario,
            u.nome,
            u.email,
            u.ativo,
            u.id_perfil,
            p.nome_perfil AS perfil_nivel 
            FROM usuarios u
            INNER JOIN perfis p
            ON p.id_perfil = u.id_perfil
            ORDER BY u.nome";

            $stmt = $pdo->query($sql);

            $usuarios = [];

            while($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                $usuario = new usuario(
                    id:        $row['id_usuario'],
                    nome:      $row['nome'],
                    email:     $row['email'],
                    senhaHash: "",
                    IDperfil:  $row['id_perfil'],
                    ativo:     (bool)$row['ativo']
                );

                $usuario->perfilNome = $row['perfil_nivel'];

                array_push($usuarios, $usuario);
            }

            return $usuarios;
        }

        public static function BuscarPorID(int $id)
        {
            $pdo = self::getConexao();

            $sql = "SELECT u.id_usuario,
            u.nome,
            u.email,
            u.ativo,
            u.id_perfil,
            p.nome_perfil AS perfil_nivel 
            FROM usuarios u
            INNER JOIN perfis p
            ON p.id_perfil = u.id_perfil
            WHERE u.id_usuario = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id'=>$id]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$row)
                {
                    new Exception("ID de Usuario não Existe. Tente Outro ou, Adicione um usuário com este respectivo ID.");
                    return null;
                }

            $usuario = new usuario(
                id:        $row['id_usuario'],
                nome:      $row['nome'],
                email:     $row['email'],
                senhaHash: "",
                IDperfil:  $row['id_perfil'],
                ativo:     (bool)$row['ativo']
            );

            $usuario->perfilNome = $row['perfil_nivel'];

            return $usuario;
        }

         public static function BuscarPorEmail(string $email)
        {
            $pdo = self::getConexao();

            $sql = "SELECT u.id_usuario,
            u.nome,
            u.email,
            u.ativo,
            u.id_perfil,
            p.nome_perfil AS perfil_nivel 
            FROM usuarios AS u
            INNER JOIN perfis p
            ON p.id_perfil = u.id_perfil
            WHERE u.email = :email";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email'=>$email]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$row)
                {
                    new Exception("Email de Usuario não Existe. Tente Outro ou, Adicione um usuário com este respectivo Email.");
                    return null;
                }

            $usuario = new usuario(
                id:        $row['id_usuario'],
                nome:      $row['nome'],
                email:     $row['email'],
                senhaHash: "",
                IDperfil:  $row['id_perfil'],
                ativo:     (bool)$row['ativo']
            );

            $usuario->email = $row['perfil_nivel'];

            return $usuario;
        }

        public static function excluir(int $id)
        {
            $pdo = self::getConexao();
            $sql = "DELETE FROM `usuarios` WHERE `id` = :id";
            $stmt = $pdo->prepare($sql);

            $stmt->execute([':id'=>$id]);

            return $stmt;

            if($stmt->rowCount()===0)
                {
                    return false;
                }
            return true;
        }

        public function atualizar()
        {
            $pdo = self::getConexao();

            $sql = "UPDATE `usuarios` SET `nome` = :nome, `email` = :email,
            `ativo`=:ativo, `id_perfil`=:perfil WHERE `id_usuario`:=id";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':nome'     => $this->nome,
                ':email'    => $this->email,
                ':senha'    => $this->senhaHash,
                ':ativo'    => $this->ativo,
                ':IDperfil' => $this->IDperfil]);

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