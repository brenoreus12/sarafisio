<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configurações do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sara_fisio";

// Criar conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
} else {
    echo "Conexão bem-sucedida!<br>";
}

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coletar dados do formulário
    $nome = htmlspecialchars(trim($_POST['nome']));
    $email = htmlspecialchars(trim($_POST['email']));
    $whatsapp = htmlspecialchars(trim($_POST['whatsapp']));
    $data = htmlspecialchars(trim($_POST['data']));
    $hora = htmlspecialchars(trim($_POST['hora']));

    // Exibir dados recebidos para garantir que estão sendo capturados corretamente
    echo "Nome: $nome<br>";
    echo "E-mail: $email<br>";
    echo "WhatsApp: $whatsapp<br>";
    echo "Data: $data<br>";
    echo "Hora: $hora<br>";

    // Verificar se a combinação de data e hora já foi usada
    $sql_check = "SELECT * FROM agendamentos WHERE data_agendamento = ? AND hora_agendamento = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ss", $data, $hora);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Se já existe um agendamento com a mesma data e hora
        echo "A data e hora selecionadas já foram reservadas. Por favor, escolha outra combinação.<br>";
    } else {
        // Se a data e hora estão disponíveis, inserir os dados no banco de dados
        $sql_insert = "INSERT INTO agendamentos (nome, email, whatsapp, data_agendamento, hora_agendamento)
                       VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sssss", $nome, $email, $whatsapp, $data, $hora);

        if ($stmt_insert->execute()) {
            echo "Seu agendamento foi realizado com sucesso!<br>";
            
            // Enviar o e-mail de notificação
            $to = "brenoreus12@gmail.com";
            $subject = "Novo Agendamento";
            $message = "
            <html>
            <head>
                <title>Novo Agendamento</title>
            </head>
            <body>
                <h2>Detalhes do Agendamento:</h2>
                <p><strong>Nome:</strong> $nome</p>
                <p><strong>E-mail:</strong> $email</p>
                <p><strong>WhatsApp:</strong> $whatsapp</p>
                <p><strong>Data:</strong> $data</p>
                <p><strong>Hora:</strong> $hora</p>
            </body>
            </html>
            ";

            // Cabeçalhos do e-mail
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: $email" . "\r\n";

            // Enviar o e-mail
            mail($to, $subject, $message, $headers);
        } else {
            echo "Erro ao agendar. Por favor, tente novamente.<br>";
            echo "Erro: " . $stmt_insert->error . "<br>";
        }
    }

    // Fechar o prepared statement
    $stmt_check->close();
    $stmt_insert->close();
}

// Fechar a conexão com o banco de dados
$conn->close();
?>
