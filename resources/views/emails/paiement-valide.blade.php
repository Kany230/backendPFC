@component('mail::message')
# Confirmation de paiement

Bonjour {{ $nom }},

Nous vous confirmons que votre paiement a été validé avec succès.

**Détails du paiement :**
- Montant : {{ $montant }} FCFA
- Date : {{ $datePaiement }}
- Méthode : {{ $methodePaiement }}
- Référence : {{ $reference }}

Vous trouverez ci-joint votre quittance de paiement.

Vous pouvez également télécharger votre quittance en cliquant sur le bouton ci-dessous :

@component('mail::button', ['url' => $lienQuittance])
Télécharger la quittance
@endcomponent

Pour toute question, n'hésitez pas à nous contacter.

Cordialement,<br>
L'équipe du CROUS
@endcomponent 