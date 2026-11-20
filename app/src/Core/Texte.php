<?php
declare(strict_types=1);

namespace App\Core;

use Normalizer;

/** Assainissement des textes saisis : Unicode (cyrillique compris), caractères invisibles/de contrôle, homoglyphes. */
final class Texte
{
    /**
     * - normalise en NFC (une même lettre = une seule représentation) ;
     * - supprime octets nuls, caractères de contrôle, invisibles (zéro-largeur, trait d'union conditionnel)
     *   et de direction du texte (RLO/LRO/isolats « Trojan Source », catégorie Unicode Cc/Cf/Co/Cn) ;
     * - ramène les fins de ligne à \n et les séparateurs Unicode de ligne/paragraphe à \n (ou espace).
     * L'entrée doit déjà être de l'UTF-8 valide (vérifié en amont par Request::utf8Valide()).
     */
    public static function nettoyer(string $s, bool $multiligne = false): string
    {
        $norm = Normalizer::normalize($s, Normalizer::FORM_C);
        $s = $norm === false ? $s : $norm;
        $s = str_replace(["\r\n", "\r", "\u{2028}", "\u{2029}"], "\n", $s);
        $s = preg_replace($multiligne ? '/[^\P{C}\n\t]/u' : '/\p{C}/u', '', $s) ?? '';
        if (!$multiligne) {
            $s = preg_replace('/\p{Z}+/u', ' ', $s) ?? '';
        }
        return trim($s);
    }

    /** Vrai si une même « suite de lettres » mélange latin et cyrillique/grec (ex. « раypal » : p, a cyrilliques). */
    public static function melangeAlphabets(string $s): bool
    {
        foreach (preg_split('/[^\p{L}\p{M}]+/u', $s, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $mot) {
            if (preg_match('/\p{Latin}/u', $mot) && preg_match('/[\p{Cyrillic}\p{Greek}]/u', $mot)) {
                return true;
            }
        }
        return false;
    }

    /** Vrai si la chaîne ne contient que de l'ASCII imprimable (emails : exclut les homoglyphes cyrilliques). */
    public static function ascii(string $s): bool
    {
        return $s !== '' && preg_match('/^[\x21-\x7E]+$/', $s) === 1;
    }

    /** Échappe %, _ et \ pour un LIKE ... ESCAPE '\\'. */
    public static function echapperLike(string $s): string
    {
        return addcslashes($s, '%_\\');
    }
}
