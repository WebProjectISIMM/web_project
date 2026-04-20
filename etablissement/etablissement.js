// ── Filter list ──────────────────────────────────────────────────────────────
function filterList() {
    let searchInput = document.getElementById('searchInput').value.toLowerCase();
    let locationFilter = document.getElementById('locationFilter').value;
    let cards = document.getElementsByClassName('list-item');

    for (let card of cards) {
        let name = card.getAttribute('data-name').toLowerCase();
        let location = card.getAttribute('data-location');

        let matchesSearch = name.includes(searchInput);
        let matchesLocation = locationFilter === "" || location === locationFilter;

        card.style.display = (matchesSearch && matchesLocation) ? "flex" : "none";
    }
}

// ── Booking confirmation ──────────────────────────────────────────────────────
// agencyName   : display name shown on the ticket card
// locationName : city / branch label
// serviceKey   : one of 'banque' | 'cinema' | 'resto' | 'administration'
function confirmBooking(agencyName, locationName, serviceKey) {
    // ── 1. Create a unique scope key for this specific establishment branch ──
    const establishmentSlug = agencyName.toLowerCase().replace(/[^a-z0-9]/g, '_');

    // ── 1. Increment the per-service sequential counter ──────────────────────
    const counterKey = `ticketCounter_${establishmentSlug}`;
    const currentCount = parseInt(localStorage.getItem(counterKey) || '0') + 1;
    localStorage.setItem(counterKey, currentCount);

    const ticketNumber = "A-" + String(currentCount).padStart(3, '0');

    // ── 2. Build the client-facing ticket object ──────────────────────────────
    const waitTime  = "~" + Math.floor(Math.random() * 20 + 5) + " min";
    const peopleAhead = currentCount - 1;   // everyone who booked before in this session

    const newTicket = {
        id:           "T-" + Date.now(),
        agency:       agencyName,
        location:     locationName || "Centre Ville",
        ticketNumber: ticketNumber,
        waitTime:     waitTime,
        peopleAhead:  peopleAhead,
        serviceKey:   serviceKey,
        establishmentKey: establishmentSlug
    };

    // ── 3. Save to the client's per-user ticket list ─────────────────────────
    const clientKey   = `activeTickets_${USER_ID}`;
    const clientTickets = JSON.parse(localStorage.getItem(clientKey) || '[]');
    clientTickets.push(newTicket);
    localStorage.setItem(clientKey, JSON.stringify(clientTickets));
    localStorage.removeItem('activeTicket'); // cleanup legacy key

    // ── 4. Inject into the agent's service-scoped queue ──────────────────────
    const agentQueueKey = `agent_queue_state_${establishmentSlug}`;
    let agentState = JSON.parse(localStorage.getItem(agentQueueKey) || 'null');

    if (!agentState) {
        // First ticket of the day for this service: seed a fresh state
        agentState = {
            currentNum:    0,
            currentTicket: null,
            waitingCount:  0,
            servedCount:   0,
            upcomingData:  []
        };
    }

    // Add queue entry for the agent to call
    agentState.upcomingData.push({
        id:   ticketNumber,
        name: "Client",          // anonymous — no name at booking time
        time: waitTime,
        clientTicketId: newTicket.id
    });
    agentState.waitingCount = agentState.upcomingData.length;

    localStorage.setItem(agentQueueKey, JSON.stringify(agentState));

    // ── 5. Confirm and redirect ───────────────────────────────────────────────
    alert(`✅ Ticket ${ticketNumber} réservé avec succès chez ${agencyName} !\n⏱ Temps estimé : ${waitTime}`);
    window.location.href = '../profilClient/ProfilClient.php';
}
