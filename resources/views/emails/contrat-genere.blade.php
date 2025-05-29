@component('mail::message')
# {{ $sujet }}

Bonjour {{ $nom }},

Votre contrat de logement a été généré avec succès.

**Détails du contrat :**
- Référence : {{ $reference }}
- Pavillon : {{ $pavillon }}
- Chambre : {{ $chambre }}
- Date de début : {{ $dateDebut }}
- Date de fin : {{ $dateFin }}
- Montant : {{ $montant }} FCFA

Vous trouverez ci-joint votre contrat au format PDF. Veuillez l'imprimer en deux exemplaires, les signer et les retourner au bureau du CROUS.

Pour toute question concernant votre contrat, n'hésitez pas à nous contacter.

Cordialement,<br>
L'équipe du CROUS
@endcomponent 