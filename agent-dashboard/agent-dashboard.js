// Simulated queue data
let currentNum = 42;
let waitingCount = 12;
let servedCount = 48;

const upcomingData = [
    { id: 'A-043', name: 'Sami Ben Ali', time: '8 min' },
    { id: 'A-044', name: 'Leila Trabelsi', time: '12 min' },
    { id: 'A-045', name: 'Anonyme', time: '15 min' },
    { id: 'B-012', name: 'Karim Gharbi', time: '22 min' },
    { id: 'A-046', name: 'Faten Dridi', time: '25 min' },
    { id: 'A-047', name: 'Omar Said', time: '30 min' }
];

function callNext() {
    if (upcomingData.length > 0) {
        // Move next ticket to current
        const nextTicket = upcomingData.shift();
        currentNum = parseInt(nextTicket.id.split('-')[1]);
        
        // Update DOM
        document.getElementById('current-number').innerText = nextTicket.id;
        
        servedCount++;
        waitingCount--;
        
        updateStats();
        renderUpcoming();
        
        // Visual feedback
        const display = document.getElementById('current-number');
        display.style.animation = 'none';
        void display.offsetWidth; // trigger reflow
        display.style.animation = 'pulse 0.5s ease-in-out';
    } else {
        alert("Plus de clients dans la file !");
    }
}

function recall() {
    const display = document.getElementById('current-number');
    display.style.animation = 'none';
    void display.offsetWidth;
    display.style.animation = 'pulse 0.5s ease-in-out';
    
    // Simulate notification sound or visual ping
    console.log("Rappel du client " + display.innerText);
}

function cancelTicket() {
    if (confirm("Voulez-vous vraiment annuler ce ticket ?")) {
        callNext(); // Simply move to next for simulation
    }
}

function updateStats() {
    document.getElementById('waiting-count').innerText = waitingCount;
    document.getElementById('served-count').innerText = servedCount;
}

function renderUpcoming() {
    const list = document.getElementById('upcoming-list');
    list.innerHTML = '';
    
    // Show only first 4
    upcomingData.slice(0, 4).forEach(ticket => {
        const item = document.createElement('div');
        item.className = 'queue-item';
        item.innerHTML = `
            <div class="ticket-id">${ticket.id}</div>
            <div class="ticket-info">
                <h5>${ticket.name}</h5>
                <p>Caisse Principale</p>
            </div>
            <div class="ticket-wait">Attente: ${ticket.time}</div>
        `;
        list.appendChild(item);
    });
}

// Add animation keyframes via JS for simplicity if not in CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
`;
document.head.appendChild(style);

// Initialize
renderUpcoming();
