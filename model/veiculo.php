<?php
require_once(__DIR__ . "/../config/conexao.php");

class Veiculo
{
    private int $id_veiculo;
    private ?int $id_vaga;        // pode ser nulo se ainda não alocado
    private int $id_cliente;
    private string $placa;
    private string $cor;
    private string $marca;
    private string $modelo;
    private string $tipo_veiculo;  // ENUM no BD
    private ?string $hr_entrada;   // DATETIME/ TIMESTAMP (ajuste conforme seu BD)
    private ?string $hr_saida;     // DATETIME/ TIMESTAMP (ajuste conforme seu BD)

    // Campos auxiliares para joins (não existem na tabela):
    public ?array $cliente = null; // quando buscar com join
    public ?array $vaga = null;    // opcional

    public function __construct(
        int $id_veiculo = 0,
        ?int $id_vaga = null,
        int $id_cliente = 0,
        string $placa = "",
        string $cor = "",
        string $marca = "",
        string $modelo = "",
        string $tipo_veiculo = "",
        ?string $hr_entrada = null,
        ?string $hr_saida = null
    ) {
        $this->id_veiculo   = $id_veiculo;
        $this->id_vaga      = $id_vaga;
        $this->id_cliente   = $id_cliente;
        $this->placa        = $placa;
        $this->cor          = $cor;
        $this->marca        = $marca;
        $this->modelo       = $modelo;
        $this->tipo_veiculo = $tipo_veiculo;
        $this->hr_entrada   = $hr_entrada;
        $this->hr_saida     = $hr_saida;
    }

    public function __get($prop)
    {
        if (property_exists($this, $prop)) return $this->$prop;
        throw new Exception("Propriedade {$prop} não existe.");
    }

    public function __set($prop, $valor)
    {
        if (property_exists($this, $prop)) { $this->$prop = $valor; return; }
        throw new Exception("Propriedade {$prop} não permitida.");
    }

    /* ===================== Conexão ===================== */
    private static function getConexao()
    {
        return (new Conexao())->conexao();
    }

    /* =============== Util: ENUM de tipo_veiculo =============== 
       Busca os valores possíveis do ENUM direto do INFORMATION_SCHEMA.
       Se preferir, troque por um array fixo, ex.: return ['CARRO','MOTO',...];
    */
    public static function obterOpcoesTipoVeiculo(): array
    {
        $pdo = self::getConexao();

        // Ajuste o nome do BD se seu schema não for o database atual de conexão:
        $stmt = $pdo->query("
            SELECT COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'Veiculo'
              AND COLUMN_NAME = 'tipo_veiculo'
        ");

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || empty($row['COLUMN_TYPE'])) return [];

        // Ex.: "enum('CARRO','MOTO','CAMINHONETE')" -> ['CARRO','MOTO','CAMINHONETE']
        $enum = $row['COLUMN_TYPE'];
        preg_match("/^enum\((.*)\)$/i", $enum, $matches);
        if (!isset($matches[1])) return [];

        $vals = array_map(function($v){
            return trim($v, " '");
        }, explode(',', $matches[1]));

        return $vals;
    }

    private static function validarTipoVeiculo(string $tipo): bool
    {
        $opcoes = self::obterOpcoesTipoVeiculo();
        if (empty($opcoes)) return true; // fallback: não validar se não conseguir ler
        return in_array($tipo, $opcoes, true);
    }

    /* ===================== Inserir ===================== */
    public function inserir(): int
    {
        if (!self::validarTipoVeiculo($this->tipo_veiculo)) {
            throw new Exception("tipo_veiculo inválido para o ENUM.");
        }

        $pdo = self::getConexao();

        $sql = "INSERT INTO Veiculo
                (id_vaga, id_cliente, placa, cor, marca, modelo, tipo_veiculo, hr_entrada, hr_saida)
                VALUES
                (:id_vaga, :id_cliente, :placa, :cor, :marca, :modelo, :tipo_veiculo, :hr_entrada, :hr_saida)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":id_vaga"      => $this->id_vaga,
            ":id_cliente"   => $this->id_cliente,
            ":placa"        => $this->placa,
            ":cor"          => $this->cor,
            ":marca"        => $this->marca,
            ":modelo"       => $this->modelo,
            ":tipo_veiculo" => $this->tipo_veiculo,
            ":hr_entrada"   => $this->hr_entrada,
            ":hr_saida"     => $this->hr_saida
        ]);

        $id = (int)$pdo->lastInsertId();
        if ($id <= 0) throw new Exception("Não foi possível inserir o veículo.");

        return $id;
    }

    /* ===================== Atualizar ===================== */
    public function atualizar(): bool
    {
        if (!self::validarTipoVeiculo($this->tipo_veiculo)) {
            throw new Exception("tipo_veiculo inválido para o ENUM.");
        }

        $pdo = self::getConexao();

        $sql = "UPDATE Veiculo SET
                    id_vaga      = :id_vaga,
                    id_cliente   = :id_cliente,
                    placa        = :placa,
                    cor          = :cor,
                    marca        = :marca,
                    modelo       = :modelo,
                    tipo_veiculo = :tipo_veiculo,
                    hr_entrada   = :hr_entrada,
                    hr_saida     = :hr_saida
                WHERE id_veiculo = :id_veiculo";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":id_vaga"      => $this->id_vaga,
            ":id_cliente"   => $this->id_cliente,
            ":placa"        => $this->placa,
            ":cor"          => $this->cor,
            ":marca"        => $this->marca,
            ":modelo"       => $this->modelo,
            ":tipo_veiculo" => $this->tipo_veiculo,
            ":hr_entrada"   => $this->hr_entrada,
            ":hr_saida"     => $this->hr_saida,
            ":id_veiculo"   => $this->id_veiculo
        ]);

        return $stmt->rowCount() > 0;
    }

    /* ===================== Excluir (somente veículo) ===================== */
    public static function excluir(int $id_veiculo): bool
    {
        $pdo = self::getConexao();

        // Remove o veículo. Isso automaticamente "desvincula" do cliente,
        // porque o vínculo é o registro em Veiculo (FK id_cliente).
        $sql = "DELETE FROM Veiculo WHERE id_veiculo = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":id" => $id_veiculo]);

        return $stmt->rowCount() > 0;
    }

    /* ===================== Listar (para HTML) ===================== 
       Ideal para a lista da sua View: traz info do cliente junto.
    */
    public static function listar(): array
    {
        $pdo = self::getConexao();

        $sql = "SELECT 
                    v.*,
                    c.nome      AS cliente_nome,
                    c.tipo_cliente,
                    c.telefone  AS cliente_telefone
                FROM Veiculo v
                INNER JOIN Cliente c ON c.id_cliente = v.id_cliente
                LEFT JOIN Vaga vg    ON vg.id_vaga = v.id_vaga
                ORDER BY v.hr_entrada DESC, v.id_veiculo DESC";

        $stmt = $pdo->query($sql);
        $dados = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dados[] = $row; // pode montar objetos, mas para View é prático array assoc
        }

        return $dados;
    }

    /* ============ Buscar por ID (veículo + cliente + vaga) ============ */
    public static function buscarPorID(int $id_veiculo): ?Veiculo
    {
        $pdo = self::getConexao();

        $sql = "SELECT 
                    v.*,
                    c.id_cliente, c.tipo_cliente, c.nome, c.telefone, c.endereco, c.bairro,
                    vg.id_vaga, vg.codigo_vaga, vg.disponibilidade
                FROM Veiculo v
                INNER JOIN Cliente c ON c.id_cliente = v.id_cliente
                LEFT JOIN Vaga vg    ON vg.id_vaga = v.id_vaga
                WHERE v.id_veiculo = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([":id" => $id_veiculo]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $veic = new Veiculo(
            id_veiculo:   (int)$row['id_veiculo'],
            id_vaga:      $row['id_vaga'] !== null ? (int)$row['id_vaga'] : null,
            id_cliente:   (int)$row['id_cliente'],
            placa:        $row['placa'],
            cor:          $row['cor'],
            marca:        $row['marca'],
            modelo:       $row['modelo'],
            tipo_veiculo: $row['tipo_veiculo'],
            hr_entrada:   $row['hr_entrada'],
            hr_saida:     $row['hr_saida']
        );

        // Dados do cliente agregados
        $veic->cliente = [
            "id_cliente"   => (int)$row['id_cliente'],
            "tipo_cliente" => $row['tipo_cliente'],
            "nome"         => $row['nome'],
            "telefone"     => $row['telefone'],
            "endereco"     => $row['endereco'],
            "bairro"       => $row['bairro']
        ];

        // Dados da vaga (se houver)
        if (!empty($row['codigo_vaga'])) {
            $veic->vaga = [
                "id_vaga"        => (int)$row['id_vaga'],
                "codigo_vaga"    => $row['codigo_vaga'],
                "disponibilidade"=> $row['disponibilidade']
            ];
        }

        return $veic;
    }

    /* ============ Listar veículos por cliente (para formulário do cliente) ============ */
    public static function listarPorCliente(int $id_cliente): array
    {
        $pdo = self::getConexao();

        $sql = "SELECT * FROM Veiculo WHERE id_cliente = :id ORDER BY hr_entrada DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":id" => $id_cliente]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}