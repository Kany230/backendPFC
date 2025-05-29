@component('mail::message')
# Nouvelle facture

Bonjour {{ $nom }},

Une nouvelle facture a été générée pour votre compte.

**Détails de la facture :**
- Montant : {{ $montant }} FCFA
- Date d'échéance : {{ $dateEcheance }}
- Référence : {{ $reference }}

Vous trouverez ci-joint votre facture au format PDF.

Pour effectuer votre paiement, veuillez cliquer sur le bouton ci-dessous :

@component('mail::button', ['url' => $lienPaiement])
Effectuer le paiement
@endcomponent

Pour toute question concernant cette facture, n'hésitez pas à nous contacter.

Cordialement,<br>
L'équipe du CROUS
@endcomponent 