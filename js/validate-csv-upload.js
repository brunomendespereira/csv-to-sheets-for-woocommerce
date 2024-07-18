jQuery(document).ready(function($) {
    // Monitora mudanças em todos os inputs do tipo texto que são usados para URLs de arquivos baixáveis
    $(document).on('input', '.wc_file_url', function() {
        var fileUrl = $(this).val();

        // Verifica se a URL termina com '.csv'
        if (fileUrl.length > 0 && !fileUrl.endsWith('.csv')) {
            alert('Erro: Apenas arquivos CSV são permitidos!');
            $(this).val(''); // Limpa o campo
        }
    });

    // Adiciona um ouvinte de eventos para o botão 'Insert file URL' para mouseup e touchend
    $(document).on('mouseup touchend', '.media-button-select:not([disabled])', function() {
        setTimeout(function() {
            $('.wc_file_url').each(function() {
                var fileUrl = $(this).val();
                if (fileUrl.length > 0 && fileUrl.endsWith('.csv')) {
                    fetchCSVAndCountRows(fileUrl); // Processa o arquivo CSV e exibe a quantidade de estoque
                } else if (fileUrl.length > 0 && !fileUrl.endsWith('.csv')) {
                    alert('Erro: Apenas arquivos CSV são permitidos!');
                    $(this).val(''); // Limpa o campo
                }
            });
        }, 100); // Atraso de 100ms para garantir que o URL foi inserido
    });

    function fetchCSVAndCountRows(csvUrl) {
        $.ajax({
            url: csvUrl,
            success: function(data) {
                var rows = data.split('\n');
                var emptyFound = false;
                var errorFlag = false;

                for (var i = 0; i < rows.length; i++) {
                    if (rows[i].trim().replace(/,+/g, '') === '') {
                        if (!emptyFound) {
                            emptyFound = true; // Marca a primeira linha vazia encontrada
                        }
                    } else if (emptyFound) {
                        // Se uma linha preenchida é encontrada após uma linha vazia
                        alert('Erro: Não pode haver linhas vazias entre linhas preenchidas.');
                        $('.wc_file_url').val(''); // Limpa o campo
                        errorFlag = true;
                        break;
                    }
                }

                if (!errorFlag) {
                    updateStockAndShowPreview(rows);
                }
            },
            error: function() {
                alert('Erro ao acessar o arquivo CSV.');
                $('.wc_file_url').val(''); // Limpa o campo se houver erro ao acessar o arquivo
            }
        });
    }

    function updateStockAndShowPreview(rows) {
        var count = rows.length - 1; // Subtrai um para não contar o cabeçalho

        // Atualiza o campo de estoque no DOM
        setTimeout(function() {
            $('input[name="_manage_stock"]').prop('checked', true).change();
            setTimeout(function() {
                $('input[name="_stock"]').val(count);
            }, 500);
        }, 500);

        // Chama a função para exibir o popup
        if (typeof showCSVPreview === "function") {
            showCSVPreview(rows);
        } else {
            console.error("Função showCSVPreview não encontrada.");
        }
    }
});