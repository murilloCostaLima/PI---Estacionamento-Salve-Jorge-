<?php
require_once(__DIR__."/../config/conexao.php");
require_once(__DIR__."/../config/autoload.php");
 
class vaga
{
    private int $id_vaga;
    private int $codigo_vaga;
    private string $disponibilidade; // ENUM('disponivel','ocupada')
 
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
       1. LISTAR TODAS AS VAGAS
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
                disponibilidade: $row['disponibilidade']);
        }
        return $vagas;
    }
 
    /* =====================================================
       2. BUSCAR VAGA POR ID
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
            disponibilidade: $row['disponibilidade']);
    }
 
    /* =====================================================
       3. OCUPAR VAGA AO INSERIR VEÍCULO
          - Regras importantes aqui!
    ====================================================== */
    public static function ocuparVaga(int $codigoVaga, string $tipoVeiculo, string $tipoCliente)
    {
        $pdo = self::getConexao();
 
        // Regra: vagas 85–90 apenas motos
        if ($codigoVaga >= 85 && $codigoVaga <= 90 && $tipoVeiculo !== "moto")
        {
            throw new Exception("As vagas de 85 a 90 são exclusivas para motos.");
        }
 
        // Verifica disponibilidade
        $sql = "SELECT disponibilidade FROM vaga WHERE codigo_vaga = :cod";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":cod" => $codigoVaga]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
        if (!$row)
        {
            throw new Exception("Vaga não encontrada.");
        }
 
        if ($row["disponibilidade"] === "ocupada")
        {
            throw new Exception("A vaga já está ocupada.");
        }
 
        // Ocupa a vaga
        $sql = "UPDATE vaga SET disponibilidade = 'ocupada' WHERE codigo_vaga = :cod";
 
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":cod" => $codigoVaga]);
 
        return true;
    }
 
    /* =====================================================
       4. LIBERAR VAGA
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

    /* ===================================================================================
       5. PERMITIR QUE AS VAGAS 1-84 APENAS CARROS OU CARROS GRANDES, E 85-90 APENAS MOTOS
    ====================================================================================== */
    public static function validarVagaPorTipo(int $codigoVaga, string $tipoVeiculo)
    {
        $tipo = strtolower(trim($tipoVeiculo));

        if ($codigoVaga >= 1 && $codigoVaga <= 84 && !in_array($tipo, ["carro", "carro grande"]))
        {
            throw new Exception("Vagas 1 a 84 são permitidas apenas para carros.");
        }
        elseif ($codigoVaga >= 85 && $codigoVaga <= 90 && $tipo !== "moto")
        {
            throw new Exception("Vagas 85 a 90 são exclusivas para motos.");
        }
    }
}