<?php
require_once '../src/model/dbAPI.php';
require_once '../src/model/utils.php';
require_once '../src/model/registrationSelect.php';

$db = new DBAccess();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['nome'], $_POST['cognome'], $_POST['provincia'], $_POST['comune'], $_POST['email'], $_POST['username'], $_POST['password'], $_POST['conferma-password'])) {
        $nome = $_POST['nome'];
        $cognome = $_POST['cognome'];
        $provincia = $_POST['provincia'];
        $comune = $_POST['comune'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password2 = $_POST['conferma-password'];
        $image = getUserImageUrlByEmail($email);
        
        //TODO: MANCANO I CONTROLLI SUI CAMPI
        if($password == $password2) {
            $db->register_user($nome, $cognome, $provincia, $comune, $email, $username, $password, $image);
        }
    }
}


$PAGE_TITLE = "Registrati - BookOverflow";

$template = file_get_contents('./html/templatePage.html');
$header = getHeaderSection();
$registrati = file_get_contents('./html/registrati.html');
$footer = file_get_contents('./html/footer.html');

$provinceList = optionProvince();
$registrati = str_replace('<!-- [province] -->', $provinceList, $registrati);

$page = str_replace('<!-- [pageTitle] -->', $PAGE_TITLE, $template);
$page = str_replace('<!-- [header] -->', $header, $page);
$page = str_replace('<!-- [footer] -->', $footer, $page);
$page = str_replace('<!-- [content] -->', $registrati, $page);
echo $page;

if (isset($_POST['username'], $_POST['password'])) {
    echo "username: " . $_POST['username'] . "<br>";
    echo "password: " . $_POST['password'] . "<br>";
}
