<?php

define("ARQUIVO", "produtos.json");

function carregarProdutos() {
    if (!file_exists(ARQUIVO)) {
        file_put_contents(ARQUIVO, json_encode([]));
    }
    $dados = file_get_contents(ARQUIVO);
    return json_decode($dados, true);
}

function salvarProdutos($produtos) {
    file_put_contents(ARQUIVO, json_encode($produtos, JSON_PRETTY_PRINT));
}

// Processar ações do formulário
$produtos = carregarProdutos();
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $nome = trim($_POST['nome']);
    $preco = floatval($_POST['preco']);
    $quantidade = intval($_POST['quantidade']);

    if ($id) {
        // Atualizar
        foreach ($produtos as &$p) {
            if ($p['id'] === $id) {
                $p['nome'] = $nome;
                $p['preco'] = $preco;
                $p['quantidade'] = $quantidade;
                $mensagem = "Produto atualizado.";
                break;
            }
        }
    } else {
        // Cadastrar
        $novo = [
            "id" => uniqid(),
            "nome" => $nome,
            "preco" => $preco,
            "quantidade" => $quantidade
        ];
        $produtos[] = $novo;
        $mensagem = "Produto cadastrado.";
    }

    salvarProdutos($produtos);
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

if (isset($_GET['excluir'])) {
    $idExcluir = $_GET['excluir'];
    foreach ($produtos as $i => $p) {
        if ($p['id'] === $idExcluir) {
            array_splice($produtos, $i, 1);
            salvarProdutos($produtos);
            $mensagem = "Produto excluído.";
            break;
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Se for edição
$produtoEditar = null;
if (isset($_GET['editar'])) {
    foreach ($produtos as $p) {
        if ($p['id'] === $_GET['editar']) {
            $produtoEditar = $p;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Produtos</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        input, button { padding: 5px; margin: 5px; }
        table { margin-top: 20px; border-collapse: collapse; width: 100%; }
        table, th, td { border: 1px solid #aaa; padding: 8px; }
    </style>
</head>
<body>
    <h2><?= $produtoEditar ? 'Editar Produto' : 'Cadastrar Produto' ?></h2>

    <form method="post" action="">
    <input type="hidden" name="id" value="<?= $produtoEditar['id'] ?? '' ?>">
    <input type="text" name="nome" placeholder="Nome" required value="<?= $produtoEditar['nome'] ?? '' ?>">
    <input type="number" name="preco" step="0.01" placeholder="Preço" required value="<?= $produtoEditar['preco'] ?? '' ?>">
    <input type="number" name="quantidade" placeholder="Quantidade" required value="<?= $produtoEditar['quantidade'] ?? '' ?>">
    <button type="submit"><?= $produtoEditar ? 'Atualizar' : 'Cadastrar' ?></button>
</form>


    <h3>Lista de Produtos</h3>
    <table>
        <thead>
            <tr><th>ID</th><th>Nome</th><th>Preço</th><th>Quantidade</th><th>Ações</th></tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['nome']) ?></td>
                    <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                    <td><?= $p['quantidade'] ?></td>
                    <td>
                        <a href="?editar=<?= $p['id'] ?>">✏️ Editar</a> |
                        <a href="?excluir=<?= $p['id'] ?>" onclick="return confirm('Excluir este produto?')">🗑️ Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($produtos)): ?>
                <tr><td colspan="5">Nenhum produto cadastrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
