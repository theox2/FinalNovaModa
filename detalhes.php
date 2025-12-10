<?php
/**
 * api/admin/pedidos/detalhes.php - Detalhes do Pedido
 * COLOQUE EM: /Novamoda/api/admin/pedidos/detalhes.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../../../config.php';
    
    $pedido_id = $_GET['id'] ?? null;
    
    if (!$pedido_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ID do pedido é obrigatório'
        ]);
        exit;
    }
    
    // Buscar pedido
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.numero_pedido,
            p.status,
            p.forma_pagamento,
            p.subtotal,
            p.desconto,
            p.frete,
            p.total,
            p.data_pedido,
            p.data_atualizacao,
            u.id as cliente_id,
            u.nome as cliente_nome,
            u.email as cliente_email,
            u.telefone as cliente_telefone,
            e.cep,
            e.estado,
            e.cidade,
            e.bairro,
            e.endereco,
            e.numero,
            e.complemento
        FROM pedidos p
        LEFT JOIN usuarios u ON p.usuario_id = u.id
        LEFT JOIN enderecos e ON p.endereco_id = e.id
        WHERE p.id = ?
    ");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Pedido não encontrado'
        ]);
        exit;
    }
    
    // Buscar itens
    $stmt = $pdo->prepare("
        SELECT 
            pi.id,
            pi.produto_id,
            pi.nome_produto,
            pi.quantidade,
            pi.tamanho,
            pi.cor,
            pi.preco_unitario,
            pi.subtotal,
            p.imagem_principal
        FROM pedido_itens pi
        LEFT JOIN produtos p ON pi.produto_id = p.id
        WHERE pi.pedido_id = ?
    ");
    $stmt->execute([$pedido_id]);
    $pedido['itens'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar endereço
    $pedido['endereco'] = [
        'cep' => $pedido['cep'],
        'estado' => $pedido['estado'],
        'cidade' => $pedido['cidade'],
        'bairro' => $pedido['bairro'],
        'endereco' => $pedido['endereco'],
        'numero' => $pedido['numero'],
        'complemento' => $pedido['complemento']
    ];
    
    // Limpar dados duplicados
    unset(
        $pedido['cep'],
        $pedido['estado'],
        $pedido['cidade'],
        $pedido['bairro'],
        $pedido['numero'],
        $pedido['complemento']
    );
    
    // Formatar valores
    $pedido['id'] = (int)$pedido['id'];
    $pedido['cliente_id'] = (int)$pedido['cliente_id'];
    $pedido['subtotal'] = (float)$pedido['subtotal'];
    $pedido['desconto'] = (float)$pedido['desconto'];
    $pedido['frete'] = (float)$pedido['frete'];
    $pedido['total'] = (float)$pedido['total'];
    
    echo json_encode([
        'success' => true,
        'data' => $pedido
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar detalhes do pedido',
        'error' => $e->getMessage()
    ]);
}
?>