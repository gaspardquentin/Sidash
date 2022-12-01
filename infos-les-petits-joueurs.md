# Informations NDI 2022 pour l'équipe Les Petits Joueurs

Chef d'équipe : antoinecuinet#4346

En cas de souci, n'hésitez pas à contacter l'équipe de l'OFNI sur le discord avec le tag `@orga_nuit`


## Backend
- Si vous avez un backend node: ne pas changer le port du serveur express de celui qui vous est alloué : `:26862`
- Si vous avez un backend php: c’est bien, moi aussi j’aime bien PHP. J’ai pas d’instructions particulières sinon.

## Déploiement automatique
À chaque _push_ sur GitLab de votre projet, votre code sera mis à jour sur le serveur
Vous pourriez avoir besoin de faire d'autres actions pour que votre projet soit pleinement déployé (installer des dépendances `npm install --production ` ou `composer install --no-dev`, lancer des migrations, que sais-je encore…)
Dans ce cas, personnalisez le script `deploy_script.sh` dans votre dépôt, qui sera exécuté à chaque déploiement _après_ la mise à jour du code
Des notifications Discord seront envoyées au fur et à mesure du déploiement sur le discord de l'OFNI, suivez-les (#🌙-nuit-hooks)!

En complément, vous pouvez installer [GitLab CI/CD](https://docs.gitlab.com/ee/ci/quick_start/#create-a-gitlab-ciyml-file) dans votre dépôt pour avoir de l'intégration continue

## Connexion à la base de données
- Accès PHPMyAdmin : https://nuit-info.ofni.asso.fr/phpmyadmin/
- Utilisateur : les_petits_joueurs
- Mot de passe : 729e0631f6dbe64d993e
- Base de données : les_petits_joueurs
- Petit tip: Il y a un thème sombre activable depuis le menu principal

## Identifiants gitlab
- Adresse du dépôt gitlab : https://gitlab.nuit-info.ofni.asso.fr/ndi-2022/les-petits-joueurs
- À chaque push sur la branch `main` de ce dépôt, votre code sera redéployé sur le serveur
- Pour clôner le dépôt :
   1. (optionnel) `git config --global credential.helper 'cache --timeout=3600'` pour enregistrer le mot de passe pendant une heure
   2. `git clone https://gitlab.nuit-info.ofni.asso.fr/ndi-2022/les-petits-joueurs.git`
### Antoine CUINET
   - Adresse mail : antoine.cuinet@edu.univ-fcomte.fr
   - Nom d'utilisateur : antoine.cuinet
   - Mot de passe : antoi-9ab3cbce1f4c

### Tristan AMIOTTE-SUCHET
   - Adresse mail : tristan.amiotte-suchet@edu.univ-fcomte.fr
   - Nom d'utilisateur : tristan.amiotte-suchet
   - Mot de passe : trist-47d6f0afa41c

### Julie MAGNIN
   - Adresse mail : julie.magnin03@edu.univ-fcomte.fr
   - Nom d'utilisateur : julie.magnin
   - Mot de passe : julie-6a9bb9f6d0cf

### noam FAIVRE
   - Adresse mail : noam.faivre@edu.univ-fcomte.fr
   - Nom d'utilisateur : noam.faivre
   - Mot de passe : noamf-37b3bde59d44

### Tom DENIAU
   - Adresse mail : tom.deniau@edu.univ-fcomte.fr
   - Nom d'utilisateur : tom.deniau
   - Mot de passe : tomde-5c76b495f153

### Raphael TATIN
   - Adresse mail : raphael.tatin@edu.univ-fcomte.fr
   - Nom d'utilisateur : raphael.tatin
   - Mot de passe : rapha-6517743d1875

### Gaspard Quentin
   - Adresse mail : gaspard.quentin@edu.univ-fcomte.fr
   - Nom d'utilisateur : gaspard.quentin
   - Mot de passe : gaspa-82d6fcb41fe3

### Celian BRENIAUX
   - Adresse mail : celain.breniaux@edu.univ-fcomte.fr
   - Nom d'utilisateur : celian.breniaux
   - Mot de passe : celia-986ef7352273

### Nathan Gurgey
   - Adresse mail : nathan.gurgey@edu.univ-fcomte.fr
   - Nom d'utilisateur : nathan.gurgey
   - Mot de passe : natha-39b46d5b76a8

### Ismail Esen
   - Adresse mail : ismail.esen@edu.univ-fcomte.fr
   - Nom d'utilisateur : ismail.esen
   - Mot de passe : ismai-efc71354540a

