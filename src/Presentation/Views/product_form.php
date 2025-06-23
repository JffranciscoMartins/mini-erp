<?php
namespace MiniERP\Presentation\Views;

use MiniERP\Presentation\Controllers\ProductController;
use MiniERP\Infrastructure\Repositories\MysqlProductRepository;
use MiniERP\Infrastructure\Repositories\MysqlStockRepository;

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/Infrastructure/Database/Database.php';

$controller = new ProductController();
$controller->handleRequest();

$productRepo = new MysqlProductRepository();
$stockRepo = new MysqlStockRepository();
$products = $productRepo->findAll();

$editingProductId = $_GET['edit'] ?? null;
$editingProduct = $editingProductId ? $productRepo->findById((int)$editingProductId) : null;
$editingStocks = $editingProductId ? $stockRepo->findByProductId((int)$editingProductId) : [];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Mini ERP - Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="p-4 bg-light">
<div class="container">
    <h2 class="mb-4"><?= $editingProduct ? 'Editar Produto' : 'Cadastrar Produto' ?></h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            Produto <?= $_GET['success'] === 'created' ? 'criado' : 'atualizado' ?> com sucesso!
        </div>
    <?php endif; ?>

    <form method="POST" class="card p-4 mb-5">
        <input type="hidden" name="product_id" value="<?= $editingProduct?->getId() ?? '' ?>" />
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Nome</label>
                <input type="text" name="name" class="form-control" value="<?= $editingProduct?->getName() ?? '' ?>" required />
            </div>
            <div class="col-md-6">
                <label class="form-label">Preço</label>
                <input type="number" name="price" step="0.01" class="form-control" value="<?= $editingProduct?->getPrice() ?? '' ?>" required />
            </div>
        </div>

        <label class="form-label">Variações e Estoque</label>
        <div id="variation-container">
            <?php foreach ($editingStocks as $stock): ?>
                <div class="input-group mb-2">
                    <input type="text" name="variations[]" value="<?= htmlspecialchars($stock->getVariation()) ?>" class="form-control" />
                    <input type="number" name="quantities[]" value="<?= $stock->getQuantity() ?>" class="form-control" />
                </div>
            <?php endforeach; ?>
            <?php if (empty($editingStocks)): ?>
                <div class="input-group mb-2">
                    <input type="text" name="variations[]" placeholder="Variação (ex: P - Azul)" class="form-control" />
                    <input type="number" name="quantities[]" placeholder="Quantidade" class="form-control" />
                </div>
            <?php endif; ?>
        </div>
        <button type="button" class="btn btn-sm btn-secondary mb-3" onclick="addVariation()">Adicionar Variação</button>

        <button type="submit" class="btn btn-primary"><?= $editingProduct ? 'Atualizar' : 'Salvar' ?> Produto</button>
    </form>

    <hr />

    <h3>Produtos</h3>
    <div class="mb-5">
        <?php foreach ($products as $product): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5><?= htmlspecialchars($product->getName()) ?> - R$ <?= number_format($product->getPrice(), 2, ',', '.') ?></h5>
                    <form class="buy-form row g-2" data-product-id="<?= $product->getId() ?>">
                        <div class="col-md-4">
                            <select name="variation" class="form-select" required>
                                <option value="">Variação</option>
                                <?php foreach ($stockRepo->findByProductId($product->getId()) as $stock): ?>
                                    <option value="<?= htmlspecialchars($stock->getVariation()) ?>">
                                        <?= htmlspecialchars($stock->getVariation()) ?> (Estoque: <?= $stock->getQuantity() ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="quantity" min="1" value="1" class="form-control" required />
                        </div>
                        <div class="col-md-6 d-flex gap-2">
                            <button type="submit" class="btn btn-success">Comprar</button>
                            <a href="?edit=<?= $product->getId() ?>" class="btn btn-warning">Editar</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h3>Carrinho</h3>
    <div id="cart-info" class="mb-5 p-3 bg-white rounded shadow-sm">
        <p>Carregando carrinho...</p>
    </div>

    <h3>Consulta CEP</h3>
    <div class="row g-2">
        <div class="col-md-4">
            <input type="text" id="cep" placeholder="Digite o CEP (somente números)" class="form-control" />
        </div>
        <div class="col-md-2">
            <button id="checkCepBtn" class="btn btn-primary">Verificar CEP</button>
        </div>
        <div class="col-md-6">
            <pre id="cepResult" class="bg-light p-2 rounded"></pre>
        </div>
    </div>
</div>

<script>
    function addVariation() {
        const container = document.getElementById('variation-container');
        container.insertAdjacentHTML('beforeend', `
            <div class="input-group mb-2">
                <input type="text" name="variations[]" placeholder="Variação" class="form-control" />
                <input type="number" name="quantities[]" placeholder="Quantidade" class="form-control" />
            </div>
        `);
    }

    async function fetchCart() {
        const res = await fetch('?action=view_cart');
        const data = await res.json();

        const cartDiv = document.getElementById('cart-info');

        if (!data.cart || data.cart.length === 0) {
            cartDiv.innerHTML = '<p>Carrinho vazio.</p>';
            return;
        }

        let html = `<ul class="list-group mb-3">`;
        data.cart.forEach(item => {
            html += `<li class="list-group-item">${item.product_name} - ${item.variation} | Qty: ${item.quantity} | R$ ${(item.price * item.quantity).toFixed(2)}</li>`;
        });
        html += `</ul>`;
        html += `<p>Subtotal: R$ ${data.subtotal.toFixed(2)}</p>`;
        html += `<p>Frete: R$ ${data.shipping.toFixed(2)}</p>`;
        html += `<h5>Total: R$ ${data.total.toFixed(2)}</h5>`;

        cartDiv.innerHTML = html;
    }

    document.querySelectorAll('.buy-form').forEach(form => {
        form.addEventListener('submit', async e => {
            e.preventDefault();
            const productId = form.getAttribute('data-product-id');
            const formData = new FormData(form);
            formData.append('product_id', productId);

            const res = await fetch('?action=add_to_cart', {
                method: 'POST',
                body: formData,
            });

            const data = await res.json();
            if (data.error) {
                alert('Erro: ' + data.error);
            } else {
                alert(data.success);
                fetchCart();
            }
        });
    });

    document.getElementById('checkCepBtn').addEventListener('click', async () => {
        const cep = document.getElementById('cep').value.replace(/\D/g, '');
        if (cep.length !== 8) {
            alert('Digite um CEP válido com 8 números.');
            return;
        }

        const res = await fetch(`?action=check_cep&cep=${cep}`);
        const data = await res.json();

        if (data.error) {
            alert('Erro: ' + data.error);
            document.getElementById('cepResult').textContent = '';
        } else {
            document.getElementById('cepResult').textContent = JSON.stringify(data, null, 2);
        }
    });

    fetchCart();
</script>
</body>
</html>
