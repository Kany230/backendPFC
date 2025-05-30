<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $numero_facture }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        .facture-info {
            margin-bottom: 30px;
        }
        .facture-info table {
            width: 100%;
        }
        .facture-info td {
            padding: 5px;
            vertical-align: top;
        }
        .details {
            margin-bottom: 30px;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
        }
        .details th, .details td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .details th {
            background-color: #f5f5f5;
        }
        .total {
            text-align: right;
            margin-top: 20px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="Logo CROUS" class="logo">
        <h1>FACTURE</h1>
    </div>

    <div class="facture-info">
        <table>
            <tr>
                <td width="50%">
                    <strong>CROUS</strong><br>
                    Adresse du CROUS<br>
                    Téléphone<br>
                    Email
                </td>
                <td width="50%" style="text-align: right;">
                    <strong>Facturé à:</strong><br>
                    {{ $etudiant->nom }} {{ $etudiant->prenom }}<br>
                    Matricule: {{ $etudiant->matricule }}<br>
                    {{ $etudiant->email }}<br>
                    {{ $etudiant->telephone }}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="height: 20px;"></td>
            </tr>
            <tr>
                <td>
                    <strong>N° Facture:</strong> {{ $numero_facture }}<br>
                    <strong>Date:</strong> {{ $date }}
                </td>
                <td style="text-align: right;">
                    <strong>Chambre:</strong> {{ $chambre->nom }}<br>
                    <strong>Bâtiment:</strong> {{ $chambre->batiment->nom }}
                </td>
            </tr>
        </table>
    </div>

    <div class="details">
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Période</th>
                    <th>Montant</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Location Chambre Universitaire</td>
                    <td>{{ $paiement->periode }}</td>
                    <td>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tbody>
        </table>

        <div class="total">
            <p>
                <strong>Total:</strong> {{ number_format($paiement->montant, 0, ',', ' ') }} FCFA
            </p>
        </div>
    </div>

    <div class="footer">
        <p>Merci de votre confiance - CROUS</p>
        <p>Cette facture a été générée automatiquement et ne nécessite pas de signature.</p>
    </div>
</body>
</html> 