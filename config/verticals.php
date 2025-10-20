<?php
/**
 * Configuração Centralizada de Verticais
 *
 * Define todas as verticais disponíveis no sistema, seus metadados e comportamentos.
 * Este é o único lugar onde as verticais devem ser definidas.
 *
 * @package Sunyata
 * @since 2025-10-20
 */

return [
    'docencia' => [
        'nome' => 'Docência',
        'icone' => '👨‍🏫',
        'descricao' => 'Ferramentas para planejamento de aulas, criação de conteúdo educacional e gestão pedagógica.',
        'ferramentas' => [
            'Canvas Docente',
            'Canvas Pesquisa',
            'Biblioteca de Prompts (Jogos)',
            'Guia de Prompts (Jogos)',
            'Repositório de Prompts'
        ],
        'disponivel' => true,
        'requer_info_extra' => false,
        'requer_aprovacao' => false,
        'ordem' => 1
    ],

    'pesquisa' => [
        'nome' => 'Pesquisa',
        'icone' => '🔬',
        'descricao' => 'Recursos para estruturação de projetos de pesquisa acadêmica e científica.',
        'ferramentas' => [
            'Canvas Docente',
            'Canvas Pesquisa',
            'Repositório de Prompts'
        ],
        'disponivel' => true,
        'requer_info_extra' => false,
        'requer_aprovacao' => false,
        'ordem' => 2
    ],

    'ifrj_alunos' => [
        'nome' => 'IFRJ - Alunos',
        'icone' => '🎓',
        'descricao' => 'Área exclusiva para alunos do IFRJ com ferramentas de apoio ao aprendizado.',
        'ferramentas' => [
            'Biblioteca de Prompts (Jogos)',
            'Guia de Prompts (Jogos)',
            'Canvas Pesquisa',
            'Repositório de Prompts'
        ],
        'disponivel' => true,
        'requer_info_extra' => true, // Precisa de nível e curso
        'requer_aprovacao' => false,
        'form_extra' => 'onboarding-ifrj.php',
        'ordem' => 3
    ],

    'juridico' => [
        'nome' => 'Jurídico',
        'icone' => '⚖️',
        'descricao' => 'Ferramentas especializadas para profissionais do Direito.',
        'descricao_aprovacao' => ' <strong>Requer aprovação.</strong>',
        'ferramentas' => [
            'Canvas Jurídico',
            'Guia de Prompts (Jurídico)',
            'Padrões Avançados (Jurídico)',
            'Repositório de Prompts'
        ],
        'disponivel' => true,
        'requer_info_extra' => false,
        'requer_aprovacao_setting' => 'juridico_requires_approval', // Dinâmico via Settings!
        'form_aprovacao' => 'onboarding-juridico.php',
        'ordem' => 4
    ],

    'vendas' => [
        'nome' => 'Vendas',
        'icone' => '📈',
        'descricao' => 'Ferramentas para otimizar processos de vendas e relacionamento com clientes.',
        'ferramentas' => [],
        'disponivel' => false,
        'requer_info_extra' => false,
        'requer_aprovacao' => false,
        'ordem' => 5
    ],

    'marketing' => [
        'nome' => 'Marketing',
        'icone' => '📢',
        'descricao' => 'Recursos para criação de conteúdo, campanhas e estratégias de marketing digital.',
        'ferramentas' => [],
        'disponivel' => false,
        'requer_info_extra' => false,
        'requer_aprovacao' => false,
        'ordem' => 6
    ],

    'licitacoes' => [
        'nome' => 'Licitações',
        'icone' => '📋',
        'descricao' => 'Ferramentas para elaboração de propostas e gestão de processos licitatórios.',
        'ferramentas' => [],
        'disponivel' => false,
        'requer_info_extra' => false,
        'requer_aprovacao' => false,
        'ordem' => 7
    ],

    'rh' => [
        'nome' => 'Recursos Humanos',
        'icone' => '👥',
        'descricao' => 'Soluções para recrutamento, seleção e gestão de pessoas.',
        'ferramentas' => [],
        'disponivel' => false,
        'requer_info_extra' => false,
        'requer_aprovacao' => false,
        'ordem' => 8
    ],

    'geral' => [
        'nome' => 'Geral',
        'icone' => '🌐',
        'descricao' => 'Ferramentas de propósito geral para diversas áreas e aplicações.',
        'ferramentas' => [],
        'disponivel' => false,
        'requer_info_extra' => false,
        'requer_aprovacao' => false,
        'ordem' => 9
    ]
];
