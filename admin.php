<?php
session_start(); // Начинаем сессию

include 'db_connection.php'; // Используем централизованное подключение

// Проверка, отправлена ли форма для добавления товара
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];

    // Обработка загрузки изображения
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "img/";
        $image = $targetDir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image);
    }

    // Вставка товара в базу данных
    $stmt = $mysqli->prepare("INSERT INTO products (name, price, description, image, category_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdssi", $name, $price, $description, $image, $category_id);

    if ($stmt->execute()) {
        echo "<script>alert('Товар добавлен успешно!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Ошибка при добавлении товара: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// Проверка, отправлена ли форма для добавления категории
if (isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];

    // Вставка категории в базу данных
    $stmt = $mysqli->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param("s", $category_name);

    if ($stmt->execute()) {
        echo "<script>alert('Категория добавлена успешно!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Ошибка при добавлении категории: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// Получение категорий для выпадающего списка
$query = "SELECT id, name FROM categories";
$result = $mysqli->query($query);
$categories = [];
if ($result->num_rows > 0) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}

// Получение товаров для отображения в таблице
$query = "
    SELECT p.id, p.name, p.price, p.description, p.image, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id
";
$result = $mysqli->query($query);
$products = [];
if ($result->num_rows > 0) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
}


if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $targetDir = "img/";
    $image = $targetDir . basename($_FILES["image"]["name"]);
    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $image)) {
        echo "<script>alert('Ошибка при загрузке изображения.');</script>";
    }
}


// Проверка, отправлена ли форма для редактирования товара
if (isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];

    // Обработка загрузки нового изображения
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "img/";
        $image = $targetDir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image);
    } else {
        // Если изображение не загружено, используем текущее изображение
        $query = "SELECT image FROM products WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_image = $result->fetch_assoc();
        $image = $current_image['image'];
    }

    // Обновление товара в базе данных
    $stmt = $mysqli->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ?, category_id = ? WHERE id = ?");
    $stmt->bind_param("sdssii", $name, $price, $description, $image, $category_id, $product_id);

    if ($stmt->execute()) {
        echo "<script>alert('Товар обновлен успешно!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Ошибка при обновлении товара: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// Получение всех пользователей для отображения в таблице
$usersQuery = "SELECT id, username FROM users"; // Убираем is_admin из запроса, т.к. signin.php не использует его
$usersResult = $mysqli->query($usersQuery);
$users = [];
if ($usersResult && $usersResult->num_rows > 0) {
    $users = $usersResult->fetch_all(MYSQLI_ASSOC);
}

// Закрытие соединения
// $mysqli->close(); // Соединение будет закрыто в конце скрипта, если еще используется
// Переносим $mysqli->close(); в самый конец PHP блока перед <!DOCTYPE html>
// или убедимся, что оно вызывается только один раз.
// Для безопасности, если $mysqli используется только для этих запросов вверху,
// можно закрыть его здесь, но если есть еще запросы ниже, то нет.
// Судя по коду, все DB операции вверху, так что можно закрыть.
// Однако, если $mysqli используется в HTML части (маловероятно для этого файла), то не закрывать.
// Давайте предположим, что все DB операции завершены.
// $mysqli->close(); // Закомментируем пока, чтобы убедиться, что нет других использований ниже.
// Фактически, $mysqli->close() уже есть в конце PHP блока.
$mysqli->close(); // Ensure the connection is closed here
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ Панель</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        /* Стили для модального окна */
        .modal {
            display: none; /* Скрыто по умолчанию */
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px;
            color: black;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 80%; 
            max-width: 500px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<header>
    <nav>
        <ul>
            <li><a href="index.php">Главная</a></li>
            <li><a href="tovars.php">Наши товары</a></li>
            <li><a href="assets/vendor/logout.php">Выход</a></li>
        </ul>
    </nav>
</header>

<main>
    <h1 style="text-align: center;">Админ Панель</h1>

    <section>
        <h2>Управление товарами</h2>
        <button class="add-product" id="addProductBtn">Добавить товар</button>
        <button class="delete-product" onclick="deleteSelected()">Удалить товар</button>
        <button class="edit-product" onclick="editSelected()">Редактировать товар</button>

        <table>
            <thead>
                <tr>
                    <th>Выбрать</th>
                    <th>Имя</th>
                    <th>Цена</th>
                    <th>Категория</th>
                    <th>Фото</th>
                    <th>Описание</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr >

                    <td>
                        <input type="checkbox" class="product-checkbox" data-id="<?= $product['id'] ?>">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    </td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['price']) ?> руб.</td>
                    <td><?= htmlspecialchars($product['category_name']) ?></td>
                    <td>
                        <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width: 50px; height: 50px;">
                    </td>
                    <td><?= htmlspecialchars($product['description']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
    </section>

    <section>
        <h2>Управление категориями</h2>
        <button id="addCategoryBtn">Добавить категорию</button>
        <button onclick="editCategory()">Редактировать категорию</button>
        <button onclick="deleteCategory()">Удалить категорию</button>

        <h3>Список категорий</h3>
        <ul id="categoryList">
    <?php foreach ($categories as $category): ?>
        <li>
            <input type="checkbox" class="category-checkbox" data-id="<?= $category['id'] ?>">
            <?= htmlspecialchars($category['name']) ?>
        </li>
    <?php endforeach; ?>
</ul>

    </section>

    <section>
        <h2>Управление пользователями</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя пользователя</th>
                    <th>Статус админа</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= ($user['username'] === 'admin') ? 'Да' : 'Нет' ?></td>
                        <td>
                            <?php if ($user['username'] !== 'admin'): // Нельзя редактировать или удалять основного админа 'admin' ?>
                                <button onclick="openEditUserModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>')">Редактировать</button>
                                <?php if ($_SESSION['user_id'] != $user['id']): // Нельзя удалить себя ?>
                                    <button onclick="deleteUser(<?= $user['id'] ?>)">Удалить</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- Модальное окно для редактирования пользователя -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeEditUserModal">&times;</span>
            <h2>Редактировать пользователя</h2>
            <form id="editUserForm" method="POST">
                <input type="hidden" name="edit_user_id" id="editUserId">
                <div>
                    <label for="editUsername">Имя пользователя:</label>
                    <input type="text" name="edit_username" id="editUsername" required>
                </div>
                <div>
                    <label for="editPassword">Новый пароль (оставьте пустым, чтобы не менять):</label>
                    <input type="password" name="edit_password" id="editPassword">
                </div>
                <button type="submit" name="save_user_changes">Сохранить изменения</button>
            </form>
        </div>
    </div>

    <!-- Модальное окно для добавления товара -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeProductModal">&times;</span>
            <h2>Добавить товар</h2>
            <form id="addProductForm" method="POST" enctype="multipart/form-data">
                <div>
                    <label for="name">Имя товара:</label>
                    <input type="text" name="name" required>
                </div>
                <div>
                    <label for="price">Цена:</label>
                    <input type="number" name="price" required step="0.01">
                </div>
                <div>
                    <label for="description">Описание:</label>
                    <textarea name="description" required></textarea>
                </div>
                <div>
                    <label for="category_id">Категория:</label>
                    <select name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="image">Загрузить изображение:</label>
                    <input type="file" name="image" accept="image/*" required>
                </div>
                <button type="submit" name="add_product">Сохранить</button>
            </form>
        </div>
    </div>

    <!-- Модальное окно для редактирования товара -->
<div id="editProductModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeEditProductModal">&times;</span>
        <h2>Редактировать товар</h2>
        <form id="editProductForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="editProductId">
            <div>
                <label for="name">Имя товара:</label>
                <input type="text" name="name" id="editProductName" required>
            </div>
            <div>
                <label for="price">Цена:</label>
                <input type="number" name="price" id="editProductPrice" required step="0.01">
            </div>
            <div>
                <label for="description">Описание:</label>
                <textarea name="description" id="editProductDescription" required></textarea>
            </div>
            <div>
                <label for="category_id">Категория:</label>
                <select name="category_id" id="editProductCategoryId" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="image">Загрузить изображение:</label>
                <input type="file" name="image" accept="image/*" id="editProductImage">
            </div>
            <button type="submit" name="edit_product">Сохранить изменения</button>
        </form>
    </div>
</div>


    <!-- Модальное окно для добавления категории -->
    <div id="addCategoryModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeCategoryModal">&times;</span>
            <h2>Добавить категорию</h2>
            <form method="POST" id="addCategoryForm">
                <div>
                    <label for="category_name">Имя категории:</label>
                    <input type="text" name="category_name" required>
                </div>
                <button type="submit" name="add_category">Сохранить</button>
            </form>
        </div>
    </div>



    

</main>

<footer>
    <p>&copy; 2024 Интернет-каталог товаров</p>
</footer>

<script>
    // Открыть модальное окно для добавления товара
    document.getElementById("addProductBtn").onclick = function() {
        document.getElementById("addProductModal").style.display = "block";
    }

    // Закрыть модальное окно для добавления товара
    document.getElementById("closeProductModal").onclick = function() {
        document.getElementById("addProductModal").style.display = "none";
    }

    // Закрыть модальное окно при нажатии вне его
    window.onclick = function(event) {
        const productModal = document.getElementById("addProductModal");
        const categoryModal = document.getElementById("addCategoryModal");
        const editUserModal = document.getElementById("editUserModal"); // Добавляем editUserModal
        if (event.target == productModal) {
            productModal.style.display = "none";
        } else if (event.target == categoryModal) {
            categoryModal.style.display = "none";
        } else if (event.target == editUserModal) { // Закрытие editUserModal
            editUserModal.style.display = "none";
        }
    }

    // Открыть модальное окно для добавления категории
    document.getElementById("addCategoryBtn").onclick = function() {
        document.getElementById("addCategoryModal").style.display = "block";
    }

    // Закрыть модальное окно для добавления категории
    document.getElementById("closeCategoryModal").onclick = function() {
        document.getElementById("addCategoryModal").style.display = "none";
    }

    function deleteSelected() {
    const selectedCheckboxes = document.querySelectorAll(".product-checkbox:checked");
    if (selectedCheckboxes.length === 0) {
        alert("Пожалуйста, выберите хотя бы один товар для удаления.");
        return;
    }

    const productIds = Array.from(selectedCheckboxes).map(checkbox => checkbox.dataset.id);
    if (confirm("Вы уверены, что хотите удалить выбранные товары?")) {
        fetch("delete_product.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ product_ids: productIds }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Товары удалены успешно.");
                window.location.reload();
            } else {
                alert("Ошибка при удалении товаров: " + data.error);
            }
        })
        .catch(error => console.error("Ошибка:", error));
    }
}

// JavaScript для модального окна редактирования пользователя
const editUserModal = document.getElementById("editUserModal");
const closeEditUserModalBtn = document.getElementById("closeEditUserModal");

function openEditUserModal(userId, username) {
    document.getElementById("editUserId").value = userId;
    document.getElementById("editUsername").value = username;
    document.getElementById("editPassword").value = ""; // Очищаем поле пароля
    editUserModal.style.display = "block";
}

if(closeEditUserModalBtn) {
    closeEditUserModalBtn.onclick = function() {
        editUserModal.style.display = "none";
    }
}

document.getElementById("editUserForm")?.addEventListener("submit", function(event) {
    event.preventDefault();
    const userId = document.getElementById("editUserId").value;
    const username = document.getElementById("editUsername").value;
    const password = document.getElementById("editPassword").value;

    const formData = {
        user_id: userId,
        username: username
    };
    if (password) {
        formData.password = password;
    }

    fetch('assets/vendor/edit_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Данные пользователя обновлены.');
            editUserModal.style.display = "none";
            window.location.reload();
        } else {
            alert('Ошибка при обновлении данных: ' + (data.error || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Произошла ошибка сети при обновлении пользователя.');
    });
});

// Функция toggleAdmin удаляется, так как админ статус определяется по username === 'admin'
// function toggleAdmin(userId, isAdmin) { ... }

function deleteUser(userId) {
    if (confirm('Вы уверены, что хотите удалить этого пользователя? Это действие необратимо.')) {
        fetch('assets/vendor/delete_user.php', { // Новый файл для обработки
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Пользователь удален.');
                window.location.reload();
            } else {
                alert('Ошибка при удалении пользователя: ' + (data.error || 'Неизвестная ошибка'));
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Произошла ошибка сети.');
        });
    }
}





function editSelected() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    if (checkboxes.length !== 1) {
        alert('Пожалуйста, выберите один товар для редактирования.');
        return;
    }
    const productId = checkboxes[0].getAttribute('data-id');
    const row = checkboxes[0].closest('tr');

    // Заполняем форму редактирования данными выбранного товара
    document.getElementById('editProductId').value = productId;
    document.getElementById('editProductName').value = row.cells[1].innerText;
    document.getElementById('editProductPrice').value = parseFloat(row.cells[2].innerText);
    document.getElementById('editProductDescription').value = row.cells[5].innerText;
    document.getElementById('editProductCategoryId').value = checkboxes[0].dataset.categoryId; // Добавьте атрибут data-category-id к чекбоксу

    document.getElementById('editProductModal').style.display = "block";
}

// Закрытие модального окна редактирования
document.getElementById('closeEditProductModal').onclick = function() {
    document.getElementById('editProductModal').style.display = "none";
};

// Закрытие модального окна редактирования при клике вне его
window.onclick = function(event) {
    const modalEdit = document.getElementById('editProductModal');
    if (event.target === modalEdit) {
        modalEdit.style.display = "none";
    }
};







function editCategory() {
    const selectedCheckboxes = document.querySelectorAll(".category-checkbox:checked");
    if (selectedCheckboxes.length !== 1) {
        alert("Пожалуйста, выберите одну категорию для редактирования.");
        return;
    }

    const categoryId = selectedCheckboxes[0].dataset.id;
    const categoryName = selectedCheckboxes[0].parentNode.textContent.trim();

    // Открываем модальное окно
    document.querySelector("#addCategoryModal h2").textContent = "Редактировать категорию";
    document.querySelector("#addCategoryForm [name='category_name']").value = categoryName;
    document.querySelector("#addCategoryForm").action = "edit_category.php";
    document.querySelector("#addCategoryForm").insertAdjacentHTML('beforeend', `<input type="hidden" name="category_id" value="${categoryId}">`);
    document.getElementById("addCategoryModal").style.display = "block";
}


    function deleteCategory() {
    const selectedCheckboxes = document.querySelectorAll(".category-checkbox:checked");
    if (selectedCheckboxes.length === 0) {
        alert("Пожалуйста, выберите хотя бы одну категорию для удаления.");
        return;
    }

    const categoryIds = Array.from(selectedCheckboxes).map(checkbox => checkbox.dataset.id);
    if (confirm("Вы уверены, что хотите удалить выбранные категории?")) {
        // Выполняем AJAX-запрос на сервер для удаления категорий
        fetch("delete_category.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ category_ids: categoryIds }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Категории удалены успешно.");
                window.location.reload();
            } else {
                alert("Ошибка при удалении категорий: " + data.error);
            }
        })
        .catch(error => console.error("Ошибка:", error));
    }
}




</script>

</body>
</html>
