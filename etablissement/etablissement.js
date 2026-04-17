 //filtre 
 function filterList() {
            let searchInput = document.getElementById('searchInput').value.toLowerCase();
            let locationFilter = document.getElementById('locationFilter').value;
            let cards = document.getElementsByClassName('list-item');

            for (let card of cards) {
                let name = card.getAttribute('data-name').toLowerCase();
                let location = card.getAttribute('data-location');

                let matchesSearch = name.includes(searchInput);
                let matchesLocation = locationFilter === "" || location === locationFilter;

                if (matchesSearch && matchesLocation) {
                    card.style.display = "flex";
                } else {
                    card.style.display = "none";
                }
            }
        }

function confirmBooking(agencyName, locationName) {
    alert("Ticket réservé avec succès chez " + agencyName + " !");
    
    // Create new ticket object with a unique ID
    const newTicket = {
        id: "T-" + Date.now(), // Unique ID based on timestamp
        agency: agencyName,
        location: locationName || "Centre Ville", 
        ticketNumber: "A-" + Math.floor(Math.random() * 90 + 10), 
        waitTime: "~" + Math.floor(Math.random() * 20 + 5) + " min", 
        peopleAhead: Math.floor(Math.random() * 10 + 1)
    };
    
    // Get existing tickets or initialize new array
    let tickets = [];
    const storageKey = `activeTickets_${USER_ID}`;
    const savedTickets = localStorage.getItem(storageKey);
    if (savedTickets) {
        tickets = JSON.parse(savedTickets);
    }
    
    // Add new ticket and save
    tickets.push(newTicket);
    localStorage.setItem(storageKey, JSON.stringify(tickets));


    // Cleanup old single-ticket key if it exists
    localStorage.removeItem('activeTicket');

    // Redirect back to profile page
    window.location.href = '../profilClient/ProfilClient.php';
}
