<?php
require_once(__DIR__."/../config/conexao.php");

    class cliente
    {
        private int     $id;
        private string  $nome;
        private string  $telefone;
        private string  $endereco;
        private string  $bairro;
        private string  $tipo_cliente;

        public function __construct(
            int    $id = 0,
            string $nome,
            string $telefone,
            string $endereco,
            string $bairro,
            string $tipo_cliente,)
        {
            $this->id           = $id;
            $this->nome         = $nome;
            $this->telefone     = $telefone;
            $this->endereco     = $endereco;
            $this->bairro       = $bairro;
            $this->tipo_cliente = $tipo_cliente;
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
                case "nome":
                    $this->nome = $valor;
                break;
                case "telefone":
                    $this->telefone = $valor;
                break;
                case "endereco":
                    $this->endereco = $valor;
                break;
                case "bairro":
                    $this->bairro = $valor;
                break;
                case "tipo_cliente":
                    $this->tipo_cliente = $valor;
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

            $sql = " INSERT INTO `client` (`nome`,`email`,`senha`,`ativo`, `id_perfil`)
            VALUES (:nome, :email, :senha, :ativo, :IDperfil)";

            $stmt= $pdo->prepare($sql);

            $stmt->execute([
                ':nome'     => $this->nome,
                ':telefone'    => $this->telefone,
                ':ativo'    => $this->ativo,
                ':IDperfil' => $this->IDperfil
            ]);

            $ultimoID = $pdo->lastInsertId();

            if($ultimoID<=0)
                {
                    throw new Exception("Não foi Possível inserir o usuario");
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
                $usuario = new cliente(
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

            $usuario = new cliente(
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

            $usuario = new cliente(
                id:        $row['id_cliente'],
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