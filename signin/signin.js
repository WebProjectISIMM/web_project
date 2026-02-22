 function switchForm() {
            const login = document.getElementById('login-form');/*récupère l’élément HTML d’id login-form*/
            const signup = document.getElementById('signup-form');
            /*si le formulaire de login est cache */
            if (login.style.display === 'none') {
                /*on affiche le login*/
                login.style.display = 'block';
                /*et on cache le signup*/
                signup.style.display = 'none';
            } else {
                login.style.display = 'none';
                signup.style.display = 'block';
            }
        }

        function togglePass(id) {
            const input = document.getElementById(id);
            input.type = input.type === "password" ? "text" : "password";
        }