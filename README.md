# Sistema de Cadastro e Manutenção de Equipamentos

Este é um sistema web desenvolvido em PHP para gerenciar o cadastro de equipamentos e o histórico de suas manutenções, incluindo preventivas e corretivas. O sistema oferece funcionalidades para calcular indicadores de manutenção como MTBF (Tempo Médio Entre Falhas) e MTTR (Tempo Médio Para Reparo).

 

## ✨ Funcionalidades Principais

-   **Autenticação de Usuários:** Sistema de login e registro seguro com hash de senhas.
-   **Cadastro de Equipamentos:** Permite registrar novos equipamentos com informações como TAG, setor, descrição e foto.
-   **Gestão de Manutenções:**
    -   Registro de manutenções preventivas, preditivas e corretivas.
    -   Cálculo automático do tempo de reparo para manutenções corretivas.
-   **Plano de Manutenção Preventiva:**
    -   Visualização de equipamentos com manutenções preventivas programadas.
    -   Alertas visuais para manutenções vencidas ou próximas do vencimento.
-   **Histórico Completo:** Página de detalhes para cada equipamento, exibindo todas as manutenções realizadas.
-   **Relatórios e Indicadores:**
    -   Cálculo e exibição de MTBF e MTTR por equipamento em um período selecionado.
    -   Gráficos para visualização rápida dos indicadores.
-   **Busca e Paginação:** Facilita a navegação e a busca por equipamentos específicos.
-   **Impressão:** Funcionalidade para imprimir relatórios e listas de pendências.

## 🛠️ Tecnologias Utilizadas

-   **Backend:** PHP
-   **Frontend:** HTML5, CSS3, JavaScript
-   **Banco de Dados:** MySQL / MariaDB
-   **Biblioteca Gráfica:** Chart.js (para os relatórios)
-   **Ícones:** Font Awesome

## 🚀 Como Executar o Projeto

Para executar este projeto localmente, você precisará de um ambiente de servidor web com PHP e MySQL, como o XAMPP, WAMP ou MAMP.

### Pré-requisitos

-   PHP 7.4 ou superior
-   MySQL ou MariaDB
-   Um servidor web (Apache)

### Passos para Instalação

1.  **Clone o repositório:**
    ```bash
    git clone https://github.com/seu-usuario/seu-repositorio.git
    ```

2.  **Mova os arquivos:**
    -   Mova a pasta do projeto para o diretório `htdocs` do seu XAMPP (ex: `c:\xampp\htdocs\`).

3.  **Crie o Banco de Dados:**
    -   Abra o phpMyAdmin (`http://localhost/phpmyadmin`).
    -   Crie um novo banco de dados chamado `sistema_cadastro_manutencao`.
    -   Importe o arquivo `database.sql` (você precisará criar este arquivo exportando a estrutura do seu banco atual) para criar as tabelas `equipamentos`, `manutencoes` e `usuarios`.

4.  **Configure a Conexão:**
    -   Os arquivos PHP já estão configurados para se conectar ao banco de dados com o usuário `root` e sem senha, que é o padrão do XAMPP. Se suas credenciais forem diferentes, ajuste-as nos arquivos PHP.

5.  **Acesse o sistema:**
    -   Abra seu navegador e acesse `http://localhost/CadastrarEquipamentos/login.php`.

## 🏗️ Estrutura do Banco de Dados

O sistema utiliza 3 tabelas principais:

-   `usuarios`: Armazena os dados de login dos usuários.
-   `equipamentos`: Contém os detalhes de cada equipamento, incluindo o intervalo de manutenção preventiva.
-   `manutencoes`: Guarda o histórico de todas as manutenções realizadas em cada equipamento.

## 🤝 Contribuição

Contribuições são bem-vindas! Se você tiver sugestões para melhorar o projeto, sinta-se à vontade para abrir uma *issue* ou enviar um *pull request*.

1.  Faça um *fork* do projeto.
2.  Crie uma nova *branch* (`git checkout -b feature/nova-funcionalidade`).
3.  Faça o *commit* de suas alterações (`git commit -m 'Adiciona nova funcionalidade'`).
4.  Faça o *push* para a *branch* (`git push origin feature/nova-funcionalidade`).
5.  Abra um *Pull Request*.

---
Desenvolvido com ❤️ por CRR.
