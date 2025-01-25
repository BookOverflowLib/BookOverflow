<?php
// exception con messaggi personalizzati per ridurre la duplicazione di codice

namespace CustomExceptions;
use Exception;

// consente di fare fare throw solo di exception personalizzate dopo un catch
class CustomException extends Exception {
    public function __construct($message) {
        parent::__construct($message);
    }
}

class UsernameAlreadyExistsException extends CustomException {
    public function __construct() {
        parent::__construct("Errore: username già in uso");
    }
}

class InvalidProvinciaException extends CustomException {
    public function __construct() {
        parent::__construct("Errore: provincia non valida");
    }
}

class InvalidComuneException extends CustomException {
    public function __construct() {
        parent::__construct("Errore: comune non esistente");
    }
}

class EmailAlreadyExistsException extends CustomException {
    public function __construct() {
        parent::__construct("Errore: email già in uso");
    }
}

class IncorrectCredentialsException extends CustomException {
    public function __construct() {
        parent::__construct("Errore: credenziali non corrette");
    }
}

class GenericRegistrationException extends CustomException {
    public function __construct() {
        parent::__construct("Errore durante la registrazione");
    }
}

class GenericCustomException_Alt extends CustomException {
    public function __construct($article, $action) {
        parent::__construct("Errore durante " . $article . $action);
    }
}

class GenericCustomException extends CustomException {
    public function __construct($text) {
        parent::__construct("Errore: " . $text);
    }
}