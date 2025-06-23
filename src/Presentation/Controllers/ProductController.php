<?php

namespace MiniERP\Presentation\Controllers;

use MiniERP\Application\UseCases\CreateProductUseCase;
use MiniERP\Application\UseCases\UpdateProductUseCase;
use MiniERP\Infrastructure\Repositories\MysqlProductRepository;
use MiniERP\Infrastructure\Repositories\MysqlStockRepository;

class ProductController
{
    private CreateProductUseCase $createProductUseCase;
    private UpdateProductUseCase $updateProductUseCase;
    private MysqlProductRepository $productRepository;
    private MysqlStockRepository $stockRepository;

    public function __construct()
    {
        $this->productRepository = new MysqlProductRepository();
        $this->stockRepository = new MysqlStockRepository();

        $this->createProductUseCase = new CreateProductUseCase(
            $this->productRepository,
            $this->stockRepository
        );

        $this->updateProductUseCase = new UpdateProductUseCase(
            $this->productRepository,
            $this->stockRepository
        );

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function handleRequest(): void
    {
        $action = $_GET['action'] ?? null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleFormSubmission();
        }

        match ($action) {
            'add_to_cart' => $this->addToCart(),
            'view_cart' => $this->viewCart(),
            'check_cep' => $this->checkCep(),
            default => null
        };
    }

    private function handleFormSubmission(): void
    {
        $name = trim($_POST['name'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $productId = $_POST['product_id'] ?? null;

        $stockItems = [];
        foreach ($_POST['variations'] ?? [] as $i => $variation) {
            $variation = trim($variation);
            $quantity = (int) ($_POST['quantities'][$i] ?? 0);

            if ($variation !== '') {
                $stockItems[] = [
                    'variation' => $variation,
                    'quantity' => $quantity
                ];
            }
        }

        if ($productId) {
            $this->updateProductUseCase->execute((int)$productId, $name, $price, $stockItems);
            header('Location: /public/index.php?success=updated');
        } else {
            $this->createProductUseCase->execute($name, $price, [], $stockItems);
            header('Location: /public/index.php?success=created');
        }

        exit;
    }

    private function addToCart(): void
    {
        header('Content-Type: application/json');

        $productId = (int) ($_POST['product_id'] ?? 0);
        $variation = $_POST['variation'] ?? '';
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

        $product = $this->productRepository->findById($productId);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Produto não encontrado']);
            exit;
        }

        $stocks = $this->stockRepository->findByProductId($productId);
        $stockAvailable = 0;
        foreach ($stocks as $stock) {
            if ($stock->getVariation() === $variation) {
                $stockAvailable = $stock->getQuantity();
                break;
            }
        }

        if ($stockAvailable < $quantity) {
            http_response_code(400);
            echo json_encode(['error' => 'Estoque insuficiente']);
            exit;
        }

        $key = $productId . '::' . $variation;
        $cart =& $_SESSION['cart'];

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $quantity;
        } else {
            $cart[$key] = [
                'product_id' => $productId,
                'product_name' => $product->getName(),
                'variation' => $variation,
                'price' => $product->getPrice(),
                'quantity' => $quantity,
            ];
        }

        echo json_encode(['success' => 'Produto adicionado ao carrinho']);
        exit;
    }

    private function viewCart(): void
    {
        $cart = $_SESSION['cart'] ?? [];
        $subtotal = 0;

        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shipping = match (true) {
            $subtotal >= 52 && $subtotal <= 166.59 => 15,
            $subtotal > 200 => 0,
            default => 20
        };

        $total = $subtotal + $shipping;

        header('Content-Type: application/json');
        echo json_encode([
            'cart' => $cart,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
        ]);
        exit;
    }

    private function checkCep(): void
    {
        header('Content-Type: application/json');

        $cep = preg_replace('/\D/', '', $_GET['cep'] ?? '');

        if (strlen($cep) !== 8) {
            http_response_code(400);
            echo json_encode(['error' => 'CEP inválido']);
            exit;
        }

        $url = "https://viacep.com.br/ws/{$cep}/json/";

        $response = file_get_contents($url);
        if (!$response) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao consultar o CEP']);
            exit;
        }

        $data = json_decode($response, true);
        if (isset($data['erro'])) {
            http_response_code(404);
            echo json_encode(['error' => 'CEP não encontrado']);
            exit;
        }

        echo json_encode($data);
        exit;
    }
}
