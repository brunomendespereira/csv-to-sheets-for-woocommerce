jQuery(document).ready(function($) {
    function showCSVPreview(rows) {
        var preview = '<div style="font-weight: bold;">Se estiver tudo correto, continue configurando o produto.</div>';
        preview += '<table>';
        var maxRows = Math.min(5, rows.length);
        for (var i = 0; i < maxRows; i++) {
            var cols = rows[i].split(',');
            preview += '<tr>';
            for (var j = 0; j < cols.length; j++) {
                if (i === 0) {
                    preview += '<th>' + cols[j] + '</th>';
                } else {
                    preview += '<td>' + cols[j] + '</td>';
                }
            }
            preview += '</tr>';
        }
        preview += '</table>';

        // Adiciona a mensagem de "Mais x linhas" abaixo da tabela
        if (rows.length > 5) {
            var remainingRows = rows.length - 5;
            preview += '<div class="more-rows">Mais ' + remainingRows + ' linhas...</div>';
        }

        // Exibe o modal com a pré-visualização do CSV
        $('#csvPreviewContent').html(preview);
        $('#csvPreviewModal').show();
    }

    // Fecha o modal quando o usuário clica no botão de fechar
    $(document).on('click', '#closeModal', function() {
        $('#csvPreviewModal').hide();
    });

    // Fecha o modal quando o usuário clica fora do conteúdo do modal
    $(document).on('click', '#csvPreviewModal', function(event) {
        if ($(event.target).is('#csvPreviewModal')) {
            $('#csvPreviewModal').hide();
        }
    });

    // Expõe a função para ser chamada de outro arquivo
    window.showCSVPreview = showCSVPreview;
});
