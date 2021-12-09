<!-- 
pas oublier modifier lien de redirection automatique
attendre anti spam
Valid and send email fromwith data from contact form -->
<?php
function cleanData($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}



$errors = array();

if (!empty($_POST)) {
    checkData($_POST);
} else {
    $errors[] = "votre formulaire est vide";
}

function checkdata($dataSendByUser)
{
    /*   print_r($_POST);
    echo ("<br>");*/
    $return = false;
    $verifiedValues = array();

    $humans = cleanData(($dataSendByUser['humans']));
    if (!empty($humans)) {
        $errors[] = "Anti Spam activé";
        $return = false;
    } else $return = true;

    if ($dataSendByUser["chooseContactModeResult"] == "byMail" && $return = true) {
        $verifiedValues["chooseContactModeResult"] = "Par mail";
        $message = cleanData($dataSendByUser["message"]);
        if (strlen($message) <= 10) {
            $errors[] = "($message) votre message n'est pas valide, il doit faire au moins 10 caractères.";
            $return = false;
        } else {
            $return = true;
            $verifiedValues['message'] = $message;
        }
    } elseif ($dataSendByUser["chooseContactModeResult"] == "byPhone" && $return = true) {
        $verifiedValues["chooseContactModeResult"] = "Par telephone";
        $date = cleanData($dataSendByUser["date"]);
        function isValidDate($date, $format = 'Y-m-d')
        {
            $dt = DateTime::createFromFormat($format, $date);
            return $dt && $dt->format($format) === $date;
        }
        $dateTimeUserDate = strtotime($date);
        $actualDate = strtotime(date("Y-m-d"));
        if ($actualDate <= $dateTimeUserDate && isValidDate($date)) {
            $return = true;
            $verifiedValues["date"] = $date;
        } else {
            $errors[] = "($date) la date choisie n'est pas valide, elle ne  doit pas être un dimanche ou inférieure à la date du jour. ";
            $return = false;
        }

        $hour = cleanData($dataSendByUser["hour"]);
        $verifiedValues["hour"] = $hour;
    } else {
        $errors[] = "Choix du mode de contact non valide.";
        $return = false;
    }

    $email = cleanData($dataSendByUser["email"]);
    if (!empty($email) && $return = true) {
        if (!$email == filter_var($email, FILTER_VALIDATE_EMAIL) && !filter_var($email, FILTER_SANITIZE_EMAIL)) {
            $errors[] = "($email) votre adresse email n'est pas valide, elle doit être au format: monnom@mondomaine.extension";
            $return = false;
        } else {
            $return = true;
            $verifiedValues["email"] = $email;
        }
    }

    $phone = cleanData(($dataSendByUser["phone"]));
    if (!empty($phone) && $return = true) {
        if (!preg_match("#(?:(?:\+|00)33[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$#", $phone)) {
            $errors[] = "($phone) votre téléphone est incorrect, votre numéro doit être composé de chiffres, de tirets, d'espaces. Soit au format français ou international.";
            $return = false;
        } else {
            $return = true;
            $verifiedValues["phone"] = $phone;
        }
    }
    if (empty($verifiedValues["phone"]) && empty($verifiedValues["email"])) {
        $errors[] = " Il faut obligatoirement un moyen de contact, soit téléphone soit email.";
    }
    $verifiedValues["prenom"] = cleanData($dataSendByUser["prenom"]);
    $verifiedValues["nom"] = cleanData($dataSendByUser["nom"]);
    $verifiedValues["entreprise"] = cleanData($dataSendByUser["entreprise"]);
    $verifiedValues["typeOfSite"] = $dataSendByUser["typeOfSite"];
    $verifiedValues["necessaryFeatures"] = implode(", ", $dataSendByUser["necessaryFeatures"]);

    if ($return == true && empty($errors)) {
        sendMail($verifiedValues);
    } else {
        array_unshift($errors , "<p>Erreur rencontrée lors de la validation des données transmises par le formulaire.</p><p> Voici les erreurs rencontrées:</p>");
        showResultToUser($errors);
    }
}

function sendMail($dataToSend)
{

    if (!empty($dataToSend["email"])) {
        $nomSender = substr($dataToSend["email"], 0, strpos($dataToSend["email"], "@"));
        $vvvv = $nomSender;
    } else $vvvv = $dataToSend["phone"];

    $headers =
        "From: " . $vvvv . "\r\n" .
        'Cc: nospam@nospam.proxad.net' . "\r\n" .
        "MIME-Version: 1.0\r\n" .
        "Content-Transfer-Encoding: quoted-printable\r\n" .
        "Content-type: text/html; charset=iso-8859-1" . "\r\n";

    $message = "<!DOCTYPE html><html lang='fr'><html><head><meta charset='UTF-8'>";
    $message .= "<p>Message envoye depuis la page de contact de mon site: </p> <p>Envoye par: </p>";
    if (!empty($dataToSend["prenom"])) {
        $message .= "<p>Prenom: " . $dataToSend["prenom"] . "</p>";
    } else $message .= "<p>Pas de prenom </p>";
    if (!empty($dataToSend["nom"])) {
        $message .= "<p>Nom: " . $dataToSend["nom"] . "</p>";
    } else $message .= "<p>Pas de nom de famille </p>";
    if (!empty($dataToSend["entreprise"])) {
        $message .= "<p>entreprise: " . $dataToSend["entreprise"] . "</p>";
    } else $message .= "<p>Pas de d'entreprise </p>";
    $message .= "<p>Moyen de contact choisi: " . $dataToSend["chooseContactModeResult"] . "</p>";
    if (!empty($dataToSend["email"])) {
        $message .= "<p>Email: " . $dataToSend["email"] . "</p>";
    } else $message .= "<p>Pas de mail </p>";
    if (!empty($dataToSend["phone"])) {
        $message .= "<p>Telephone: " . $dataToSend["phone"] . "</p>";
    } else $message .= "<p>Pas de telephone</p>";
    if (!empty($dataToSend["message"])) {
        $message .= "<p>Message: " . $dataToSend["message"] . "</p>";
    } else $message .= "<p>Pas de message</p>";
    if (!empty($dataToSend["date"])) {
        $message .= "<p>Date: " . $dataToSend["date"] . "</p>";
    } else $message .= "<p>Pas de date</p>";
    if (!empty($dataToSend["hour"])) {
        $message .= "<p>Heure: entre " . $dataToSend["hour"] . "h </p>";
    } else $message .= "<p>Pas de d'heure</p>";
    $message .= "<p>Type de site: " . $dataToSend["typeOfSite"] . "</p>";
    $message .= "<p> Fonctionnalites necessaires : " . $dataToSend["necessaryFeatures"] . "</p>";
    $message .= '</body></html>';

    $sendMail = mail("pommine@free.fr", "Envoi depuis la page Contact", $message, $headers);
    if ($sendMail) {
        $return = "<h2>Félicitations</h2><p>Merci pour votre demande de contact, je reviens vers vous le plus rapidement possible!</p>";
        showResultToUser($return);
    } else {
        $return = array();
        array_unshift($return , "<p>Malheureusement, il y a des erreurs lors de l'envoi du formulaire.</p>");
        $return[] = "<p>Merci de réessayer ultérieurement</p>";
        showResultToUser($return);
    }
}


function showResultToUser($messageResult)
{

    if (is_array($messageResult)) {
        $errorsResult = implode("<p>", $messageResult);
        $bootstrapClass = "alert-danger";
    } else $bootstrapClass = "alert-success";
    $html = "<!DOCTYPE html><html lang='fr'><head>";
    $html .= "<meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1'>";
    $html .= "<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css' integrity='sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh' crossorigin='anonymous'>";
    $html .= "</head><body>";
    $html .= "<div class='container' style='display: flex; align-items: center; text-align: center; height: 100%;'>";
    $html .= "<div class='m-4 p-5 rounded' style='background-color: #f2f2f2; width: 100%;'>";
    if (isset($errorsResult)) {
        $html .="<h1>Message non envoyé:</h1>";
    } else  $html .= "<h1>Message envoyé</h1>";
    $html .= "<div class='alert " . $bootstrapClass  . "'>";
    if (isset($errorsResult)) {
        $html .= $errorsResult;
    } else  $html .= $messageResult;
    $html .= "</div>"; 
    $html .= "<p>Redirection automatique vers la page d'accueil dans <span id='counter'>20</span> secondes</p>"; 
    $html .= "<button type='button' id='redirect' onclick=window.location.href='http://localhost/portfoliov2/' class='btn btn-secondary'>Retour à l'accueil</button>"; 
    $html .= "</div></div>"; 
    $html .= "</div>";
    $html .= "<script>var counterElt = document.getElementById('counter');
    function decreaseCounter() {
        var counter = Number(counterElt.textContent);
        if (counter > 1) {
            counterElt.textContent = counter - 1;
        } else {
            clearInterval(intervalId);
            var titre = document.getElementById('redirect');
            titre.click();   
        }
    }
    var intervalId = setInterval(decreaseCounter, 1000);</script>";
    $html .= "</body></html>";
    echo $html;
}


?>