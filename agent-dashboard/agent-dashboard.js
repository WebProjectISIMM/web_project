/**
 * SmartQueue – Agent Dashboard (main view)
 * The queue is fetched directly from the database based on the agent's establishment.
 */

const profile = getProfile();
let globalQueue = [];
let currentServingTicket = JSON.parse(localStorage.getItem('current_serving_ticket_' + SERVICE_KEY)) || null;
let servedCount = parseInt(localStorage.getItem('served_count_' + SERVICE_KEY)) || 0;

// ── Fetch Queue from API ──────────────────────────────────────────────────────
function fetchQueue() {
    fetch('/Web_Project/api/tickets.php?action=get-queue&establishment=' + encodeURIComponent(SERVICE_KEY), {
        method: 'GET',
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            globalQueue = data.queue || [];
            updateStats();
            renderUpcoming();
        }
    })
    .catch(error => console.error('Error fetching queue:', error));
}

// ── Call next client ──────────────────────────────────────────────────────────
function callNext() {
    if (getCounterStatus() === 'closed') {
        alert("Veuillez ouvrir le guichet pour appeler des clients.");
        return;
    }

    if (globalQueue.length > 0) {
        const nextTicket = globalQueue[0]; // peek

        fetch('/Web_Project/api/tickets.php?action=serve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ ticket_id: nextTicket.id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                globalQueue.shift(); // remove from local queue list
                currentServingTicket = nextTicket;
                localStorage.setItem('current_serving_ticket_' + SERVICE_KEY, JSON.stringify(currentServingTicket));
                
                servedCount++;
                localStorage.setItem('served_count_' + SERVICE_KEY, servedCount);

                addToHistory({
                    id: nextTicket.ticket_number,
                    name: nextTicket.name || 'Client',
                    time: nextTicket.wait_time || ''
                }, 'servi');

                updateUIWithCurrentTicket();
                updateStats();
                renderUpcoming();
                pulseNumber();
            } else {
                alert('❌ Erreur: ' + (data.message || 'Impossible de servir le ticket'));
            }
        })
        .catch(error => console.error('Error:', error));
    } else {
        alert("Aucun client en attente dans votre file !");
    }
}

// ── Recall current client ─────────────────────────────────────────────────────
function recall() {
    if (getCounterStatus() === 'closed') return;
    pulseNumber();
}

// ── Cancel the next ticket in queue ──────────────────────────────────────────
function cancelTicket() {
    if (getCounterStatus() === 'closed') return;

    if (globalQueue.length > 0) {
        if (confirm("Voulez-vous vraiment annuler le premier ticket en file d'attente ?")) {
            const nextTicket = globalQueue[0];
            
            fetch('/Web_Project/api/tickets.php?action=cancel', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ ticket_id: nextTicket.id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    globalQueue.shift();
                    
                    addToHistory({
                        id: nextTicket.ticket_number,
                        name: nextTicket.name || 'Client',
                        time: nextTicket.wait_time || ''
                    }, 'annulé');
                    
                    updateStats();
                    renderUpcoming();
                } else {
                    alert('❌ Erreur: ' + (data.message || 'Impossible d\'annuler le ticket'));
                }
            })
            .catch(error => console.error('Error:', error));
        }
    } else {
        alert("Aucun ticket à annuler.");
    }
}

// ── Open / Close counter toggle ───────────────────────────────────────────────
function toggleCounter() {
    const newStatus = toggleCounterStatus();
    updateCounterUI(newStatus);
}

function updateCounterUI(status) {
    const dot     = document.getElementById('status-dot');
    const text    = document.getElementById('status-text');
    const btnText = document.getElementById('btn-toggle-text');

    if (status === 'open') {
        dot.className  = 'dot dot-open';
        text.innerText = 'Guichet Ouvert';
        btnText.innerText = 'Fermer Guichet';
    } else {
        dot.className  = 'dot dot-closed';
        text.innerText = 'Guichet Fermé';
        btnText.innerText = 'Ouvrir Guichet';
    }
}

// ── Visual pulse on current number ────────────────────────────────────────────
function pulseNumber() {
    const display = document.getElementById('current-number');
    if (!display) return;
    display.style.animation = 'none';
    void display.offsetWidth; // reflow
    display.style.animation  = 'pulse 0.5s ease-in-out';
}

// ── Update stat cards ─────────────────────────────────────────────────────────
function updateStats() {
    const waitingEl = document.getElementById('waiting-count');
    const servedEl  = document.getElementById('served-count');
    if (waitingEl) waitingEl.innerText = globalQueue.length;
    if (servedEl)  servedEl.innerText  = servedCount;
}

function updateUIWithCurrentTicket() {
    if (currentServingTicket) {
        document.getElementById('current-number').innerText = currentServingTicket.ticket_number;
        document.getElementById('current-name').innerText   = currentServingTicket.name || 'Client';
    } else {
        document.getElementById('current-number').innerText = 'A-000';
        document.getElementById('current-name').innerText   = 'En attente du premier client…';
    }
}

// ── Render upcoming queue list ────────────────────────────────────────────────
function renderUpcoming() {
    const list = document.getElementById('upcoming-list');
    if (!list) return;

    list.innerHTML = '';

    if (globalQueue.length === 0) {
        list.innerHTML = `
            <div style="text-align:center; padding: 30px; color: var(--text-muted);">
                <i class="fas fa-inbox fa-2x" style="margin-bottom:12px; opacity:0.4;"></i>
                <p>Aucun client en attente</p>
            </div>`;
        return;
    }

    globalQueue.slice(0, 6).forEach((ticket, index) => {
        const item = document.createElement('div');
        item.className = 'queue-item';
        item.innerHTML = `
            <div class="ticket-id">${ticket.ticket_number}</div>
            <div class="ticket-info">
                <h5>${ticket.name || 'Client'}</h5>
                <p>${profile.service || 'Service'}</p>
            </div>
            <div class="ticket-wait">Attente: ${ticket.wait_time || '~' + (index + 1) * 5 + ' min'}</div>
        `;
        list.appendChild(item);
    });
}

// ── Live refresh every 5 seconds (picks up new bookings automatically) ────────
function liveRefresh() {
    fetchQueue();
}

// ── Bootstrap ─────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    updateUIWithCurrentTicket();
    updateCounterUI(getCounterStatus());
    
    // Initial fetch
    fetchQueue();

    // Poll every 5 s so new bookings appear without a page reload
    setInterval(liveRefresh, 5000);
});

