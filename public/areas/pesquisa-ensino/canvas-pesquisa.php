<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/auth.php';

require_login();

// Incluir navbar
include __DIR__ . '/../../../src/views/navbar.php';

// Renderizar o canvas pesquisa
readfile(__DIR__ . '/../../../public/ferramentas/canvas-pesquisa.html');
