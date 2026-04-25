/**
 * SmartQueue – Agent Dashboard (main view)
 * The queue is already scoped to this agent's service via dashboard-core.js
 * No letter-filtering needed — every ticket in the queue belongs to this service.
 */

let state   = getQueueState();
const profile = getProfile();

// ── Call next client ──────────────────────────────────────────────────────────
function callNext() {
    if (getCounterStatus() === 'closed') {
        alert("Veuillez ouvrir le guichet pour appeler des clients.");
        return;
    }

    state = getQueueState(); // re-read in case a new booking just arrived

    if (state.upcomingData.length > 0) {
        const nextTicket = state.upcomingData.shift(); // take first in queue

        state.currentTicket = nextTicket;
        state.currentNum    = parseInt(nextTicket.id.split('-')[1]) || 0;
        state.servedCount   = (state.servedCount || 0) + 1;
        state.waitingCount  = state.upcomingData.length;

        addToHistory(nextTicket, 'servi');
        saveQueueState(state);

        document.getElementById('current-number').innerText = nextTicket.id;
        document.getElementById('current-name').innerText   = nextTicket.name || 'Client';

        updateStats();
        renderUpcoming();
        pulseNumber();
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

    state = getQueueState();

    if (state.upcomingData.length > 0) {
        if (confirm("Voulez-vous vraiment annuler ce ticket ?")) {
            const canceled = state.upcomingData.shift();
            addToHistory(canceled, 'annulé');
            state.waitingCount = state.upcomingData.length;
            saveQueueState(state);
            updateStats();
            renderUpcoming();
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
    display.style.animation = 'none';
    void display.offsetWidth; // reflow
    display.style.animation  = 'pulse 0.5s ease-in-out';
}

// ── Update stat cards ─────────────────────────────────────────────────────────
function updateStats() {
    state = getQueueState();
    const waitingEl = document.getElementById('waiting-count');
    const servedEl  = document.getElementById('served-count');
    if (waitingEl) waitingEl.innerText = state.upcomingData.length;
    if (servedEl)  servedEl.innerText  = state.servedCount || 0;
}

// ── Render upcoming queue list ────────────────────────────────────────────────
function renderUpcoming() {
    const list = document.getElementById('upcoming-list');
    if (!list) return;

    state = getQueueState();
    list.innerHTML = '';

    if (state.upcomingData.length === 0) {
        list.innerHTML = `
            <div style="text-align:center; padding: 30px; color: var(--text-muted);">
                <i class="fas fa-inbox fa-2x" style="margin-bottom:12px; opacity:0.4;"></i>
                <p>Aucun client en attente</p>
            </div>`;
        return;
    }

    state.upcomingData.slice(0, 6).forEach((ticket, index) => {
        const item = document.createElement('div');
        item.className = 'queue-item';
        item.innerHTML = `
            <div class="ticket-id">${ticket.id}</div>
            <div class="ticket-info">
                <h5>${ticket.name || 'Client'}</h5>
                <p>${profile.service || SERVICE_KEY}</p>
            </div>
            <div class="ticket-wait">Attente: ${ticket.time || '~' + (index + 1) * 5 + ' min'}</div>
        `;
        list.appendChild(item);
    });
}

// ── Live refresh every 5 seconds (picks up new bookings automatically) ────────
function liveRefresh() {
    updateStats();
    renderUpcoming();
}

// ── Bootstrap ─────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    state = getQueueState();

    // Show current ticket if one was being served
    if (state.currentTicket) {
        document.getElementById('current-number').innerText = state.currentTicket.id;
        document.getElementById('current-name').innerText   = state.currentTicket.name || 'Client';
    } else {
        document.getElementById('current-number').innerText = 'A-000';
        document.getElementById('current-name').innerText   = 'En attente du premier client…';
    }

    updateStats();
    renderUpcoming();
    updateCounterUI(getCounterStatus());

    // Poll every 5 s so new bookings appear without a page reload
    setInterval(liveRefresh, 5000);
});
