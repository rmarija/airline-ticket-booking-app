<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Avio Karta</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; padding: 20px; color: #333; }
        .karta { border: 2px dashed #0056b3; padding: 20px; border-radius: 10px; background-color: #f9f9f9; }
        .header { text-align: center; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        .detalji p { margin: 8px 0; font-size: 16px; }
        .relacija { font-size: 22px; font-weight: bold; text-align: center; margin: 20px 0; color: #0056b3; }
        .footer { margin-top: 30px; font-size: 13px; text-align: center; color: #555; border-top: 1px solid #ccc; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="karta">
        <div class="header">
            <h2>🛫 Vaša Elektronska Karta</h2>
        </div>

        <div class="relacija">
            {{ $let->polaziste }} ✈️ {{ $let->odrediste }}
        </div>
        
        <div class="detalji">
            <p><strong>Ime putnika:</strong> {{ $rezervacija->ime_putnika }}</p>
            <p><strong>Email:</strong> {{ $rezervacija->email }}</p>
            <p><strong>Broj sedišta:</strong> {{ $rezervacija->broj_sedista }}</p>
            <p><strong>Let ID:</strong> {{ $let->id }}</p>
            <hr>
            <p><strong>Cena:</strong> {{ $rezervacija->ukupna_cena }} EUR</p>
        </div>

        <div class="footer">
            <p><strong>Instrukcije za plaćanje i čekiranje:</strong><br> 
            Uplatu možete izvršiti na račun agencije. Molimo Vas da budete na aerodromu najmanje 2 sata pre planiranog leta radi čekiranja i predaje prtljaga.</p>
        </div>
    </div>
</body>
</html>