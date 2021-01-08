/*
  Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */


// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

let $ = require('jquery')
require('select2')

$('select').select2()
let $contactButton = $('#contactButton');
$contactButton.click( e=> {
    e.preventDefault();
    $('#contactForm').slideDown();
    $contactButton.slideUp();
})

// Suppression de éléments
document.querySelectorAll('[data-delete]').forEach( a => {
    a.addEventListener('click', e=> {
        e.preventDefault()
        fetch(a.getAttribute('href'),{
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({'_token': a.dataset.token})
        }).then(response => response.json())
            .then(data=> {
                if (data.success){
                    a.parentNode.parentNode.removeChild(a.parentNode)
                }else {
                    alert(data.error())
                }
            })
            .catch(e => alert(e))
    })
});


// start the Stimulus application
import './bootstrap';
