document.addEventListener('DOMContentLoaded', () => {
    renderTickets();
});

function renderTickets() {
    const ticketContainer = document.getElementById('ticket-container');
    const headerTitle = document.getElementById('ticket-count-text');
    
    // Support both old 'activeTicket' and new 'activeTickets' for smooth migration
    let tickets = [];
    const savedTickets = localStorage.getItem('activeTickets');
    const oldTicket = localStorage.getItem('activeTicket');

    if (savedTickets) {
        tickets = JSON.parse(savedTickets);
    } else if (oldTicket) {
        // Migrate old single ticket to new array format
        const ticket = JSON.parse(oldTicket);
        if (!ticket.id) ticket.id = "T-OLD";
        tickets.push(ticket);
        localStorage.setItem('activeTickets', JSON.stringify(tickets));
        localStorage.removeItem('activeTicket');
    }
    
    // Update header text
    const count = tickets.length;
    headerTitle.innerHTML = `Vous avez <b>${count} ticket${count !== 1 ? 's' : ''}</b> actif${count !== 1 ? 's' : ''} en ce moment`;
    
    if (count > 0) {
        ticketContainer.innerHTML = ''; // Clear previous content
        
        // Loop through and build HTML for each ticket
        tickets.forEach(ticket => {
            const ticketCard = document.createElement('div');
            ticketCard.className = 'card';
            ticketCard.style.marginBottom = '20px'; // Add spacing between multiple cards
            
            ticketCard.innerHTML = `
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
                    <button class="btn-cancel" onclick="cancelSpecificTicket('${ticket.id}')">Annuler mon ticket</button>
                </div>
            `;
            ticketContainer.appendChild(ticketCard);
        });
    } else {
        // No tickets active
        ticketContainer.innerHTML = `
            <p class="empty-state">Aucun ticket actif. Veuillez prendre un nouveau ticket.</p>
        `;
    }
}

function cancelSpecificTicket(id) {
    if (confirm("Êtes-vous sûr de vouloir annuler ce ticket ?")) {
        let tickets = JSON.parse(localStorage.getItem('activeTickets') || '[]');
        tickets = tickets.filter(t => t.id !== id);
        localStorage.setItem('activeTickets', JSON.stringify(tickets));
        renderTickets();
    }
}

// Keep old function for compatibility if called elsewhere without ID
function cancelTicket() {
    const tickets = JSON.parse(localStorage.getItem('activeTickets') || '[]');
    if (tickets.length > 0) {
        cancelSpecificTicket(tickets[0].id);
    }
}
