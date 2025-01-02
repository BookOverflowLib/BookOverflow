let icon = document.getElementById('hamburger-icon')
let menu = document.getElementById('hamburger-menu')
let button = document.getElementById('hamburger')
let header = document.getElementsByClassName('header-container')[0]

button.addEventListener('click', () => {
    console.log('click')
    if (menu.classList.contains('active') && icon.classList.contains('active')) {
        if(header.classList.contains('activeHeader') && window.scrollY < 30) {
            header.classList.remove('activeHeader')
        }
        menu.classList.remove('active')
        icon.classList.remove('active')
        header.classList.remove('hamburger-active')
    }else {
        if (!header.classList.contains('activeHeader')) {
            header.classList.add('activeHeader')
        }
        menu.classList.add('active')
        icon.classList.add('active')
    }
})
