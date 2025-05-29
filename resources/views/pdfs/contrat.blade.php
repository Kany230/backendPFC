<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contrat - {{ $contrat->reference }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .reference {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 5px;
            width: 200px;
        }
        .info-value {
            display: table-cell;
            padding: 5px;
        }
        .signatures {
            margin-top: 50px;
        }
        .signature-block {
            float: left;
            width: 45%;
            margin-right: 5%;
            border-top: 1px solid #000;
            padding-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="CROUS Logo" class="logo">
        <h1>CONTRAT DE LOGEMENT ÉTUDIANT</h1>
        <div class="reference">Référence: {{ $contrat->reference }}</div>
    </div>

    <div class="section">
        <div class="section-title">1. PARTIES CONTRACTANTES</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Le CROUS:</div>
                <div class="info-value">
                    Centre Régional des Œuvres Universitaires et Scolaires<br>
                    Représenté par son Directeur
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">L'étudiant:</div>
                <div class="info-value">
                    {{ $utilisateur->nom }} {{ $utilisateur->prenom }}<br>
                    {{ $utilisateur->email }}<br>
                    Numéro étudiant: {{ $utilisateur->numero_etudiant }}
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">2. OBJET DU CONTRAT</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Type de contrat:</div>
                <div class="info-value">{{ $contrat->type }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Pavillon:</div>
                <div class="info-value">{{ $pavillon->nom }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Chambre:</div>
                <div class="info-value">{{ $chambre->numero }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">3. CONDITIONS FINANCIÈRES</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Montant:</div>
                <div class="info-value">{{ number_format($contrat->montant, 0, ',', ' ') }} FCFA</div>
            </div>
            <div class="info-row">
                <div class="info-label">Fréquence de paiement:</div>
                <div class="info-value">{{ $contrat->frequence_paiement }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">4. DURÉE</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Date de début:</div>
                <div class="info-value">{{ $contrat->dateDebut->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date de fin:</div>
                <div class="info-value">{{ $contrat->dateFin->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">5. CONDITIONS GÉNÉRALES</div>
        <p>Le présent contrat est soumis aux conditions générales d'hébergement du CROUS, dont l'étudiant reconnaît avoir pris connaissance.</p>
    </div>

    <div class="signatures">
        <div class="signature-block">
            Le Directeur du CROUS<br>
            Date et signature
        </div>
        <div class="signature-block">
            L'étudiant<br>
            Date et signature<br>
            (Précédée de la mention "Lu et approuvé")
        </div>
    </div>
</body>
</html> 