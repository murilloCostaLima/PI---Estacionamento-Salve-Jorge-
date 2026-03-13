<?php
    require_once(__DIR__ . "/../config/conexao.php");

class cliente
{
    private int     $id;
    private string  $nome;
    private string  $telefone;
    private string  $endereco;
    private string  $bairro;
    private string  $tipo_cliente; // ENUM: 'Mensalista', 'Avulso', etc.

    public function __construct(
        int    $id = 0,
        string $nome = "",
        string $telefone = "",
        string $endereco = "",
        string $bairro = "",
        string $tipo_cliente = ""
    ) {
        $this->id           = $id;
        $this->nome         = $nome;
        $this->telefone     = $telefone;
        $this->endereco     = $endereco;
        $this->bairro       = $bairro;
        $this->tipo_cliente = $tipo_cliente;
    }

    // Getters e Setters mágicos simplificados
    public function __get(string $prop)
    {
        if (property_exists($this, $prop)) {
            return $this->$prop;
        }
        throw new Exception("Propriedade {$prop} não existe");
    }

    public function __set(string $prop, $valor)
    {
        if (property_exists($this, $prop)) {
            $this->$prop = $valor;
        } else {
            throw new Exception("Propriedade {$prop} não permitida");
        }
    }

    private static function getConexao()
    {
        return (new Conexao())->conexao();
    }

    public static function inserir($nome, $telefone, $endereco, $bairro, $tipo_cliente)
    {
        $pdo = self::getConexao();

        $sql = "INSERT INTO cliente (nome, telefone, endereco, bairro, tipo_cliente)
                VALUES (:nome, :telefone, :endereco, :bairro, :tipo_cliente)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
        ':nome'         => $nome,
        ':telefone'     => $telefone,
        ':endereco'     => $endereco,
        ':bairro'       => $bairro,
        ':tipo_cliente' => $tipo_cliente]);

        $ultimoID = $pdo->lastInsertId();

        if ($ultimoID <= 0)
        {
            throw new Exception("Não foi possível inserir o cliente.");
        }

        return $ultimoID;
    }

    public static function listar()
    {
        $pdo = self::getConexao();
        $sql = "SELECT * FROM cliente ORDER BY nome";
        $stmt = $pdo->query($sql);

        $clientes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clientes[] = new Cliente(
                id:           $row['id_cliente'],
                nome:         $row['nome'],
                telefone:     $row['telefone'],
                endereco:     $row['endereco'],
                bairro:       $row['bairro'],
                tipo_cliente: $row['tipo_cliente']
            );
        }
        return $clientes;
    }

    public static function buscarPorID(int $id)
    {
        $pdo = self::getConexao();
        $sql = "SELECT * FROM cliente WHERE id_cliente = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return new cliente(
            id:           $row['id_cliente'],
            nome:         $row['nome'],
            telefone:     $row['telefone'],
            endereco:     $row['endereco'],
            bairro:       $row['bairro'],
            tipo_cliente: $row['tipo_cliente']
        );
    }

    public function atualizar()
    {
        $pdo = self::getConexao();
        $sql = "UPDATE cliente SET 
                nome = :nome, 
                telefone = :telefone, 
                endereco = :endereco, 
                bairro = :bairro, 
                tipo_cliente = :tipo_cliente 
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ':nome'         => $this->nome,
            ':telefone'     => $this->telefone,
            ':endereco'     => $this->endereco,
            ':bairro'       => $this->bairro,
            ':tipo_cliente' => $this->tipo_cliente,
            ':id'           => $this->id
        ]);
    }

    public static function excluir(int $id)
    {
        $pdo = self::getConexao();
        $sql = "DELETE FROM cliente WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}

try{
    print_r(cliente::inserir("Maria", "11 37484-4857", "R. Virgínia Ferni", "avenida itaquera", "Mensal")); 
}catch(Exception $err){
    echo $err->getMessage();
}