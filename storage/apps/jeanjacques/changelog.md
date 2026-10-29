Bonjour à tous,

Suite à des retours, je reviens vers vous concernant la dernière version livrée qui est buggée .

Dans les paramètres, j'ai laissé malencontreusement l'adresse de la plateforme d'intégration.

Vraiment désolé pour ce contretemps, je corrige tout et j'envoie une nouvelle version prope.

-------------------- V5.1.4 - 04 Mai 2026 -----------------------------

[version retirée]

Bonjour,

Suite à la découverte de 2 anomalies dans le processus de reconditionnement, je vous transmets une nouvelle version du package de reconditionnement. V5.1.4

Ces anomalies concernaient :
- Un cas particulier lié à la récupération de l'indice des CPU.
- Un échec de l'installation des 5 applications.

Tout est corrigé et je reste à votre disposition pour tout questionnement.

la nouvelle version est téléchargeable ICI.

Comme d'habitude , n'oubliez pas de renseigner les variable costumer_id et key de votre fichier param.ini

Bonne semaine,

-------------------- V5.1.3 - 27 Mars 2026 -----------------------------

Bonjour à tous,

Il m’a été demandé d’indiquer la catégorie lors de l’affichage du mini-audit.
Avec la nouvelle version V5.1.3, le mini-audit va l’afficher, mais il s’agit d’une valeur brute sans pondération.
Elle est basée sur les données que je récupère uniquement sur le matériel et non celles du Tectech.
Elle tient compte de la pondération automatique qui concernent les PC avec notamment un indice CPU de 1 point.

Le nombre de connexions sur le Tectech étant bridé par mois, je ne peux pas interroger l’application. 

En plus de fournir la catégorie, le mini-audit permet aussi de créer une mini-fiche avec le QR Code pour la mise à jour de Salesforce.
Le menu, comme lors de l’audit, permet de choisir le type de QR Code généré,  suivant que l’on utilise une douchette ou l’application sur un smartphone.
Autres points :

- si le fichier param.ini n'est pas complété des key ? Aucun fichier généré ?
Dans ce cas, le process précise que le traitement Tectech ne pourra pas avoir lieu et propose donc soit de sortir, soit de continuer en précisant que Tectech ne sera pas mise à jour car les fichiers de mise à jour ne seront pas créés, par contre les autres fichiers le seront car étrangers à Tectech .
Rappel, si vous décidez de sortir et de relancer le processus après mise à jour du fichier Param.ini, il est conseillé de valider l’option « réinitialisation de l’identifiant » afin de supprimer les précédents indicateurs stockés dans le répertoire temporaire du PC.

- si le n° du lot n'est pas indiqué en fin d'audit ? Aucun fichier généré ?
La saisie du N° de lot est obligatoire, et n’est pas outre passable.
Si le N° de lot est inconnu de Tectech, un message apparaît pour le signaler, et Tectech ne sera pas mise à jour.
Malgré tout le fichier csv est créé, il suffit de le mettre à jour avec le N° du lot pour faire la mise à jour via l’IHM.

Comme d’habitude, je reste disponible en cas de souci ou de demande d’amélioration. 


-------------------- V5.1.1 - 28 Février 2026 -----------------------------

Bonjour à tous,

Suite aux remontées  diverses et variées , j'ai modifié le package pour tenter de répondre aux problématiques posées par ce nouveau SI.

La nouvelle version V.5.1.1 est disponible ICI

J'ai corrigé un peu la disposition, réglé une problématique concernant la détection des disques de type NVME  et ajouté des transferts de données supplémentaires vers le Tectech.

Enfin , dans le cas où le numéro de série est présent plusieurs, je propose de choisir le matériel qui va t'être mis à jour.

Sinon, pour rappel:
Si un PC a été intégré dans le Bolc avant son reconditionnment .Donc sans numéro de série (cf maj-bolc) et normalement dans le Tectech.
Par exemple: BXPC25-0700.

Et si ce même  PC était aussi connu  dans les tables matériel ou commande .
par exemple avec l'identifiant du reconditionneur pro = EM_123_456.

Lors de la mise à jour dans tectech , c'est l'article qui match avec le N° de série qui va être mis à jour
En l'espèce ici : EM_123_456.

Il faudra donc supprimer le matériel avec l id ESN =BXPC25-0700 et mettre à jour le matériel  pro = EM_123_456 pour lui indiquer que son 
ID ESN est :  BXPC25-0700 

Sinon, n'oubliez pas de mettre à jour le fichier param.ini.

Je reste, comme d'habitude à votre disposition.

Bonne fin de weekend,


-------------------- V5.1.0 - 21 Février 2026 -----------------------------

Bonsoir à tous,

Suite à des remontées  d'anomalies diverses et variées  dans la version V5.0.9 , de demandes de certains ESN et de la découverte des spécificités  du SI de Tectech, je vous mets à disposition une nouvelle version V5.1.0.
Téléchargeable ICI
J'avance, dans mon développement, au même rythme de l'application Tectech. en production.
Autre contrainte dans le cadre de mon développement, il est, pour le moment, impossible de supprimer des items sans passer par un salon aussi bien en prod qu'en intégration.

Je reste comme toujours à disposition.

Bon weekend,

-------------------- V5.0.9 - 15 Février 2026 -----------------------------

Bonjour à tous

Comme convenu, j'ai déposé sur le drive la dernière version  V5.0.9, téléchargeable ICI 

Suite à une incompréhension les versions précédentes effaçaient les données de la base en cas de mise à jour.

Et grâce aux remontées de Philippe P. et de Jean-Sébastien , j'ai modifié le processus pour tenir compte de la possibilité de ne pas re-numéroter les PC en provenance des reconditionneurs pros. 

Désolé pour ces contre-temps , mais j'ai dû également interrompre mon développement en l'absence d'électricité suite au passage de la tempête Nils. 

Je vous joint également une mini-notice qui explique les principes généraux du nouveau package.

Comme à chaque livraison, il faudra que vous mettiez à jour le customer id et sa clé dans le fichier param.

Je reste , comme d'habitude à votre disposition pour répondre à vos remontées et questionnements.

Bonne fin de week-end,

Notice : https://cloud.drop.tf/s/TyFn6Ta6krnibc3


-------------------- V5.0.4 - 30 Janvier 2026 -----------------------------

Version uniquement pour TecTech.

-------------------- V4.8.5 - 13 Décembre 2025 -----------------------------

Je viens de corriger l'anomalie générée par la mise en exploitation du dernier patch tuesday de Microsoft.

Je pense que grâce à cette correction, le problème doit être résolu.

J'ai apporté une correction, mais n'étant pas chez moi, je n'ai pas pu tester.

-------------------- V4.8.1 - 2 Juillet 2025 -----------------------------

Suite à des remontées d’anomalies de la part de Jean-Sébastien, Charles et Marc C., je reviens vers vous avec une nouvelle version du package de reconditionnement, la V4.8.1.
Les anomalies concernaient :
La liaison entre les menus.
L’accès aux sites des constructeurs pour mettre à jour les bios et les drivers.
La pondération automatique concernant les PC avec un cpu d’un point avec un disque de type SSD

-------------------- V4.8.0 - 27 Mai 2025 --------------------------------

La possibilité de choisir d’utiliser la nouvelle ou l’ancienne catégorisation.

La possibilité de saisir le nom commercial du modèle à la place de la référence interne, notamment pour les PC Lenovo ou Asus. C’est ce nom qui va apparaître sur la mini-fiche.

La possibilité de générer le certificat d'achat/garanti avec les caractéristiques du PC, utile lors des ventes à l’extérieur.

La possibilité de choisir le type de QR Code qui va être généré sur la mini-fiche. Soit pour utilisation avec une douchette, soit pour utilisation avec l’application Salesforce sur un smartphone.

La correction d'une anomalie dans le cadre de la nouvelle référence du type de processeur (ex : 11eme gen), permet de pouvoir récupérer l’indice depuis le site de benchmark.  

Un raccourci donnant des informations sur le contrôle parental est maintenant présent sur le bureau.

La sauvegarde des dossiers est directement envoyée sur le site d'Emmaüs Connect qui est accessible à l'adresse : https://audits.emmaus-connect.org

Pour les ESN qui ont participé à la phase de tests du dernier package V4.7.8, celui-ci a évolué et il faut donc télécharger la dernière version V4.8.0.

Désolé pour le temps de téléchargement mais le package ne contient pas que l’audit, mais toute une boîte à outils qui permet de jouer toutes les phases du reconditionnement (Tests, accès au site des constructeurs, enregistrements des informations, installations automatiques des applis, nettoyage des PC…)

Cette nouvelle version est téléchargeable avec ce lien

Je reste à votre disposition pour tout renseignement et n’hésitez pas à me remonter toutes anomalies ou erreurs constatées.