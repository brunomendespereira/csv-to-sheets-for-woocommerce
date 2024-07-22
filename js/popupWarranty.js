document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.requestWarranty');
    const popup = document.getElementById('popup');
    const submitWarrantyRequestButton = document.getElementById('submitWarrantyRequest');
    const selectedItemsContainer = document.getElementById('selected-items-container');
    const selectedCount = document.getElementById('selected-count');

    if (!popup || !submitWarrantyRequestButton || !selectedItemsContainer || !selectedCount) {
        console.error('Elemento popup ou algum elemento necessário não encontrado');
        return;
    }

    checkboxes.forEach(function(checkbox, index) {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                addSelectedItem(this.closest('tr'));
            } else {
                removeSelectedItem(this.closest('tr').dataset.index);
            }
            updateSelectedCount();
        });
    });

    submitWarrantyRequestButton.addEventListener('click', function() {
        if (!validateForm()) {
            alert('Por favor, preencha o motivo de todos os campos.');
            return;
        }
        // Adicione aqui a lógica para enviar o formulário
    });

    function addSelectedItem(row) {
        const clone = row.cloneNode(true);
        clone.dataset.index = row.dataset.index;

        // Remove a primeira coluna de checkbox
        clone.removeChild(clone.children[0]);

        // Adiciona uma nova coluna para a justificativa
        const justificationCell = document.createElement('td');
        const justificationInput = document.createElement('input');
        justificationInput.type = 'text';
        justificationInput.placeholder = 'Justifique o Motivo*';
        justificationInput.classList.add('justification-input');
        justificationInput.required = true; // Torna o campo obrigatório
        justificationCell.appendChild(justificationInput);
        clone.appendChild(justificationCell);

        // Adicionar classes genéricas às colunas do popup
for (let i = 0; i < clone.children.length; i++) {
    clone.children[i].classList.add('popup-column-' + i);
}
justificationCell.classList.add('popup-justification-column');

        // Define explicitamente a largura das células clonadas
        const originalCells = row.children;
        const cloneCells = clone.children;

        for (let i = 0; i < originalCells.length; i++) {
            if (i === 0) continue; // Ignora a primeira coluna de checkbox
            const originalWidth = originalCells[i].getBoundingClientRect().width;
            cloneCells[i - 1].style.width = `${originalWidth}px`;
        }

        selectedItemsContainer.querySelector('tbody').appendChild(clone);
    }

    function removeSelectedItem(index) {
        const item = selectedItemsContainer.querySelector(`tr[data-index="${index}"]`);
        if (item) {
            selectedItemsContainer.querySelector('tbody').removeChild(item);
        }
    }

    function updateSelectedCount() {
        const count = selectedItemsContainer.querySelector('tbody').children.length;
        selectedCount.textContent = count;
        if (count > 0) {
            popup.classList.remove('popup-hidden');
            popup.classList.add('popup-visible');
        } else {
            popup.classList.remove('popup-visible');
            popup.classList.add('popup-hidden');
        }
    }

    function validateForm() {
        const inputs = selectedItemsContainer.querySelectorAll('.justification-input');
        for (let input of inputs) {
            if (!input.value.trim()) {
                input.focus();
                return false;
            }
        }
        return true;
    }
});
