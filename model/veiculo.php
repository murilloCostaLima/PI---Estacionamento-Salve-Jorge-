<?php
require_once(__DIR__ . "/../config/conexao.php");
require_once(__DIR__ . "/../config/autoload.php");

class veiculo
{
    private ?int     $id_veiculo;
    private ?int    $id_vaga;
    private int     $id_cliente;
    private string  $placa;
    private string  $cor;
    private string  $marca;
    private string  $modelo;
    private string  $tipo_veiculo; // ENUM no BD
    private ?string $hr_entrada;   // DATETIME/ TIMESTAMP (ajuste conforme o BD)
    private ?string $hr_saida;     // DATETIME/ TIMESTAMP (ajuste conforme o BD)

    // Campos auxiliares para joins (não existem na tabela):
    public ?array $cliente = null; // quando buscar com join
    public ?array $vaga = null;    // opcional

    public function __construct(
        ?int $id_veiculo = 0,
        ?int $id_vaga = null,
        int $id_cliente = 0,
        string $placa = "",
        string $cor = "",
        string $marca = "",
        string $modelo = "",
        string $tipo_veiculo,
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
        if (property_exists($this, $prop)) {
            $this->$prop = $valor;
            return;
        }
        throw new Exception("Propriedade {$prop} não permitida.");
    }

    /* ===================== Conexão ===================== */
    private static function getConexao()
    {
        return (new Conexao())->conexao();
    }

    /* =============== ENUM de tipo_veiculo =============== 
       Busca os valores possíveis do ENUM direto do INFORMATION_SCHEMA.
       Se preferir, troque por um array fixo, ex.: return ['CARRO','MOTO',...];
    */
    public static function obterOpcoesTipoVeiculo(): array
    {
        $pdo = self::getConexao();

        $stmt = $pdo->query("
            SELECT COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'veiculo'
              AND COLUMN_NAME = 'tipo_veiculo'
        ");

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || empty($row['COLUMN_TYPE'])) return [];

        // Ex.: "enum('CARRO','MOTO','CARRO GRANDE')" -> ['CARRO','MOTO','CARRO GRANDE']
        $enum = $row['COLUMN_TYPE'];
        preg_match("/^enum\((.*)\)$/i", $enum, $matches);
        if (!isset($matches[1])) return [];

        $vals = array_map(function ($v) {
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
    public static function inserir(
        int    $id_vaga,
        int    $id_cliente,
        string $placa,
        string $cor,
        string $marca,
        string $modelo,
        string $tipo_veiculo
    ): int {
        $pdo = self::getConexao();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Normalizar placa
        $placa = strtoupper(trim($placa));

        try {
            $pdo->beginTransaction();

            // Verificar duplicidade de placa
            $stmt = $pdo->prepare("SELECT 1 FROM veiculo WHERE placa = :placa LIMIT 1");
            $stmt->execute([":placa" => $placa]);

            if ($stmt->fetch()) {
                throw new Exception("Esta placa já está cadastrada. Por favor, utilize outra.");
            }

            // Buscar dados da vaga
            $stmt = $pdo->prepare("SELECT codigo_vaga, disponibilidade FROM vaga WHERE id_vaga = :id");
            $stmt->execute([":id" => $id_vaga]);
            $vaga = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$vaga)
                throw new Exception("Vaga não encontrada.");

            if ($vaga['disponibilidade'] === 'ocupada')
                throw new Exception("Vaga já está ocupada.");

            // Validar regra moto (85–90)
            vaga::validarVagaPorTipo((int)$vaga['codigo_vaga'], $tipo_veiculo);

            // Buscar cliente
            $stmt = $pdo->prepare("SELECT tipo_cliente FROM cliente WHERE id_cliente = :id");
            $stmt->execute([":id" => $id_cliente]);
            $tipoCliente = $stmt->fetchColumn();

            if (!$tipoCliente)
                throw new Exception("Cliente não encontrado.");

            // Inserir veículo
            $stmt = $pdo->prepare("
            INSERT INTO veiculo(id_vaga, id_cliente, placa, cor, marca, modelo, tipo_veiculo, hr_entrada, hr_saida)
            VALUES(:vaga, :cliente, :placa, :cor, :marca, :modelo, :tipo, NOW(), NULL)");

            $stmt->execute([
                ":vaga"    => $id_vaga,
                ":cliente" => $id_cliente,
                ":placa"   => $placa,
                ":cor"     => $cor,
                ":marca"   => $marca,
                ":modelo"  => $modelo,
                ":tipo"    => $tipo_veiculo
            ]);

            $idVeiculo = (int)$pdo->lastInsertId();

            // Ocupa vaga
            $stmt = $pdo->prepare("UPDATE vaga SET disponibilidade = 'ocupada' WHERE id_vaga = :vaga");
            $stmt->execute([":vaga" => $id_vaga]);

            // Criar chave automaticamente
            $stmt = $pdo->prepare("INSERT INTO chave (id_veiculo, id_vaga) VALUES (:v, :g)");
            $stmt->execute([":v" => $idVeiculo, ":g" => $id_vaga]);

            $pdo->commit();
            return $idVeiculo;
        } catch (Throwable $e) {
            if ($pdo->inTransaction())
                $pdo->rollBack();

            throw new Exception("Erro ao inserir veículo: " . $e->getMessage());
        }
    }
    /* ================= Registrar Saída ================= */
    public static function registrarSaida(int $id_veiculo): bool
    {
        $pdo = self::getConexao();

        try {
            $pdo->beginTransaction();

            $dados = $pdo->query("SELECT v.id_vaga, c.tipo_cliente
                FROM veiculo v JOIN cliente c
                ON c.id_cliente=v.id_cliente
                WHERE v.id_veiculo=$id_veiculo
                FOR UPDATE")->fetch(PDO::FETCH_ASSOC);

            if (!$dados) throw new Exception("Veículo não encontrado.");

            $pdo->prepare("UPDATE veiculo SET hr_saida=NOW()
            WHERE id_veiculo=:id")->execute([":id" => $id_veiculo]);

            if ($dados['tipo_cliente'] !== "Mensal") {
                $pdo->prepare("
                UPDATE vaga SET disponibilidade='disponivel'
                WHERE id_vaga=:vaga")->execute([":vaga" => $dados['id_vaga']]);
            }

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw new Exception("Erro ao registrar saída: {$e->getMessage()}");
        }
    }
    /* ===================== Atualizar ===================== */
    public static function atualizar(
        ?int    $id_veiculo,
        ?int   $id_vaga,
        int    $id_cliente,
        string $placa,
        string $cor,
        string $marca,
        string $modelo,
        string $tipo_veiculo,
        ?string $hr_entrada = null,
        ?string $hr_saida = null
    ): bool {
        if (!self::validarTipoVeiculo($tipo_veiculo)) {
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
            ":id_vaga"      => $id_vaga,
            ":id_cliente"   => $id_cliente,
            ":placa"        => $placa,
            ":cor"          => $cor,
            ":marca"        => $marca,
            ":modelo"       => $modelo,
            ":tipo_veiculo" => $tipo_veiculo,
            ":hr_entrada"   => $hr_entrada,
            ":hr_saida"     => $hr_saida,
            ":id_veiculo"   => $id_veiculo
        ]);

        return $stmt->rowCount() > 0;
    }

    /* ===================== Excluir (somente veículo) ===================== */
    public static function excluir(int $id_veiculo): bool
    {
        $pdo = self::getConexao();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $pdo->beginTransaction();

            $stmtSel = $pdo->prepare("SELECT id_vaga, hr_saida FROM veiculo WHERE id_veiculo = :id FOR UPDATE");
            $stmtSel->execute([":id" => $id_veiculo]);
            $row = $stmtSel->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $pdo->rollBack();
                return false;
            }

            $id_vaga  = $row['id_vaga'] !== null ? (int)$row['id_vaga'] : null;
            $ativa    = $row['hr_saida'] === null;

            // Exclui chaves vinculadas
            $stmtC = $pdo->prepare("DELETE FROM chave WHERE id_veiculo = :id");
            $stmtC->execute([":id" => $id_veiculo]);

            // Exclui o veículo
            $stmtV = $pdo->prepare("DELETE FROM veiculo WHERE id_veiculo = :id");
            $stmtV->execute([":id" => $id_veiculo]);
            $apagou = $stmtV->rowCount() > 0;

            // Se o veículo ainda estava ocupando a vaga, liberar
            if ($apagou && $ativa && $id_vaga !== null) {
                $stmtUpd = $pdo->prepare("UPDATE vaga SET disponibilidade = 'disponivel' WHERE id_vaga = :id_vaga");
                $stmtUpd->execute([":id_vaga" => $id_vaga]);
            }

            $pdo->commit();
            return $apagou;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw new Exception("Falha ao excluir veículo/chave: " . $e->getMessage(), 0, $e);
        }
    }

    /* ===================== Listar (para HTML) ===================== 
                        traz info do cliente junto.
    */
    public static function listar(): array
    {
        $pdo = self::getConexao();

        $sql = "SELECT v.*, c.nome AS cliente_nome,
                    c.tipo_cliente,
                    c.telefone  AS cliente_telefone
                FROM veiculo v
                INNER JOIN cliente c ON c.id_cliente = v.id_cliente
                LEFT JOIN vaga vg    ON vg.id_vaga = v.id_vaga
                ORDER BY v.hr_entrada DESC, v.id_veiculo DESC";

        $stmt = $pdo->query($sql);
        $dados = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dados[] = $row; // pode montar objetos, mas para View é prático array assoc
        }

        return $dados;
    }

    /* ============ Buscar por ID (veículo + cliente + vaga) ============ */
    public static function buscarPorID(int $id_veiculo): ?veiculo
    {
        $pdo = self::getConexao();

        $sql = "SELECT 
                    v.*,
                    c.id_cliente, c.tipo_cliente, c.nome, c.telefone, c.endereco, c.bairro,
                    vg.id_vaga, vg.codigo_vaga, vg.disponibilidade
                FROM veiculo v
                INNER JOIN cliente c ON c.id_cliente = v.id_cliente
                LEFT JOIN vaga vg    ON vg.id_vaga = v.id_vaga
                WHERE v.id_veiculo = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([":id" => $id_veiculo]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $veic = new veiculo(
            id_veiculo: (int)$row['id_veiculo'],
            id_vaga: $row['id_vaga'] !== null ? (int)$row['id_vaga'] : null,
            id_cliente: (int)$row['id_cliente'],
            placa: $row['placa'],
            cor: $row['cor'],
            marca: $row['marca'],
            modelo: $row['modelo'],
            tipo_veiculo: (bool)$row['tipo_veiculo'],
            hr_entrada: $row['hr_entrada'],
            hr_saida: $row['hr_saida']
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

        // Dados da vaga
        if (!empty($row['codigo_vaga'])) {
            $veic->vaga = [
                "id_vaga"        => (int)$row['id_vaga'],
                "codigo_vaga"    => $row['codigo_vaga'],
                "disponibilidade" => $row['disponibilidade']
            ];
        }

        return $veic;
    }

    /* ============ Listar veículos por cliente (para formulário do cliente) ============ */
    public static function listarPorCliente(int $id_cliente): array
    {
        $pdo = self::getConexao();

        $sql = "SELECT * FROM veiculo WHERE id_cliente = :id ORDER BY hr_entrada DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":id" => $id_cliente]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ============ Filtros do Formulário inicial ============ */
    public static function listarComFiltros(array $filtros): array
    {
        $pdo = self::getConexao();

        // SQL base
        $sql = "SELECT v.*, 
               c.nome AS cliente_nome,
               c.telefone AS cliente_telefone,
               c.tipo_cliente,
               vg.codigo_vaga
        FROM veiculo v
        INNER JOIN cliente c ON c.id_cliente = v.id_cliente
        LEFT JOIN vaga vg ON vg.id_vaga = v.id_vaga
        WHERE 1 = 1";

        $params = [];

        // Opção 'desativado' desativada, amenos que entre na opção de editar
        if (
            empty($filtros['tipo_cliente']) ||
            strtolower($filtros['tipo_cliente']) !== 'desativado'
        ) {
            if (empty($filtros['busca'])) {
                $sql .= " AND c.tipo_cliente != 'Desativado'";
            }
        }

        // 🔍 Busca por placa, nome ou telefone
        if (!empty($filtros['busca'])) {
            $sql .= " AND (v.placa LIKE :busca OR c.nome
                LIKE :busca OR c.telefone LIKE :busca)";
            $params[':busca'] = '%' . $filtros['busca'] . '%';
        }

        // Filtro por tipo de veículo
        if (!empty($filtros['tipo_veiculo'])) {
            $sql .= " AND v.tipo_veiculo = :tipo_veiculo";
            $params[':tipo_veiculo'] = $filtros['tipo_veiculo'];
        }

        // 👤 Filtro por tipo de cliente
        if (!empty($filtros['tipo_cliente'])) {
            $sql .= " AND c.tipo_cliente = :tipo_cliente";
            $params[':tipo_cliente'] = ucfirst(strtolower($filtros['tipo_cliente']));
        }

        // Ordenação
        $sql .= " ORDER BY v.hr_entrada ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // NOVO MÉTODO – transacional
    public static function inserirComPDO(
        PDO $pdo,
        int $codigo_vaga,
        int $id_cliente,
        string $placa,
        string $cor,
        string $marca,
        string $modelo,
        string $tipo_veiculo
    ): void {

        // ===== Regras de negócio =====
        // if ($codigo_vaga >= 1 && $codigo_vaga <= 84 && $tipo_veiculo === 'moto') {
        //     throw new Exception("Vagas 1 a 84 são permitidas apenas para carros.");
        // }

        if ($codigo_vaga >= 85 && $codigo_vaga <= 90 && $tipo_veiculo !== 'moto') {
            throw new Exception("Vagas 85 a 90 são exclusivas para motos.");
        }

        // Buscar vaga disponível
        $stmt = $pdo->prepare("
        SELECT id_vaga FROM vaga 
        WHERE codigo_vaga = :vaga 
        AND disponibilidade = 'disponivel' FOR UPDATE");

        $stmt->execute([':vaga' => $codigo_vaga]);
        $id_vaga = $stmt->fetchColumn();

        if (!$id_vaga) {
            throw new Exception("Vaga não disponível.");
        }

        // Inserir veículo
        $stmt = $pdo->prepare("
        INSERT INTO veiculo (id_vaga, id_cliente, placa, cor, marca, modelo, tipo_veiculo, hr_entrada)
        VALUES (:vaga, :cliente, :placa, :cor, :marca, :modelo, :tipo, NOW())");

        $stmt->execute([
            ':vaga'    => $id_vaga,
            ':cliente' => $id_cliente,
            ':placa'   => strtoupper($placa),
            ':cor'     => $cor,
            ':marca'   => $marca,
            ':modelo'  => $modelo,
            ':tipo'    => $tipo_veiculo
        ]);

        // Ocupar vaga
        $stmt = $pdo->prepare("
        UPDATE vaga 
        SET disponibilidade = 'ocupada' 
        WHERE id_vaga = :vaga");

        $stmt->execute([':vaga' => $id_vaga]);
    }
}
