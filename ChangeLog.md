# CHANGELOG TAB FOR [DOLIBARR ERP CRM](https://www.dolibarr.org)

## 1.0

Initial version

## 2.0 

- Création des templates pour la partie globale 

## 2.5

- Création des menus + pages : 
    ### Onglet - Général
        - Chiffres d'Affaires
        - Encours client et fournisseurs
        - Marge brute N
        - Trésorerie prévisionnelle

    ### Onglet - Encours C/F
        - Encours client 
        - Encours fournisseurs
        - Encours client/fournisseurs
        - Encours client dépassés 
        - Encours fournisseur dépassés
        - Encours client dépassés depuis + de 12 mois

    ### Onglet - Trésorerie 
        - Trésorerie nette
        - Charges totales
        - Encours client à 30 jours

    ### Onglet - Trésorerie 
        - Production les + proches 
        - Montant des productions en cours
        - Nombre des productions en cours
        - Commandes clients validées ce-jour
        - Client à produire
        
## 3.0

- correction des encours (aucune période)

## 3.1 

- correction de la marge brute N

## 3.2 

- Correction de la progression du CA (onglet general - CA boxe)

## 3.3

- Onglet Général terminé : 
    - correction des notes de frais validés (onglet general - boxe tresorerie )
    - correction des factures fournisseurs validées (hors emprunts) (onglet general - boxe tresorerie )
    - correction de l'extrafield pour spécifier si une facture fournisseur est une charge fixe ou non : 
        - Par défaut ce champs n'est plus requis et est toujours sur "Aucune". 
        - Il faut penser à bien définir le type de CF au besoin. 

## 3.4

- Onglet général - boxe trésorerie (charges variables) : 
    - Les notes de frais récupérées sont désormais validés ET approuvés
    - Les factures fournisseurs validées ne compte plus les charges fixes (facture avec l'extrafield - charge)

## 3.5

- Onglet Encours Client et Fournisseur (listes des encours dépassés) : 
    - Amélioration du système de pagination pour les listes des encours dépassés
