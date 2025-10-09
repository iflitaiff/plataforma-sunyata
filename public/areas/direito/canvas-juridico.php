<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/auth.php';

require_login();

// Verificar se o usuário tem acesso à vertical Jurídico
if (!isset($_SESSION['access']['law']) || $_SESSION['access']['law'] !== true) {
    header('Location: /areas/direito/solicitar-acesso.php');
    exit;
}

// Incluir navbar
include __DIR__ . '/../../../src/views/navbar.php';

// Renderizar o canvas jurídico
readfile(__DIR__ . '/../../../public/ferramentas/canvas-juridico.html');
