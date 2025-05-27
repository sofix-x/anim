<?php
session_start(); // Начинаем сессию

// Подключение к базе данных
$conn = new mysqli('localhost', 'root', 'root', 'comsugoitoys');
