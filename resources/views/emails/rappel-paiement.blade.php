@component('mail::message')
# Rappel de paiement

Bonjour {{ $nom }},

Nous vous rappelons que vous avez un paiement de **{{ $montant }} FCFA** à effectuer avant le **{{ $dateEcheance }}**.

Pour effectuer votre paiement, veuillez cliquer sur le bouton ci-dessous :

@component('mail::button', ['url' => $lienPaiement])
Effectuer le paiement
@endcomponent

Si vous avez déjà effectué ce paiement, veuillez ne pas tenir compte de ce message.

Pour toute question, n'hésitez pas à nous contacter.

Cordialement,<br>
L'équipe du CROUS
@endcomponent 