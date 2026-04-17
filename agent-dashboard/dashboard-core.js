/**
 * Shared logic for Agent Dashboard modules
 * Handles localStorage synchronization and queue state
 */

const COUNTER_KEY = 'agent_counter_status'; // 'open' or 'closed'
const QUEUE_STATE_KEY = 'agent_queue_state';
const HISTORY_KEY = 'agent_queue_history';
const PROFILE_KEY = 'agent_profile_settings';

// Initial state if not exists
const defaultState = {
    currentNum: 42,
    currentTicket: { id: 'A-042', name: 'Zied Hammami', time: 'Servi' },
    waitingCount: 12,
    servedCount: 48,
    upcomingData: [
        { id: 'A-043', name: 'Sami Ben Ali', time: '8 min' },
        { id: 'A-044', name: 'Leila Trabelsi', time: '12 min' },
        { id: 'A-045', name: 'Anonyme', time: '15 min' },
        { id: 'B-012', name: 'Karim Gharbi', time: '22 min' },
        { id: 'A-046', name: 'Faten Dridi', time: '25 min' },
        { id: 'A-047', name: 'Omar Said', time: '30 min' }
    ]
};

const defaultProfile = {
    name: 'Ahmed Mansour',
    role: 'Agent de Caisse',
    counter: 'Guichet 03',
    service: 'Caisse Principale',
    counterLetter: 'A'
};

function getQueueState() {
    const saved = localStorage.getItem(QUEUE_STATE_KEY);
    return saved ? JSON.parse(saved) : defaultState;
}

function saveQueueState(state) {
    localStorage.setItem(QUEUE_STATE_KEY, JSON.stringify(state));
}

function getHistory() {
    const saved = localStorage.getItem(HISTORY_KEY);
    return saved ? JSON.parse(saved) : [];
}

function addToHistory(ticket, status = 'servi') {
    const history = getHistory();
    const entry = {
        ...ticket,
        time: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }),
        date: new Date().toLocaleDateString('fr-FR'),
        status: status // 'servi' or 'annulé'
    };
    history.unshift(entry);
    localStorage.setItem(HISTORY_KEY, JSON.stringify(history));
}

function getProfile() {
    if (window.userProfile) {
        return {
            ...defaultProfile,
            name: window.userProfile.name,
            role: window.userProfile.role,
            service: window.userProfile.establishment
        };
    }
    const saved = localStorage.getItem(PROFILE_KEY);
    return saved ? JSON.parse(saved) : defaultProfile;
}


function saveProfile(profile) {
    localStorage.setItem(PROFILE_KEY, JSON.stringify(profile));
}

function getCounterStatus() {
    return localStorage.getItem(COUNTER_KEY) || 'open';
}

function toggleCounterStatus() {
    const current = getCounterStatus();
    const next = current === 'open' ? 'closed' : 'open';
    localStorage.setItem(COUNTER_KEY, next);
    return next;
}

// Update UI profile info everywhere
document.addEventListener('DOMContentLoaded', () => {
    const profile = getProfile();
    const nameEls = document.querySelectorAll('.user-info h4, header h1');
    const roleEls = document.querySelectorAll('.user-info p');
    
    nameEls.forEach(el => {
        if (el.tagName === 'H1') {
            el.innerText = `Bonjour, ${profile.name.split(' ')[0]} 👋`;
        } else {
            el.innerText = profile.name;
        }
    });
    
    roleEls.forEach(el => {
        el.innerText = profile.role;
    });

    const avatarEl = document.querySelector('.avatar');
    if (avatarEl) {
        const initials = profile.name.split(' ').map(n => n[0]).join('').toUpperCase();
        avatarEl.innerText = initials;
    }
});
