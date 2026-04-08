// Use shared logic from dashboard-core.js
let state = getQueueState();
const profile = getProfile();
const agentLetter = profile.counterLetter || 'A';

function getFilteredUpcoming() {
    return state.upcomingData.filter(ticket => ticket.id.startsWith(agentLetter));
}

function callNext() {
    if (getCounterStatus() === 'closed') {
        alert("Veuillez ouvrir le guichet pour appeler des clients.");
        return;
    }

    const filtered = getFilteredUpcoming();

    if (filtered.length > 0) {
        // Find the index of the first matching ticket in the main array
        const nextTicket = filtered[0];
        const ticketIndex = state.upcomingData.findIndex(t => t.id === nextTicket.id);
        
        // Remove from main array
        state.upcomingData.splice(ticketIndex, 1);
        
        state.currentTicket = nextTicket; // Store full ticket info
        state.currentNum = parseInt(nextTicket.id.split('-')[1]);
        
        // Add to history
        addToHistory(nextTicket, 'servi');
        
        // Update DOM
        document.getElementById('current-number').innerText = nextTicket.id;
        document.getElementById('current-name').innerText = nextTicket.name;
        
        state.servedCount++;
        state.waitingCount--;
        
        saveQueueState(state);
        updateStats();
        renderUpcoming();
        
        // Visual feedback
        pulseNumber();
    } else {
        alert(`Plus de clients avec la lettre ${agentLetter} dans la file !`);
    }
}

function recall() {
    if (getCounterStatus() === 'closed') return;
    pulseNumber();
}

function cancelTicket() {
    if (getCounterStatus() === 'closed') return;
    if (confirm("Voulez-vous vraiment annuler ce ticket ?")) {
        const filtered = getFilteredUpcoming();
        if (filtered.length > 0) {
            const nextTicket = filtered[0];
            const ticketIndex = state.upcomingData.findIndex(t => t.id === nextTicket.id);
            
            const canceledTicket = state.upcomingData.splice(ticketIndex, 1)[0];
            addToHistory(canceledTicket, 'annulé');
            state.waitingCount--;
            saveQueueState(state);
            updateStats();
            renderUpcoming();
        }
    }
}

function toggleCounter() {
    const newStatus = toggleCounterStatus();
    updateCounterUI(newStatus);
}

function updateCounterUI(status) {
    const dot = document.getElementById('status-dot');
    const text = document.getElementById('status-text');
    const btnText = document.getElementById('btn-toggle-text');
    
    if (status === 'open') {
        dot.className = 'dot dot-open';
        text.innerText = 'Guichet Ouvert';
        btnText.innerText = 'Fermer Guichet';
    } else {
        dot.className = 'dot dot-closed';
        text.innerText = 'Guichet Fermé';
        btnText.innerText = 'Ouvrir Guichet';
    }
}

function pulseNumber() {
    const display = document.getElementById('current-number');
    display.style.animation = 'none';
    void display.offsetWidth;
    display.style.animation = 'pulse 0.5s ease-in-out';
}

function updateStats() {
    // Show total waiting for THIS agent's letter? Or total for establishment?
    // User said "he can choose the letter... and the ticket... should only be starting with that letter"
    // So waiting count should probably reflect the filtered list for this view.
    const filtered = getFilteredUpcoming();
    document.getElementById('waiting-count').innerText = filtered.length;
    document.getElementById('served-count').innerText = state.servedCount;
}

function renderUpcoming() {
    const list = document.getElementById('upcoming-list');
    if (!list) return;
    list.innerHTML = '';
    
    // Filter by agent letter
    const filtered = getFilteredUpcoming();
    
    filtered.slice(0, 4).forEach(ticket => {
        const item = document.createElement('div');
        item.className = 'queue-item';
        item.innerHTML = `
            <div class="ticket-id">${ticket.id}</div>
            <div class="ticket-info">
                <h5>${ticket.name}</h5>
                <p>Spécialité: ${agentLetter}</p>
            </div>
            <div class="ticket-wait">Attente: ${ticket.time}</div>
        `;
        list.appendChild(item);
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    updateStats();
    renderUpcoming();
    updateCounterUI(getCounterStatus());
    
    if (state.currentTicket) {
        document.getElementById('current-number').innerText = state.currentTicket.id;
        document.getElementById('current-name').innerText = state.currentTicket.name;
    } else {
        document.getElementById('current-number').innerText = `${agentLetter}-000`;
        document.getElementById('current-name').innerText = `En attente (Guichet ${agentLetter})...`;
    }
});
