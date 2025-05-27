<?php
session_start();
include 'db_connection.php'; // Используем централизованное подключение

if ($mysqli->connect_error) {
    die("Ошибка подключения: " . $mysqli->connect_error);
}

// Получение всех товаров из базы данных
$productQuery = "SELECT * FROM products";
$productResult = $mysqli->query($productQuery);

$filteredProducts = [];
if ($productResult->num_rows > 0) {
    while ($row = $productResult->fetch_assoc()) {
        $filteredProducts[] = $row;
    }
}

if (isset($_GET['category_id']) || isset($_GET['max_price']) || isset($_GET['search'])) {
    $categoryId = isset($_GET['category_id']) ? $_GET['category_id'] : '';
    $maxPrice = isset($_GET['max_price']) ? $_GET['max_price'] : '';
    $searchTerm = isset($_GET['search']) ? strtolower($_GET['search']) : '';

    // Фильтрация товаров
    $filteredProducts = array_filter($filteredProducts, function($product) use ($categoryId, $maxPrice, $searchTerm) {
        $matchesCategory = $categoryId === "" || $product['category_id'] == $categoryId;
        $matchesPrice = $maxPrice === "" || $product['price'] <= $maxPrice;
        $matchesSearch = strpos(strtolower($product['name']), $searchTerm) !== false;

        return $matchesCategory && $matchesPrice && $matchesSearch;
    });
}

// Вывод отфильтрованных товаров
if (empty($filteredProducts)) {
    echo '<p>Нет товаров, соответствующих вашим критериям.</p>';
} else {
    foreach ($filteredProducts as $product) {
        echo '
            <div class="product" data-category-id="' . $product['category_id'] . '" data-price="' . $product['price'] . '" data-id="' . $product['id'] . '">
                <img src="' . $product['image'] . '" alt="' . $product['name'] . '">
                <h3>' . $product['name'] . '</h3>
                <p>' . $product['description'] . '</p>
                <p>Цена: ' . $product['price'] . ' руб.</p>
                <button class="add-to-cart" onclick="addToCart(this)">Добавить в корзину</button>
                <div class="quantity-control" style="display: none;">
                    <button onclick="decreaseQuantity(this)">-</button>
                    <span class="quantity">1</span>
                    <button onclick="increaseQuantity(this)">+</button>
                </div>
            </div>
        ';
    }
}
?>
