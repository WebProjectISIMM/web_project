document.addEventListener('DOMContentLoaded', () => {
    renderTicket();
});

function renderTicket() {
    const ticketContainer = document.getElementById('ticket-container');
    const headerTitle = document.getElementById('ticket-count-text');
    
    // Check if there is an active ticket in localStorage
    const savedTicket = localStorage.getItem('activeTicket');
    
    if (savedTicket) {
        const ticket = JSON.parse(savedTicket);
        
        // Update header text
        headerTitle.innerHTML = 'Vous avez <b>1 ticket</b> actif en ce moment';
        
        // Build the ticket card HTML
        ticketContainer.innerHTML = `
            <div class="card">
                <div class="card-header">
                    <div class="logotext-header">
                        <div class="logo-ticket">${ticket.agency.substring(0, 4)}</div>
                        <div class="text-header">
                            <span class="nom-header">${ticket.agency}</span>
                            <div class="localisation-identif">
                                <i class="fas fa-map-marker-alt" style="color: var(--primary-color);"></i>
                                <span class="color">${ticket.location}</span>
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
                        <span class="numero-gras">${ticket.ticketNumber}</span>
                    </div>
                    <div class="middle-2">
                        <p class="text-middle">ESTIMATION</p>
                        <span class="text2-middle">${ticket.waitTime}</span>
                    </div>
                </div>
                
                <div class="card-bottom">
                    <div class="personnes-file">
                        <i class="fas fa-users" style="color: var(--primary-color);"></i>
                        <p class="txt">${ticket.peopleAhead} personnes avant vous</p>
                    </div>
                    <button class="btn-cancel" onclick="cancelTicket()">Annuler mon ticket</button>
                </div>
            </div>
        `;
    } else {
        // No ticket active
        headerTitle.innerHTML = 'Vous avez <b>0 ticket</b> actif en ce moment';
        ticketContainer.innerHTML = `
            <p class="empty-state">Aucun ticket actif. Veuillez prendre un nouveau ticket.</p>
        `;
    }
}

function cancelTicket() {
    if (confirm("Êtes-vous sûr de vouloir annuler ce ticket ?")) {
        localStorage.removeItem('activeTicket');
        renderTicket();
    }
}
