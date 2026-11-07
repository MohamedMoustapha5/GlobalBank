# Système de Traduction - France/Anglais

## 📋 Vue d'ensemble

Un système de traduction bidirectionnel (FR/EN) a été ajouté au projet UBS. Le sélecteur est visible dans le menu de navigation sous forme de boutons 🇫🇷 FR et 🇬🇧 EN.

## 🚀 Utilisation

### 1. Sur la page `index.php` (déjà intégré)

Le sélecteur de langue est présent dans le menu et le menu mobile. Aucune modification nécessaire.

### 2. Sur les autres pages PHP

Pour ajouter le système de traduction sur d'autres pages, incluez simplement au début :

```php
<?php
require_once 'init_lang.php';
// Reste du code...
?>
```

### 3. Utiliser une traduction dans le code

Utilisez la fonction `trans()` pour récupérer une traduction :

```php
// Exemples :
echo trans('accueil');           // Affiche "Accueil" ou "Home"
echo trans('connexion');         // Affiche "Connexion" ou "Login"
echo trans('mon_compte');        // Affiche "Mon compte" ou "My account"
```

### 4. Accéder à la langue actuelle

```php
$current_lang = $_SESSION['lang']; // 'fr' ou 'en'
```

## 📝 Ajouter une traduction

Pour ajouter de nouvelles entrées, modifiez le fichier `lang.php` :

```php
$lang['fr'] = [
    'ma_cle' => 'Mon texte en français',
    // ... autres traductions
];

$lang['en'] = [
    'ma_cle' => 'My text in English',
    // ... autres traductions
];
```

Puis utilisez dans le code :

```php
echo trans('ma_cle'); // Affiche le texte en français ou anglais
```

## 🎨 Fichiers modifiés/créés

- ✅ `init_lang.php` - Gestion de la langue et des traductions
- ✅ `lang.php` - Dictionnaire de traductions
- ✅ `css/lang-switcher.css` - Style du sélecteur de langue
- ✅ `index.php` - Sélecteur de langue dans le menu

## 🔧 Comment ça marche ?

1. L'utilisateur clique sur 🇫🇷 FR ou 🇬🇧 EN dans le menu
2. La langue est stockée dans `$_SESSION['lang']`
3. Les traductions sont automatiquement chargées via `init_lang.php`
4. La page se recharge avec le nouveau langage

## ⚠️ Notes importantes

- La langue est stockée en session (persiste pendant la visite)
- Par défaut, le français est activé
- Les non-traductions affichent la clé (ex: "ma_cle" si absent)
- Le système est sans risque et n'affecte pas le code existant

## 📋 Traductions disponibles dans `lang.php`

- Navigation (Accueil, Connexion, Mon compte, etc.)
- Admin (Tableau de bord, Virements, etc.)
- Formulaires (Email, Mot de passe, etc.)
- Vérification de statut
- Et plus...

---

**Créé le:** 2026-04-05
**Auteur:** Système de traduction UBS
