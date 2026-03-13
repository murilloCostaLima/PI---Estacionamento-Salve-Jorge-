<?php
require_once(__DIR__."/../config/conexao.php");
 
class vaga
{
    private int $id_vaga;
    private int $codigo_vaga;
    private string $disponibilidade; // ENUM('disponível','ocupada')
 
    public function __construct(
        int $id_vaga = 0,
        int $codigo_vaga,
        string $disponibilidade = "disponível")
    {
        $this->id_vaga        = $id_vaga;
        $this->codigo_vaga    = $codigo_vaga;
        $this->disponibilidade = $disponibilidade;
    }
 
    private static function getConexao()
    {
        return (new Conexao())->conexao();
    }
 
    /* =====================================================
       1. NÃO PERMITIR MAIS QUE 90 VAGAS
    ====================================================== */
    public static function inserir(string $codigoVaga, bool $disponibilidade): string
    {
        $pdo = self::getConexao();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try
        {
            // Sempre começa transação
            $pdo->beginTransaction();

            // Verifica quantas vagas já existem
            $stmtCount = $pdo->query("SELECT COUNT(*) AS total FROM vaga FOR UPDATE");
            $count = (int) $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

            if ($count >= 90)
            {
                // Cancela transação e lança erro
                $pdo->rollBack();
                throw new Exception("O estacionamento já possui o máximo de 90 vagas.");
            }

            // Insere a nova vaga
            $sql = "INSERT INTO vaga (codigo_vaga, disponibilidade)
                    VALUES (:codigo, :disp)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':codigo', $codigoVaga);
            $stmt->bindValue(':disp',   $disponibilidade, PDO::PARAM_BOOL);

            $stmt->execute();

            // Finaliza a transação
            $pdo->commit();

            return $pdo->lastInsertId();

        }  
        catch (Throwable $e)
        {

            if ($pdo->inTransaction())
            {
                $pdo->rollBack();
            }

            throw new Exception("Erro ao inserir vaga: " . $e->getMessage(), 0, $e);
        }
    }
 
    /* =====================================================
       2. LISTAR TODAS AS VAGAS
    ====================================================== */
    public static function listar()
    {
        $pdo = self::getConexao();
        $stmt = $pdo->query("SELECT * FROM vaga ORDER BY codigo_vaga ASC");
 
        $vagas = [];
 
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $vagas[] = new vaga(
                id_vaga:        $row['id_vaga'],
                codigo_vaga:    $row['codigo_vaga'],
                disponibilidade: $row['disponibilidade']
            );
        }
        return $vagas;
    }
 
    /* =====================================================
       3. BUSCAR VAGA POR ID
    ====================================================== */
    public static function buscarPorID(int $id)
    {
        $pdo = self::getConexao();
 
        $stmt = $pdo->prepare("SELECT * FROM vaga WHERE id_vaga = :id");
        $stmt->execute([":id" => $id]);
 
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
        if (!$row) return null;
 
        return new vaga(
            id_vaga:         $row['id_vaga'],
            codigo_vaga:     $row['codigo_vaga'],
            disponibilidade: $row['disponibilidade']
        );
    }
 
    /* =====================================================
       4. OCUPAR VAGA AO INSERIR VEÍCULO
          - Regras importantes aqui!
    ====================================================== */
    public static function ocuparVaga(int $codigoVaga, string $tipoVeiculo, string $tipoCliente)
    {
        $pdo = self::getConexao();
 
        // Regra: vagas 85–90 apenas motos
        if ($codigoVaga >= 85 && $codigoVaga <= 90 && $tipoVeiculo !== "moto") {
            throw new Exception("As vagas de 85 a 90 são exclusivas para motos.");
        }
 
        // Verifica disponibilidade
        $sql = "SELECT disponibilidade FROM vaga WHERE codigo_vaga = :cod";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":cod" => $codigoVaga]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
        if (!$row) {
            throw new Exception("Vaga não encontrada.");
        }
 
        if ($row["disponibilidade"] === "ocupada") {
            throw new Exception("A vaga já está ocupada.");
        }
 
        // Ocupa a vaga
        $sql = "UPDATE vaga SET disponibilidade = 'ocupada' WHERE codigo_vaga = :cod";
 
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":cod" => $codigoVaga]);
 
        return true;
    }
 
    /* =====================================================
       5. LIBERAR VAGA
          - Somente avulso
    ====================================================== */
    public static function liberarVaga(int $codigoVaga, string $tipoCliente)
    {
        if ($tipoCliente === "mensal") {
            throw new Exception("Clientes mensais não liberam vaga (vaga fixa).");
        }
 
        $pdo = self::getConexao();
 
        $sql = "UPDATE vaga SET disponibilidade = 'disponivel' WHERE codigo_vaga = :cod";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":cod" => $codigoVaga]);
 
        return true;
    }
}
try{
    print_r(vaga::inserir(3, "disponivel")); 
}catch(Exception $err){
    echo $err->getMessage();
}
?>