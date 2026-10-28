<?php
    // Gestione dei dati inviati dal form
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        header ("location: form.html");
    }

    $nome = isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : 'Utente';
    $peso = isset($_POST['peso']) ? floatval($_POST['peso']) : 0;
    $altezza_cm = isset($_POST['altezza']) ? floatval($_POST['altezza']) : 0;

    $bmi_formatted = "0.00";
    $categoria = "NON VALIDO";
    $class_card = "sottopeso";

    if ($peso > 0 && $altezza_cm > 0) {
        $altezza_m = $altezza_cm / 100;
        $bmi = $peso / ($altezza_m * $altezza_m);
        $bmi_formatted = number_format($bmi, 2, '.', '');

        if ($bmi < 18.5) {
            $categoria = "SOTTOPESO";
            $class_card = "sottopeso";
        } elseif ($bmi <= 24.9) {
            $categoria = "NORMOPESO";
            $class_card = "normopeso";
        } elseif ($bmi <= 29.9) {
            $categoria = "SOVRAPPESO";
            $class_card = "sovrappeso";
        } else {
            $categoria = "OBESITÀ";
            $class_card = "obesita";
        }
    }

    $data = date("d/m/o,H:i:s");
    $ip = $_SERVER["SERVER_ADDR"];

    $record = implode("," , [$data, $ip, $nome, $peso, $altezza_cm, $bmi_formatted, $categoria]).PHP_EOL;
    $nomeFile = "log.csv";

    $file = fopen($nomeFile, "a");

    fwrite($file, $record);
    fclose($file);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Risultato BMI</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="bg-container">
        <h1>Il tuo BMI</h1>
        
        <div class="card-result card <?= $class_card ?>">
            <div class="nome"><?= $nome ?></div>
            <div class="dettagli">Peso <?= $peso ?> kg · Altezza <?= $altezza_cm ?> cm</div>
            <div class="valore-bmi"><?= $bmi_formatted ?></div>
            <div class="stato"><?= $categoria ?></div>
        </div>

        <a href="form.html" class="link-back">Calcola di nuovo</a>
    </div>

</body>
</html>