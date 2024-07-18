function copyCell(element) {
    var cell = element.closest('td');
    var textToCopy = cell.textContent.trim();
    navigator.clipboard.writeText(textToCopy).then(function() {
        alert('Conteúdo copiado para a área de transferência!');
    }).catch(function(error) {
        console.error('Erro ao copiar o conteúdo: ', error);
    });
}