<?php
require_once(__DIR__ . "/../config/conexao.php");
require_once(__DIR__ . "/../config/autoload.php");

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
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //Verificar se telefone já existe
        $stmt = $pdo->prepare("SELECT 1 FROM cliente WHERE telefone = :telefone LIMIT 1");

        $stmt->execute([':telefone' => $telefone]);

        if ($stmt->fetch()) {
            throw new Exception("Este telefone já está cadastrado. Por favor, utilize outro.");
        }

        //Inserir o cliente
        $sql = "
            INSERT INTO cliente (nome, telefone, endereco, bairro, tipo_cliente)
            VALUES (:nome, :telefone, :endereco, :bairro, :tipo_cliente)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nome'         => $nome,
            ':telefone'     => $telefone,
            ':endereco'     => $endereco,
            ':bairro'       => $bairro,
            ':tipo_cliente' => $tipo_cliente
        ]);

        $ultimoID = $pdo->lastInsertId();

        if ($ultimoID <= 0) {
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
                id: $row['id_cliente'],
                nome: $row['nome'],
                telefone: $row['telefone'],
                endereco: $row['endereco'],
                bairro: $row['bairro'],
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
            id: $row['id_cliente'],
            nome: $row['nome'],
            telefone: $row['telefone'],
            endereco: $row['endereco'],
            bairro: $row['bairro'],
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
            WHERE id_cliente = :id";

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


    public static function excluir(int $id_cliente): bool
    {
        $pdo = self::getConexao();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $pdo->beginTransaction();

            // Buscar veículos do cliente
            $stmtV = $pdo->prepare("SELECT id_veiculo FROM veiculo WHERE id_cliente = :id");
            $stmtV->execute([":id" => $id_cliente]);
            $veiculos = $stmtV->fetchAll(PDO::FETCH_COLUMN);

            foreach ($veiculos as $idVeiculo) {
                veiculo::excluir((int)$idVeiculo);
            }

            // Excluir cliente
            $stmtC = $pdo->prepare("DELETE FROM cliente WHERE id_cliente = :id");
            $stmtC->execute([":id" => $id_cliente]);

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw new Exception("Erro ao excluir cliente: " . $e->getMessage());
        }
    }
    // NOVO MÉTODO – NÃO substitui o antigo
    public static function inserirComPDO(
        PDO $pdo,
        string $nome,
        string $telefone,
        string $endereco,
        string $bairro,
        string $tipo_cliente
    ): int {
        $sql = "
        INSERT INTO cliente (nome, telefone, endereco, bairro, tipo_cliente)
        VALUES (:nome, :telefone, :endereco, :bairro, :tipo)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome'     => $nome,
            ':telefone' => $telefone,
            ':endereco' => $endereco,
            ':bairro'   => $bairro,
            ':tipo'     => ucfirst(strtolower($tipo_cliente))
        ]);

        return (int)$pdo->lastInsertId();
    }
}
