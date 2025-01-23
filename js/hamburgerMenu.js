function hamburgerMenu() {
    const hamburger = document.getElementById('hamburger');
    const hamburgerChiuso = document.querySelectorAll('#hamburger > span')[0];
    const hamburgerAperto = document.querySelectorAll('#hamburger > span')[1];


    let menu = document.getElementById('hamburger-menu')
    let header = document.getElementsByClassName('header-container')[0]

    hamburger.addEventListener('click', function () {
        hamburgerChiuso.classList.toggle('active');
        hamburgerAperto.classList.toggle('active');

        hamburger.ariaPressed === "true" ? hamburger.ariaPressed = "false" : hamburger.ariaPressed = "true";

        if (menu.classList.contains('active')) {
            if (header.classList.contains('active') && window.scrollY < 30) {
                header.classList.remove('active')
            }
            menu.classList.remove('active')
            header.classList.remove('hamburger-active')
        } else {
            if (!header.classList.contains('active')) {
                header.classList.add('active')
            }
            menu.classList.add('active')
        }
    })
}

hamburgerMenu();

// let icon = document.getElementById('hamburger-icon')
// let menu = document.getElementById('hamburger-menu')
// let button = document.getElementById('hamburger')
// let header = document.getElementsByClassName('header-container')[0]

// button.addEventListener('click', () => {
//     console.log('click')
//     if (menu.classList.contains('active') && icon.classList.contains('active')) {
//         if(header.classList.contains('active') && window.scrollY < 30) {
//             header.classList.remove('active')
//         }
//         menu.classList.remove('active')
//         icon.classList.remove('active')
//         header.classList.remove('hamburger-active')
//     }else {
//         if (!header.classList.contains('active')) {
//             header.classList.add('active')
//         }
//         menu.classList.add('active')
//         icon.classList.add('active')
//     }
// })


