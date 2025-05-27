<?php
session_start(); // Начинаем сессию

// Подключение к базе данных
$conn = new mysqli('localhost', 'sugoi_user', '0000', 'sugoi_store_db');
