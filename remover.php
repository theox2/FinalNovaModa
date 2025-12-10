<?php
/**
 * api/carrinho/remover.php - Remover item do carrinho
 * Método: POST
 * Body: { usuario_id, produto_id }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Apenas POST permitido']));
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $usuario_id = $input['usuario_id'] ?? null;
    $produto_id = $input['produto_id'] ?? null;
    
    if (!$usuario_id || !$produto_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'usuario_id e produto_id são obrigatórios'
        ]);
        exit;
    }
    
    // Buscar carrinho do usuário
    $stmt = $pdo->prepare("SELECT id FROM carrinhos WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $carrinho = $stmt->fetch();
    
    if (!$carrinho) {
        echo json_encode([
            'success' => false,
            'message' => 'Carrinho não encontrado'
        ]);
        exit;
    }
    
    // Remover item
    $stmt = $pdo->prepare("
        DELETE FROM carrinho_itens 
        WHERE carrinho_id = ? AND produto_id = ?
    ");
    $stmt->execute([$carrinho['id'], $produto_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Item removido do carrinho',
        'itens_removidos' => $stmt->rowCount()
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao remover do carrinho',
        'error' => $e->getMessage()
    ]);
}
?>