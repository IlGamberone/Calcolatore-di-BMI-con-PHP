<?php
$mostra_risultato = false;

// Variabili per i messaggi di errore
$errore_nome = "";
$errore_peso = "";
$errore_altezza = "";

// Variabili per il ripopolamento dei campi
$valore_nome = "";
$valore_peso = "";
$valore_altezza = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Recupero e sanitizzazione dei valori per ripopolare il form
    $valore_nome = isset($_POST['nome']) ? trim(htmlspecialchars($_POST['nome'])) : '';
    $valore_peso = isset($_POST['peso']) ? htmlspecialchars($_POST['peso']) : '';
    $valore_altezza = isset($_POST['altezza']) ? htmlspecialchars($_POST['altezza']) : '';

    // 2. Validazione specifica per ciascun campo (senza funzioni dentro empty())
    if ($valore_nome === '') {
        $errore_nome = "Valore non valido";
    }

    $peso = isset($_POST['peso']) ? floatval($_POST['peso']) : 0;
    if ($peso <= 0) {
        $errore_peso = "Valore non valido";
    }

    $altezza_cm = isset($_POST['altezza']) ? floatval($_POST['altezza']) : 0;
    if ($altezza_cm <= 0) {
        $errore_altezza = "Valore non valido";
    }

    // 3. Se NON ci sono errori, procediamo al calcolo e salvataggio
    if ($errore_nome === '' && $errore_peso === '' && $errore_altezza === '') {
        $nome = $valore_nome;
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

        $data = date("d/m/Y, H:i:s");
        $ip = $_SERVER["SERVER_ADDR"];

        $record = implode(",", array($data, $ip, $nome, $peso, $altezza_cm, $bmi_formatted, $categoria)) . PHP_EOL;
        $nomeFile = "log.csv";

        $file = fopen($nomeFile, "a");
        if ($file) {
            fwrite($file, $record);
            fclose($file);
        }

        $mostra_risultato = true;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $mostra_risultato ? 'Risultato BMI' : 'Calcolatore BMI' ?></title>
    <style>
        /* Stili generali di base */
        body {
            background-color: #f1f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        .bg-container {
            background-color: #f1f4f6;
            width: 400px;
            padding: 50px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-sizing: border-box;
        }

        h1 {
            color: #1d232a;
            font-size: 24px;
            margin-bottom: 24px;
            font-weight: 700;
        }

        /* Stili del Form */
        .card-form {
            background-color: #ffffff;
            width: 100%;
            max-width: 320px;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
        }

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-weight: 700;
            font-size: 13px;
            color: #1d232a;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
            color: #1d232a;
            outline: none;
        }

        input:focus {
            border-color: #9ca3af;
        }

        /* Stile per i messaggi di errore */
        .errore-testo {
            color: #dc2626;
            font-size: 12px;
            margin-top: 4px;
            font-weight: 600;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #23272f;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 8px;
        }

        button:hover {
            background-color: #15181c;
        }

        /* Stili del Risultato */
        .card-result {
            width: 100%;
            max-width: 320px;
            padding: 24px 16px;
            border-radius: 12px;
            border-width: 2px;
            border-style: solid;
            text-align: center;
            box-sizing: border-box;
        }

        .card-result .nome {
            font-size: 18px;
            font-weight: 700;
            color: #1d232a;
            margin-bottom: 4px;
        }

        .card-result .dettagli {
            font-size: 13px;
            color: #4b5563;
            margin-bottom: 16px;
        }

        .card-result .valore-bmi {
            font-size: 40px;
            font-weight: 800;
            color: #1d232a;
            line-height: 1;
            margin-bottom: 8px;
        }

        .card-result .stato {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        /* Varianti di colore per le 4 fasce di BMI */
        .card.sottopeso {
            background-color: #E3EEF9;
            border-color: #2F6FB3;
        }
        .card.sottopeso .stato {
            color: #2F6FB3;
        }

        .card.normopeso {
            background-color: #E3F4E6;
            border-color: #256D34;
        }
        .card.normopeso .stato {
            color: #256D34;
        }

        .card.sovrappeso {
            background-color: #FCEBD7;
            border-color: #9A4F06;
        }
        .card.sovrappeso .stato {
            color: #9A4F06;
        }

        .card.obesita {
            background-color: #FBE2E2;
            border-color: #B32D2D;
        }
        .card.obesita .stato {
            color: #B32D2D;
        }

        .link-back {
            margin-top: 24px;
            color: #1d232a;
            font-size: 13px;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="bg-container">
        <?php if ($mostra_risultato): ?>
            <h1>Il tuo BMI</h1>
            
            <div class="card-result card <?= $class_card ?>">
                <div class="nome"><?= $nome ?></div>
                <div class="dettagli">Peso <?= $peso ?> kg · Altezza <?= $altezza_cm ?> cm</div>
                <div class="valore-bmi"><?= $bmi_formatted ?></div>
                <div class="stato"><?= $categoria ?></div>
            </div>

            <a href="index.php" class="link-back">Calcola di nuovo</a>
        <?php else: ?>
            <h1>Calcolatore BMI</h1>
            <div class="card-form">
                <form action="index.php" method="POST" novalidate>
                    <div class="form-group">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" value="<?= $valore_nome ?>" placeholder="Luca">
                        <?php if ($errore_nome !== ''): ?>
                            <div class="errore-testo"><?= $errore_nome ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="peso">Peso (kg)</label>
                        <input type="number" step="0.1" id="peso" name="peso" value="<?= $valore_peso ?>" placeholder="70">
                        <?php if ($errore_peso !== ''): ?>
                            <div class="errore-testo"><?= $errore_peso ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="altezza">Altezza (cm)</label>
                        <input type="number" id="altezza" name="altezza" value="<?= $valore_altezza ?>" placeholder="178">
                        <?php if ($errore_altezza !== ''): ?>
                            <div class="errore-testo"><?= $errore_altezza ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit">Calcola</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>