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
