<?php
// Upload Test Script - Remove after testing
echo "<h2>Configurações de Upload do Servidor</h2>";

echo "<strong>file_uploads:</strong> " . (ini_get('file_uploads') ? 'Habilitado' : 'Desabilitado') . "<br>";
echo "<strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "<br>";
echo "<strong>post_max_size:</strong> " . ini_get('post_max_size') . "<br>";
echo "<strong>max_execution_time:</strong> " . ini_get('max_execution_time') . " segundos<br>";
echo "<strong>max_input_time:</strong> " . ini_get('max_input_time') . " segundos<br>";
echo "<strong>memory_limit:</strong> " . ini_get('memory_limit') . "<br>";
echo "<strong>upload_tmp_dir:</strong> " . (ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : 'Sistema padrão') . "<br>";

echo "<h3>Teste de Upload</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    echo "<strong>Arquivo recebido:</strong><br>";
    echo "Nome: " . $_FILES['test_file']['name'] . "<br>";
    echo "Tamanho: " . $_FILES['test_file']['size'] . " bytes<br>";
    echo "Tipo: " . $_FILES['test_file']['type'] . "<br>";
    echo "Erro: " . $_FILES['test_file']['error'] . "<br>";
    echo "Arquivo temporário: " . $_FILES['test_file']['tmp_name'] . "<br>";
    
    if ($_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
        echo "<span style='color: green;'><strong>Upload bem-sucedido!</strong></span><br>";
    } else {
        $upload_errors = array(
            UPLOAD_ERR_INI_SIZE => 'Arquivo muito grande (limite do PHP)',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande (limite do formulário)',
            UPLOAD_ERR_PARTIAL => 'Upload parcial do arquivo',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
            UPLOAD_ERR_NO_TMP_DIR => 'Diretório temporário não encontrado',
            UPLOAD_ERR_CANT_WRITE => 'Erro ao escrever arquivo',
            UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
        );
        echo "<span style='color: red;'><strong>Erro:</strong> " . (isset($upload_errors[$_FILES['test_file']['error']]) ? $upload_errors[$_FILES['test_file']['error']] : 'Erro desconhecido') . "</span><br>";
    }
}

echo "<form method='post' enctype='multipart/form-data'>";
echo "<input type='file' name='test_file' accept='.pdf'><br><br>";
echo "<input type='submit' value='Testar Upload'>";
echo "</form>";

echo "<h3>Verificação de Diretórios</h3>";
$upload_dir = 'image/catalog/docs/';
echo "<strong>Diretório de upload:</strong> " . $upload_dir . "<br>";
echo "<strong>Existe:</strong> " . (is_dir($upload_dir) ? 'Sim' : 'Não') . "<br>";
echo "<strong>Permissões:</strong> " . (is_writable($upload_dir) ? 'Escrita permitida' : 'Escrita negada') . "<br>";

if (!is_dir($upload_dir)) {
    echo "<strong>Tentando criar diretório...</strong><br>";
    if (mkdir($upload_dir, 0755, true)) {
        echo "<span style='color: green;'>Diretório criado com sucesso!</span><br>";
    } else {
        echo "<span style='color: red;'>Erro ao criar diretório!</span><br>";
    }
}
?>
