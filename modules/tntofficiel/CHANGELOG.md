




# CHANGELOG




## 1.0.1 le 2018-11-20

- `#6660` Suppression des surcharges et sélection du contexte pour une commande TNT (BOOM-4050 et BOOM-5821).
- `#6737` Affichage du 1er numéro de suivi dans le champ "Numéro de suivi" et dans une nouvelle colonne dans la liste des colis (BO).
- `#6738` Amélioration de la gestion du cache (erreurs si le cache est mal installé comme l'extension APCu).
- `#6700` Ajout d'une constante pour le statut permettant de déclencher la création d'expédition.
- `#6739` Désactivation des checkbox pour la sélection des boutiques dans les transporteurs TNT de Prestashop.




## 1.0.2 le 2018-11-21

- `#6874` Poids maximum d'expédition pour DEPOT à 30kg pour ajouter un colis.
- `#6876` :
  - Prévenir les exceptions de BDD si les tables ne sont pas créées à l'installation.
  - Correction du sélecteur pour le format des BT (inversion avec et sans logo).
  - Le champ expéditeur société est limité à 32 caractères au lieu de 128 dans la configuration.
  - SOAP/cURL : Vérification du certificat serveur via le fichier cacert.pem de Prestashop.
- `#6892` MAJ de la documentation et amélioration de la compatibilité pour Prestashop 1.7.5.0.




## 1.0.3 le 2019-01-10

- `#6073` Actualisation automatique du lien de la preuve de livraison sur le détail d'une commande.
- `#5984` Actualisation automatique du statut de suivis des colis en back-office (nouvelle colonne sur la liste des colis).
- `#5984` Actualisation automatique du statut livré de la commande sur la page de commande et via action groupée sur la liste.
- `#6898` Statuts de commande optionnels pour la création d'expédition et la livraison des colis (via constantes).
- `#6913` Ajouter un lien de suivi de l'ensemble des colis pour une commande, disponible pour l'envoi de l'email dès le statut expédiée.
  - {tntofficiel_tracking_url_text} et {tntofficiel_tracking_url_html}
- `#6958` :
  - Amélioration de la création des transporteurs, pour les langues installées sur la boutique (champs delay).
  - Fix sur la méthode isPaymentReady (une erreur de typage).
  - Fix AdminCarrierSettingController->viewAccess (signature incomplète et méthode inutilisée) (compatibilité PHP7.2).
  - Fix Restauration du timeout de connection de sockets SOAP.
- `#7334` Configuration tarifaire : Impossible d'ajouter plus de 10 tranches. Le maximum est maintenant de 128 tranches.
- `#7335` Configuration tarifaire : Aide à la saisie avec auto-correction des virgules (,) en point (.), vérification directe du format des nombres et arrondis automatique.
- Ajout de l'Offre Essentiel.
- Suppression des fonts icon inutilisée.
- Prévenir les conflits PHP pour les pdf.




## 1.0.4 le 2019-05-27

- `#7493` Correction sans affichage de l'erreur si la date de ramassage est invalide (Action groupée, détail d'un commande).
- `#7498` Amélioration du pdf manifeste.




## 1.0.5 le 2019-06-20

- `#7587` Amélioration de la protection contre le downgrade.
- `#7425` Amélioration du suivi des colis.
- `#7631` Amélioration du suivi des colis (suite).
- `#7592` Mise en place des services P* (18h).




## 1.0.6 le 2019-11-27

- `#7653` Amélioration de la compatibilité avec les thèmes.
- `#7699` Fix : Pas de transporteurs affichés pour la destination 78882 ST QUENTIN EN YVELINES CEDEX.
- `#7650` TNECO-117 - BO - Actualisation du couple CP/Ville
- `#7842` Trim sur le code postal, ville, nom de société, etc.
- `#7863` Suppression des avertissements avec chmod.
- `#7864` Fix CSS sur les informations complémentaires en BO.
- `#7865` Corrections des appels avec addJS et addCSS.
- `#8136` MAJ URL ZDA et prise en charge de l'absence du certificat Prestashop pour les requêtes cURL.
- `#8127` Ajout des services ASSU et RP ASSU.




## 1.0.7 le 2019-12-23

- `#8197` Week-end du calendrier non sélectionnable pour la date de ramassage.
- `#8390` Prise en compte de la livraison offerte globalement à partir de la configuration Prestashop.
- `#8350` [EVOL] Optimisation avec cache+ttl en BDD pour les appels au Webservice.
- `#8394` Prévenir les appels inutiles au webservice le week-end ou si la dates est passées (pickupdate, shippingdate).
- `#8395` Gestion des dépendances d'appels au webservice lors d'erreurs de communication.
- `#8396` Optimisation des timeouts des requêtes pour limiter l'effet gel si le webservice est en surcharge.
  - 'Error Fetching http headers', 'Service Temporarily Unavailable', 'Internal Server Error'.




## 1.0.8 le 2020-03-26

- `#8479` Message d'information sur les commandes associées à un transporteur qui n'est plus disponible sur le compte.
- `#8480` Ne pas journaliser d'erreurs si un autre module interroge le hook pour les variables d'e-mails avec une commande non TNT.
- `#8481` Correction de la date de ramassage par défaut le wek-end pour le ramassage régulier.
- `#8570` Vérou Exclusif pour la journalisation.
- `#8389` Limiter la journalisation en taille occupée (64Mib par dossier).
- `#8401` [EVOL] Prix et date prévisionnelle de livraison en fonction du point de livraison.
- `#8630` Ajout et Edition d'adresse en BO : Rendre accessible la vérification du code postal ville en back-office lorsque la maintenance est activée.
- `#8638` Optimisation pour la montée en charge.
- `#8639` Optimisation Mémoïsation getPSCart.
- `#7866` Détail de commande en BO : Popin de correction de la ville et avertissement sur la compatibilité B2B/B2C du transporteur avec l'adresse.




## 1.0.9 le 2020-06-11

- `#8763` Mise en cache de la faisabilité les jours fériés.
- `#8781` Refactoring de la gestion des exceptions.
- `#8782` Exclure certaines mise en cache possible mais inutile.
- `#8783` Ne pas faire de vérification directe du code postal/ville sur le formulaire d'édition/ajout d'adresse dans le tunnel de commande.
- `#8402` [EVOL] Confirmation de sauvegarde de la configuration du compte sur les boutiques d'un groupe.




## 1.0.10 le 2020-10-12

- `#8970` Compatibilité de la librairie PDF avec les futures versions de PHP 7.
- `#9049` Correctif de la date de fermeture >= 15h.
- `#9069` Style et largeur de champs dans la configuration du compte (small screen)
- `#9068` Fix: Vérifier la validité des objets avant une propriété.
- Journalisation de la désinstallation dans une méthode séparée.
- Gel du statut des colis 2 jours après livraison.
- `#8403` [EVOL] Sélection d'un statut déclenchant la création d'expédition et d'un second optionnel à appliquer ensuite.
- `#8407` [EVOL] Actualisation automatique du statut de livraison des colis.
- `#8818` [EVOL] Gestion des statuts de livraison des colis.
- `#8405` [EVOL] Rappel du taux de TVA.
- `#8406` [EVOL] Rappel des départements concernés dans la zone tarifaire 1 ou 2.
- `#8404` [EVOL] Assurance valeur déclarée.
- `#9100` Prévenir la mise en cache si plus de 65535 octets.
- `#9095` v1.0.10 validator.
- `#9110` Installation : Message d'erreur explicite indiquant la version minimum requise de Prestashop.
- `#9112` Fix variable globale smarty $link.




## 1.0.11 le 2020-11-09

- `#9113` Supporter plus de 10 marqueurs pour la carte des points de livraison.
- `#9206` Validateur 1.0.11




## 1.0.12 le 2021-06-01

- `#9042` BO - Compatibilité des commandes en back-office sous PS1.7.7.0 (hook ActionGetAdminOrderButtons, displayAdminOrderSide, js, css).
- `#9228` FO - Fix validation des informations complémentaires.
- `#9236` FO - Fix affichage des informations complémentaires.
- `#9238` BO - Fix nouveau lien sur la liste des commandes TNT.
- `#9237` BO - Fix action groupée pour modifier le statut en simple boutique.
- `#9240` BO - Fix Validation directe du code postal et de la ville sur le détail d'une commande.
- `#9239` BO - Erreur de validation du code postal/villle sur le nouveau formulaire d'adresse.
- `#9501` Exception MySQL possible lors de la recherche si aucune adresse pour un client.
- `#9529` Deprecated : FrontController addJS(), addCSS()
- `#9568` Journalisation de la suppression des transporteurs TNT inutilisés.
- `#8847` Message remplaçant l'erreur account number is not registered.
- `#9231` Validation Pestashop 1.7.7.0
- `#9661` Fonctions serialize/unserialize interdite.
- `#9662` PHP sans HTML (use smarty).
- `#9614` isContextReady avec vérification d'installation des tables.
