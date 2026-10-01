/**
 * Main Public JavaScript
 * E-Vaccination Management System
 */

document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.custom-alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });

    // Public Vaccine Search / Filter
    const vaccineSearch = document.getElementById('vaccineSearchInput');
    if (vaccineSearch) {
        vaccineSearch.addEventListener('keyup', function() {
            const query = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.vaccine-item-card');
            
            cards.forEach(function(card) {
                const text = card.textContent.toLowerCase();
                if (text.includes(query)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Public Hospital Search / Filter
    const hospitalSearch = document.getElementById('hospitalSearchInput');
    if (hospitalSearch) {
        hospitalSearch.addEventListener('keyup', function() {
            const query = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.hospital-item-card');
            
            cards.forEach(function(card) {
                const text = card.textContent.toLowerCase();
                if (text.includes(query)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});

