<?php
session_start();

$usuarios = [
    'admin' => 'admin'
];

$caixa_inicial = (float)trim(fgets(STDIN));
$quantidade_vendida = (int)trim(fgets(STDIN));
$dinheiro_recebido = (float)trim(fgets(STDIN));
$preco_item = (float)trim(fgets(STDIN));
$estoque_item = (int)trim(fgets(STDIN));

$log = [];
$total_vendas = 0;
$caixa_inicial =  0;
$itens = [];

function limpar_tela() {
    system('clear');
}

function hora_atual() {
    return date('d/m/Y H:i:s');
}

function registrar_acao($mensagem) {
    global $log;
    $log[] = $mensagem;
    file_put_contents('log.txt', "$mensagem\n", FILE_APPEND);
}

function login() {
    global $usuarios;
    limpar_tela();
    echo "Login\n";
    echo "Nome de usuário: ";
    $nome_usuario = trim(fgets(STDIN));
    echo "Senha: ";
    $senha = trim(fgets(STDIN));
    
    if (isset($usuarios[$nome_usuario]) && $usuarios[$nome_usuario] == $senha) {
        $_SESSION['nome_usuario'] = $nome_usuario;
        registrar_acao("Usuário $nome_usuario fez login em " . hora_atual());
        return true;
    } else {
        echo "Credenciais inválidas.\n";
        return false;
    }
}

function logout() {
    global $log;
    $nome_usuario = $_SESSION['nome_usuario'];
    registrar_acao("Usuário $nome_usuario fez logout em " . hora_atual());
    session_unset();
}

function registrar_usuario() {
    global $usuarios;
    limpar_tela();
    echo "Registrar Novo Usuário\n";
    echo "Nome de usuário: ";
    $nome_usuario = trim(fgets(STDIN));
    echo "Senha: ";
    $senha = trim(fgets(STDIN));
    $usuarios[$nome_usuario] = $senha;
    registrar_acao("Usuário $nome_usuario registrado em " . hora_atual());
    echo "Usuário $nome_usuario registrado com sucesso.\n";
}

function inicializar_caixa() {
    global $caixa_inicial;
    limpar_tela();
    echo "Inicializar Caixa\n";
    echo "Valor inicial no caixa: ";
    $caixa_inicial = trim(fgets(STDIN));
    registrar_acao("Caixa inicializado com R$$caixa_inicial em " . hora_atual());
}

function realizar_venda() {
    global $total_vendas, $caixa_inicial, $itens;
    limpar_tela();
    echo "Realizar Venda\n";
    echo "ID do Item: ";
    $id_item = trim(fgets(STDIN));

    if (!isset($itens[$id_item])) {
        echo "Item não encontrado.\n";
        return;
    }

    $item = $itens[$id_item];
    echo "Quantidade vendida: ";
    $quantidade_vendida = trim(fgets(STDIN));

    if ($item['estoque'] < $quantidade_vendida) {
        echo "Estoque insuficiente.\n";
        return;
    }

    $total_venda = $item['preco'] * $quantidade_vendida;
    echo "Valor total da venda: R$$total_venda\n";
    echo "Dinheiro recebido: ";
    $dinheiro_recebido = trim(fgets(STDIN));

    if ($dinheiro_recebido < $total_venda) {
        echo "Dinheiro insuficiente.\n";
        return;
    }

    $troco = $dinheiro_recebido - $total_venda;
    echo "Venda registrada. Troco: R$$troco\n";
    sleep(3);
    $itens[$id_item]['estoque'] -= $quantidade_vendida;
    $total_vendas += $total_venda;
    $caixa_inicial += $total_venda;
    registrar_acao("Usuário {$_SESSION['nome_usuario']} vendeu $quantidade_vendida x {$item['nome']} por R$$total_venda e R$$troco de troco em " . hora_atual());

}

function visualizar_log() {
    global $log;
    limpar_tela();
    echo "Log do Sistema\n";
    foreach ($log as $entrada) {
        echo "$entrada\n";
    }
    echo "\nPressione Enter para continuar...";
    fgets(STDIN);
}

function adicionar_item() {
    global $itens;
    limpar_tela();
    echo "Adicionar Item\n";
    echo "ID do Item: ";
    $id_item = trim(fgets(STDIN));
    echo "Nome do Item: ";
    $nome_item = trim(fgets(STDIN));
    echo "Preço do Item: ";
    $preco_item = trim(fgets(STDIN));
    echo "Quantidade em Estoque: ";
    $estoque_item = trim(fgets(STDIN));

    $itens[$id_item] = [
        'nome' => $nome_item,
        'preco' => $preco_item,
        'estoque' => $estoque_item
    ];
    registrar_acao("Item $nome_item adicionado com ID $id_item em " . hora_atual());
    echo "Item $nome_item adicionado com sucesso.\n";
}

function alterar_item() {
    global $itens;
    limpar_tela();
    echo "Alterar Item\n";
    echo "ID do Item: ";
    $id_item = trim(fgets(STDIN));

    if (!isset($itens[$id_item])) {
        echo "Item não encontrado.\n";
        return;
    }

    $item = $itens[$id_item];
    echo "Nome do Item (atual: {$item['nome']}): ";
    $nome_item = trim(fgets(STDIN));
    echo "Preço do Item (atual: {$item['preco']}): ";
    $preco_item = trim(fgets(STDIN));
    echo "Quantidade em Estoque (atual: {$item['estoque']}): ";
    $estoque_item = trim(fgets(STDIN));

    $itens[$id_item] = [
        'nome' => $nome_item ?: $item['nome'],
        'preco' => $preco_item ?: $item['preco'],
        'estoque' => $estoque_item ?: $item['estoque']
    ];
    registrar_acao("Item $id_item alterado por usuário {$_SESSION['nome_usuario']} em " . hora_atual());
    echo "Item alterado com sucesso.\n";
}

function deletar_item() {
    global $itens;
    limpar_tela();
    echo "Deletar Item\n";
    echo "ID do Item: ";
    $id_item = trim(fgets(STDIN));

    if (!isset($itens[$id_item])) {
        echo "Item não encontrado.\n";
        return;
    }

    unset($itens[$id_item]);
    registrar_acao("Item $id_item deletado por usuário {$_SESSION['nome_usuario']} em " . hora_atual());
    echo "Item deletado com sucesso.\n";
}

function menu_principal() {
    global $total_vendas, $caixa_inicial;   
    limpar_tela();
    echo "Bem-vindo, {$_SESSION['nome_usuario']}\n";
    echo "Total de Vendas: R$$total_vendas\n";
    echo "Caixa Atual: R$$caixa_inicial\n";
    echo "1. Realizar Venda\n";
    echo "2. Registrar Novo Usuário\n";
    echo "3. Visualizar Log\n";
    echo "4. Adicionar Item\n";
    echo "5. Alterar Item\n";
    echo "6. Deletar Item\n";
    echo "7. Logout\n";
    echo "Escolha uma opção: ";
    $opcao = trim(fgets(STDIN));
    
    switch ($opcao) {
        case 1:
            realizar_venda();
            break;
        case 2:
            registrar_usuario();
            break;
        case 3:
            visualizar_log();
            break;
        case 4:
            adicionar_item();
            break;
        case 5:
            alterar_item();
            break;
        case 6:
            deletar_item();
            break;
        case 7:
            logout();
            break;
        default:
            echo "Opção inválida.\n";
    }
}


while (true) {
    if (!isset($_SESSION['nome_usuario'])) {
        if (login()) {
        } else {
            echo "Falha no login. Tente novamente.\n";
        }
    } else {
        menu_principal();
    }
}
?>
