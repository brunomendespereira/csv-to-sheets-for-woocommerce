document.addEventListener('DOMContentLoaded', function() {
    function updateWarrantyStatus() {
        var warrantyValidUntil = document.getElementById('warranty-valid-until').value;
        var warrantyDate = new Date(warrantyValidUntil);
        var currentDate = new Date();

        if (currentDate.getTime() <= warrantyDate.getTime()) {
            document.getElementById('warranty-status').textContent = "Ativa";
            document.getElementById('warranty-status').style.color = 'green';
            activateCheckboxes(true); // Ativa os checkboxes
        } else {
            document.getElementById('warranty-status').textContent = "Inativa";
            document.getElementById('warranty-status').style.color = 'red';
            activateCheckboxes(false); // Desativa os checkboxes
        }
    }

    function activateCheckboxes(isActive) {
        var checkboxes = document.querySelectorAll('.requestWarranty');
        checkboxes.forEach(function(checkbox) {
            checkbox.disabled = !isActive;
            if (!isActive) {
                checkbox.title = "Prazo de garantia expirado";
            } else {
                checkbox.title = "";
            }
        });
    }

    function scheduleWarrantyCheck() {
        var warrantyValidUntil = document.getElementById('warranty-valid-until').value;
        var warrantyDate = new Date(warrantyValidUntil);
        var currentDate = new Date();
        console.log( warrantyDate.getTime(), currentDate.getTime());
        console.log( warrantyDate.getTime() - currentDate.getTime());
        var timeDifference = warrantyDate.getTime() - currentDate.getTime();

        if (timeDifference > 0) {
            setTimeout(function() {
                updateWarrantyStatus();
            }, timeDifference);
        } else {
            // Se a garantia já expirou, atualiza o status imediatamente
            updateWarrantyStatus();
        }
    }

    // Chama a função imediatamente para configuração inicial e agenda a verificação
    updateWarrantyStatus();
    scheduleWarrantyCheck();
});
