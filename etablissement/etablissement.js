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
    const establishmentSlug = agencyName.toLowerCase().replace(/[^a-z0-9]/g, '_');

    // Send only identity data — the server generates the ticket number,
    // people_ahead (real queue depth) and wait_time (5 min × people_ahead).
    fetch('/Web_Project/api/tickets.php?action=create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
            agency:            agencyName,
            location:          locationName || 'Centre Ville',
            service_key:       serviceKey,
            establishment_key: establishmentSlug
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Erreur serveur');
        return response.json();
    })
    .then(data => {
        console.log('API Response:', data);
        if (data.success) {
            const num   = data.ticket_number;
            const wait  = data.wait_time;
            const ahead = data.people_ahead;
            alert(
                `✅ Ticket ${num} réservé avec succès chez ${agencyName} !\n` +
                `👥 ${ahead} personne${ahead !== 1 ? 's' : ''} avant vous\n` +
                `⏱ Temps estimé : ${wait}`
            );
            setTimeout(() => {
                window.location.href = '../profilClient/ProfilClient.php';
            }, 1000);
        } else {
            alert('❌ Erreur: ' + (data.message || 'Impossible de créer le ticket'));
        }
    })
    .catch(error => {
        console.error('API Error:', error);
        alert('❌ Erreur de connexion.\n\nVérifiez que vous êtes connecté et réessayez.');
    });
}
