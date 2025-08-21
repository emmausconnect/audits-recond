🍉 v2.3.0 🍉 | 17/08/25

———— Inititalisation PC ————

+ Lors de l'installation Firefox,
  Désactivation des fonctionnalités d'IA de Firefox  et des Smart Tab Groups.

  Les flags mis sur false sont les suivants :
  browser.ml.enable, browser.tabs.groups.smart.enabled,
  browser.tabs.groups.smart.optin, browser.tabs.groups.smart.userEnabled,
  browser.tabs.groups.enabled

———— Audits ————

+ Lors de la demande "Envoi vers le BOLC", le bouton "Envoyer" est
  sélectionné par défaut, plutôt que le bouton "Ne pas envoyer".
  Permettant de valider l'envoi avec la touche "Entrer" directement.

———— Audits PC ————
+ Hotfix à la 4.8.1 du Jean-Jacques pour corriger la catégorie
  maximum pour les CPU avec < 3500 points.


🍉 v2.2.12 🍉 | 23/07/25

———— Inititalisation PC ————

+ Lors de l'installation de Firefox
  Les modifications suivantes sont faites à Microsoft Edge
   - Désactive la demande d'import au premier lancement.
   - Désactive l'icône de Chat Copilot.
   - Désactive les recommendations.
   - Désactive les tabs sponsorisés.
   - Désactive les Microsoft Rewards.

🍉 v2.2.11 🍉 | 04/07/25

———— Audits PC ————

+ Passage à la version 4.8.1 du Jean-Jacques.

+ Lors d'un audit PC, le bouton "J'ai compris,
  continuer tout de même" est sélectionné par défaut.
  permettant de continuer avec la touche "Entrer" plutôt
  que cela ouvre l'URL du téléchargement du JJ.

🍉 v2.2.10 🍉 | 13/06/25

———— Téléchargements (Update, Software...) ————

+ Énième tentative de correction de l'erreur Divid By Zero
  qui fait planter le programme lors d'un téléchargement.
  Reporté par Philippe Ruppli.

🍉 v2.2.9 🍉 | 04/06/25

———— Audits Android / iOS ————

+ Supprime le report du % de batterie résiduel qui était ajouté
  également dans la colonne "Autonomie batterie en minute" du BOLC.
  Reporté par Philippe Ruppli

🍉 v2.2.8 🍉 | 04/06/25

———— Audits Android ————

+ Corrige un problème lors de l'assignation de la RAM avec la méthode de
  récuperation alternative de la RAM. "GB" était ajouté à la mauvaise variable.
  Reporté par Marc Vaneeckhoutte.

🍉 v2.2.7 🍉 | 03/06/25

———— Audits PC & Android & iOS ————

+ Règle un problème pour la hauteur de la
  minifiche "grande" (anciennement horizontal)
  Le QR Code était parfois coupé.

🍉 v2.2.6 🍉 | 29/05/25

[Important]
+ Fix la mise à jour automatique buggée. La 2.2.5 ne pouvait pas se lancer.

🍉 v2.2.5 🍉 | 28/05/25

———— Audits PC ————

+ Ajout de la nouvelle catégorisation • JJ 4.8.0 (juin 2025)
+ Modification du lien pour télécharger la nouvelle version du JJ (4.8.0)
+ Bouton pour faire l'audit basé sur l'ancienne catégorisation (pré juin 2025)
+ Modifications faites à la nanofiche "Horizontal", maintenant appelée "Grande".

———— Audits Android / iOS ————

+ Modifications faites à la nanofiche "Horizontal", maintenant appelée "Grande".

🍉 v2.2.4 🍉 | 27/05/25

———— Audits PC ————

+ Amélioration de la fiche d'audit final pour être similaire aux audits tél.
+ Ajout de la version "Horizontal" (Grande) de la nanofiche pour impression
  sur QL-700.

🍉 v2.2.3 🍉 | 21/05/25

———— Inititalisation PC ————

+ Firefox sera maintenant définit en lecteur PDF par défaut dès l'installation.
+ SumatraPDF n'est plus installable.

+ Libre Office ne sera pas installé si il l'est déjà (par défaut).
  - Il sera affiché en vert et la case sera décochée.
  - Si Libre Office n'est pas installé, il sera affiché en rouge et la case
    sera cochée.

🍉 v2.2.2 🍉 | 08/05/25

———— Audits Android ————

+ Ajout d'un champs pour carte SD
  - Celui-ci ajoutera la taille de la carte SD dans le champs "Stockage"
  de l'audit.
  - Ceci ne change pas le stockage total enregistré dans le BOLC.
  - Inscrire dans le champs Observation manuellement si nécessaire.
  - Ajuster la pondération manuellement pour réfléter le changement.

🍉 v2.2.1 🍉 | 27/04/25

———— Audits iOS ————

+ Amélioration de la stabilité des audits iOS.

🍉 v2.2.0 🍉 | 13/04/25

———— Général ————

+ Mise à jour des liens vers audits.emmaus-connect.org suite à la migration.
+ audits.drop.tf reste disponible en tant que miroir (1 min entre les synchros).

———— Audits ————

+ Migration de l'envoi des audits vers la nouvelle API.
  https://audits.emmaus-connect.org/api/

———— Init PC ————

+ Ajout de Firefox définit en navigateur par défaut.

+ Désactive le démarrage rapide (hibernation) de Windows 10 et 11.

+ Délai d'extinction de l'écran modifié.
  - 1 minute sur batterie.
  - 5 minutes sur secteur.

+ Délai de mise en veille modifié.
  - 5 minutes sur batterie.
  - 15 minutes sur secteur.

+ L'ajout des raccourcis sur le bureau est coché par défaut.
  "Documents", "Ce PC" et "Téléchargements", "Utilisateur".

+ Lors du debloat execute fsutil.exe behavior set disableLastAccess 1 pour
  désactiver la mise à jour de la date d'accès des fichiers.
  Cela permet de réduire l'usure des disques SSD et améliore les performances.
  https://learn.microsoft.com/fr-FR/windows-server/administration/windows-commands/fsutil-behavior

+ Tentative de correction (Encore...) d'un bug lors du téléchargement.
  "Divide by zero" qui apparaît quand la connexion est instable.

🍉 v2.1.2 🍉 | 03/04/25

———— Init PC ————

+ VC Redist est maintenant installé par défaut lors de l'installation
de Libre Office. Ceci devrait corriger l'erreur de DLL manquante.

+ Win11 : Rajoute l'option Désepingler Store / Courrier / Edge de la taskbar
  dans la section "Scripts Activés", au lieu qu'il soit executé de force.
  Pour le moment, sur Windows 10, le script s'execute obligatoirement.

———— Audits PC ————

+ Update de l'URL de téléchargement de l'outil de Jean-Jacques qui ajoute la
  région de ROUBAIX. Aucune modification n'est apporté. C'est tjrs considéré
  comme la version 4.7.7.

🍉 v2.1.1 🍉 | 08/03/25

———— Général ————

+ Sur proposition d'Agnès Souque, la mise à jour semi-automatique d'EmCoTech
  sera maintenant placé dans le même dossier que l'ancienne version plutôt
  que sur le bureau.

———— Audits téléphones ————

+ Sur proposition d'Agnès Souque, les audits sont maintenant placés
  dans un dossier "Audits Android" où "Audits iOS" sur le bureau.

———— Audits Android ————

+ Un message bleu signalant des problèmes connus avec Windows Update a fait son
  apparition sur la fenêtre d'audit Android.

+ Une vérification de la connexion avec l'appareil Android sera faite plusieurs
  fois durant l'audit. En cas de problème, l'audit sera immédiatement arrêté.
  En effet, il se peut que la connexion soit perdue sans que l'audit s'arrête,
  ce qui causait des audits complètement vides.

———— Init PC ————

+ Le bouton "Nettoyage de fin d'audit" supprimera dorénavant les fichiers
  EmCoTech.exe du bureau.

                           🍉 v2.1.0 🍉 | 04/03/25

———— Parcours ————

+ Modernisation du fond d'écran Emmaüs Connect.
  https://audits.emmaus-connect.org/emmaus_wallpaper.png

———— Audits téléphones ————

+ Ajout d'un nouveau format pour la Nanofiche : Horizontal.
  Celle-ci permet une meilleure visibilité, elle propose une police d'écriture
  plus grande et plus de place pour les éventuels commentaires ajoutés pendant
  l'audit. Doit obligatoirement être imprimée en mode 29*90mm et non en 29*42mm.

+ Refonte graphique de l'audit HTML, plus clair et plus lisible.

+ Le lien vers le pilote de la QL-700 sur la fiche HTML a été corrigé.

                           🍉 v2.0.17 🍉 | 27/02/25

———— Audits ————

+ Ajout du support de ROUBAIX.

                           ❤️ v2.0.16 ❤️ | 14/02/25

———— Initialisation PC ————

+ Passe à la version stable de LibreOffice, en esperant que cela corrige le
  bug de DLL manquante sous Windows 10.

    🍉 v2.0.15 🍉

Date: 02/02/25 en stable

———— Initialisation PC ————

+ Correction du lien téléchargement de VLC

    🍉 v2.0.14 🍉

Date: 24/01/25 en stable

———— Audits ————

+ Ajout du numéro de don sur le QR Code.

    🍉 v2.0.13 🍉

Date: 13/01/25 en stable

———— Audits PC ————

+ Corrige un bug dans mon implémentation du JJ lors de l'affichage des prix
  EC après pondération du PC.

    🍉 v2.0.12 🍉

Date: 20/12/24 en stable

———— Audits (Tout) ————

+ Vérfication plus stricte des formats d'ID EC.

———— Audits iOS ————

+ Corrige le matching Kimovil avec les iPad de 5e Génération.
+ Corrige le matching Kimovil pour les iPhone SE (2016).

    🍉 v2.0.11 🍉

Date: 13/12/24 en stable

———— Audits PC ————

+ Modification du lien vers la 4.7.7 du JJ.

    🍉 v2.0.10 🍉

Date: 09/12/24 en stable

———— Audits Android / PC ————

+ Ajout Don ID sur le CSV via le bouton 'Extraction BOLC'.&

    🍉 v2.0.9 🍉

Date: 24/11/24 en stable

———— Audits ————

+ Ajout du nom du CSV envoyé sur le BOLC dans le fichier HTML pour permettre
  de retrouver quel appareil a posé problème dans les logs d'imports du BOLC.

    🍉 v2.0.8 🍉

Date: 20/11/24 en stable

———— Général ————

+ Lors de la mise à jour d'EmCoTech, le nouveau programme (placé sur le bureau)
  sera lancé sans interaction, et la version actuelle, automatiquement fermée.

+ Un aperçu du changelog sera affiché directement lors du popup de mise à jour.

+ Ajoute le support de La Villette dans les audits.
+ Corrige un problème avec le format des QR Code pour la vente via douchette.

    🍉 v2.0.7 🍉

Date: 19/11/24 en stable

———— Audit PC ————

+ Ajout du QR Code pour la vente via douchette.

———— Audit Android / iOS ————

+ Ajout du QR Code pour la vente via douchette.
+ Corrige une erreur, dans cas particulier quand un nom commercial
  n'est pas trouvé (Reporté par Marc Vaneeckhoutte).
+ Amélioration du matching Kimovil pour les appareils Huawei.

    🍉 v2.0.6 🍉

Date: 10/11/24 en stable

———— Audit PC ————

+ Update du lien de téléchargement de l'outil de Jean-Jacques
  en 4.7.6 du 10/11/24.

    🍉 v2.0.5 🍉

Date: 30/09/24 en stable

———— Audit PC ————

+ Ajout d'un message de warning indiquant l'utilisation préférable de l'outil de
  Jean-Jacques pour les Audits PC lors du clique sur "Démarrer l'audit".

+ Tentative d'amélioration de la récupération CPU (erreur Internet Explorer).

———— Audit Android, iOS, PC ————

+ La minifiche a maintenant une police d'écriture plus réduite (11 à 9).
  Elle est toujours correctement lisible après impression sur QL-700.

    🍉 v2.0.4 🍉

Date: 17/09/24 en stable

———— Audit Android & Audit PC ————

+ Gestion beaucoup plus robuste de l'envoi vers audits.drop.tf.
  > vérification de la réponse HTTP
  > affichage du message d'erreur complet
  > propose le renvoi de l'audit en cas d'erreur de dépôt

    🍉 v2.0.3 🍉

Date: 13/09/24 en stable

———— Audit Android ————

+ Correction d'un bogue reporté par Marc Vaneeckhoutte

    🍉 v2.0.2 🍉

Date: 01/09/24 en stable

———— Audit PC ————

+ Ajoute les instructions pour l'impression de la minifiche.
+ Ajoute les liens vers la QL-700 ainsi que le papier imprimable.
+ Retire "OBS:" de la minifiche.

———— Audit iOS ————

+ Amélioration de la vérification de la présence de l'outil iOS.

    🍉 v2.0.1 🍉

Date: 05/07/24 en stable

———— Audit PC ————

+ Grande amélioration de la rapidité d'execution du script.
+ Correction de la liste déroulante des statuts existants.

———— Audit iOS ————

+ Correction de la liste déroulante des statuts existants.
+ Amélioration du matching iPad

———— Audit iOS & Android ————

+ Correction de la liste déroulante des statuts existants.
+ Précise "Pas d'antenne réseau" quand il n'y a pas d'IMEI de trouvé, dans la
  fenêtre Observations et pondération, ainsi que dans fichier d'audit final
  dans la section IMEI.

    🍉 v2.0.0 🍉

Date: 26/07/24 en stable

———— Général ————

+ Ajout des audits pour iOS (iPhone et iPad) !

+ Ajout d'une détection qui corrigera un identifiant mal formé.
  Par exemple si une tablette est entrée avec un ID "STSM24-0000",
  un popup s'affichera vous informant du changement automatique vers STTA24-0000.

  Si vous estimez qu'il s'agit d'une erreur, l'ID est toujours changeable
  dans la fenêtre "Observations et pondérations".

———— Audit iOS ————

+ Il y aura sûrement des bugs, j'attends vos retours !
+ Case cochée par défaut "Éteindre automatiquement l'appareil à la fin de l'audit"

———— Initialisation PC ————

+ Le nettoyage post-audit dans l'onglet "Initialisation PC" supprimera également
  tous les fichiers .html et .png, ainsi que les raccourcis vers VLC, SumatraPDF
  et Microsoft Edge du bureau.

    🍉 v1.9.8 🍉

Date: 19/07/24 en stable

———— Audit Android ————

+ Corrige l'envoi automatique vers le BOLC
+ Ajoute un champ "Bénévole"
+ Adaptation aux nouvelles colonnes BOLC
+ Changement de MARAUDE pour INVENDABLE

———— Audit PC ————

+ Corrige l'envoi automatique vers le BOLC
+ Lien vers la version 4.7.3 du JJ

———— Initialisation PC ————

+ Chrome est maintenant coché par défaut dans les logiciels à désinstaller.
+ Ajout d'informations au survol de certains élèments, signalé par un petit (i).
+ Correction du téléchargement de VLC.
+ Lors du clique sur "Nettoyage post audit", cela supprime maintenant aussi
  les raccourcis vers VLC et SumatraPDF qui sont sur le bureau.
+ Le bouton Nettoyage post audit est maintenant sur la page initialisation.

    🍉 v1.9.7 🍉

Date: 12/07/24 en stable

———— Initialisation PC ————

+ Sur Windows 11, ajout d'une option pour épingler la barre des tâche à gauche
  plutôt qu'au centre.

    🍉 v1.9.6 🍉

Date: 04/07/24 en stable

———— Initialisation PC ————

+ Sur Windows 10, le dossier Musique ne sera plus caché dans l'explorateur après
  le lancement du script d'initialisation "Win10Debloat".

+ Les apps Microsoft Store et Courrier et Edge seront automatiquement
  désépinglés de la barre des tâches.

———— AUDITS.DROP.TF ————

+ Amélioration de l'affichage des audits.
+ Affiche le nombre d'audits pour la région.
+ Possibilité de trier par date où nom en cliquant dans l'entête du tableau.
+ Les liens seront maintenant ouvert dans un nouvel onglet.
+ Format de date plus lisible.
+ Ajout d'une colonne type d'appareil.

———— Audit PC ————

+ Corrige la récupération de l'indice CPU lors de l'audit PC.

+ Amélioration du format de vérification de l'ID.

+ L'envoi vers audits.drop.tf est plus robuste. Si l'envoi ne fonctionne pas,
  une fenêtre s'ouvrira pour proposer de retenter celui-ci.

———— Audit Android ————

+ Amélioration du format de vérification de l'ID.

+ L'envoi vers audits.drop.tf est plus robuste. Si l'envoi ne fonctionne pas,
  une fenêtre s'ouvrira pour proposer de retenter celui-ci.

    🍉 v1.9.5 🍉

Date: 25/06/24 en stable

———— Audit PC ————

+ Ajout de la version d'EmCoTech en plus de la version du JJ dans l'entête
  html du fichier d'audit PC.

———— Initialisation PC ————

+ Suppression de Chrome de la liste des apps installable.
  Acté par la réunion des référents du mercredi 26/06/2024.

+ Ajout d'un bouton de "Nettoyage de fin d'audit", qui aura comme action de vider
  la corbeille et le dossier téléchargement ainsi que d'oublier
  le réseau Wi-Fi actuellement connecté.
  Ce bouton est pour le moment dans l'onglet "Audit PC" par manque de place.

    🍉 v1.9.4 🍉

Date: 25/06/24 en stable

———— Initialisation PC ————

+ Proposition d'Agnès Souque : Ajout d'une option pour ajouter les raccourcis
  "Documents", "Ce PC" et "Téléchargements" sur le bureau.
  Cette option est décoché par défaut.

———— Audit Android ————

+ Hotifx : Corrige un bug empêchant les audits des téléphones Android.

    🍉 v1.9.3 🍉

Date: 17/06/24 en stable

———— Initialisation PC ————

+ Corrige un bug dans le téléchargement de Libre Office.

+ Désinstalltion (encore) plus robuste de OneDrive sur Windows 11.

+ Firefox a maintenant uBlock Origin automatiquement installé lors de son
  installation, et Firefox aura toutes les options de télémétries et de vie privée
  désactivés par défaut. Les liens sponsorisés sont aussi désactivés par défaut.

+ Firefox ne se démarrera donc plus automatiquement, car cela n'est plus
  nécessaire.

+ Chrome et Edge auront maintenant uBlock Origin Lite de pré-installé au lieu
  de uBlock Origin, suite à la fin du support du manifest v2 pour ces deux
  navigateurs.

+ Chrome et Edge seront lancés automatiquement et il faudra valider à la main
  l'ajout de uBlock Origin Lite en haut à droite. Ceci permet d'autoriser la
  suppression de l'extension par l'utilisateur, ce qui n'était pas possible
  auparavant.


———— Audit Android & Audit PC ————

+ Ajoute la possibilité d'ajouter un numéro de don lors de la saisie de l'ID EC.
  Ceci permet d'avoir le lien fait automatiquement entre un don du BOLC et
  le matériel, sans avoir du au préalable ajouter cet ID EC dans le
  don.

+ Les audits sont maintenant automatiquement envoyés sur audits.drop.tf pour
  faciliter la centralisation des audits par région. Pour le moment il n'y
  a aucun moyen de supprimer des audits.
  Si un audit est fait deux fois avec le même ID, il supprimera l'ancien.

———— Audit Android ————

+ Ajout d'une case à cocher pour forcer la catégorie en MARAUDE.

    🍉 v1.9.2 🍉

Date: 06/06/24 en stable

———— Audit Android ————

+ Les téléphones du constructeur "TCL" seront maintenant mis dans le BOLC sans
  valeur dans le champs constructeur. Le constructeur "AUTRE" ne semblant pas
  être importable dans le BOLC bien que ce constructeur existe bel et bien.

    🍉 v1.9.1 🍉

Date: 05/06/24 en stable

———— Initialisation PC ————

+ Retire --disable-interactivity à la
  commande winget de désinstallation de OneDrive (Windows 11 uniquement).
  Ceci devrait *enfin* désinstaller correctement OneDrive. Hein Anakin, Hein ?

———— Audit Android ————

+ Bug reporté par Emmanuel HAUGAZEAU : Impossible d'ajouter les téléphones avec
  le constructeur "TCL" sur le BOLC.
  Les téléphones du constructeur "TCL" seront maintenant mis dans le BOLC avec
  le constructeur "Autre" et TCL sera ajouté au nom du modèle pour
  palier à ce problème.

        v1.9.0

Date: 01/06/24 en stable

———— Général ————

+ Bug reporté par Philippe Ruppli : Une erreur s'affichait si aucun driver où
  périphérique audio n'était présent, une vérification est maintenant faite.

———— Audit PC ————

+ **Ajout de l'envoi automatique vers le BOLC.**
+ Amélioration visuelle de l'interface de mi-audit.
+ Ajout du champs "Bénévole en charge du reconditionnement".
+ Ajout du champs "Statut materiel en PA".
+ Ajout du champs "Bénévole en charge du reconditionnement".

———— Audit Android ————

+ Dans la fenêtre "Observations et pondération", ajout d'un lien vers Kimovil
  pour le téléphone en cours de traitement.
+ Dans la fenêtre d'envoi vers le BOLC, l'ID unique de l'appareil est affiché.
+ Ajout de Lyon et Créteil dans dans la liste des participants à la bêta de
  l'envoi automatique vers le BOLC.

        v1.8.16

Date: 24/05/24 en stable

———— Audit PC ————

+ Corrige un bogue qui ne prenait pas en compte la catégorie pondérée sur les
  différents support d'export (Copie pour BOLC, Minifiche, QR Code).

———— Initialisation PC ————

+ Changement du miroir utilisé pour le téléchargement de libre office, en
  espérant que cela permettera un téléchargement plus rapide.

        v1.8.15
Date: 30/04/24 en stable

———— Audit PC ————

+ L'ouverture de la mini-fiche ouvre maintenant directement
  la boîte de dialogue d'impression.

———— Audit Android ————

+ Correction de l'ajout erroné du caractère "r" sur la catégorie lors de la Copie
  pour Sheets.

        v1.8.14
Date: 26/04/24 en stable

———— Initialisation PC ————

+ Ajout des paramètres --accept-source-agreements --disable-interactivity à la
  commande winget de désinstallation de OneDrive (Windows 11 uniquement).
  Ceci devrait améliorer le déroulement automatique du processus
  pour qu'il ne soit plus bloqué.

———— Audit Android ————

+ (Strasbourg) Envoi automatique de l'audit html vers le serveur de partage.
+ Ajout de Saint-Denis, Marseille et Grenoble dans la liste des participants
  à la bêta de l'envoi automatique vers le BOLC.
+ Ajout des conseils pour l'impression de la nano-fiche.
+ Suppression des autres types de mini-fiche.
+ L'ouverture de la nano-fiche ouvre maintenant la boîte d'impression.
+ Modification de l'emplacement des boutons, et textes d'explications revus.

        v1.8.12
Date: 25/03/24 en stable

———— Audit Android ————

+ Les téléphones en CAT Forcée MARAUDE étaient reportés en tant CAT HC sur le BOLC
  Le problème a été réglé partiellement en le mettant plutôt en INVENDABLE dans le
  BOLC, avec une mention "CATEGORIE REEL: MARAUDE" dans le "Commentaire Statut",
  En attendant la mise à jour du BOLC avec le support de la catégorie "MARAUDE".

+ Ajout de Lille dans la liste des participants à la bêta de
  l'envoi automatique vers le BOLC.

        v1.8.11
Date: 18/03/24 en stable

———— Général ————

+ Suite à des blocages avec la désinstallation OneDrive, la fenêtre console
  s'affichera pendant la désinstallation de celui-ci, ce qui permettra de passer
  cette étape si bloquée, en fermant la fenêtre de console,
  plutôt que de redémarrer tout le script.

———— Audit Android ————

+ Ajout de Maison-Blanche dans la liste des participants à la bêta de
  l'envoi automatique vers le BOLC.

        v1.8.10
Date: 16/03/24 en stable

———— Audit Android ————

+ Envoi automatique vers le BOLC en bêta pour la région de Strasbourg.
  Une fenêtre vous demandant si vous souhaitez envoyer l'audit vers le BOLC
  apparaît après la fenêtre Observations & pondérations.

  >>> Si votre région souhaite être ajoutée dès la prochaine version, envoyez
      moi un email dès maintenant à jschroeder@emmaus-connect.org

+ La détection du stockage fonctionne maintenant sur les Galaxy S4 sous Android 5.
+ La détection l'IMEI fonctionne sur les Galaxy S4 sous Android 5.
  ---
  La fenêtre IMEI s'affichera automatiquement sur Galaxy S4.
  Le script lira le contenu de l'écran, et donc l'IMEI.
  ---
  Il faut que l'écran soit bien éteint et qu'il n'y est pas de mot de passe,
  et que le téléphone se dévérouille avec un swipe-to-unlock pour que
  cela fonctionne. Ce sont normalement les paramètres par défaut.

        v1.8.9
Date: 06/03/24 en stable

———— Audit PC ————

+ Lien mise à jour vers la version 4.6.8.1 du Jean-Jacques

———— Audit Android ————

+ Mise à jour du QR Code utilisé sur la nano-fiche : il est plus compact.
+ Remplace les occurences du mot "Fabricant" par "Constructeur" pour être
  en parité avec le nom utilisé dans le BOLC.

        v1.8.8
Date: 24/02/24 en stable

———— Général ————

+ Corrige un bug introduit dans la 1.8.7 qui empêchait la recherche de
  mise à jour.

———— Audit Android ————

+ Mise à jour de la base de données Kimovil.
+ Corrige le matching des Galaxy S4.
+ Corrige le matching des Altice S43.
+ Corrige un problème d'affichage de la batterie résiduelle qui affichait parfois
  1000% au lieu de 100%.

        v1.8.7
Date: 21/02/24 en stable

———— Audit Android ————

+ Les exports CSV / Copie pour Sheets ne contiennent plus les mentions de taille
  GB, MB, ... pour être en parité avec l'outil d'audit des PC.
+ Ajout d'un nano-fiche encore plus petite.
+ La fenêtre Observations et pondération est maintenant fermable pour interrompre
  un audit android immédiatement sans avoir a le valider obligatoirement.
+ Améliore compatibilité avec le BOLC des téls Altice.

        v1.8.6
Date: 18/02/24 en stable

———— Audit Android ————

+ Corrige un bug lors du calcul automatique de la RAM.
+ Kimovil s'ouvre maintenant dans le navigateur par défaut et non dans edge.
+ Amélioration du matching Kimovil avec les smartphones Samsung.

        v1.8.5
Date: 16/02/24 en stable

+ Sortie de la 1.8.5 en stable.

Les versions 1.8.0 à 1.8.4 étaient toutes en bêta et n'étaient jamais disponibles
en version stable.

        v1.8.5
Date: 15/02/24 en bêta

———— Audit Android ————

+ Amélioration du matching Kimovil.
+ Fix d'un bug qui faisait que le nom de Modèle était vide dans l'export
  quand le champ "nom commercial" était vide.
+ Dans la fenêtre de choix de l'identifiant EC, affiche maintenant
  plusieurs exemples de formats pour faciliter la compréhension de la syntaxe.
+ Fix d'un bug qui forcait a rentrer manuellement le score AnTuTu a chaque audit.
+ S'il n'y a pas d'observation, la mention OBS. est supprimée de la minifiche.
+ Ajout d'une "macro-fiche" en test, qui prend moins de place.

    v1.8.2, 1.8.3
Date: 11/02/24 en bêta

———— Général ————

+ Un bouton "Revenir à l'accueil" a été ajouté à toutes les pages.
+ Les boutons "Changelog" et "À propos" sont affichés seulement sur la page
    d'accueil du programme.

Date: 13/02/24 en bêta

———— Audit Android ————

+ Assouplissement des règles pour le format de l'identifiant EC.
  - Autorise 4 où 5 chiffres pour la partie après le tiret.
  - Autorise à la place de SM pour "SMARTPHONE", aussi "TA" pour tablette et
    "TE" pour téléphone.
  - Basé sur le fichier : Identification unique d'un équipement v1 :
  https://docs.google.com/document/d/1Cqlau1MkQy01_E4FfXvGhC6M3c6a9kUHktv_pHbmoes

+ L'Identifiant EC est maintenant automatiquement passé en majuscule.

        v1.8.1
Date: 11/02/24 en bêta

———— Audit Android ————

+ Il est maintenant possible de bypass les règles de format pour l'Identifiant EC
  en ajoutant "test" n'importe où dans l'identifiant. Ceci permet de faire des
  tests rapides si nécessaire.

+ Quand aucun IMEI n'est trouvé et que l'appareil n'a pas d'IMEI, car il n'a pas
  de slot de carte SIM, rajoute une précision sur la possibilité de
  simplement sur "OK" lors de la demande d'entrée manuelle d'un IMEI.

+ Suppression de la section Infos SoC (System on Chip)
  Le modèle de SoC (CPU) est maintenant mis dans la partie Infos Système

+ Rajout de "Numéro de Build" dans les étapes de l'audit Android.
  Proposition de @Charles Marvin

+ Dans l'audit final, précision sur la localisation du QR Code dans le téléphone
  qui est dans "Gestionnaire de fichiers > Stockage > Pictures" et non dans
  la galerie comme l'on pourrait le penser.
  Proposition de @Charles Marvin

        v1.8.0
Date: 08/02/24 en bêta

———— Initialisation PC ————

+ Nouvelle section : Logiciels à désinstaller !
  Vous pouvez maintenant facilement désinstaller les logiciels suivants :
  TeamViewer, Acrobat Reader, Thunderbird, Chrome, Gimp

+ Tentative de correction d'un bug lors du téléchargement.
  "Divide by zero" qui apparaît quand la connexion est instable.

———— Audit PC ————

+ Affiche un message rouge indiquant que cet outil est réservé pour Strasbourg.

+ Un lien direct pour télécharger la dernière version du vrai JJ est ajoutée.
  Actuellement, c'est la version 4.6.7

———— Audit Android ————

+ Détection automatique du type d'appareil (tablette où smartphone)
  Ne fonctionne que si un matching Kimovil est trouvé.
  Autrement, ne pas oublier de modifier manuellement le type de l'appareil
  dans la fenêtre Observations et pondération.

+ Ajout d'une case à cocher, permettant de forcer l'entrée d'un
  score AnTuTu à la main. Ceci se fait avant le démarrage de l'audit.

+ Réécriture complète de la fenêtre d'entrée de l'identifiant EC.
  - Il y aura une vérification stricte du format de l'ID EC.
    Ceci pour éviter au maximum les erreurs de frappe.
    Le format devra respecter la charte suivante : **SM2X-XXXX
    Où les X sont obligatoirement des chiffres et les * des lettres.
    - En cas d'erreur, un rappel du format sera inscrit
      dans une notification rouge bas à droite.
    - SM (Smartphone) peut aussi être TA pour TABLETTE où TE pour téléphone.

+ Réécriture complète de la fenêtre d'entrée manuelle du score AnTuTu.
  - Quand aucun score AnTuTu n'est trouvé, ouvre Kimovil.com automatiquement.
  - La fenêtre de saisie manuelle du score AnTuTu reste maintenant
    toujours au-dessus de toutes les autres fenêtres.

+ Dans fenêtre Observations et pondération :
  - Elle reste maintenant toujours au-dessus de toutes les autres fenêtres.
  - Suppression du champ "Product"
  - Ajout d'un champ "Nom co." (Nom commercial) qui sera rempli automatiquement
    si possible. Ce champ est éditable.
      - Ce nom commercial est ajouté au nom de modèle dans la colonne "Modèle".
        Ceci permet d'avoir le nom de modèle "technique" et "commercial"
        au même endroit dans le BOLC.

+ Dans l'export HTML de l'audit :
  - Affiche des infos utiles dans le fichier HTML finale de l'audit
    pour mieux comprendre les différentes fonctions et boutons.
  - Ajout d'un bouton "Voir le changelog" en petit, à côté du numéro de version,
    dans l'entête de l'audit. Il redirige vers https://update.drop.tf/changelog
  - Ajout d'un rappel du format des colonnes et explique la différence
    entre le format du bouton "Extraire pour BOLC" et "Copier pour Sheets".
  - Le QR Code est maintenant masqué par défaut dans l'audit HTML.

+ Suppression de l'utilisation de Gadgetversus.com

+ Supprime de l'audit android, la partie cpubenchmark.com
  et androidbenchmark.com, qui n'étaient déjà plus utilisées depuis longtemps.
  Il ne reste donc plus que le matching Kimovil pour AnTuTu.

+ Correction d'un bug qui empêchait le match d'une tablette
  avec la base de données Kimovil.

        v1.7.9
Date: 25/01/24 en stable

———— Audit Android ————

+ Fix d'un bug pour les audits : lorsque les observations contenaient un
  retour à la ligne, cela cassait le format excel / sheets.
  Les retours à la ligne dans le bloc "Observations" sont maintenant remplacés
  par des tirets lors de la copie où extraction pour le BOLC.

        v1.7.8
Date: 15/01/24 en stable

———— Général ————

+ Ajout de l'historique des versions EmCoTech, en cas de problème, revenez
  sur une ancienne version ! Lien cliquable facilement depuis l'onglet
  "Changelog" depuis la nouvelle version.

  Autrement, vous pouvez écrire manuellement sur :
  https://update.drop.tf/versions

———— Audit Android ————

+ Lors d'un audit, quand une catégorisation est forcée, car pas assez de RAM où
  de Stockage, un message le précise maintenant dès la fenêtre
  "Observations et pondération", et pas uniquement sur l'audit HTML final.

  Pour passer outre une catégorisation forcée, il suffit d'appliquer
  une pondération.

+ La première colonne ("Id matériel reconditionneur") sera maintenant vide
  lors de la copie / extraction pour BOLC.
  Plutôt que d'avoir le même identifiant EC écrit deux fois.

  Lors de la copie, où extraction pour BOLC, la colonne existe
  tout de même mais sera vide.

+ Fix d'un bug lors de la création d'un audit android manuel,
  l'outil ne demandait pas la valeur AnTuTu.
  Bug apparu avec l'intégration du score AnTuTu Kimovil.

+ Fix d'un bug dans la logique qui faisait apparaître un téléphone comme HC et
  MARAUDE en même temps dans l'audit final quand la catégorisation était forcée.

+ Fix d'un bug qui affichait le mauvais prix lors de la catégorisation forcée.

        v1.7.7
Date: 14/01/24 en stable

———— Système de Mise à Jour ————

+ Amélioration du système de mise à jour, qui affiche une fenêtre à la fin
  du téléchargement de celle-ci, un simple clique fermera le programme
  immédiatement, car dans tous les cas, il faut obligatoirement fermer
  le programme avant de lancer une nouvelle version.

  Vous n'aurez plus qu'à lancer le nouvel exécutable qui sera sur votre
  bureau.

———— Initialisation PC ————

+ Pour Chrome et Edge, l'extension uBlock Origin s'installe automatiquement au
  lieu d'ouvrir une page web pour l'installation.

  Ceci rend l'extension comme "installée par votre organisation", et n'est pas
  supprimable par l'utilisateur. Je ne suis pas fan de cette solution, mais
  au vue de l'état actuel d'internet il me semble indispensable d'avoir
  uBlock Origin. N'hésitez pas a me contacter si vous avez une solution qui
  permet tout de même la désinstallation par l'utilisateur, où autre requête !

  Pour Firefox, la page web s'ouvre toujours et il faut installer manuellement.

———— Audit PC ————

+ Quand l'on clique sur "Lancer l'audit PC" depuis la page d'accueil, en plus de
  changer de tab, l'audit se lance maintenant directement.

———— Audit Android ————

+ Le calcul "virtuel" d'un faux indice AnTuTu via multiplication d'un score
  PassMark est maintenant totalement retiré car l'indice Kimovil semble stable.

+ Si l'indice AnTuTu Kimovil ne peut pas être trouvé, l'indice AnTuTu est tout de
  même cherché sur gadgetversus.com à partir du modèle de SoC. Ceci n'est pas
  retiré.

        v1.7.6
Date: 22/12/23 en bêta

———— Audit PC ————

+ Ajout d'un bouton "Copier pour BOLC" qui permet d'avoir le même
  contenue qu'"Extraire pour BOLC", mais directement dans le presse-papier,
  plutôt que dans un fichier CSV.

                           v1.7.3 - v1.7.4 - 1.7.5
Date: 20/12/23 en bêta

———— Audit Android ————
+ Correction d'un bogue pour le matching Kimovil.
+ Annulation de la mise à jour 1.7.2 pour la mise en conformité avec
  la notice v4.0 pour l'audit Android.

        v1.7.2
Date: 18/12/23 en bêta

———— Audit Android ————

+ Mise en conformité avec la Notice v4.0 de catégorisation de Marc Vaneeckhoutte
  https://docs.google.com/document/d/1CxryrAW6knoqd5bN66XtH4hktGkToWQl/

        v1.7.1
Date: 17/12/23 en bêta

———— Général ————

+ Ajout d'une jolie page d'accueil pour plus de simplicité dans l'utilisation.

+ Fusion de l'onglet "Installation en ligne" et "Initialisation PC".

+ Si internet est disponible, le change log est chargée depuis une page distante,
  pour que vous puissiez voir les nouveautés non disponibles sur votre version
  actuelle, autrement, un change log local est chargé à la place.

+ Le change log est maintenant en police à largeur fixe (monospace)

———— Initialisation PC ————

+ Ouvre l'URL d'uBlock Origin sur Edge également, si l'installation de Firefox
  est cochée.

+ Meilleure gestion des erreurs de téléchargements de logiciels.
  La barre de chargement se supprime maintenant correctement en cas d'erreur.

+ Message d'erreur plus clair à la fin de l'installation des apps,
  avec la liste complète des apps non téléchargée s'affichant dans
  une notification qui ne disparaitra pas.

+ Ouvre la page d'accueil des logiciels qui sont en erreurs lors du téléchargement,
  pour procéder à un téléchargement et installation manuel.

———— Audit Android ————

+ Ajout des résultats depuis Kimovil.com !
  Se fait sur une base téléchargée en local, et ne requiert donc pas de connexion
  internet.
  Il est cependant vivement recommandé d'avoir tout de même une connexion internet
  pour que les autres méthodes de recherche en ligne puissent fonctionner dans le
  cas où Kimovil ne retourne pas de résultat.

+ Dans la fenêtre "Observations et pondération", la police utilisée pour plusieurs
  éléments est désormais en police à largeur fixe (monospace) pour une lisibilité
  accrue, notamment pour la lecture du numéro de série, de l'IMEI, etc.

+ Dans la fenêtre "Observations et pondération", un nouveau champ est disponible
  pour l'identifiant Emmaüs (ID), et est éditable.

+ Mise en conformité avec le la Notice v3.0 de catégorisation de Marc Vaneeckhoutte
  https://docs.google.com/document/d/1dydrtJ562luIbs6x1Mix--g_LDH74Rl3

+ Ajout d'une catégorisation forcée en MARAUDE si RAM strictement sous 2Go.

+ Ajout d'une catégorisation forcée en HC si stockage strictement sous 32Go.

+ Ajout d'une catégorisation forcée en C si stockage strictement
  égal 32Go et que la RAM est strictement égale à 2Go.

+ Si un téléphone a une catégorisation forcée, mais qu'il reçoit une pondération,
  alors la catégorisation forcée n'est plus prise en compte, et un message vert
  l'indiquera donc sur l'audit.

+ Correction d'un bogue qui ne prenait pas en compte la catégorie pondérée
  sur le QR Code.

        v1.7.0
Date: 11/12/23 en bêta

———— Général ————

+ Ajout d'un système de mise à jour, un popup apparait au lancement du logiciel
  si une version supérieure est disponible.

+ Si vous êtes sur une bêta, et qu'une version stable est disponible pour la même
  version, un popup apparaîtra également pour vous inviter a plutôt télécharger
  la version stable.

+ Si vous êtes sur une bêta, et qu'une version stable égale où plus avancée est
  disponible, ainsi qu'une version bêta supérieure, alors vous aurez
  la possibilité de télécharger soit (1) la nouvelle version stable,
  soit (2) la nouvelle version bêta.

———— Audit Android ————

+ Adaptation pour la MàJ du BOLC du 19 déc. 2023
  -
  Le format pour l'export vers le BOLC est mise à jour et ajoute
  la colonne "Commentaire statut". Quand vous cliquez sur "Extraire pour le BOLC"
  le fichier CSV alors téléchargé est directement importable dans le
  BOLC vers le début de Janvier 2024.
  -
  Pour les personnes ayants un fichier de suivi personnel, le bouton
  "Copier pour Sheets" permet de sauvegarder plus de données, avec des
  colonnes en plus en fin de tableau, qui n'existent pas dans le BOLC.
  Le début du tableau respecte tout de même le format que le BOLC.
  -
  Le format des colonnes (du bouton "Copie pour Sheets") ajoute donc une
  nouvelle colonne "Commentaire statut".

  Ce nouveau format de colonne est le suivant :

  Id matériel reconditionneur, ID Emmaüs Connect, Type matériel, Catégorie,
  Statut, Commentaire statut, Fabricant, Modèle, Capacité Résiduelle (%),
  Date de vente, Observation, Grade esthétique, IMEI, Processeur (SoC), OS,
  Taille stockage, RAM, Taille Ecran, Resolution, Chargeur, Operateur,
  Couleur, Points (avant pond.), Catégorie (avant pond.), Prix (avant pond.),
  Pondération en %, Points (après pond.), Catégorie (après pond.),
  Prix (après pond.), Date de prise en charge, Numéro de Série, Indice Antutu

+ Dans un soucis de compatibilité avec le BOLC, la colonne
  "Constructeur" (Fabricant) n'ajoutera plus le nom du modèle.
  Par exemple "Xiaomi Redmi" n'affichera plus que "Xiaomi"

+ Ajout des colonnes "Statut matériel PA" et "Commentaire statut"
  dans la fenêtre Observation et pondération

+ Règle un bug avec la batterie résiduelle des Galaxy Tab 5Se affichant 10% au
  lieu de 100%.

        v1.6.10

———— Audit Android ————

+ Fix d'un bug pour la détection du stockage sur les Samsung
  A5 / A6 avec 32 Go.

+ Si le téléphone a < 2 Go de RAM, la catégorie est forcée en HC
  Quelle que soit la pondération choisie.

+ Ajout de l'affichage du stockage de la RAM dans l'écran
  Observations et pondération.

+ La version de l'audit a été ajoutée aux infos de débogage

+ Ajoute le contenu du QR Code en texte sous celui-ci.

+ Quand un audit est fait "manuellement", les sections inutiles
  sont maintenant cachées au lieu d'afficher "KO"

+ Un texte est ajouté en rouge sur l'audit quand il
  est fait manuellement

        v1.6.9

———— Audit Android ————

+ Ajout d'un bouton pour démarrer un audit manuel, ceci
  ne requiert pas de connecter un appareil.
  Vous devrez alors remplir tous les champs manuellement.
  Pratique pour les appareils non supportés par cet outil,
  par exemple, les iPhone, iPad, ...

        v1.6.8

———— Audit Android ————

+ Ajout d'un bouton "Ne pas désactiver le mode développeur"
  sur la page d'Audit Android.
  Ne pas hésiter à cocher la case si besoins de faire
  plusieurs tests d'audit sur le même appareil.

+ Ajout du "product.name" aux résultats de l'audit.
  Permettra à terme de mieux identifier l'appareil.

+ Ajout d'un workaround pour obtenir le vrai nom de modèle
  sur les appareils Xiaomi

        v1.6.7

———— Général ————

+ Ajout de deux liens de téléchargements
  au-dessus du change log, pour télécharger
  respectivement la dernière version stable et bêta.

———— Audit Android ————

+ Si IMEI vide, utilise la valeur du numéro de série à la place
  dans l'extraction BOLC ainsi que Copie pour Suivie.
  Ceci évite un problème avec les appareils n'ayant que le Wi-Fi,
  et par conséquent, n'ayant pas d'IMEI.

+ Ajout de l'IMEI et du SerialNo dans la partie Observations et
  pondération.

+ Les champs Modèle, IMEI et Serial, dans la partie Observations
  et pondération sont maintenant copiables !

        v1.6.6

———— Général ————

+ Création de ce change log :)

+ Version bêta maintenant disponible via l'URL :

           drop.tf/emcotechbeta

———— Windows ————

+ Ajout d'un nouveau script qui ajoute une clé de registre
  pour empêcher la nouvelle barre de recherche Bing
  apparaissant au milieu de l'écran. Ce script est automatiquement
  lancé tant que Win10Debloat est coché.

———— Audit Android ————

+ Si un audit du même nom existe déjà sur le bureau, le fichier
  sera maintenant remplacé, plutôt que d'être ajouté sous le premier document.

+ Ajout d'un bouton 'Copier pour Sheets', qui permet d'ajouter
  au presse-papier en format Sheets/Excel directement.

    Les colonnes sont égales au format du BOLC
    et de nouvelles colonnes sont ajoutées en fin de tableau, pour stocker
    notamment le score AnTuTu, la Pondération, etc

  Le format des colonnes pour le fichier 'Sheets' :

  Id matériel reconditionneur, ID Emmaüs Connect, Type matériel, Catégorie,
  Statut, Fabricant, Modèle, Capacité Résiduelle (%), Date de vente,
  Observation, Grade esthétique, IMEI, Processeur (SoC), OS, Taille stockage,
  RAM, Taille Ecran, Resolution, Chargeur, Operateur, Couleur,
  Points (avant pond.), Catégorie (avant pond.), Prix (avant pond.),
  Pondération en %, Points (après pond.), Catégorie (après pond.),
  Prix (après pond.), Date de prise en charge, Numéro de Série, Indice Antutu

+ Ajout dans l'audit, la version utilisée

+ Ajout d'une apostrophe devant l'IMEI pour éviter
  le formatage automatique d'Excel
  Nécessite de cliquer au moins une fois sur la case sur Excel (?)

+ Si la pondération est de 0%, n'affiche plus
la catégorie 'Après pondération' dans l'audit.

Observations et pondération
    + Ajout du Fabricant et Marque dans un champ
      éditable.

    + Ajout du modèle dans un champ non éditable

    - Si la Marque et le Fabricant sont égaux,
      uniquement le texte de Fabricant sera utilisé,
      au lieu de fusionner les deux, pour les fichers
      subséquents. Cela évite d'avoir
      un fichier HTML qui se nomme Samsung Samsung SM-...

Batterie
    + Correction du texte du statut de la batterie reportant
      BATTERY_STATUS_CHARGING au lieu de BATTERY_HEALTH_GOOD
    + Ajout de la température
    + Ajout de la tension (mV)
    + Ajout de la technologie de la batterie
    + Samsung : Correction pour le % résiduelle
    - Suppression du 'Nombre de cycles' : valeur instable