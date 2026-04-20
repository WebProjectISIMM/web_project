/**
 * SmartQueue – Shared Agent Dashboard Logic
 * All localStorage keys are scoped to the agent's SERVICE (sector)
 * so each service has a completely independent queue and history.
 */

// ── Determine service scope (Establishment level) ─────────────────────────
// We now scope by specific establishment (e.g., BIAT Marina) instead of generic sector.
const SERVICE_KEY = (window.userProfile && window.userProfile.establishment)
    ? window.userProfile.establishment.toLowerCase().replace(/[^a-z0-9]/g, '_')
    : 'default';

// ── Per-service localStorage keys ────────────────────────────────────────────
const COUNTER_KEY     = `agent_counter_status_${SERVICE_KEY}`;
const QUEUE_STATE_KEY = `agent_queue_state_${SERVICE_KEY}`;
const HISTORY_KEY     = `agent_queue_history_${SERVICE_KEY}`;
const PROFILE_KEY     = 'agent_profile_settings';

// ── Default state (empty queue — real tickets come from client bookings) ───────
function buildDefaultState() {
    return {
        currentNum:    0,
        currentTicket: null,
        waitingCount:  0,
        servedCount:   0,
        upcomingData:  []
    };
}

const defaultProfile = {
    name:          'Agent',
    role:          SERVICE_KEY,
    counter:       'Guichet 01',
    service:       SERVICE_KEY,
    counterLetter: 'A'
};

// ── State management ─────────────────────────────────────────────────────────
function getQueueState() {
    const saved = localStorage.getItem(QUEUE_STATE_KEY);
    return saved ? JSON.parse(saved) : buildDefaultState();
}

function saveQueueState(state) {
    localStorage.setItem(QUEUE_STATE_KEY, JSON.stringify(state));
}

// ── History management ────────────────────────────────────────────────────────
function getHistory() {
    const saved = localStorage.getItem(HISTORY_KEY);
    return saved ? JSON.parse(saved) : [];
}

function addToHistory(ticket, status = 'servi') {
    const history = getHistory();
    history.unshift({
        ...ticket,
        time:   new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
        date:   new Date().toLocaleDateString('fr-FR'),
        status: status
    });
    localStorage.setItem(HISTORY_KEY, JSON.stringify(history));
}

// ── Profile ───────────────────────────────────────────────────────────────────
function getProfile() {
    if (window.userProfile) {
        return {
            ...defaultProfile,
            name:    window.userProfile.name,
            role:    window.userProfile.role,
            service: window.userProfile.establishment,
            counterLetter: 'A'
        };
    }
    const saved = localStorage.getItem(PROFILE_KEY);
    return saved ? JSON.parse(saved) : defaultProfile;
}

function saveProfile(profile) {
    localStorage.setItem(PROFILE_KEY, JSON.stringify(profile));
}

// ── Counter (guichet open/closed) ─────────────────────────────────────────────
function getCounterStatus() {
    return localStorage.getItem(COUNTER_KEY) || 'open';
}

function toggleCounterStatus() {
    const next = getCounterStatus() === 'open' ? 'closed' : 'open';
    localStorage.setItem(COUNTER_KEY, next);
    return next;
}

// ── Boot: fill profile UI elements ───────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const profile = getProfile();

    document.querySelectorAll('.user-info h4').forEach(el => { el.innerText = profile.name; });
    document.querySelectorAll('.user-info p').forEach(el  => { el.innerText = profile.role || SERVICE_KEY; });

    const h1 = document.querySelector('header h1');
    if (h1) h1.innerText = `Bonjour, ${profile.name.split(' ')[0]} 👋`;

    const avatarEl = document.querySelector('.avatar');
    if (avatarEl) {
        avatarEl.innerText = profile.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
    }
});
