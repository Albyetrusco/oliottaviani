<?php
require 'db.php';

$ordine_id = (int)$_POST['ordine_id'];
$piatto_id = (int)$_POST['piatto_id'];
$variante  = (int)($_POST['variante'] ?? 0);
$extra     = $_POST['extra'] ?? [];

/* prezzo base */
$stmt = $pdo->prepare("SELECT prezzo FROM piatti WHERE id=?");
$stmt->execute([$piatto_id]);
$prezzo = (float)$stmt->fetchColumn();

/* inserisco riga comanda */
$pdo->prepare("
    INSERT INTO ordini_dettagli
    (ordine_id, piatto_id, quantita, prezzo)
    VALUES (?,?,1,?)
")->execute([$ordine_id,$piatto_id,$prezzo]);

$ordine_dettaglio_id = $pdo->lastInsertId();

/* VARIANTE */
if($variante){
    $pdo->prepare("
        INSERT INTO ordini_dettagli_opzioni
        (ordine_dettaglio_id,tipo,riferimento_id)
        VALUES (?,'variante',?)
    ")->execute([$ordine_dettaglio_id,$variante]);
}

/* EXTRA */
foreach($extra as $e){
    $stmt = $pdo->prepare("SELECT prezzo_extra FROM piatti_extra WHERE id=?");
    $stmt->execute([$e]);
    $extra_prezzo = (float)$stmt->fetchColumn();

    $pdo->prepare("
        INSERT INTO ordini_dettagli_opzioni
        (ordine_dettaglio_id,tipo,riferimento_id,prezzo_extra)
        VALUES (?,'extra',?,?)
    ")->execute([$ordine_dettaglio_id,$e,$extra_prezzo]);
}

echo "OK";
