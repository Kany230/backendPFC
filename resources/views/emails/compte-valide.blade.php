<!DOCTYPE html>
<html>
<head>
    <title>Compte validé</title>
</head>
<body>
    <h1>Bonjour {{ $user->prenom }} {{ $user->nom }},</h1>
    
    <p>Votre compte sur la plateforme PatrimoineCROUST a été validé par l'administrateur.</p>
    
    <p>Vous pouvez maintenant vous connecter à votre compte en utilisant votre email et votre mot de passe.</p>
    
    <p>Lien de connexion : <a href="{{ config('app.url') }}/login">Se connecter</a></p>
    
    <p>Cordialement,<br>
    L'équipe PatrimoineCROUST</p>
</body>
</html> 