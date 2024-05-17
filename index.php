<?php

class Usuario {
    public $login;
    public $senha;

    public function __construct($login, $senha) {
        $this->login = $login;
        $this->senha = $senha;
    }
}

class Caixa {
    private $usuarios = [];
    private $usuarioLogado = null;
    private $log = [];
    private $totalVendas = 0;

    public function adicionarUsuario($login, $senha) {
        $usuario = new Usuario($login, $senha);
        $this->usuarios[] = $usuario;
    }

    public function logar($login, $senha) {
        foreach ($this->usuarios as $usuario) {
            if ($usuario->login === $login && $usuario->senha === $senha) {
                $this->usuarioLogado = $usuario;
                $this->log[] = "Usuário {$usuario->login} logou às " . date('d/m/Y H:i:s');
                return true;
            }
        }
        return false;
    }

    public function deslogar() {
        if ($this->usuarioLogado) {
            $this->log[] = "Usuário {$this->usuarioLogado->login} deslogou às " . date('d/m/Y H:i:s');
            $this->usuarioLogado = null;
        }
    }

    public function vender($valor, $item) {
        if ($this->usuarioLogado) {
            $this->log[] = "Usuário {$this->usuarioLogado->login} realizou uma venda do item {$item} no valor de {$valor} às " . date('d/m/Y H:i:s');
            $this->totalVendas += $valor;
            echo "Venda registrada!\n";
        }
    }

    public function verHistorico() {
        return $this->log;
    }

    public function mostrarMenu() {
        if ($this->usuarioLogado) {
            system('clear');
            echo "Menu:\n";
            echo "1. Vender\n";
            echo "2. Cadastrar novo usuário\n";
            echo "3. Verificar log\n";
            echo "4. Deslogar\n";
            echo "Usuário logado: {$this->usuarioLogado->login}\n";
            echo "Total de vendas: {$this->totalVendas}\n";
        } else {
            echo "Menu:\n";
            echo "1. Realizar login\n";
        }
    }
}

$caixa = new Caixa();
$caixa->adicionarUsuario('admin', 'admin');
$caixa->adicionarUsuario('user', 'user');

while (true) {
    $caixa->mostrarMenu();
    $opcao = readline("Digite a opção desejada: ");

    if ($caixa->usuarioLogado) {
        switch ($opcao) {
            case 1:
                $valor = (float) readline("Digite o valor da venda: ");
                $item = readline("Digite o nome do item: ");
                $caixa->vender($valor, $item);
                break;
            case 2:
                $login = readline("Digite o novo login: ");
                $senha = readline("Digite a nova senha: ");
                $caixa->adicionarUsuario($login, $senha);
                break;
            case 3:
                $historico = $caixa->verHistorico();
                foreach ($historico as $evento) {
                    echo $evento . "\n";
                }
                break;
            case 4:
                $caixa->deslogar();
                break;
            default:
                echo "Opção inválida\n";
        }
    } else {
        if ($opcao == 1) {
            $login = readline("Digite o login: ");
            $senha = readline("Digite a senha: ");
            if ($caixa->logar($login, $senha)) {
                echo "Login realizado com sucesso!\n";
            } else {
                echo "Usuário ou senha inválidos\n";
            }
        } else {
            echo "Opção inválida\n";
        }
    }
}
?>
