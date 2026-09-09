<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Veloro AvioKarte - Elektronska karta</title>
    <style>
        @page { margin: 0; }

        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #1e293b;
            font-size: 12px;
        }

        .traka-vrh { height: 8px; background: #e8873a; }

        .zaglavlje {
            background: #1a2744;
            color: #ffffff;
            padding: 22px 32px;
        }
        .logo { font-size: 26px; font-weight: bold; letter-spacing: 1px; }
        .logo-pod { font-size: 11px; color: #a8b6cc; letter-spacing: 2px; }
        .tip-dokumenta { font-size: 11px; color: #a8b6cc; letter-spacing: 2px; text-align: right; }
        .rezervacija-kod { font-size: 18px; font-weight: bold; color: #ffffff; text-align: right; }

        .omot { padding: 28px 32px; }

        .relacija-blok {
            background: #f8fafc;
            border: 1px solid #dbe3ed;
            border-left: 5px solid #e8873a;
            padding: 20px 24px;
            margin-bottom: 24px;
        }
        .grad { font-size: 26px; font-weight: bold; color: #1a2744; }
        .grad-oznaka { font-size: 10px; color: #64748b; letter-spacing: 2px; }
        .strelica { font-size: 22px; color: #e8873a; text-align: center; }

        .naslov-sekcije {
            font-size: 10px;
            letter-spacing: 2px;
            color: #64748b;
            border-bottom: 1px solid #dbe3ed;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }

        table.podaci { width: 100%; border-collapse: collapse; }
        table.podaci td { padding: 7px 0; vertical-align: top; }
        .oznaka { color: #64748b; font-size: 11px; width: 42%; }
        .vrednost { font-weight: bold; color: #1e293b; font-size: 13px; }

        .kutija-sediste {
            background: #1a2744;
            color: #ffffff;
            text-align: center;
            padding: 14px 10px;
        }
        .sediste-broj { font-size: 30px; font-weight: bold; line-height: 1; }
        .sediste-tekst { font-size: 9px; letter-spacing: 2px; color: #a8b6cc; }

        .kutija-cena {
            background: #fdf3ea;
            border: 1px solid #f0c9a3;
            text-align: center;
            padding: 14px 10px;
        }
        .cena-iznos { font-size: 24px; font-weight: bold; color: #e8873a; line-height: 1; }
        .cena-tekst { font-size: 9px; letter-spacing: 2px; color: #a06a3c; }

        .isecak {
            border-top: 2px dashed #cbd5e1;
            margin-top: 26px;
            padding-top: 16px;
        }

        .napomena {
            background: #f8fafc;
            border: 1px solid #dbe3ed;
            padding: 14px 18px;
            font-size: 10px;
            color: #475569;
            line-height: 1.6;
        }

        .podnozje {
            margin-top: 18px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

<div class="traka-vrh"></div>

<div class="zaglavlje">
    <table width="100%">
        <tr>
            <td width="60%">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding-right: 10px; vertical-align: middle;">
                            <svg width="28" height="28" viewBox="0 0 24 24" style="transform: rotate(45deg);">
                                <polygon points="22 2 15 22 11 13 2 9 22 2" fill="#e8873a" />
                            </svg>
                        </td>
                        <td style="vertical-align: middle;">
                            <div class="logo">VELORO</div>
                            <div class="logo-pod">AVIOKARTE</div>
                        </td>
                    </tr>
                </table>
            </td>
                <div class="tip-dokumenta">BROJ REZERVACIJE</div>
                <div class="rezervacija-kod">
                    VLR-{{ str_pad($rezervacija->id, 6, '0', STR_PAD_LEFT) }}
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="omot">

    <div class="relacija-blok">
        <table width="100%">
            <tr>
                <td width="42%">
                    <div class="grad-oznaka">POLAZAK</div>
                    <div class="grad">{{ $let->polaziste }}</div>
                </td>
                <td width="16%" class="strelica">&#10230;</td>
                <td width="42%" style="text-align: right;">
                    <div class="grad-oznaka">DOLAZAK</div>
                    <div class="grad">{{ $let->odrediste }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table width="100%">
        <tr>
            <td width="58%" style="padding-right: 22px;">

                <div class="naslov-sekcije">PODACI O PUTNIKU</div>
                <table class="podaci">
                    <tr>
                        <td class="oznaka">Ime i prezime</td>
                        <td class="vrednost">{{ $rezervacija->ime_putnika }}</td>
                    </tr>
                    <tr>
                        <td class="oznaka">E-mail</td>
                        <td class="vrednost" style="font-size: 11px;">{{ $rezervacija->email }}</td>
                    </tr>
                </table>

                <div class="naslov-sekcije" style="margin-top: 22px;">PODACI O LETU</div>
                <table class="podaci">
                    <tr>
                        <td class="oznaka">Broj leta</td>
                        <td class="vrednost">{{ $let->broj_leta }}</td>
                    </tr>
                    <tr>
                        <td class="oznaka">Datum polaska</td>
                        <td class="vrednost">
                            {{ \Carbon\Carbon::parse($let->vreme_poletanja)->format('d.m.Y.') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="oznaka">Vreme poletanja</td>
                        <td class="vrednost">
                            {{ \Carbon\Carbon::parse($let->vreme_poletanja)->format('H:i') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="oznaka">Vreme sletanja</td>
                        <td class="vrednost">
                            {{ \Carbon\Carbon::parse($let->vreme_sletanja)->format('H:i') }}
                        </td>
                    </tr>
                </table>

            </td>

            <td width="42%" style="vertical-align: top;">
                <div class="kutija-sediste">
                    <div class="sediste-tekst">SEDIŠTE</div>
                    <div class="sediste-broj">{{ $rezervacija->broj_sedista }}</div>
                </div>

                <div class="kutija-cena" style="margin-top: 14px;">
                    <div class="cena-tekst">UKUPNA CENA</div>
                    <div class="cena-iznos">{{ number_format($rezervacija->ukupna_cena, 2) }} &euro;</div>
                </div>

                <table class="podaci" style="margin-top: 16px;">
                    <tr>
                        <td class="oznaka">Klasa</td>
                        <td class="vrednost" style="text-align: right;">EKONOMSKA</td>
                    </tr>
                    <tr>
                        <td class="oznaka">Ukrcavanje</td>
                        <td class="vrednost" style="text-align: right;">
                            {{ \Carbon\Carbon::parse($let->vreme_poletanja)->subMinutes(40)->format('H:i') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="isecak">
        <div class="napomena">
            <strong>Napomena:</strong> Ova karta predstavlja potvrdu Vaše rezervacije.
            Molimo Vas da na aerodrom dođete najkasnije 90 minuta pre poletanja
            i da uz kartu priložite važeći identifikacioni dokument.
            Karta važi isključivo za navedenog putnika i navedeni let.
        </div>

        <div class="podnozje">
            VELORO AVIOKARTE &nbsp;&middot;&nbsp;
            Izdato {{ \Carbon\Carbon::now()->format('d.m.Y. H:i') }} &nbsp;&middot;&nbsp;
            www.veloro-aviokarte.rs
        </div>
    </div>

</div>

</body>
</html>