<?php
/**
 * api/admin/pedidos/atualizar.php - Atualizar Status do Pedido
 * COLOQUE EM: /Novamoda/api/admin/pedidos/atualizar.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Apenas POST permitido']));
}

error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../../../config.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $pedido_id = $input['pedido_id'] ?? null;
    $status = $input['status'] ?? null;
    
    if (!$pedido_id || !$status) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'pedido_id e status são obrigatórios'
        ]);
        exit;
    }
    
    // Validar status
    $status_validos = ['pendente', 'processando', 'enviado', 'entregue', 'cancelado'];
    if (!in_array($status, $status_validos)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Status inválido'
        ]);
        exit;
    }
    
    // Verificar se pedido existe
    $stmt = $pdo->prepare("SELECT id FROM pedidos WHERE id = ?");
    $stmt->execute([$pedido_id]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Pedido não encontrado'
        ]);
        exit;
    }
    
    // Atualizar status
    $stmt = $pdo->prepare("
        UPDATE pedidos 
        SET status = ?, data_atualizacao = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([$status, $pedido_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Status atualizado com sucesso'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar pedido',
        'error' => $e->getMessage()
    ]);
}
?>