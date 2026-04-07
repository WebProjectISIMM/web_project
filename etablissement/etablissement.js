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
    
    // Save ticket data to localStorage
    const newTicket = {
        agency: agencyName,
        location: locationName || "Centre Ville", // Default if not passed
        ticketNumber: "A-" + Math.floor(Math.random() * 90 + 10), // Random A-10 to A-99
        waitTime: "~" + Math.floor(Math.random() * 20 + 5) + " min", // Random 5 to 25 min
        peopleAhead: Math.floor(Math.random() * 10 + 1) // Random 1 to 10
    };
    
    localStorage.setItem('activeTicket', JSON.stringify(newTicket));

    // Redirect back to profile page as if ticket was added
    window.location.href = '../profilClient/ProfilClient.html';
}
