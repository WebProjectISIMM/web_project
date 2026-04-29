document.addEventListener('DOMContentLoaded', () => {
    renderTickets();
});

function renderTickets() {
    const ticketContainer = document.getElementById('ticket-container');
    const headerTitle = document.getElementById('ticket-count-text');

    // Fetch tickets from API
    fetch('/Web_Project/api/tickets.php?action=get-user-tickets', {
        method: 'GET',
        credentials: 'include'
    })
    .then(response => {
        if (!response.ok) throw new Error('Erreur serveur');
        return response.json();
    })
    .then(data => {
        if (!data.success) {
            throw new Error(data.message || 'Erreur lors du chargement des tickets');
        }

        const tickets = data.tickets || [];
        const count = tickets.length;
        
        // Update header
        headerTitle.innerHTML = `Vous avez <b>${count} ticket${count !== 1 ? 's' : ''}</b> actif${count !== 1 ? 's' : ''} en ce moment`;

        if (count > 0) {
            ticketContainer.innerHTML = '';
            
            tickets.forEach(ticket => {
                const ticketCard = document.createElement('div');
                ticketCard.className = 'card';
                ticketCard.style.marginBottom = '20px';
                
                ticketCard.innerHTML = `
                    <div class="card-header">
                        <div class="logotext-header">
                            <div class="logo-ticket">${(ticket.agency || 'N/A').substring(0, 4)}</div>
                            <div class="text-header">
                                <span class="nom-header">${ticket.agency || 'Établissement'}</span>
                                <div class="localisation-identif">
                                    <i class="fas fa-map-marker-alt" style="color: var(--primary-color);"></i>
                                    <span class="color">${ticket.location || 'Localisation'}</span>
                                </div>
                            </div>
                        </div>
                        <div class="header-right">
                            <p class="btn-header">en attente..</p>
                        </div>
                    </div>
                    
                    <div class="card-middle">
                        <div class="middle-1">
                            <p class="text-middle">VOTRE NUMÉRO</p>
                            <span class="numero-gras">${ticket.ticket_number || 'N/A'}</span>
                        </div>
                        <div class="middle-2">
                            <p class="text-middle">ESTIMATION</p>
                            <span class="text2-middle">${ticket.wait_time || '~10 min'}</span>
                        </div>
                    </div>
                    
                    <div class="card-bottom">
                        <div class="personnes-file">
                            <i class="fas fa-users" style="color: var(--primary-color);"></i>
                            <p class="txt">${ticket.people_ahead || 0} personnes avant vous</p>
                        </div>
                        <button class="btn-cancel" onclick="cancelSpecificTicket(${ticket.id})">Annuler mon ticket</button>
                    </div>
                `;
                ticketContainer.appendChild(ticketCard);
            });
        } else {
            ticketContainer.innerHTML = `
                <p class="empty-state">Aucun ticket actif. Veuillez prendre un nouveau ticket.</p>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        ticketContainer.innerHTML = `
            <div style="padding: 20px; background: #fff3cd; border-radius: 8px; color: #856404;">
                <i class="fas fa-exclamation-triangle"></i> Erreur lors du chargement des tickets.<br>
                <small>Veuillez rafraîchir la page.</small>
            </div>
        `;
    });
}

function cancelSpecificTicket(id) {
    if (confirm("Êtes-vous sûr de vouloir annuler ce ticket ?")) {
        fetch('/Web_Project/api/tickets.php?action=cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({ ticket_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Ticket annulé avec succès');
                renderTickets();
            } else {
                alert('❌ Erreur: ' + (data.message || 'Impossible d\'annuler le ticket'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Erreur de connexion');
        });
    }
}

function cancelTicket() {
    const ticketContainer = document.getElementById('ticket-container');
    const buttons = ticketContainer.querySelectorAll('.btn-cancel');
    if (buttons.length > 0) {
        buttons[0].click();
    }
}
